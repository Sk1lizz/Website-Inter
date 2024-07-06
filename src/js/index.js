document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("icon").addEventListener("click", function () {
        document.querySelector(".bottom_parth").classList.toggle("open");
        // Получаем все дочерние элементы элемента с id 'icon'
        var iconSpans = document.querySelectorAll('#icon span');

        // Проходимся по элементам массива и добавляем или удаляем класс 'open'
        for (var i = 0; i < iconSpans.length; i++) {
            iconSpans[i].classList.toggle('open');
        }
    })
})

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

document.querySelector('.menu #icon').addEventListener('click', function () {
    var submenu = document.querySelector('.submenu');
    submenu.classList.toggle('open');

    window.onscroll = function () {
        submenu.classList.remove('open');
    }
});

