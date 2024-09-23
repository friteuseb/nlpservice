import os
from flask import Flask, request, jsonify
from flask_cors import CORS
import importlib
import logging

# Configuration du logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

def create_dev_app():
    app = Flask(__name__)
    CORS(app)

    # Tentative d'importation des fonctions d'analyse réelles
    try:
        nlp_module = importlib.import_module('app.nlp.analyzer')
        NLPAnalyzer = getattr(nlp_module, 'NLPAnalyzer')
        
        try:
            nlp_analyzer = NLPAnalyzer()
            logger.info("Utilisation des fonctions d'analyse réelles avec le modèle complet")
        except OSError:
            logger.warning("Modèle fr_core_news_lg non trouvé, tentative d'utilisation d'un modèle plus léger")
            class LightNLPAnalyzer(NLPAnalyzer):
                def load_resources(self):
                    import spacy
                    try:
                        self.nlp = spacy.load("fr_core_news_sm")
                    except OSError:
                        logger.warning("Aucun modèle spaCy trouvé. Utilisation d'un pipeline vide.")
                        self.nlp = spacy.blank("fr")
                    # Autres initialisations si nécessaire
            
            nlp_analyzer = LightNLPAnalyzer()
            logger.info("Utilisation des fonctions d'analyse réelles avec un modèle léger ou vide")
        
    except ImportError as e:
        logger.warning(f"Module d'analyse non trouvé: {e}, utilisation des fonctions de test")
        
        class MockNLPAnalyzer:
            def analyze_text(self, text):
                return {"result": f"Analyse simulée de : {text[:50]}..."}
            
            def calculate_similarity(self, text1, text2):
                return {"similarity": 0.85}
        
        nlp_analyzer = MockNLPAnalyzer()

    @app.route('/')
    def home():
        return "NLP Service is running. Use /api/analyze or /api/similarity for analysis."

    @app.route('/api/analyze', methods=['POST'])
    def analyze():
        data = request.json
        if not data or 'content' not in data:
            return jsonify({"error": "No text provided for analysis"}), 400
        
        text = data['content']
        result = nlp_analyzer.analyze_text(text)
        return jsonify(result)

    @app.route('/api/similarity', methods=['POST'])
    def similarity():
        data = request.json
        if not data or 'text1' not in data or 'text2' not in data:
            return jsonify({"error": "Both texts are required for similarity calculation"}), 400
        
        text1 = data['text1']
        text2 = data['text2']
        result = nlp_analyzer.calculate_similarity(text1, text2)
        return jsonify(result)

    return app

if __name__ == '__main__':
    app = create_dev_app()
    port = int(os.environ.get('PORT', 5000))
    app.run(debug=True, host='0.0.0.0', port=port)
