import os
from dotenv import load_dotenv

load_dotenv()

class Config:
    API_KEY = os.environ.get('API_KEY') or 'default-api-key'
    RATE_LIMIT_ENABLED = os.environ.get('RATE_LIMIT_ENABLED', 'True').lower() == 'true'