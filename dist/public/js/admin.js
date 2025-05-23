async function loadTeams() {
    const teams = await fetchJson('/api/admin/teams/active');

    // Добавим команду Архив вручную
    teams.push({ id: archiveTeamId, name: 'Архив' });

    teamSelect.innerHTML = '';
    teams.forEach(team => {
        const option = document.createElement('option');
        option.value = team.id;
        option.textContent = team.name;
        teamSelect.appendChild(option);
    });

    if (!currentTeamId && teams.length > 0) {
        currentTeamId = teams[0].id;
    }

    teamSelect.value = currentTeamId;
    await loadPlayers(currentTeamId);
}

async function loadPlayers(teamId) {
    let url = `/api/admin/players?teamId=${teamId}`;
    if (+teamId === archiveTeamId) {
        url = '/api/admin/players/archive';
    }

    const players = await fetchJson(url);

    playersDiv.innerHTML = '';

    players.forEach(player => {
        const div = document.createElement('div');
        div.classList.add('player-item');
        div.textContent = `${player.name} — ${player.position}, №${player.number}`;

        if (+teamId === archiveTeamId) {
            const restoreBtn = document.createElement('button');
            restoreBtn.textContent = 'Восстановить';
            restoreBtn.classList.add('button-small');
            restoreBtn.onclick = () => restorePlayer(player.id);
            div.appendChild(restoreBtn);
        } else {
            const archiveBtn = document.createElement('button');
            archiveBtn.textContent = 'В архив';
            archiveBtn.classList.add('button-small');
            archiveBtn.onclick = () => archivePlayer(player.id);
            div.appendChild(archiveBtn);

            div.style.cursor = 'pointer';
            div.onclick = () => showPlayerStats(player.id);
        }

        playersDiv.appendChild(div);
    });
}

