const express = require('express');
const path = require('path');
const adminRoutes = require('./src/routes/admin');

const app = express();

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Статика — отдаём публичные файлы (css, js, картинки)
app.use(express.static(path.join(__dirname, 'public')));

// Подключаем API маршруты
app.use('/api/admin', adminRoutes);

// Страница админки
app.get('/admin', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'admin.html'));
});

// Порт сервера
const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
    console.log(`Сервер запущен на порту ${PORT}`);
});
