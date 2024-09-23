from flask import Blueprint, request, jsonify, current_app
from ..nlp.analyzer import NLPAnalyzer
import base64
from flask_limiter import Limiter
from flask_limiter.util import get_remote_address

limiter = Limiter(key_func=get_remote_address)

api_bp = Blueprint('api', __name__)
nlp_analyzer = NLPAnalyzer()

@api_bp.route('/analyze', methods=['POST'])
@limiter.limit("5 per minute")
def analyze():
    current_app.logger.debug("Received analyze request")
    data = request.json
    if not data or 'content' not in data:
        current_app.logger.warning("No text provided for analysis")
        return jsonify({"error": "No text provided for analysis"}), 400

    try:
        text = base64.b64decode(data['content']).decode('utf-8')
        generate_graph = data.get('generate_sentiment_graph', False)
        current_app.logger.debug(f"Decoded text: {text[:50]}... Generate graph: {generate_graph}")
        
        result = nlp_analyzer.analyze_text(text, generate_sentiment_graph=generate_graph)
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

    try:
        text1 = base64.b64decode(data['text1']).decode('utf-8', errors='ignore')
        text2 = base64.b64decode(data['text2']).decode('utf-8', errors='ignore')
        current_app.logger.debug(f"Decoded text1: {text1}")
        current_app.logger.debug(f"Decoded text2: {text2}")
    except Exception as e:
        current_app.logger.error(f"Error decoding base64: {str(e)}")
        return jsonify({"error": f"Error decoding base64: {str(e)}"}), 400

    try:
        result = nlp_analyzer.calculate_similarity(text1, text2)
        current_app.logger.debug(f"Similarity result: {result}")
        return jsonify(result)
    except Exception as e:
        current_app.logger.error(f"Error during similarity calculation: {str(e)}")
        return jsonify({"error": f"Error during similarity calculation: {str(e)}"}), 500

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


@api_bp.errorhandler(429)
def ratelimit_handler(e):
    return jsonify({"error": "Rate limit exceeded. Please try again later."}), 429

@api_bp.errorhandler(Exception)
def handle_exception(e):
    current_app.logger.error(f"Unhandled exception: {str(e)}")
    return jsonify({"error": "An unexpected error occurred"}), 500