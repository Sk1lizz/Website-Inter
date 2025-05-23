document.addEventListener("DOMContentLoaded", function () {
  // Обработчик клика по иконке, открытие/закрытие меню
  document.getElementById("icon").addEventListener("click", function () {
    document.querySelector(".bottom_parth").classList.toggle("open");

    var iconSpans = document.querySelectorAll('#icon span');
    for (var i = 0; i < iconSpans.length; i++) {
      iconSpans[i].classList.toggle('open');
    }
  });

  // Обработчик клика по иконке в меню для открытия/закрытия подменю
  document.querySelector('.menu #icon').addEventListener('click', function () {
    var submenu = document.querySelector('.submenu');
    submenu.classList.toggle('open');

    window.onscroll = function () {
      submenu.classList.remove('open');
    }
  });

  // Функция показа/скрытия меню при скролле страницы
  function toggleMenuVisibility() {
    var pageHeight = window.pageYOffset || document.documentElement.scrollTop;
    var menu = document.querySelector('header');

    if (pageHeight > 100) {
      if (!menu.classList.contains('hide')) {
        menu.classList.add('hide');
      }
    } else {
      menu.classList.remove('hide');
    }
  }
  window.addEventListener('scroll', toggleMenuVisibility);

  // Загрузка дней рождения с сервера и вставка в DOM
  fetch('/api/birthdays')
    .then(res => res.json())
    .then(data => {
      const container = document.querySelector('.birthday_players');

      // Удаляем только блоки игроков, не трогая заголовок
      const playerBlocks = container.querySelectorAll('.block_birthday_players');
      playerBlocks.forEach(block => block.remove());

      // Добавляем новые блоки с игроками
      data.forEach(player => {
        const block = document.createElement('div');
        block.classList.add('block_birthday_players');
        block.innerHTML = `
          <div class="name_player">${player.name}</div>
          <div class="data_title">${player.birthday}</div>
          <div class="timer">Через ${player.days_left} дн.</div>
        `;
        container.appendChild(block);
      });
    })
    .catch(err => console.error('Ошибка загрузки дней рождения:', err));
});

app.get('/admin', (req, res) => {
  res.sendFile(path.join(__dirname, 'dist', 'views', 'admin.html'));
});

