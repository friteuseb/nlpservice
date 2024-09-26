from flask import render_template, send_from_directory
from . import web_bp
import os

# Chemin vers le répertoire contenant le fichier openapi.yaml
swagger_dir = os.path.join(os.path.dirname(__file__), 'static', 'swagger')

@web_bp.route('/')
def home():
    return render_template('index.html')

@web_bp.route('/api-docs')
def swagger_ui():
    return render_template('swagger.html')

@web_bp.route('/openapi.yaml')
def send_openapi_spec():
    return send_from_directory(swagger_dir, 'openapi.yaml')