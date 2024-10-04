import os
from dotenv import load_dotenv

load_dotenv()

class Config:
    API_KEY = os.environ.get('API_KEY') or 'default-api-key'