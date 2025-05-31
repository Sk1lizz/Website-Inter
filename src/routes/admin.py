from flask import Blueprint, request, jsonify
from db import connection

admin_bp = Blueprint('admin', __name__)

@admin_bp.route('/teams/active', methods=['GET'])
def get_active_teams():
    with connection.cursor() as cursor:
        cursor.execute("SELECT id, name FROM teams WHERE name != 'Архив игроков' ORDER BY name")
        return jsonify(cursor.fetchall())

@admin_bp.route('/players/archive', methods=['GET'])
def get_archived_players():
    with connection.cursor() as cursor:
        cursor.execute("SELECT id, name, number, position FROM players WHERE team_id = %s", (3,))
        return jsonify(cursor.fetchall())

@admin_bp.route('/players/<int:player_id>/move', methods=['PUT'])
def move_player(player_id):
    new_team_id = request.json.get('newTeamId')
    with connection.cursor() as cursor:
        cursor.execute("UPDATE players SET team_id = %s WHERE id = %s", (new_team_id, player_id))
    return jsonify({"success": True})

@admin_bp.route('/players', methods=['POST'])
def add_player():
    data = request.json
    with connection.cursor() as cursor:
        cursor.execute("""
            INSERT INTO players (team_id, name, patronymic, number, position, birth_date, height_cm, weight_kg, is_captain, join_date)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, NOW())
        """, (
            data['team_id'], data['name'], data['patronymic'], data['number'], data['position'],
            data['birth_date'], data['height_cm'], data['weight_kg'], data.get('is_captain', 0)
        ))
        player_id = cursor.lastrowid
        cursor.execute("""
            INSERT INTO player_statistics_2025 (player_id, matches, goals, assists, zeromatch, lostgoals, zanetti_priz)
            VALUES (%s, 0, 0, 0, 0, 0, 0)
        """, (player_id,))
        cursor.execute("""
            INSERT INTO player_statistics_all (player_id, matches, goals, assists, zeromatch, lostgoals, zanetti_priz)
            VALUES (%s, 0, 0, 0, 0, 0, 0)
        """, (player_id,))
    return jsonify({"success": True, "playerId": player_id})

@admin_bp.route('/players/<int:player_id>/statistics', methods=['GET'])
def get_player_statistics(player_id):
    with connection.cursor() as cursor:
        cursor.execute("SELECT * FROM player_statistics_2025 WHERE player_id = %s", (player_id,))
        return jsonify(cursor.fetchone())

@admin_bp.route('/players/<int:player_id>/statistics', methods=['PUT'])
def update_player_statistics(player_id):
    stats = request.json
    with connection.cursor() as cursor:
        cursor.execute("""
            UPDATE player_statistics_2025 SET matches=%s, goals=%s, assists=%s, zeromatch=%s, lostgoals=%s, zanetti_priz=%s
            WHERE player_id=%s
        """, (
            stats['matches'], stats['goals'], stats['assists'], stats['zeromatch'], stats['lostgoals'], stats['zanetti_priz'], player_id
        ))
    return jsonify({"success": True})
