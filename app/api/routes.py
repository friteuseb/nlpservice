<<<<<<< HEAD
from flask import Blueprint, request, jsonify, current_app
=======
from flask import Blueprint, request, jsonify
>>>>>>> 161b832178d4b22a52039ba1897d95c71c8ef49f
from ..nlp.analyzer import NLPAnalyzer
import base64
import logging
from flask_limiter import Limiter
from flask_limiter.util import get_remote_address

api_bp = Blueprint('api', __name__)
nlp_analyzer = NLPAnalyzer()

<<<<<<< HEAD
=======
# Configuration du logging
logging.basicConfig(filename='api_requests.log', level=logging.INFO,
                    format='%(asctime)s - %(levelname)s - %(message)s')

>>>>>>> 161b832178d4b22a52039ba1897d95c71c8ef49f
# Initialisation du limiteur de requêtes
limiter = Limiter(key_func=get_remote_address)

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
        current_app.logger.debug(f"Decoded text: {text[:50]}...")
    except Exception as e:
        current_app.logger.error(f"Invalid base64 encoding: {str(e)}")
        return jsonify({"error": f"Invalid base64 encoding: {str(e)}"}), 400

    try:
        current_app.logger.info(f"Analyzing text: {text[:50]}...")
        result = nlp_analyzer.analyze_text(text)
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
    current_app.logger.debug(f"Request data: {data}")

    if not data or 'text1' not in data or 'text2' not in data:
        current_app.logger.warning("Both texts are required for similarity calculation")
        return jsonify({"error": "Both texts are required for similarity calculation"}), 400

    try:
        text1 = base64.b64decode(data['text1']).decode('utf-8')
        text2 = base64.b64decode(data['text2']).decode('utf-8')
        current_app.logger.debug(f"Decoded text1: {text1}")
        current_app.logger.debug(f"Decoded text2: {text2}")
    except Exception as e:
        current_app.logger.error(f"Invalid base64 encoding: {str(e)}")
        return jsonify({"error": f"Invalid base64 encoding: {str(e)}"}), 400

    try:
        current_app.logger.info("Calculating similarity...")
        result = nlp_analyzer.calculate_similarity(text1, text2)
        current_app.logger.debug(f"Similarity result: {result}")
        return jsonify(result)
    except Exception as e:
        current_app.logger.error(f"Error during similarity calculation: {str(e)}")
        return jsonify({"error": f"Error during similarity calculation: {str(e)}"}), 500

@api_bp.errorhandler(429)
def ratelimit_handler(e):
    return jsonify({"error": "Rate limit exceeded. Please try again later."}), 429