<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Фоны игроков</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        body { padding: 20px; font-family: Arial, sans-serif; }
        h1 { margin-bottom: 20px; }

        .styled-table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            font-size: 14px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .styled-table th, .styled-table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .styled-table thead {
            background-color: #2D62B5;
            color: white;
        }

        .background-select {
            padding: 4px;
        }

        .save-background {
            padding: 4px 8px;
            background-color: #2D62B5;
            color: white;
            border: none;
            border-radius: 4px;
            margin-left: 6px;
            cursor: pointer;
        }

        .save-background:hover {
            background-color: #204b91;
        }

        img.preview {
            width: 60px;
            height: 40px;
            object-fit: cover;
            margin-right: 8px;
            vertical-align: middle;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

         .can-change-checkbox {
             margin: 0;
    display: inline-block !important;
    appearance: auto !important;
    transform: scale(1.2);
    margin-right: 4px;
    vertical-align: middle;
}

    td > div {
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

    </style>
</head>
<body>

<h1>Назначение фона игрокам</h1>

<table class="styled-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Фон</th>
        </tr>
    </thead>
    <tbody id="playersTable"></tbody>
</table>

<script>
    const availableBackgrounds = [
        { key: "", name: "— Без фона —" },
        { key: "1", name: "Полосы рваные" },
        { key: "2", name: "Стена" },
        { key: "3", name: "Соты" },
        { key: "4", name: "Золото" },
         { key: "5", name: "Дракон" },
    { key: "6", name: "Кремль" },
    { key: "7", name: "Инь и Янь" },
    { key: "8", name: "Самурай" }
    ];

    async function loadPlayers() {
        try {
            const res = await fetch('/api/get_players_with_backgrounds.php');
            const players = await res.json();
            const tbody = document.getElementById('playersTable');
            tbody.innerHTML = '';

            players.forEach(player => {
                const currentBg = player.background_key || "";
                const canChange = player.can_change_background === 1 || player.can_change_background === "1";

                const bgSelect = `
                    <select class="background-select" data-player-id="${player.id}">
                        ${availableBackgrounds.map(bg => `
                            <option value="${bg.key}" ${bg.key === currentBg ? 'selected' : ''}>
                                ${bg.name}
                            </option>`).join('')}
                    </select>
                `;

                const checkbox = `
    <input type="checkbox" class="can-change-checkbox" data-player-id="${player.id}" ${canChange ? 'checked' : ''}>
    <span style="font-size: 13px;">Игрок может менять фон</span>
`;

                const button = `
                    <button class="save-background" data-player-id="${player.id}">Сохранить</button>
                `;

                const tr = document.createElement('tr');
tr.innerHTML = `
    <td>${player.id}</td>
    <td>${player.name}</td>
    <td>
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            ${bgSelect}
            <input type="checkbox" class="can-change-checkbox" data-player-id="${player.id}" ${canChange ? 'checked' : ''}>
            <span style="font-size: 13px;">Игрок может менять фон</span>
            ${button}
        </div>
    </td>
`;
                tbody.appendChild(tr);
            });

        } catch (err) {
            console.error("Ошибка загрузки игроков:", err);
        }
    }

    document.addEventListener('DOMContentLoaded', loadPlayers);

    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('save-background')) {
            const playerId = e.target.dataset.playerId;
            const select = document.querySelector(`select[data-player-id="${playerId}"]`);
            const checkbox = document.querySelector(`input.can-change-checkbox[data-player-id="${playerId}"]`);
            const key = select.value;
            const name = select.selectedOptions[0].textContent;
            const canChange = checkbox.checked ? 1 : 0;

            try {
                const res = await fetch('/api/set_player_background.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        player_id: playerId,
                        background_key: key,
                        background_name: name,
                        can_change_background: canChange
                    })
                });

                const result = await res.json();
                if (result.success) {
                    alert("Фон и доступ успешно сохранены");
                } else {
                    alert("Ошибка: " + (result.message || "неизвестно"));
                }
            } catch (err) {
                console.error("Ошибка при сохранении фона:", err);
                alert("Ошибка при сохранении фона");
            }
        }
    });
</script>

</body>
</html>
