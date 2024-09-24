from flask import render_template
from . import web_bp

@web_bp.route('/')
def home():
    return render_template('index.html')