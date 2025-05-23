const express = require('express');
const router = express.Router();
const db = require('../../db');
const pool = require('../../db');
const archiveTeamId = 3; // id команды архива

// Получить список команд
router.get('/teams', async (req, res) => {
    try {
        const [teams] = await pool.query('SELECT id, name FROM teams');
        res.json(teams);
    } catch (error) {
        console.error(error);
        res.status(500).send('Ошибка при получении команд');
    }
});

// Получить игроков и их статистику по ID команды
router.get('/players/:teamId', async (req, res) => {
    const teamId = req.params.teamId;
    try {
        const [players] = await pool.query('SELECT id, name FROM players WHERE team_id = ?', [teamId]);
        const playerIds = players.map(p => p.id);

        if (playerIds.length === 0) {
            return res.json([]); // Нет игроков — сразу ответ
        }

        const placeholders = playerIds.map(() => '?').join(',');
        const [stats] = await pool.query(`
            SELECT player_id, matches, goals, assists, zeromatch, lostgoals, zanetti_priz 
            FROM player_statistics_2025 
            WHERE player_id IN (${placeholders})
        `, playerIds);

        const statsMap = {};
        stats.forEach(s => {
            statsMap[s.player_id] = s;
        });

        const result = players.map(p => ({
            id: p.id,
            name: p.name,
            stats: statsMap[p.id] || {
                matches: 0,
                goals: 0,
                assists: 0,
                zeromatch: 0,
                lostgoals: 0,
                zanetti_priz: 0
            }
        }));

        res.json(result);
    } catch (error) {
        console.error(error);
        res.status(500).send('Ошибка при получении игроков');
    }
});

module.exports = router;

router.post('/players', async (req, res) => {
    const {
        team_id,
        name,
        patronymic,
        number,
        position,
        birth_date,
        join_date,
        height_cm,
        weight_kg
    } = req.body;


    try {
        const [result] = await pool.query(`
            INSERT INTO players (team_id, name, patronymic, number, position, birth_date, join_date, height_cm, weight_kg)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        `, [team_id, name, patronymic, number, position, birth_date, join_date, height_cm, weight_kg]);

        const playerId = result.insertId;

        await pool.query(`
            INSERT INTO player_statistics_2025 (player_id, matches, goals, assists, zeromatch, lostgoals, zanetti_priz)
            VALUES (?, 0, 0, 0, 0, 0, 0)
        `, [playerId]);

        res.status(201).json({ success: true, playerId });
    } catch (error) {
        console.error('Ошибка при добавлении игрока', error);
        res.status(500).send('Ошибка при добавлении игрока');
    }
});

// PUT /api/admin/players/:playerId/archive
router.put('/players/:playerId/archive', async (req, res) => {
    const playerId = req.params.playerId;
    const { new_team_id } = req.body;

    try {
        await pool.query('UPDATE players SET team_id = ? WHERE id = ?', [archiveTeamId, playerId]);
        res.sendStatus(200);
    } catch (err) {
        console.error(err);
        res.status(500).json({ error: 'Ошибка сервера при обновлении игрока' });
    }
});

// Обработчик обновления статистики нескольких игроков
router.put('/players/statistics', async (req, res) => {
    const { updates } = req.body; // ожидаем массив { playerId, stats }

    if (!Array.isArray(updates)) {
        return res.status(400).json({ error: 'Некорректный формат данных' });
    }

    try {
        const conn = await pool.getConnection();
        try {
            await conn.beginTransaction();

            for (const update of updates) {
                const { playerId, stats } = update;
                await conn.query(`
                    UPDATE player_statistics_2025 SET
                        matches = ?,
                        goals = ?,
                        assists = ?,
                        zeromatch = ?,
                        lostgoals = ?,
                        zanetti_priz = ?
                    WHERE player_id = ?
                `, [
                    stats.matches,
                    stats.goals,
                    stats.assists,
                    stats.zeromatch,
                    stats.lostgoals,
                    stats.zanetti_priz,
                    playerId
                ]);
            }

            await conn.commit();
            conn.release();
            res.sendStatus(200);
        } catch (err) {
            await conn.rollback();
            conn.release();
            throw err;
        }
    } catch (err) {
        console.error('Ошибка обновления статистики:', err);
        res.status(500).json({ error: 'Ошибка сервера при обновлении статистики' });
    }
});

module.exports = router;

router.put('/players/:id', async (req, res) => {
    const playerId = req.params.id;
    const {
        name, patronymic, birth_date, number,
        position, height_cm, weight_kg, team_id
    } = req.body;

    try {
        await pool.query(`
            UPDATE players SET
                name = ?, patronymic = ?, birth_date = ?, number = ?,
                position = ?, height_cm = ?, weight_kg = ?, team_id = ?
            WHERE id = ?
        `, [
            name, patronymic, birth_date, number,
            position, height_cm, weight_kg, team_id,
            playerId
        ]);

        res.sendStatus(200);
    } catch (err) {
        console.error('Ошибка при обновлении игрока:', err);
        res.status(500).json({ error: 'Ошибка сервера' });
    }
});

router.get('/players/:playerId', async (req, res) => {
    const playerId = req.params.playerId;
    console.log('Загружаем данные игрока с ID:', playerId);

    try {
        const [players] = await pool.query('SELECT * FROM players WHERE id = ?', [playerId]);

        console.log('Данные игрока:', players);
        if (players.length === 0) {
            return res.status(404).json({ error: 'Игрок не найден' });
        }
        res.json(players[0]);
    } catch (error) {
        console.error(error);
        res.status(500).json({ error: 'Ошибка при получении данных игрока' });
    }

    // Добавь в любом месте файла:
    router.get('/players/:playerId', async (req, res) => {
        const playerId = req.params.playerId;
        console.log('-> API: получаем игрока с ID', playerId);

        try {
            const [players] = await pool.query('SELECT * FROM players WHERE id = ?', [playerId]);
            console.log('-> Результат SQL:', players);
            res.json(players[0] || null);
        } catch (e) {
            console.error('Ошибка при запросе игрока:', e);
            res.status(500).json({ error: 'Ошибка сервера' });
        }
    });

    // Внизу файла:
    module.exports = router;
});