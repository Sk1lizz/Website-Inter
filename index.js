const express = require('express');
const app = express();
app.use(express.json());
const path = require('path');
const db = require('./db');
require('dotenv').config();


const adminApiRoutes = require('./dist/routes/admin'); // или где у тебя лежит API
app.use('/api/admin', adminApiRoutes);

app.use(express.json());

// Раздаём статику из папки dist (например, сборка фронтенда)
app.use(express.static(path.join(__dirname, 'dist')));

// API для дней рождения
app.get('/api/birthdays', async (req, res) => {
    try {
        const query = `
           SELECT
  name, -- полное имя
  SUBSTRING_INDEX(name, ' ', 1) AS first_name,  -- первое слово (имя)
  SUBSTRING_INDEX(name, ' ', -1) AS last_name,  -- последнее слово (фамилия)
  DATE_FORMAT(birth_date, '%d.%m.%Y') AS birthday,
  TIMESTAMPDIFF(DAY, CURDATE(),
    DATE_FORMAT(
      IF(DATE_FORMAT(birth_date, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d'),
        DATE_FORMAT(CONCAT(YEAR(NOW()), '-', DATE_FORMAT(birth_date, '%m-%d')), '%Y-%m-%d'),
        DATE_FORMAT(CONCAT(YEAR(NOW()) + 1, '-', DATE_FORMAT(birth_date, '%m-%d')), '%Y-%m-%d')
      ),
    '%Y-%m-%d')
  ) AS days_left
FROM players
ORDER BY days_left ASC
LIMIT 3
        `;

        const [rows] = await db.query(query);
        res.json(rows);
    } catch (error) {
        console.error('Ошибка при получении дней рождения:', error);
        res.status(500).json({ error: 'Ошибка при получении дней рождения' });
    }
});

// Пример другого API маршрута
app.get('/api/time', async (req, res) => {
    try {
        const [rows] = await db.query('SELECT NOW() AS now');
        res.json({ serverTime: rows[0].now });
    } catch (error) {
        console.error('Ошибка при запросе к БД:', error);
        res.status(500).send('Ошибка подключения к БД');
    }
});

// Отдаём index.html для всех маршрутов, кроме /api/*
app.get(/^\/(?!api).*/, (req, res) => {
    res.sendFile(path.join(__dirname, 'dist', 'index.html'));
});

// Запуск сервера
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Сервер запущен на порту ${PORT}`);
});


// *******************************************************************


// 1. Получить команды (без Архива)
app.get('/api/admin/teams/active', async (req, res) => {
    try {
        const [teams] = await db.query('SELECT id, name FROM teams WHERE name != "Архив игроков" ORDER BY name');
        res.json(teams);
    } catch (e) {
        res.status(500).json({ error: 'Server error' });
    }
});

// 2. Получить игроков из архива (team_id архива заранее известен, например 3)
app.get('/api/admin/players/archive', async (req, res) => {
    try {
        const archiveTeamId = 3; // заменить на ID архива
        const [players] = await db.query('SELECT id, name, number, position FROM players WHERE team_id = ?', [archiveTeamId]);
        res.json(players);
    } catch (e) {
        res.status(500).json({ error: 'Server error' });
    }
});

// 3. Переместить игрока из архива в другую команду
app.put('/api/admin/players/:playerId/move', async (req, res) => {
    try {
        const playerId = req.params.playerId;
        const { newTeamId } = req.body;
        await db.query('UPDATE players SET team_id = ? WHERE id = ?', [newTeamId, playerId]);
        res.json({ success: true });
    } catch (e) {
        res.status(500).json({ error: 'Server error' });
    }
});

// 4. Добавить нового игрока
app.post('/api/admin/players', async (req, res) => {
    try {
        const { team_id, name, patronymic, number, position, birth_date, height_cm, weight_kg, is_captain } = req.body;

        const [result] = await db.query(`INSERT INTO players (team_id, name, patronymic, number, position, birth_date, height_cm, weight_kg, is_captain, join_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())`,
            [team_id, name, patronymic, number, position, birth_date, height_cm, weight_kg, is_captain || 0]);

        // Создаем пустую статистику для нового игрока
        const playerId = result.insertId;
        await db.query(`INSERT INTO player_statistics_2025 (player_id, matches, goals, assists, zeromatch, lostgoals, zanetti_priz) VALUES (?, 0, 0, 0, 0, 0, 0)`, [playerId]);
        await db.query(`INSERT INTO player_statistics_all (player_id, matches, goals, assists, zeromatch, lostgoals, zanetti_priz) VALUES (?, 0, 0, 0, 0, 0, 0)`, [playerId]);

        res.json({ success: true, playerId });
    } catch (e) {
        console.error(e);
        res.status(500).json({ error: 'Server error' });
    }
});

// 5. Получить статистику игрока
app.get('/api/admin/players/:playerId/statistics', async (req, res) => {
    try {
        const playerId = req.params.playerId;
        // Можно выбрать либо текущий сезон, либо общий — в примере текущий
        const [stats] = await db.query('SELECT * FROM player_statistics_2025 WHERE player_id = ?', [playerId]);
        res.json(stats[0] || null);
    } catch (e) {
        res.status(500).json({ error: 'Server error' });
    }
});

// 6. Обновить статистику игрока
app.put('/api/admin/players/:playerId/statistics', async (req, res) => {
    try {
        const playerId = req.params.playerId;
        const { matches, goals, assists, zeromatch, lostgoals, zanetti_priz } = req.body;
        await db.query(
            `UPDATE player_statistics_2025 SET matches=?, goals=?, assists=?, zeromatch=?, lostgoals=?, zanetti_priz=? WHERE player_id=?`,
            [matches, goals, assists, zeromatch, lostgoals, zanetti_priz, playerId]
        );
        res.json({ success: true });
    } catch (e) {
        res.status(500).json({ error: 'Server error' });
    }
});


