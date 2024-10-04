import os
import json
import numpy as np
import faiss
from sentence_transformers import SentenceTransformer
import pickle
from flask import current_app

class FAISSSimilarity:
    def __init__(self, app=None):
        self.app = app
        self.dimension = 384  # Assurez-vous que cette dimension est correcte
        if app is not None:
            self.init_app(app)

    def init_app(self, app):
        self.app = app
        self.FAISS_INDEX_PATH = os.path.join(app.root_path, '..', 'faiss_index.pkl')
        self.TEXTS_PATH = os.path.join(app.root_path, '..', 'texts.json')
        self.MODEL_NAME = 'paraphrase-multilingual-MiniLM-L12-v2'
        self.model = SentenceTransformer(self.MODEL_NAME)
        
        self._load_or_create_index()

    def _load_or_create_index(self):
        try:
            if os.path.exists(self.FAISS_INDEX_PATH) and os.path.exists(self.TEXTS_PATH):
                self.app.logger.info(f"Chargement de l'index existant depuis {self.FAISS_INDEX_PATH}")
                self.index = faiss.read_index(self.FAISS_INDEX_PATH)
                with open(self.TEXTS_PATH, 'r') as f:
                    self.texts = json.load(f)
            else:
                self.app.logger.info("Création d'un nouvel index FAISS")
                base_index = faiss.IndexFlatL2(self.dimension)
                self.index = faiss.IndexIDMap(base_index)
                self.texts = {}
        except Exception as e:
            self.app.logger.error(f"Erreur lors du chargement/création de l'index FAISS : {str(e)}")
            raise

    def _save_state(self):
        try:
            self.app.logger.info(f"Tentative de sauvegarde de l'index dans {self.FAISS_INDEX_PATH}")
            faiss.write_index(self.index, self.FAISS_INDEX_PATH)
            self.app.logger.info("Index FAISS sauvegardé avec succès")

            self.app.logger.info(f"Tentative de sauvegarde des textes dans {self.TEXTS_PATH}")
            with open(self.TEXTS_PATH, 'w') as f:
                json.dump(self.texts, f)
            self.app.logger.info("Textes sauvegardés avec succès")
        except Exception as e:
            self.app.logger.error(f"Erreur lors de la sauvegarde de l'état FAISS: {str(e)}", exc_info=True)
            raise

    def clear_index(self):
        try:
            self.app.logger.info("Début du nettoyage de l'index FAISS")
            base_index = faiss.IndexFlatL2(self.dimension)
            self.index = faiss.IndexIDMap(base_index)
            self.texts = {}
            self.app.logger.info("Nouvel index FAISS créé")
            
            self.app.logger.info("Tentative de sauvegarde de l'état")
            self._save_state()
            self.app.logger.info("État sauvegardé avec succès")
            
            return True
        except Exception as e:
            self.app.logger.error(f"Erreur lors du nettoyage de l'index FAISS: {str(e)}", exc_info=True)
            raise

    def add_texts(self, items):
        try:
            self.reset_index_if_inconsistent()
            
            texts = [item['text'] for item in items]
            self.app.logger.debug(f"Textes extraits : {texts[:50]}...")

            embeddings = self.model.encode(texts)
            self.app.logger.info(f"Encodage terminé. Shape des embeddings : {embeddings.shape}")

            ids = np.array([int(item['id']) for item in items], dtype=np.int64)
            self.app.logger.info(f"IDs générés : {ids}")

            self.index.add_with_ids(embeddings, ids)
            self.app.logger.info("Embeddings ajoutés avec succès")

            for item in items:
                self.texts[item['id']] = item['text']
            self.app.logger.info("Textes ajoutés au dictionnaire")

            self._save_state()
            self.app.logger.info("État sauvegardé avec succès")

            return len(items)
        except Exception as e:
            self.app.logger.error(f"Erreur dans add_texts: {str(e)}", exc_info=True)
            raise

    def find_similar(self, item_id, k=5):
        try:
            self.app.logger.info(f"Début de find_similar pour l'ID: {item_id}")
            
            if self.index.ntotal == 0:
                self.app.logger.warning("L'index est vide")
                return []
            
            if item_id not in self.texts:
                self.app.logger.warning(f"ID {item_id} non trouvé dans les textes")
                return []
            
            query_embedding = self.model.encode([self.texts[item_id]])[0]
            D, I = self.index.search(np.array([query_embedding]), k)
            
            results = []
            for i, idx in enumerate(I[0]):
                text_id = str(idx)
                if text_id in self.texts:
                    similarity = 1 / (1 + D[0][i])
                    results.append({
                        "id": text_id,
                        "text": self.texts[text_id],
                        "similarity": float(similarity)
                    })
            
            self.app.logger.info(f"Résultats de la recherche: {results}")
            return results
        except Exception as e:
            self.app.logger.error(f"Erreur dans find_similar: {str(e)}", exc_info=True)
            raise

    def check_consistency(self):
        faiss_size = self.index.ntotal
        texts_size = len(self.texts)
        if faiss_size != texts_size:
            self.app.logger.warning(f"Incohérence détectée : FAISS index size = {faiss_size}, texts dict size = {texts_size}")
            return False
        return True

    def reset_index_if_inconsistent(self):
        if not self.check_consistency():
            self.app.logger.warning("Réinitialisation de l'index due à une incohérence")
            self.clear_index()
            return True
        return False

    def get_status(self):
        try:
            return {
                "num_texts": self.index.ntotal if hasattr(self.index, 'ntotal') else 0,
                "index_size": self.index.ntotal * self.dimension if hasattr(self.index, 'ntotal') else 0
            }
        except Exception as e:
            self.app.logger.error(f"Error in get_status: {str(e)}", exc_info=True)
            raise