document.addEventListener('DOMContentLoaded', async () => {

    // Элементы страницы
    const teamSelect = document.getElementById('teamSelect');
    const playersDiv = document.getElementById('players');
    const playerStatsDiv = document.getElementById('playerStats');
    const statsForm = document.getElementById('statsForm');
    const cancelStatsBtn = document.getElementById('cancelStats');
    const newPlayerForm = document.getElementById('newPlayerForm');

    // Переменные для состояния
    let currentTeamId = null;
    let currentPlayerId = null;
    const archiveTeamId = 3; // ID команды Архив в БД

    // Вспомогательная функция для fetch + обработка ошибок
    async function fetchJson(url, options = {}) {
        try {
            const res = await fetch(url, options);
            if (!res.ok) throw new Error(`Ошибка ${res.status} при загрузке ${url}`);
            return await res.json();
        } catch (e) {
            alert(e.message);
            console.error(e);
            return null;
        }
    }

    // Загрузка списка команд (включая архив)
    async function loadTeams() {
        const teams = await fetchJson('/api/admin/teams/active');
        if (!teams) return;

        // Добавляем Архив вручную
        teams.push({ id: archiveTeamId, name: 'Архив' });

        // Очистка и заполнение селекта командами
        teamSelect.innerHTML = '';
        teams.forEach(team => {
            const option = document.createElement('option');
            option.value = team.id;
            option.textContent = team.name;
            teamSelect.appendChild(option);
        });

        // Если команда ещё не выбрана — выбрать первую
        if (!currentTeamId && teams.length > 0) {
            currentTeamId = teams[0].id;
        }

        teamSelect.value = currentTeamId;
        await loadPlayers(currentTeamId);
    }

    // Загрузка игроков команды (или архива)
    async function loadPlayers(teamId) {
        let url = `/api/admin/players?teamId=${teamId}`;
        if (+teamId === archiveTeamId) {
            url = '/api/admin/players/archive';
        }

        const players = await fetchJson(url);
        if (!players) return;

        playersDiv.innerHTML = '';

        players.forEach(player => {
            const div = document.createElement('div');
            div.classList.add('player-item');
            div.textContent = `${player.name} — ${player.position}, №${player.number}`;

            if (+teamId === archiveTeamId) {
                // Кнопка "Восстановить" для архива
                const restoreBtn = document.createElement('button');
                restoreBtn.textContent = 'Восстановить';
                restoreBtn.classList.add('button-small');
                restoreBtn.onclick = () => restorePlayer(player.id);
                div.appendChild(restoreBtn);
            } else {
                // Кнопка "В архив" для активных команд
                const archiveBtn = document.createElement('button');
                archiveBtn.textContent = 'В архив';
                archiveBtn.classList.add('button-small');
                archiveBtn.onclick = () => archivePlayer(player.id);
                div.appendChild(archiveBtn);

                // Клик по игроку — показать статистику
                div.style.cursor = 'pointer';
                div.onclick = () => showPlayerStats(player.id);
            }

            playersDiv.appendChild(div);
        });
    }

    // Отправить игрока в архив (переместить в архивную команду)
    async function archivePlayer(playerId) {
        const res = await fetch(`/api/admin/players/${playerId}/move`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ newTeamId: archiveTeamId })
        });

        if (!res.ok) {
            alert('Ошибка при отправке в архив');
            return;
        }

        await loadPlayers(currentTeamId);
        playerStatsDiv.style.display = 'none';
    }

    // Восстановить игрока из архива в выбранную команду
    async function restorePlayer(playerId) {
        const teams = await fetchJson('/api/admin/teams/active');
        if (!teams) return;

        const teamName = prompt(`Введите ID команды для восстановления:\n${teams.map(t => `${t.id} - ${t.name}`).join('\n')}`);
        const selectedTeam = teams.find(t => t.id == teamName);

        if (!selectedTeam) {
            alert('Неверный выбор команды');
            return;
        }

        const res = await fetch(`/api/admin/players/${playerId}/move`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ newTeamId: selectedTeam.id })
        });

        if (!res.ok) {
            alert('Ошибка при восстановлении игрока');
            return;
        }

        await loadPlayers(currentTeamId);
    }

    // Показать статистику игрока
    async function showPlayerStats(playerId) {
        currentPlayerId = playerId;

        const stats = await fetchJson(`/api/admin/players/${playerId}/statistics`);
        if (!stats) {
            alert('Статистика не найдена');
            return;
        }

        statsForm.matches.value = stats.matches || 0;
        statsForm.goals.value = stats.goals || 0;
        statsForm.assists.value = stats.assists || 0;
        statsForm.zeromatch.value = stats.zeromatch || 0;
        statsForm.lostgoals.value = stats.lostgoals || 0;
        statsForm.zanetti_priz.value = stats.zanetti_priz || 0;

        playerStatsDiv.style.display = 'block';
    }

    // Сохранить статистику игрока
    statsForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!currentPlayerId) return;

        const data = {
            matches: +statsForm.matches.value,
            goals: +statsForm.goals.value,
            assists: +statsForm.assists.value,
            zeromatch: +statsForm.zeromatch.value,
            lostgoals: +statsForm.lostgoals.value,
            zanetti_priz: +statsForm.zanetti_priz.value,
        };

        const res = await fetch(`/api/admin/players/${currentPlayerId}/statistics`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (res.ok) {
            alert('Статистика сохранена');
        } else {
            alert('Ошибка при сохранении статистики');
        }
    });

    // Отмена редактирования статистики
    cancelStatsBtn.addEventListener('click', () => {
        playerStatsDiv.style.display = 'none';
    });

    // Добавление нового игрока
    newPlayerForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!currentTeamId) {
            alert('Выберите команду');
            return;
        }

        const formData = new FormData(newPlayerForm);

        const data = {
            team_id: currentTeamId,
            name: formData.get('name'),
            number: formData.get('number'),
            position: formData.get('position'),
            birth_date: formData.get('birth_date'),
            height_cm: formData.get('height_cm') || null,
            weight_kg: formData.get('weight_kg') || null,
            is_captain: formData.get('is_captain') ? 1 : 0,
            patronymic: extractPatronymic(formData.get('name'))
        };

        const res = await fetch('/api/admin/players', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (res.ok) {
            alert('Игрок добавлен');
            newPlayerForm.reset();
            await loadPlayers(currentTeamId);
        } else {
            alert('Ошибка при добавлении игрока');
        }
    });

    // Функция для извлечения отчества из полного имени (если есть 3 слова)
    function extractPatronymic(fullName) {
        const parts = fullName.trim().split(' ');
        return parts.length === 3 ? parts[2] : '';
    }

    // Событие изменения команды
    teamSelect.addEventListener('change', async () => {
        currentTeamId = teamSelect.value;
        await loadPlayers(currentTeamId);
        playerStatsDiv.style.display = 'none';
    });

    // Запускаем начальную загрузку команд и игроков
    await loadTeams();

});

router.put('/players/statistics', async (req, res) => {
    const { updates } = req.body; // массив { playerId, stats }

    if (!Array.isArray(updates)) {
        return res.status(400).json({ error: 'Некорректный формат данных' });
    }

    try {
        // Чтобы обновить статистику всех игроков за один запрос — используем транзакцию
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

router.put('/players/:playerId/statistics', async (req, res) => {
    const playerId = req.params.playerId;
    const stats = req.body;

    try {
        await pool.query(`
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
        res.sendStatus(200);
    } catch (err) {
        console.error('Ошибка обновления статистики одного игрока:', err);
        res.status(500).json({ error: 'Ошибка сервера' });
    }

    router.get('/players/:id', async (req, res) => {
        const playerId = req.params.id;
        try {
            const [rows] = await pool.query('SELECT * FROM players WHERE id = ?', [playerId]);
            if (rows.length === 0) {
                return res.status(404).json({ error: 'Игрок не найден' });
            }
            res.json(rows[0]);
        } catch (err) {
            console.error('Ошибка при получении игрока по ID:', err);
            res.status(500).json({ error: 'Ошибка сервера' });
        }
    });
});