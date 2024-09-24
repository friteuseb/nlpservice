from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity, euclidean_distances, manhattan_distances
import numpy as np
from nltk.translate.bleu_score import sentence_bleu
from nltk.tokenize import word_tokenize

class SimilarityCalculator:
    def __init__(self):
        self.vectorizer = TfidfVectorizer()

    def calculate_similarity(self, text1, text2, method='cosine'):
        if method == 'cosine':
            return self._cosine_similarity(text1, text2)
        elif method == 'euclidean':
            return self._euclidean_similarity(text1, text2)
        elif method == 'manhattan':
            return self._manhattan_similarity(text1, text2)
        elif method == 'jaccard':
            return self._jaccard_similarity(text1, text2)
        elif method == 'bleu':
            return self._bleu_similarity(text1, text2)
        else:
            raise ValueError("Invalid similarity method")

    def _cosine_similarity(self, text1, text2):
        tfidf_matrix = self.vectorizer.fit_transform([text1, text2])
        return float(cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:2])[0][0])

    def _euclidean_similarity(self, text1, text2):
        tfidf_matrix = self.vectorizer.fit_transform([text1, text2])
        distance = euclidean_distances(tfidf_matrix[0:1], tfidf_matrix[1:2])[0][0]
        return 1 / (1 + distance)  # Convert distance to similarity

    def _manhattan_similarity(self, text1, text2):
        tfidf_matrix = self.vectorizer.fit_transform([text1, text2])
        distance = manhattan_distances(tfidf_matrix[0:1], tfidf_matrix[1:2])[0][0]
        return 1 / (1 + distance)  # Convert distance to similarity

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