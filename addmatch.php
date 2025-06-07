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

    <style>
    
    body {
     font-family: Arial, sans-serif;
     background: #f0f0f0;
     margin: 0;
     padding: 20px;
 }
 
 .admin-panel {
     max-width: 600px;
     margin: 40px auto;
     padding: 25px;
     border: 2px solid #333;
     border-radius: 10px;
     background: #fff;
     box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
 }
 
 .admin-panel h2 {
     margin-top: 0;
     text-align: center;
     color: #222;
     font-size: 24px;
 }
 
 .admin-panel form {
     display: flex;
     flex-direction: column;
     gap: 15px;
 }
 
 .admin-panel label {
     display: flex;
     flex-direction: column;
     font-weight: bold;
     color: #333;
 }
 
 .admin-panel input,
 .admin-panel select {
     width: 100%;
     padding: 8px 10px;
     margin-top: 5px;
     border: 1px solid #ccc;
     border-radius: 6px;
     font-size: 14px;
 }
 
 .admin-panel button {
     padding: 10px 20px;
     font-size: 16px;
     border: none;
     border-radius: 6px;
     cursor: pointer;
     background-color: #00509d;
     color: white;
     transition: background-color 0.3s ease;
     margin-top: 10px;
 }
 
 .admin-panel button:hover {
     background-color: #003f7d;
 }
 
 .success-message {
     text-align: center;
     color: green;
     font-weight: bold;
     margin-bottom: 15px;
 }
 
 .error-message {
     text-align: center;
     color: red;
     font-weight: bold;
     margin-bottom: 15px;
 }
 
 @media (max-width: 600px) {
     .admin-panel {
         padding: 15px;
     }
     .admin-panel h2 {
         font-size: 20px;
     }
     .admin-panel button {
         font-size: 14px;
         padding: 8px 15px;
     }
 }
 
 
     </style>

    <title>Добавить матч</title>

</head>

<body>
<div class="admin-panel">

    <form method="post" action="logout.php" style="float:right;">
        <button type="submit">Выйти</button>
    </form>

    <form action="admin.php" method="get" style="display: inline-block; margin-top: 20px;">
        <button type="submit">Статистика</button>
    </form>

    <form action="success.php" method="get" style="display: inline-block; margin-top: 20px;">
        <button type="submit">Ачивки</button>
    </form>

    <form action="statisticsall.php" method="get" style="display: inline-block; margin-top: 20px;">
    <button type="submit">Статистика общая</button>
</form>


    <h2>Добавить матч</h2>

    <form id="addMatchForm">
        <label>Команда:
            <select id="matchTeamSelect" name="teams_id" required></select>
        </label>
        <label>Дата матча:
            <input type="date" name="date" required>
        </label>
        <label>Год отдельно:
            <input type="number" name="year" required>
        </label>
        <label>Название чемпионата:
            <input type="text" name="championship_name" required>
        </label>
        <label>Тур:
            <input type="text" name="tour">
        </label>
        <label>Соперник:
            <input type="text" name="opponent" required>
        </label>
        <label>Наши голы:
            <input type="number" name="our_goals" required>
        </label>
        <label>Голы соперника:
            <input type="number" name="opponent_goals" required>
        </label>
        <label>Голы кто забивал (текстом):
            <input type="text" name="goals">
        </label>
        <label>Голевые кто отдавал (текстом):
            <input type="text" name="assists">
        </label>
        <label>Результат матча:
            <select name="match_result" required>
                <option value="W">Победа</option>
                <option value="L">Поражение</option>
                <option value="X">Ничья</option>
            </select>
        </label>
        <button type="submit">Добавить матч</button>
    </form>

    <div id="matchMessage"></div>

</div> 

<script>


// Закрытие модалки
function closeAddMatchModal() {
    document.getElementById("addMatchModal").style.display = "none";
    document.getElementById("matchMessage").innerHTML = ""; // очистка сообщений
    document.getElementById("addMatchForm").reset(); // сброс формы
}

// Загрузка команд
async function loadTeamsIntoMatchForm() {
    try {
        const res = await fetch("/api/get_teams.php");
        if (!res.ok) throw new Error(`Ошибка ${res.status} при загрузке команд`);
        const teams = await res.json();

        const matchTeamSelect = document.getElementById("matchTeamSelect");
        matchTeamSelect.innerHTML = "";
        teams.forEach(team => {
            const option = document.createElement("option");
            option.value = team.id;
            option.textContent = team.name;
            matchTeamSelect.appendChild(option);
        });
    } catch (err) {
        alert(err.message);
        console.error(err);
    }
}

// Отправка формы матча
document.getElementById("addMatchForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = {};
    formData.forEach((value, key) => data[key] = value);

    data.teams_id = matchTeamSelect.value;
    data.our_team = matchTeamSelect.options[matchTeamSelect.selectedIndex]?.textContent || '';

    try {
        const res = await fetch("/api/matches.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify(data)
        });

        const result = await res.json();
        const messageDiv = document.getElementById("matchMessage");

        if (res.ok && result?.success) {
            messageDiv.className = "success-message";
            messageDiv.textContent = `✅ Матч добавлен! ID: ${result.match_id}`;
            console.log("📋 Полученные данные:", result.received_data);
            document.getElementById("addMatchForm").reset();
        } else {
            messageDiv.className = "error-message";
            messageDiv.textContent = `❌ Ошибка при добавлении матча: ${result?.error || "неизвестная"}`;
            console.error("🪵 Сервер вернул:", result);
        }
    } catch (err) {
        console.error("Ошибка при отправке матча:", err);
        const messageDiv = document.getElementById("matchMessage");
        messageDiv.className = "error-message";
        messageDiv.textContent = "❌ Ошибка при отправке запроса!";
    }
});

// При загрузке страницы — загружаем команды
document.addEventListener("DOMContentLoaded", loadTeamsIntoMatchForm);
</script>

</body>
</html>
