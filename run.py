from app.main import app
import os

if __name__ == '__main__':
    env = os.environ.get('FLASK_ENV', 'production')
    debug = env == 'development'
    app.run(debug=debug, host='0.0.0.0', port=5000)