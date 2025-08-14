document.addEventListener("DOMContentLoaded", function () {
  // Обработчик клика по иконке основного меню
  const mainIcon = document.getElementById("icon");
  if (mainIcon) {
    mainIcon.addEventListener("click", function () {
      const bottomParth = document.querySelector(".bottom_parth");
      if (bottomParth) {
        bottomParth.classList.toggle("open");

        const iconSpans = mainIcon.querySelectorAll("span");
        iconSpans.forEach((span) => span.classList.toggle("open"));
      }
    });
  } else {
    console.warn("Элемент с id='icon' не найден");
  }

  // Обработчик клика по иконке подменю (используем другой id, например, submenu-icon)
  const submenuIcon = document.querySelector(".menu #submenu-icon");
  if (submenuIcon) {
    submenuIcon.addEventListener("click", function () {
      const submenu = document.querySelector(".submenu");
      if (submenu) {
        submenu.classList.toggle("open");

        // Закрытие подменю при скролле
        window.onscroll = function () {
          submenu.classList.remove("open");
        };
      } else {
        console.warn("Элемент с классом 'submenu' не найден");
      }
    });
  } else {
    console.warn("Элемент с id='submenu-icon' в .menu не найден");
  }

  // Функция показа/скрытия меню при скролле страницы
  function toggleMenuVisibility() {
    const pageHeight = window.pageYOffset || document.documentElement.scrollTop;
    const menu = document.querySelector("header");
    if (menu) {
      if (pageHeight > 100) {
        menu.classList.add("hide");
      } else {
        menu.classList.remove("hide");
      }
    } else {
      console.warn("Элемент header не найден");
    }
  }

  window.addEventListener("scroll", toggleMenuVisibility);
});