const express = require('express');
const db = require('./db');
require('dotenv').config();

const app = express();
app.use(express.json());

app.get('/', async (req, res) => {
    try {
        const [rows] = await db.query('SELECT NOW() AS now');
        res.json({ serverTime: rows[0].now });
    } catch (error) {
        console.error('Ошибка при запросе к БД:', error);
        res.status(500).send('Ошибка подключения к БД');
    }
});


const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Сервер запущен на порту ${PORT}`);
});