# backend/app.py
from flask import Flask, send_from_directory
from flask_cors import CORS
from routes.admin import admin_bp
from db import connection
import os

app = Flask(__name__, static_folder='public', static_url_path='/')
CORS(app)

# Роуты API
app.register_blueprint(admin_bp, url_prefix='/api/admin')

# Страница админки
@app.route('/admin')
def admin_page():
    return send_from_directory('views', 'admin.html')

# Запуск сервера
if __name__ == '__main__':
    port = int(os.environ.get('PORT', 3000))
    app.run(host='0.0.0.0', port=port)