import nltk
from nltk.tokenize import word_tokenize, sent_tokenize
from nltk.corpus import stopwords
from nltk.util import ngrams
from collections import Counter
import logging
import spacy
from transformers import pipeline
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import numpy as np


class NLPAnalyzer:
    def __init__(self):
        self.setup_logging()
        self.load_resources()

    def setup_logging(self):
        logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
        self.logger = logging.getLogger(__name__)

    def load_resources(self):
        try:
            nltk.download('punkt', quiet=True)
            nltk.download('stopwords', quiet=True)
            self.nlp = spacy.load("fr_core_news_lg")
            self.sentiment_analyzer = pipeline("sentiment-analysis", model="nlptown/bert-base-multilingual-uncased-sentiment")
            self.stop_words = set(stopwords.words('french'))
            self.SPACY_AVAILABLE = True
            self.TRANSFORMERS_AVAILABLE = True
        except ImportError as e:
            self.logger.warning(f"Impossible de charger certaines ressources: {str(e)}")
            self.SPACY_AVAILABLE = False
            self.TRANSFORMERS_AVAILABLE = False

    def analyze_text(self, text):
        self.logger.debug(f"Starting analysis of text: {text[:50]}...")
        self.logger.info(f"Starting analysis of text: {text[:50]}...")
        
        result = {
            "sentiment": self.analyze_sentiment(text),
            "keyphrases": self.extract_keyphrases(text),
            "category": self.categorize_text(text),
            "named_entities": self.extract_named_entities(text),
            "readability_score": self.calculate_readability_score(text),
            "word_count": len(word_tokenize(text, language='french')),
            "sentence_count": len(sent_tokenize(text, language='french')),
            "language": "fr",
            "lexical_diversity": self.calculate_lexical_diversity(text),
            "top_n_grams": self.extract_top_n_grams(text),
            "semantic_coherence": self.calculate_semantic_coherence(text),
            "sentiment_distribution": self.analyze_sentiment_distribution(text)
        }
        
        result["average_sentence_length"] = result["word_count"] / result["sentence_count"] if result["sentence_count"] > 0 else 0

        self.logger.info("Analysis completed successfully.")
        self.logger.debug("Analysis completed")
        return result

    def analyze_sentiment(self, text):
        if self.TRANSFORMERS_AVAILABLE:
            result = self.sentiment_analyzer(text[:512])[0]
            return result['label']
        else:
            positive_words = set(['bon', 'excellent', 'super', 'génial', 'heureux'])
            negative_words = set(['mauvais', 'terrible', 'horrible', 'triste', 'déçu'])
            words = word_tokenize(text.lower(), language='french')
            sentiment_score = sum(1 for word in words if word in positive_words) - sum(1 for word in words if word in negative_words)
            if sentiment_score > 0:
                return "POSITIVE"
            elif sentiment_score < 0:
                return "NEGATIVE"
            else:
                return "NEUTRAL"

    def extract_keyphrases(self, text):
        words = word_tokenize(text.lower(), language='french')
        stop_words = set(stopwords.words('french'))
        keywords = [word for word in words if word.isalnum() and word not in stop_words]
        return list(set(keywords))[:5]

    def categorize_text(self, text):
        categories = {
            "technologie": ["ordinateur", "internet", "logiciel", "application"],
            "santé": ["médecin", "maladie", "traitement", "hôpital"],
            "politique": ["gouvernement", "élection", "parti", "loi"],
            "environnement": ["climat", "pollution", "recyclage", "écologie"]
        }
        words = word_tokenize(text.lower(), language='french')
        category_scores = {cat: sum(1 for word in words if word in keywords) for cat, keywords in categories.items()}
        if max(category_scores.values()) > 0:
            return max(category_scores, key=category_scores.get)
        return "Non catégorisé"

    def extract_named_entities(self, text):
        if self.SPACY_AVAILABLE:
            doc = self.nlp(text)
            return [{"text": ent.text, "type": ent.label_} for ent in doc.ents]
        else:
            return []

    def calculate_readability_score(self, text):
        sentences = sent_tokenize(text, language='french')
        words = word_tokenize(text, language='french')
        if not sentences or not words:
            return 0
        avg_sentence_length = len(words) / len(sentences)
        readability_score = 206.835 - (1.015 * avg_sentence_length) - (84.6 * (len(list(filter(lambda w: len(w) > 1, words))) / len(words)))
        return max(0, min(100, readability_score))

    def calculate_lexical_diversity(self, text):
        words = word_tokenize(text.lower(), language='french')
        return len(set(words)) / len(words) if words else 0

    def extract_top_n_grams(self, text, n=2, top=5):
        words = word_tokenize(text.lower(), language='french')
        n_grams = list(ngrams(words, n))
        return Counter(n_grams).most_common(top)

    def calculate_semantic_coherence(self, text):
        sentences = sent_tokenize(text, language='french')
        if len(sentences) < 2:
            return 1.0
        coherence_scores = []
        for i in range(len(sentences) - 1):
            common_words = set(word_tokenize(sentences[i].lower(), language='french')) & \
                           set(word_tokenize(sentences[i+1].lower(), language='french'))
            coherence_scores.append(len(common_words) / max(len(word_tokenize(sentences[i], language='french')),
                                                            len(word_tokenize(sentences[i+1], language='french'))))
        return sum(coherence_scores) / len(coherence_scores)

    def analyze_sentiment_distribution(self, text):
        sentences = sent_tokenize(text, language='french')
        sentiments = [self.analyze_sentiment(sentence) for sentence in sentences]
        return {
            "POSITIVE": sentiments.count("POSITIVE") / len(sentiments),
            "NEGATIVE": sentiments.count("NEGATIVE") / len(sentiments),
            "NEUTRAL": sentiments.count("NEUTRAL") / len(sentiments)
        }

    def calculate_similarity(self, text1, text2):
        self.logger.debug("Starting similarity calculation")
        try:
            vectorizer = TfidfVectorizer()
            tfidf_matrix = vectorizer.fit_transform([text1, text2])
            similarity = cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:2])[0][0]
            self.logger.info(f"Similarity calculated: {similarity}")
            return {"similarity": similarity}
        except Exception as e:
            self.logger.error(f"Error in similarity calculation: {str(e)}")
            raise
