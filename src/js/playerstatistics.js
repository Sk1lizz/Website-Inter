
document.addEventListener('DOMContentLoaded', function () {

    // статистика текущего сезона
    const statistics2024 = [
        // Номер, фамилия, матчей, голов забито, ассистов, матчей на 0, голов пропущено
        { number: "1", name: "Исаев", matches: "9", goals: "0", assist: "0", zeromatch: "1", lostgoals: "33", team: "proand8x8" },
        { number: "2", name: "Хакимов", matches: "12", goals: "10", assist: "3", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "3", name: "Фирдавс", matches: "12", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "4", name: "Малышев", matches: "2", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "5", name: "Волокитин", matches: "6", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "6", name: "Касулин", matches: "12", goals: "2", assist: "2", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "7", name: "Тапчан", matches: "9", goals: "0", assist: "5", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "8", name: "Швамбергер", matches: "1", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "proand8x8" },
        { number: "9", name: "Белоножкин", matches: "26", goals: "5", assist: "11", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "10", name: "Костич", matches: "22", goals: "9", assist: "5", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "11", name: "Полевой", matches: "5", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "12", name: "Шаропов", matches: "11", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "14", name: "Голуб", matches: "10", goals: "9", assist: "6", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "15", name: "Королев", matches: "5", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "17", name: "Петрищев", matches: "14", goals: "2", assist: "4", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "18", name: "Губский", matches: "0", goals: "0", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "19", name: "Агатов", matches: "0", goals: "0", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "20", name: "Власов", matches: "24", goals: "3", assist: "8", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "21", name: "Саидов", matches: "1", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "22", name: "Макарчев", matches: "10", goals: "5", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "23", name: "Бубнов", matches: "4", goals: "3", assist: "3", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "24", name: "Лешанков", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "25", name: "Грумынский", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "26", name: "Голованов", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "28", name: "Ларин", matches: "2", goals: "0", assist: "0", zeromatch: "0", lostgoals: "5", team: "pro" },
        { number: "29", name: "Свирщевский", matches: "3", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "30", name: "Салимгареев", matches: "3", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "31", name: "Дудочкин", matches: "2", goals: "0", assist: "0", zeromatch: "0", lostgoals: "3", team: "8x8" },
        { number: "32", name: "Волконский", matches: "24", goals: "12", assist: "6", zeromatch: "0", lostgoals: "0", team: "pro", },
        { number: "33", name: "Пашаев", matches: "4", goals: "1", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "35", name: "Сыпченко", matches: "13", goals: "0", assist: "1", zeromatch: "2", lostgoals: "32", team: "pro" },
        { number: "37", name: "Шеин", matches: "21", goals: "5", assist: "7", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "41", name: "Синицын", matches: "19", goals: "0", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "47", name: "Языков", matches: "18", goals: "26", assist: "13", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "50", name: "Штепа", matches: "2", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "52", name: "Савельев", matches: "7", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "55", name: "Нестор", matches: "3", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "63", name: "Скворцов", matches: "23", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "64", name: "Беров", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "66", name: "Королев", matches: "11", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "69", name: "Долгов", matches: "3", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "72", name: "Абянов", matches: "8", goals: "2", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "74", name: "Котов", matches: "5", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "75", name: "Матвеев", matches: "22", goals: "5", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "77", name: "Иванов", matches: "7", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "81", name: "Мытько", matches: "10", goals: "0", assist: "0", zeromatch: "1b", lostgoals: "29", team: "pro" },
        { number: "86", name: "Гусев", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "89", name: "Пожидаев", matches: "3", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "90", name: "Юсуф", matches: "2", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "99", name: "Батуев", matches: "3", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        // Добавьте остальных игроков
    ];


    // Статистика за все время 
    const statisticsall = [
        // Номер, фамилия, матчей, голов забито, ассистов, матчей на 0, голов пропущено

        { number: "1", name: "Исаев", matches: "87", goals: "0", assist: "1", zeromatch: "10", lostgoals: "237", team: "proand8x8" },
        { number: "2", name: "Хакимов", matches: "12", goals: "10", assist: "3", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "3", name: "Фирдавс", matches: "12", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "4", name: "Малышев", matches: "21", goals: "1", assist: "6", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "5", name: "Волокитин", matches: "64", goals: "24", assist: "13", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "6", name: "Касулин", matches: "12", goals: "2", assist: "2", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "7", name: "Тапчан", matches: "295", goals: "164", assist: "47", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "8", name: "Швамбергер", matches: "7", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "proand8x8" },
        { number: "9", name: "Белоножкин", matches: "477", goals: "154", assist: "105", zeromatch: "0", lostgoals: "0", team: "proand8x8" },
        { number: "10", name: "Костич", matches: "51", goals: "14", assist: "5", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "11", name: "Полевой", matches: "5", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "12", name: "Шаропов", matches: "11", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "14", name: "Голуб", matches: "26", goals: "22", assist: "17", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "15", name: "Королев", matches: "5", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "17", name: "Петрищев", matches: "38", goals: "3", assist: "6", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "18", name: "Губский", matches: "235", goals: "124", assist: "19", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "19", name: "Агатов", matches: "15", goals: "1", assist: "8", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "20", name: "Власов", matches: "236", goals: "20", assist: "12", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "21", name: "Саидов", matches: "1", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "22", name: "Макарчев", matches: "59", goals: "49", assist: "24", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "23", name: "Бубнов", matches: "5", goals: "3", assist: "3", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "24", name: "Лешанков", matches: "90", goals: "8", assist: "9", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "25", name: "Грумынский", matches: "8", goals: "0", assist: "3", zeromatch: "2", lostgoals: "0", team: "pro" },
        { number: "26", name: "Голованов", matches: "32", goals: "0", assist: "3", zeromatch: "2", lostgoals: "123", team: "pro" },
        { number: "28", name: "Ларин", matches: "27", goals: "0", assist: "0", zeromatch: "4", lostgoals: "57", team: "pro" },
        { number: "29", name: "Свирщевский", matches: "3", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "30", name: "Салимгареев", matches: "23", goals: "0", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "31", name: "Дудочкин", matches: "2", goals: "0", assist: "0", zeromatch: "0", lostgoals: "3", team: "8x8" },
        { number: "32", name: "Волконский", matches: "42", goals: "14", assist: "7", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "33", name: "Пашаев", matches: "14", goals: "4", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "35", name: "Сыпченко", matches: "13", goals: "0", assist: "1", zeromatch: "2", lostgoals: "32", team: "pro" },
        { number: "37", name: "Шеин", matches: "154", goals: "33", assist: "37", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "41", name: "Синицын", matches: "36", goals: "0", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "47", name: "Языков", matches: "182", goals: "311", assist: "129", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "50", name: "Штепа", matches: "2", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "52", name: "Савельев", matches: "7", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "55", name: "Нестор", matches: "40", goals: "0", assist: "4", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "63", name: "Скворцов", matches: "104", goals: "2", assist: "9", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "64", name: "Беров", matches: "39", goals: "39", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "66", name: "Королев", matches: "11", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "69", name: "Долгов", matches: "66", goals: "14", assist: "10", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "72", name: "Абянов", matches: "13", goals: "6", assist: "4", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "74", name: "Котов", matches: "16", goals: "1", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "75", name: "Матвеев", matches: "22", goals: "5", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "77", name: "Иванов", matches: "7", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "81", name: "Мытько", matches: "19", goals: "0", assist: "0", zeromatch: "1b", lostgoals: "66", team: "pro" },
        { number: "86", name: "Гусев", matches: "10", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "89", name: "Пожидаев", matches: "3", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "90", name: "Юсуф", matches: "18", goals: "1", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "99", name: "Батуев", matches: "3", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        // Добавьте остальных игроков
    ];

    // АРХИВ

    const statisticsarchive = [

    ];

    // Получаем элемент с номером игрока
    const playerNumber = document.querySelector('.number').textContent;

    // Получаем элементы статистики за все время и за текущий сезон
    const statisticplayersall = document.querySelector('.statisticplayersall');
    const statisticthisyears = document.querySelector('.statisticthisyears');

    // Находим соответствующего игрока в статистике за все время
    const playerStatsAll = statisticsall.find(playerStat => playerStat.number === playerNumber);

    // Находим соответствующего игрока в статистике текущего сезона
    const playerStatsThisYear = statistics2024.find(playerStat => playerStat.number === playerNumber);

    // Функция для форматирования чисел с округлением до двух знаков после запятой
    const formatNumber = (number) => {
        return Math.round(number * 100) / 100;
    };

    // Если статистика за все время найдена
    if (playerStatsAll) {
        // Передаем данные в соответствующие блоки
        statisticplayersall.querySelector('.matchesall').textContent = playerStatsAll.matches;
        statisticplayersall.querySelector('.goalall').textContent = playerStatsAll.goals;
        statisticplayersall.querySelector('.assistall').textContent = playerStatsAll.assist;
        statisticplayersall.querySelector('.zeromatchall').textContent = playerStatsAll.zeromatch !== "0" ? playerStatsAll.zeromatch : "0";
        statisticplayersall.querySelector('.goallostall').textContent = playerStatsAll.lostgoals !== "0" ? playerStatsAll.lostgoals : "0";

        // Вычисляем средние значения
        const goalAverageAll = formatNumber(playerStatsAll.goals / playerStatsAll.matches);
        const assistAverageAll = formatNumber(playerStatsAll.assist / playerStatsAll.matches);
        const assistGoalsAll = formatNumber(parseFloat(playerStatsAll.goals) + parseFloat(playerStatsAll.assist));

        // Проверяем, не является ли среднее значение `NaN`
        if (isNaN(goalAverageAll)) {
            goalAverageAll = 0;
        }
        if (isNaN(assistAverageAll)) {
            assistAverageAll = 0;
        }
        if (isNaN(assistGoalsAll)) {
            assistGoalsAll = 0;
        }

        // Передаем средние значения в соответствующие блоки
        statisticplayersall.querySelector('.goalallOnaverage').textContent = goalAverageAll;
        statisticplayersall.querySelector('.assistallOnaverage').textContent = assistAverageAll;
        statisticplayersall.querySelector('.assistgoalsall').textContent = assistGoalsAll;
    }

    // Если статистика текущего сезона найдена
    if (playerStatsThisYear) {
        // Передаем данные в соответствующие блоки
        statisticthisyears.querySelector('.matches').textContent = playerStatsThisYear.matches;
        statisticthisyears.querySelector('.goal').textContent = playerStatsThisYear.goals;
        statisticthisyears.querySelector('.assist').textContent = playerStatsThisYear.assist;
        statisticthisyears.querySelector('.zeromatch').textContent = playerStatsThisYear.zeromatch !== "0" ? playerStatsThisYear.zeromatch : "0";
        statisticthisyears.querySelector('.goallost').textContent = playerStatsThisYear.lostgoals !== "0" ? playerStatsThisYear.lostgoals : "0";


        // Вычисляем средние значения
        const goalAverageThisYear = formatNumber(playerStatsThisYear.goals / playerStatsThisYear.matches);
        const assistAverageThisYear = formatNumber(playerStatsThisYear.assist / playerStatsThisYear.matches);
        const assistGoalsThisYear = formatNumber(parseFloat(playerStatsThisYear.goals) + parseFloat(playerStatsThisYear.assist));

        // Проверяем, не является ли среднее значение `NaN`
        if (isNaN(goalAverageThisYear)) {
            goalAverageThisYear = 0;
        }
        if (isNaN(assistAverageThisYear)) {
            assistAverageThisYear = 0;
        }
        if (isNaN(assistGoalsThisYear)) {
            assistGoalsThisYear = 0;
        }

        // Передаем средние значения в соответствующие блоки
        statisticthisyears.querySelector('.goalOnaverage').textContent = goalAverageThisYear;
        statisticthisyears.querySelector('.assistOnaverage').textContent = assistAverageThisYear;
        statisticthisyears.querySelector('.assistgoals').textContent = assistGoalsThisYear;
    }

});