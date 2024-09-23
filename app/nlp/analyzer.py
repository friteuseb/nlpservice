import nltk
from nltk.tokenize import word_tokenize, sent_tokenize
from nltk.corpus import stopwords
from nltk import word_tokenize, ngrams
from collections import Counter
import logging
import spacy
from transformers import pipeline
import matplotlib.pyplot as plt
import io
import base64
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.cluster import KMeans
from sklearn.decomposition import LatentDirichletAllocation
import numpy as np

class NLPAnalyzer:
    def __init__(self):
        self.setup_logging()
        self.load_resources()  


    def setup_logging(self):
        logging.basicConfig(level=logging.DEBUG, format='%(asctime)s - %(levelname)s - %(message)s')
        self.logger = logging.getLogger(__name__)

    def load_resources(self):
        self.SPACY_AVAILABLE = False
        self.TRANSFORMERS_AVAILABLE = False
        self.nlp = None
        
        try:
            nltk.download('punkt', quiet=True)
            nltk.download('stopwords', quiet=True)
            self.stop_words = set(stopwords.words('french'))
            self.logger.info("NLTK resources loaded successfully")           

            try:
                self.nlp = spacy.load("fr_core_news_md")  # ou "fr_core_news_sm" ou lg
                self.SPACY_AVAILABLE = True
                self.logger.info("spaCy model loaded successfully")
            except Exception as e:
                self.logger.warning(f"Failed to load spaCy model: {str(e)}")
            
            try:
                self.sentiment_analyzer = pipeline("sentiment-analysis", model="nlptown/bert-base-multilingual-uncased-sentiment")
                self.emotion_analyzer = pipeline("text-classification", model="bhadresh-savani/distilbert-base-uncased-emotion")
                self.TRANSFORMERS_AVAILABLE = True
                self.logger.info("Transformer models loaded successfully")
            except Exception as e:
                self.logger.warning(f"Failed to load transformer models: {str(e)}")
            
            if self.SPACY_AVAILABLE and self.TRANSFORMERS_AVAILABLE:
                self.logger.info("All resources loaded successfully")
            else:
                self.logger.warning("Some resources failed to load. Functionality may be limited.")
        
        except Exception as e:
            self.logger.error(f"Critical error loading resources: {str(e)}")


    def analyze_text(self, text, generate_sentiment_graph=False):
        self.logger.debug(f"Starting analysis of text: {text[:50]}... Generate graph: {generate_sentiment_graph}")
        
        try:
            result = {
                "sentiment_analysis": self.analyze_sentiment(text, generate_graph=generate_sentiment_graph),
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
            }
            
            result["average_sentence_length"] = result["word_count"] / result["sentence_count"] if result["sentence_count"] > 0 else 0
            
            self.logger.info("Analysis completed successfully.")
            return result
        except Exception as e:
            self.logger.error(f"Error during text analysis: {str(e)}")
            raise

    def analyze_sentiment(self, text, generate_graph=False):
        if not self.SPACY_AVAILABLE:
            self.logger.error("spaCy model is not available. Cannot perform sentiment analysis.")
            return {"error": "spaCy model not available"}
        
        if not hasattr(self, 'sentiment_analyzer') or not hasattr(self, 'emotion_analyzer'):
            self.logger.error("Sentiment or emotion analyzers are not available.")
            return {"error": "Sentiment or emotion analyzers not available"}
        
        try:
            sentences = list(self.nlp(text).sents)
            sentiments = [self.sentiment_analyzer(str(sent))[0] for sent in sentences]
            
            overall_sentiment = self.sentiment_analyzer(text[:512])[0]
            emotions = self.emotion_analyzer(text[:512])[0]
            
            # Calculate sentiment distribution
            sentiment_counts = Counter(s['label'] for s in sentiments)
            total_sentences = len(sentiments)
            sentiment_distribution = {label: count / total_sentences for label, count in sentiment_counts.items()}
            
            sentiment_graph_base64 = None
            if generate_graph:
                sentiment_graph_base64 = self._generate_sentiment_graph(sentiment_distribution)
            
            return {
                "overall_sentiment": overall_sentiment['label'],
                "overall_score": float(overall_sentiment['score']),
                "sentiment_distribution": sentiment_distribution,
                "sentence_sentiments": [{"text": str(sent), "sentiment": sent_analysis['label'], "score": float(sent_analysis['score'])} 
                                        for sent, sent_analysis in zip(sentences, sentiments)],
                "dominant_emotion": emotions['label'],
                "emotion_score": float(emotions['score']),
                "sentiment_graph": sentiment_graph_base64
            }
        except Exception as e:
            self.logger.error(f"Error in sentiment analysis: {str(e)}")
            return {"error": f"Sentiment analysis failed: {str(e)}"}

    def _generate_sentiment_graph(self, sentiment_distribution):
        plt.figure(figsize=(10, 5))
        plt.bar(sentiment_distribution.keys(), sentiment_distribution.values())
        plt.title("Distribution des sentiments dans le texte")
        plt.xlabel("Sentiments")
        plt.ylabel("Proportion")
        plt.ylim(0, 1)  # Set y-axis limit from 0 to 1 for percentage

        buf = io.BytesIO()
        plt.savefig(buf, format='png')
        buf.seek(0)
        graph_base64 = base64.b64encode(buf.getvalue()).decode('utf-8')
        plt.close()
        
        return graph_base64


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
        if not self.SPACY_AVAILABLE:
            self.logger.warning("spaCy not available, falling back to basic n-gram extraction")
            return self._basic_extract_top_n_grams(text, n, top)
        
        doc = self.nlp(text.lower())
        
        # Filtrage des mots : garder seulement les noms, verbes, adjectifs qui ne sont pas des stop words
        filtered_words = [token.lemma_ for token in doc if token.pos_ in {'NOUN', 'VERB', 'ADJ'} 
                        and token.lemma_ not in self.stop_words and token.lemma_.isalnum()]
        
        # Génération des n-grams
        n_grams = list(ngrams(filtered_words, n))
        
        # Comptage et retour des top n-grams
        return Counter(n_grams).most_common(top)

    def _basic_extract_top_n_grams(self, text, n=2, top=5):
        words = word_tokenize(text.lower(), language='french')
        filtered_words = [word for word in words if word.isalnum() and word not in self.stop_words]
        n_grams = list(ngrams(filtered_words, n))
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

    def extract_topics(self, texts, method='lda', num_topics=5):
        vectorizer = TfidfVectorizer(max_features=1000, stop_words=self.stop_words)
        X = vectorizer.fit_transform(texts)
        feature_names = vectorizer.get_feature_names()
        
        if method == 'lda':
            model = LatentDirichletAllocation(n_components=num_topics, random_state=42)
        elif method == 'kmeans':
            model = KMeans(n_clusters=num_topics, random_state=42)
        else:
            raise ValueError("Method must be 'lda' or 'kmeans'")
        
        model.fit(X)
        
        topics = []
        if method == 'lda':
            for topic_idx, topic in enumerate(model.components_):
                top_words = [feature_names[i] for i in topic.argsort()[:-10 - 1:-1]]
                topics.append({"id": topic_idx, "words": top_words})
        else:  # kmeans
            order_centroids = model.cluster_centers_.argsort()[:, ::-1]
            for i in range(num_topics):
                top_words = [feature_names[ind] for ind in order_centroids[i, :10]]
                topics.append({"id": i, "words": top_words})
        
        return topics

    def _lda_topics(self, texts, num_topics):
        vectorizer = TfidfVectorizer(max_features=1000, stop_words=self.stop_words)
        X = vectorizer.fit_transform(texts)
        
        lda = LatentDirichletAllocation(n_components=num_topics, random_state=42)
        lda.fit(X)
        
        feature_names = vectorizer.get_feature_names()
        topics = []
        for topic_idx, topic in enumerate(lda.components_):
            top_words = [feature_names[i] for i in topic.argsort()[:-10 - 1:-1]]
            topics.append({"id": topic_idx, "words": top_words})
        
        return topics

    def _kmeans_topics(self, texts, num_clusters):
        vectorizer = TfidfVectorizer(max_features=1000, stop_words=self.stop_words)
        X = vectorizer.fit_transform(texts)
        
        kmeans = KMeans(n_clusters=num_clusters, random_state=42)
        kmeans.fit(X)
        
        order_centroids = kmeans.cluster_centers_.argsort()[:, ::-1]
        terms = vectorizer.get_feature_names()
        
        topics = []
        for i in range(num_clusters):
            top_words = [terms[ind] for ind in order_centroids[i, :10]]
            topics.append({"id": i, "words": top_words})
        
        return topics

    def cluster_texts(self, texts, num_clusters=5):
        vectorizer = TfidfVectorizer(max_features=1000, stop_words=self.stop_words)
        X = vectorizer.fit_transform(texts)
        
        kmeans = KMeans(n_clusters=num_clusters, random_state=42)
        kmeans.fit(X)
        
        clusters = [{"text": text, "cluster": cluster} for text, cluster in zip(texts, kmeans.labels_)]
        return clusters