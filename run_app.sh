#!/bin/bash
source /home/friteuseb/nlpservice/venv/bin/activate
cd /home/friteuseb/nlpservice
gunicorn --workers 1 --threads 2 --bind 0.0.0.0:5000 wsgi:app