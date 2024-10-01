from flask import Blueprint, request, jsonify, current_app
from ..nlp.analyzer import NLPAnalyzer
import base64
from flask_limiter import Limiter
from flask_limiter.util import get_remote_address
from flask import request, jsonify
from ..nlp.similarity_calculator import SimilarityCalculator
import asyncio
import base64
from concurrent.futures import ThreadPoolExecutor

limiter = Limiter(key_func=get_remote_address)

api_bp = Blueprint('api', __name__)
nlp_analyzer = NLPAnalyzer()
similarity_calculator = SimilarityCalculator()

@api_bp.route('/analyze', methods=['POST'])
@limiter.limit("5 per minute")
def analyze():
    current_app.logger.debug("Received analyze request")
    data = request.json
    if not data:
        current_app.logger.warning("No data provided for analysis")
        return jsonify({"error": "No data provided for analysis"}), 400

    try:
        if 'content' in data:
            # Analyse d'un seul texte
            text = base64.b64decode(data['content']).decode('utf-8')
            generate_graph = data.get('generate_sentiment_graph', False)
            result = nlp_analyzer.analyze_text(text, generate_sentiment_graph=generate_graph)
        elif 'text1' in data and 'text2' in data:
            # Calcul de similarité entre deux textes
            text1 = base64.b64decode(data['text1']).decode('utf-8')
            text2 = base64.b64decode(data['text2']).decode('utf-8')
            method = data.get('method', 'cosine')
            result = nlp_analyzer.calculate_similarity(text1, text2, method)
        else:
            current_app.logger.warning("Invalid input format")
            return jsonify({"error": "Invalid input format. Provide either 'content' for single text analysis or 'text1' and 'text2' for similarity calculation"}), 400

        current_app.logger.debug(f"Analysis result: {result}")
        return jsonify(result)
    except Exception as e:
        current_app.logger.error(f"Error during analysis: {str(e)}")
        return jsonify({"error": f"Error during analysis: {str(e)}"}), 500

@api_bp.route('/similarity', methods=['POST'])
@limiter.limit("5 per minute")
def calculate_similarity():
    current_app.logger.debug("Received similarity calculation request")
    data = request.json
    if not data or 'text1' not in data or 'text2' not in data:
        current_app.logger.warning("Both texts are required for similarity calculation")
        return jsonify({"error": "Both texts are required for similarity calculation"}), 400

    method = data.get('method', 'cosine')  # Default to cosine similarity if not specified

    try:
        text1 = base64.b64decode(data['text1']).decode('utf-8', errors='ignore')
        text2 = base64.b64decode(data['text2']).decode('utf-8', errors='ignore')
        current_app.logger.debug(f"Decoded text1: {text1[:50]}...")
        current_app.logger.debug(f"Decoded text2: {text2[:50]}...")
    except Exception as e:
        current_app.logger.error(f"Error decoding base64: {str(e)}")
        return jsonify({"error": f"Error decoding base64: {str(e)}"}), 400

    try:
        result = nlp_analyzer.calculate_similarity(text1, text2, method)
        current_app.logger.debug(f"Similarity result: {result}")
        return jsonify(result)
    except ValueError as e:
        current_app.logger.error(f"Invalid similarity method: {str(e)}")
        return jsonify({"error": f"Invalid similarity method: {str(e)}"}), 400
    except Exception as e:
        current_app.logger.error(f"Error during similarity calculation: {str(e)}")
        return jsonify({"error": f"Error during similarity calculation: {str(e)}"}), 500




@api_bp.route('/batch_similarity', methods=['POST'])
@limiter.limit("3 per minute")  # Ajustez cette limite selon vos besoins
def batch_similarity():
    current_app.logger.debug("Received batch similarity calculation request")
    data = request.json
    if not data or 'text_pairs' not in data:
        current_app.logger.warning("Invalid input format for batch similarity")
        return jsonify({"error": "Invalid input format"}), 400

    text_pairs = data['text_pairs']
    method = data.get('method', 'cosine')

    current_app.logger.debug(f"Processing {len(text_pairs)} pairs with method: {method}")

    try:
        decoded_pairs = []
        for pair in text_pairs:
            try:
                text1 = base64.b64decode(pair['text1']).decode('utf-8')
                text2 = base64.b64decode(pair['text2']).decode('utf-8')
                decoded_pairs.append({'text1': text1, 'text2': text2})
            except Exception as e:
                current_app.logger.error(f"Error decoding text pair: {str(e)}")
                return jsonify({"error": f"Error decoding text pair: {str(e)}"}), 400

        results = similarity_calculator.calculate_batch_similarity(decoded_pairs, method)
        current_app.logger.debug(f"Batch similarity calculation completed. Results: {results}")
        return jsonify({"results": results})
    except Exception as e:
        current_app.logger.error(f"Error in batch similarity calculation: {str(e)}")
        return jsonify({"error": f"Error during calculation: {str(e)}"}), 500

    
