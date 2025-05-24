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

});

