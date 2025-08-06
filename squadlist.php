<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Создание заявки | FC Inter Moscow</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background: #f5f5f5;
    }
    input, textarea, select {
      width: 100%;
      margin: 10px 0;
      padding: 8px;
      font-size: 16px;
    }
    button {
      padding: 10px 20px;
      font-size: 18px;
      cursor: pointer;
    }
    canvas {
      border: 1px solid #000;
      margin-top: 20px;
      display: block;
      max-width: 100%;
    }
  </style>
</head>
<body>

  <h1>Создание заявки на матч</h1>

  <label>Наша команда:</label>
  <select id="ourTeam">
    <option value="FC INTER MOSCOW">FC INTER MOSCOW</option>
    <option value="FC INTER MOSCOW PRO">FC INTER MOSCOW PRO</option>
    <option value="FC INTER MOSCOW 8x8">FC INTER MOSCOW 8x8</option>
  </select>

  <label>Соперник:</label>
  <input type="text" id="opponent" placeholder="Например: FC Spartak" />

  <label>Дата матча:</label>
  <input type="date" id="matchDate" />

  <label>Турнир:</label>
  <input type="text" id="tournament" placeholder="Например: Moscow League" />

  <label>Доп. инфо (опционально):</label>
  <input type="text" id="extraInfo" placeholder="Например: Round 12, 1/2 финала, Групповой этап" />

  <label>GOALKEEPERS:</label>
  <textarea id="goalkeepers" rows="3" placeholder="Например: 45 BUTUSOV"></textarea>

  <label>DEFENDERS:</label>
  <textarea id="defenders" rows="4" placeholder="Например: 11 IVANOV - 56 SHEVCHENKO"></textarea>

  <label>MIDFIELDERS:</label>
  <textarea id="midfielders" rows="4" placeholder="Например: 86 PRONIN - 65 PETROSYAN"></textarea>

  <label>FORWARDS:</label>
  <textarea id="forwards" rows="3" placeholder="Например: 65 ANDREEV"></textarea>

  <button id="generateButton">Сгенерировать картинку</button>
  <a id="downloadLink" download="squadlist.png" style="display:none;">Скачать картинку</a>

  <canvas id="squadCanvas" width="1080" height="1080"></canvas>

  <script>
    document.getElementById('generateButton').addEventListener('click', () => {
      const canvas = document.getElementById('squadCanvas');
      const ctx = canvas.getContext('2d');

      const ourTeam = document.getElementById('ourTeam').value;
      const opponent = document.getElementById('opponent').value;
      const matchDate = document.getElementById('matchDate').value;
      const tournament = document.getElementById('tournament').value;
      const extraInfo = document.getElementById('extraInfo').value.trim();

      const goalkeepers = document.getElementById('goalkeepers').value.split('\n');
      const defenders = document.getElementById('defenders').value.split('\n');
      const midfielders = document.getElementById('midfielders').value.split('\n');
      const forwards = document.getElementById('forwards').value.split('\n');

      const img = new Image();
      img.onload = function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

        // Заголовки
        ctx.font = 'bold 48px Arial';
        ctx.fillStyle = '#FFFF00';
        ctx.textAlign = 'center';
        ctx.fillText(`${ourTeam} vs ${opponent}`, canvas.width / 2, 100);

        ctx.font = 'bold 36px Arial';
        ctx.fillStyle = '#FFFFFF';
        ctx.fillText(tournament, canvas.width / 2, 170);
        ctx.fillText(`Дата: ${matchDate}`, canvas.width / 2, 220);

        if (extraInfo !== '') {
          ctx.fillText(extraInfo, canvas.width / 2, 270);
        }

        // Игроки
        function drawPlayers(players, startX, startY) {
          ctx.font = 'bold 32px Arial';
          ctx.fillStyle = '#FFFFFF';
          ctx.textAlign = 'left';

          let y = startY;
          const lineHeight = 42;
          players.forEach(player => {
            if (player.trim() !== '') {
              ctx.fillText(player.trim(), startX, y);
              y += lineHeight;
            }
          });
        }

        drawPlayers(goalkeepers, 378, 395);
        drawPlayers(defenders, 378, 520);
        drawPlayers(midfielders, 378, 700);
        drawPlayers(forwards, 378, 880);

        const dataURL = canvas.toDataURL('image/png');
        const downloadLink = document.getElementById('downloadLink');
        downloadLink.href = dataURL;
        downloadLink.style.display = 'inline-block';
      };

      img.src = '/img/shablon.png?nocache=' + Date.now();
    });
  </script>

</body>
</html>