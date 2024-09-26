from flask import Flask, jsonify, request
from flask_cors import CORS
import nltk
import os
import logging
from logging.handlers import RotatingFileHandler
from flask_limiter import Limiter
from flask_limiter.util import get_remote_address
from .nlp.analyzer import NLPAnalyzer
from .api.routes import api_bp
from .web.routes import web_bp
from .config import Config
import gc
from dotenv import load_dotenv

load_dotenv()  # Charge les variables d'environnement depuis .env

gc.set_threshold(700, 10, 10)

def rate_limit_key_func():
    ip = get_remote_address()
    if ip == os.getenv('PERSONAL_IP'):
        return "no_limit"  # Cette clé spéciale ne sera pas limitée
    return ip

limiter = Limiter(key_func=rate_limit_key_func, default_limits=["200 per day", "50 per hour"])

nltk_resources_loaded = False

def load_nltk_resources():
    global nltk_resources_loaded
    if not nltk_resources_loaded:
        resources = ['punkt', 'stopwords', 'averaged_perceptron_tagger', 'wordnet', 'omw-1.4']
        for resource in resources:
            nltk.download(resource, quiet=True)
        nltk_resources_loaded = True

def configure_logging(app):
    logging.basicConfig(level=logging.INFO)
    file_handler = RotatingFileHandler('nlp_analysis.log', maxBytes=10240, backupCount=10)
    file_handler.setFormatter(logging.Formatter(
        '%(asctime)s %(levelname)s: %(message)s [in %(pathname)s:%(lineno)d]'
    ))
    file_handler.setLevel(logging.INFO)
    app.logger.addHandler(file_handler)
    app.logger.setLevel(logging.INFO)
    app.logger.info('NLP Service startup')

def create_app():
    app = Flask(__name__)
    CORS(app, resources={r"/api/*": {"origins": "*"}})
    app.config.from_object(Config)
    configure_logging(app)

    limiter.init_app(app)

    load_nltk_resources()

    app.nlp_analyzer = NLPAnalyzer()

    app.register_blueprint(api_bp, url_prefix='/api')
    app.register_blueprint(web_bp)

    @app.before_request
    def limit_request_size():
        max_size = 16 * 1024 * 1024  # 16MB
        if request.content_length is not None:
            if request.content_length > max_size:
                app.logger.warning(f"Request too large: {request.content_length} bytes")
                return jsonify({"error": "Request too large"}), 413
            elif request.content_length > 1 * 1024 * 1024:  # 1MB
                app.logger.warning(f"Large request: {request.content_length} bytes")

    @app.after_request
    def cleanup(response):
        gc.collect(generation=2)
        return response

    @app.errorhandler(Exception)
    def handle_exception(e):
        app.logger.error(f"Unhandled exception: {str(e)}", exc_info=True)
        return jsonify({"error": "An unexpected error occurred", "details": str(e)}), 500

    @app.errorhandler(429)
    def ratelimit_handler(e):
        app.logger.warning(f"Rate limit exceeded for IP: {get_remote_address()}")
        return jsonify(error="Limite de taux dépassée. Veuillez réessayer plus tard."), 429

    return app

app = create_app()

if __name__ == "__main__":
    app.run(debug=True, host='0.0.0.0', port=5000)