@api_bp.route('/extract_topics', methods=['POST'])
def extract_topics():
    try:
        data = request.json
        if not data or 'texts' not in data:
            return jsonify({"error": "No texts provided for topic extraction"}), 400
        
        texts = data['texts']
        num_topics = data.get('num_topics', 3)  # default to 3 if not provided
        
        topics = nlp_analyzer.extract_topics(texts, num_topics=num_topics)
        return jsonify(topics)
    except Exception as e:
        current_app.logger.error(f"Error in topic extraction: {str(e)}")
        return jsonify({"error": f"Topic extraction failed: {str(e)}"}), 500

@api_bp.route('/faiss_similarity_status', methods=['GET'])
def get_faiss_similarity_status():
    try:
        status = current_app.faiss_similarity.get_status()
        return jsonify(status)
    except Exception as e:
        current_app.logger.error(f"Error getting FAISS status: {str(e)}")
        return jsonify({"error": str(e)}), 500

@api_bp.route('/add_texts', methods=['POST'])
@limiter.limit("5 per minute")
def add_texts():
    try:
        current_app.logger.info("Début de la route add_texts")
        data = request.json
        current_app.logger.debug(f"Données reçues : {data}")
        
        if not data or 'items' not in data:
            current_app.logger.warning("Format d'entrée invalide pour add_texts")
            return jsonify({"error": "Invalid input format"}), 400
        
        items = data['items']
        current_app.logger.info(f"Nombre d'items reçus : {len(items)}")
        
        if not items:
            current_app.logger.warning("Liste d'items vide")
            return jsonify({"error": "Empty item list"}), 400
        
        current_app.logger.debug(f"Premier item : {items[0]}")
        
        current_app.logger.info("Appel de la méthode add_texts de FAISSSimilarity")
        num_added = current_app.faiss_similarity.add_texts(items)
        current_app.logger.info(f"Nombre de textes ajoutés : {num_added}")
        
        return jsonify({"message": f"Added/Updated {num_added} texts"})
    except Exception as e:
        current_app.logger.error(f"Erreur lors de l'ajout de textes à FAISS: {str(e)}", exc_info=True)
        return jsonify({"error": str(e)}), 500


@api_bp.route('/find_similar', methods=['POST'])
@limiter.limit("10 per minute")
def find_similar():
    try:
        data = request.json
        if not data or 'id' not in data:
            return jsonify({"error": "Invalid input format"}), 400
        
        item_id = data['id']
        k = data.get('k', 5)
        results = current_app.faiss_similarity.find_similar(item_id, k)
        if results is None:
            return jsonify({"error": "Text not found"}), 404
        return jsonify(results)
    except Exception as e:
        current_app.logger.error(f"Error finding similar texts: {str(e)}")
        return jsonify({"error": str(e)}), 500


@api_bp.route('/clear_faiss_index', methods=['POST'])
@limiter.limit("1 per hour")
def clear_faiss_index():
    try:
        current_app.logger.info("Requête reçue pour clear_faiss_index")
        
        if not hasattr(current_app, 'faiss_similarity'):
            current_app.logger.error("L'attribut faiss_similarity n'est pas présent dans l'application")
            return jsonify({"error": "FAISS Similarity not initialized"}), 500
        
        result = current_app.faiss_similarity.clear_index()
        if result:
            current_app.logger.info("Index FAISS nettoyé avec succès")
            return jsonify({"message": "FAISS index cleared successfully"})
        else:
            current_app.logger.warning("Le nettoyage de l'index FAISS n'a pas retourné True")
            return jsonify({"error": "Unexpected result from clear_index"}), 500
    except Exception as e:
        current_app.logger.error(f"Erreur lors du nettoyage de l'index FAISS: {str(e)}", exc_info=True)
        return jsonify({"error": f"Error clearing FAISS index: {str(e)}"}), 500

@api_bp.errorhandler(429)
def ratelimit_handler(e):
    return jsonify({"error": "Rate limit exceeded. Please try again later."}), 429

@api_bp.errorhandler(Exception)
def handle_exception(e):
    current_app.logger.error(f"Unhandled exception: {str(e)}")
    return jsonify({"error": "An unexpected error occurred"}), 500



