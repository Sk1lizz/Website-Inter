# backend/index.py
from flask import Flask, send_from_directory, jsonify, request
from flask_cors import CORS
from db import connection
import os

app = Flask(__name__, static_folder='dist', static_url_path='/')
CORS(app, resources={r"/api/*": {"origins": ["https://fcintermoscow.com", "https://www.fcintermoscow.com"]}})

@app.route('/api/time')
def get_time():
    with connection.cursor() as cursor:
        cursor.execute("SELECT NOW() AS now")
        return jsonify({"serverTime": cursor.fetchone()["now"]})

@app.route('/api/birthdays')
def get_birthdays():
    query = """
         SELECT
        name,
        DATE_FORMAT(birth_date, '%d.%m.%Y') AS birthday,
        DATEDIFF(
            IF(
                DATE_FORMAT(birth_date, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d'),
                STR_TO_DATE(CONCAT(YEAR(NOW()), '-', DATE_FORMAT(birth_date, '%m-%d')), '%Y-%m-%d'),
                STR_TO_DATE(CONCAT(YEAR(NOW()) + 1, '-', DATE_FORMAT(birth_date, '%m-%d')), '%Y-%m-%d')
            ),
            CURDATE()
        ) AS days_left
    FROM players
    ORDER BY days_left ASC
    LIMIT 3
    """
    try:
        with connection.cursor() as cursor:
            cursor.execute(query)
            result = cursor.fetchall()
            for row in result:
                row["first_name"] = row["name"].split()[0]
                row["last_name"] = row["name"].split()[-1]
            return jsonify(result)
    except Exception as e:
        import traceback
        traceback.print_exc()
        return jsonify({'error': str(e)}), 500

@app.route('/api/players/team/<int:team_id>', methods=['GET'])
def get_players_by_team(team_id):
    try:
        with connection.cursor() as cursor:
            cursor.execute("SELECT name, number, position FROM players WHERE team_id = %s", (team_id,))
            return jsonify(cursor.fetchall())
    except Exception as e:
        print('Ошибка при получении игроков:', e)
        return jsonify({'error': 'Server error'}), 500

@app.route('/api/players/number/<int:number>', methods=['GET'])
def get_player_by_number(number):
    try:
        with connection.cursor() as cursor:
            cursor.execute("SELECT * FROM players WHERE number = %s", (number,))
            result = cursor.fetchall()
            if not result:
                return jsonify({'error': 'Игрок не найден'}), 404
            return jsonify(result[0])
    except Exception as e:
        print('Ошибка при получении игрока по номеру:', e)
        return jsonify({'error': 'Ошибка сервера'}), 500

@app.route('/api/admin/players/<int:player_id>/statistics/all', methods=['GET'])
def get_player_stats_all(player_id):
    try:
        with connection.cursor() as cursor:
            cursor.execute("SELECT * FROM player_statistics_all WHERE player_id = %s", (player_id,))
            result = cursor.fetchall()
            if not result:
                return jsonify({'error': 'Нет общей статистики'}), 404
            return jsonify(result[0])
    except Exception as e:
        print("Ошибка получения общей статистики:", e)
        return jsonify({'error': 'Ошибка сервера'}), 500

@app.route('/api/player_statistics_2025')
def get_statistics_by_team():
    team_id = int(request.args.get('team_id', 0))
    try:
        with connection.cursor() as cursor:
            cursor.execute("""
                SELECT ps.*, p.name, p.position
                FROM player_statistics_2025 ps
                JOIN players p ON ps.player_id = p.id
                WHERE p.team_id = %s
            """, (team_id,))
            return jsonify(cursor.fetchall())
    except Exception as e:
        print('Ошибка запроса к базе:', e)
        return jsonify({'error': 'Ошибка при запросе к базе данных'}), 500

@app.route('/api/achievements')
def get_achievements():
    player_id = request.args.get('player_id')
    team_id = request.args.get('team_id')

    query = """
        SELECT a.*, p.name
        FROM achievements a
        JOIN players p ON a.player_id = p.id
    """
    params = []

    if player_id:
        query += " WHERE a.player_id = %s"
        params.append(player_id)
    elif team_id:
        query += " WHERE p.team_id = %s"
        params.append(team_id)

    query += " ORDER BY a.award_year DESC, a.player_id"

    try:
        with connection.cursor() as cursor:
            cursor.execute(query, params)
            return jsonify(cursor.fetchall())
    except Exception as e:
        print('Ошибка получения достижений:', e)
        return jsonify({'error': 'Ошибка сервера при получении достижений'}), 500

@app.route('/<path:path>')
def serve_static(path):
    return send_from_directory('dist', path)

@app.route('/')
def index():
    return send_from_directory('dist', 'index.html')

if __name__ == '__main__':
    port = int(os.environ.get('PORT', 3000))
    app.run(host='0.0.0.0', port=port)
