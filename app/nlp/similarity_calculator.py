from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity, euclidean_distances, manhattan_distances
import numpy as np
from nltk.translate.bleu_score import sentence_bleu
from nltk.tokenize import word_tokenize

class SimilarityCalculator:
    def __init__(self):
        self.vectorizer = TfidfVectorizer()
        self.fitted = False

    def calculate_similarity(self, text1, text2, method='cosine'):
        if method == 'cosine':
            return self._tfidf_similarity(text1, text2, cosine_similarity)
        elif method == 'euclidean':
            return self._tfidf_similarity(text1, text2, euclidean_distances, inverse=True)
        elif method == 'manhattan':
            return self._tfidf_similarity(text1, text2, manhattan_distances, inverse=True)
        elif method == 'jaccard':
            return self._jaccard_similarity(text1, text2)
        elif method == 'bleu':
            return self._bleu_similarity(text1, text2)
        else:
            raise ValueError("Invalid similarity method")

    def _tfidf_similarity(self, text1, text2, metric_func, inverse=False):
        texts = [text1, text2]
        if not self.fitted:
            self.vectorizer.fit(texts)
            self.fitted = True
        tfidf_matrix = self.vectorizer.transform(texts)
        similarity = metric_func(tfidf_matrix[0:1], tfidf_matrix[1:2])[0][0]
        if inverse:
            return 1 / (1 + similarity)
        return float(similarity)

    def _jaccard_similarity(self, text1, text2):
        set1 = set(text1.split())
        set2 = set(text2.split())
        intersection = len(set1.intersection(set2))
        union = len(set1.union(set2))
        return intersection / union if union != 0 else 0

    def _bleu_similarity(self, text1, text2):
        reference = [word_tokenize(text1)]
        candidate = word_tokenize(text2)
        return sentence_bleu(reference, candidate)

    def calculate_batch_similarity(self, text_pairs, method='cosine'):
        all_texts = [text for pair in text_pairs for text in pair.values()]
        
        if not self.fitted or len(self.vectorizer.vocabulary_) < len(set(" ".join(all_texts).split())):
            self.vectorizer.fit(all_texts)
            self.fitted = True
        
        tfidf_matrix = self.vectorizer.transform(all_texts)
        
        results = []
        for i in range(0, len(all_texts), 2):
            if method in ['cosine', 'euclidean', 'manhattan']:
                if method == 'cosine':
                    similarity = cosine_similarity(tfidf_matrix[i:i+1], tfidf_matrix[i+1:i+2])[0][0]
                elif method == 'euclidean':
                    similarity = 1 / (1 + euclidean_distances(tfidf_matrix[i:i+1], tfidf_matrix[i+1:i+2])[0][0])
                elif method == 'manhattan':
                    similarity = 1 / (1 + manhattan_distances(tfidf_matrix[i:i+1], tfidf_matrix[i+1:i+2])[0][0])
            elif method == 'jaccard':
                similarity = self._jaccard_similarity(all_texts[i], all_texts[i+1])
            elif method == 'bleu':
                similarity = self._bleu_similarity(all_texts[i], all_texts[i+1])
            else:
                raise ValueError(f"Méthode de similarité non supportée: {method}")
            
            results.append({"method": method, "similarity": float(similarity)})
        
        return results