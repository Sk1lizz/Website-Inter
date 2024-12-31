document.addEventListener('DOMContentLoaded', function () {

    // статистика текущего сезона
    const statistics2024 = [
        // Номер, фамилия, матчей, голов забито, ассистов, матчей на 0, голов пропущено
        { number: "2", name: "Хакимов", matches: "19", goals: "25", assist: "8", zeromatch: "0", lostgoals: "0", team: "8x8"},
        { number: "3", name: "Фирдавс", matches: "15", goals: "0", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "5", name: "Волокитин", matches: "14", goals: "1", assist:"1", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "6", name: "Касулин", matches: "19", goals: "3", assist: "3", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "7", name: "Тапчан", matches: "24", goals: "2", assist: "8", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "8", name: "Нарватов", matches: "3", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "9", name: "Белоножкин", matches: "52", goals: "11", assist: "19", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "10", name: "Костич", matches: "30", goals: "10", assist: "6", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "11", name: "Полевой", matches: "15", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "12", name: "Шаропов", matches: "27", goals: "11", assist: "4", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "14", name: "Голуб", matches: "19", goals: "12", assist: "10", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "16", name: "Степанян", matches: "3", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", },
        { number: "17", name: "Петрищев", matches: "27", goals: "3", assist: "5", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "18", name: "Губский", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "19", name: "Агатов", matches: "0", goals: "0", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "20", name: "Ларин И.", matches: "11", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "21", name: "Макаров", matches: "3", goals: "2", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "22", name: "Макарчев", matches: "10", goals: "7", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "23", name: "Бубнов", matches: "19", goals: "8", assist: "6", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "24", name: "Лешанков", matches: "9", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "25", name: "Матвеев", matches: "49", goals: "6", assist: "6", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "26", name: "Голованов", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "27", name: "Филимонов", matches: "6", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "28", name: "Ларин Д.", matches: "2", goals: "0", assist: "0", zeromatch: "0", lostgoals: "5", team: "pro" },
        { number: "29", name: "Свирщевский", matches: "15", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "30", name: "Салимгареев", matches: "4", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "31", name: "Дудочкин", matches: "4", goals: "0", assist: "0", zeromatch: "0", lostgoals: "3", team: "8x8" },
        { number: "32", name: "Волконский", matches: "45", goals: "19", assist: "12", zeromatch: "0", lostgoals: "0", team: "pro", },
        { number: "33", name: "Кравченко", matches: "2", goals: "1", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "35", name: "Сыпченко", matches: "29", goals: "0", assist: "1", zeromatch: "2", lostgoals: "52", team: "pro" },
        { number: "37", name: "Шеин", matches: "37", goals: "5", assist: "8", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "41", name: "Синицын", matches: "26", goals: "1", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "42", name: "Белов", matches: "7", goals: "0", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "47", name: "Языков", matches: "43", goals: "64", assist: "24", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "49", name: "Бутусов", matches: "5", goals: "0", assist: "0", zeromatch: "1", lostgoals: "0", team: "pro" },
        { number: "50", name: "Штепа", matches: "16", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "52", name: "Мкома", matches: "5", goals: "0", assist: "0", zeromatch: "0", lostgoals: "10", team: "pro" },
        { number: "55", name: "Нестор", matches: "3", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "63", name: "Скворцов", matches: "38", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "64", name: "Беров", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "66", name: "Королев", matches: "26", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "69", name: "Долгов", matches: "3", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "71", name: "Иванов", matches: "1", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "72", name: "Абянов", matches: "22", goals: "12", assist: "5", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "74", name: "Котов", matches: "17", goals: "2", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "77", name: "Иванов", matches: "16", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "81", name: "Мытько", matches: "19", goals: "0", assist: "0", zeromatch: "1b", lostgoals: "43", team: "pro" },
        { number: "86", name: "Гусев", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "87", name: "Данишевский", matches: "12", goals: "3", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "88", name: "Стребков", matches: "6", goals: "0", assist: "0", zeromatch: "0", lostgoals: "11", team: "pro", time_in: "01.08.2024" },
        { number: "89", name: "Пожидаев", matches: "5", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "90", name: "Юсуф", matches: "5", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "101", name: "Грумынский", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        // Добавьте остальных игроков
    ];


    // Статистика за все время 
    const statisticsall = [
        // Номер, фамилия, матчей, голов забито, ассистов, матчей на 0, голов пропущено
        { number: "coach", name: "Пешехонов", matches: "480", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.05.2013" },
        { number: "2", name: "Хакимов", matches: "19", goals: "23", assist: "8", zeromatch: "0", lostgoals: "0", team: "8x8", time_in: "" },
        { number: "5", name: "Волокитин", matches: "72", goals: "24", assist: "13", zeromatch: "0", lostgoals: "0", team: "8x8", time_in: "01.09.2020" },
        { number: "6", name: "Касулин", matches: "18", goals: "3", assist: "4", zeromatch: "0", lostgoals: "0", team: "8x8", time_in: "01.03.2024" },
        { number: "7", name: "Тапчан", matches: "309", goals: "166", assist: "50", zeromatch: "0", lostgoals: "0", team: "8x8", time_in: "01.08.2015" },
        { number: "8", name: "Нарватов", matches: "61", goals: "25", assist: "13", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.07.2021" },
        { number: "9", name: "Белоножкин", matches: "508", goals: "161", assist: "112", zeromatch: "0", lostgoals: "0", team: "proand8x8", time_in: "01.07.2014" },
        { number: "11", name: "Полевой", matches: "15", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.04.2024" },
        { number: "12", name: "Шаропов", matches: "27", goals: "11", assist: "4", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.07.2023" },
        { number: "14", name: "Голуб", matches: "235", goals: "25", assist: "21", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.02.2023" },
        { number: "NaN", name: "Петросян", matches: "1", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "8x8", time_in: "" },
        { number: "16", name: "Степанян", matches: "3", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.08.2024" },
        { number: "17", name: "Петрищев", matches: "52", goals: "4", assist: "7", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.03.2023" },
        { number: "18", name: "Губский", matches: "235", goals: "124", assist: "19", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.07.2014" },
        { number: "19", name: "Агатов", matches: "15", goals: "1", assist: "8", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.11.2022" },
        { number: "20", name: "Ларин И.", matches: "11", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "21", name: "Макаров", matches: "4", goals: "2", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.08.2024" },
        { number: "22", name: "Макарчев", matches: "59", goals: "51", assist: "24", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.10.2022" },
        { number: "23", name: "Бубнов", matches: "19", goals: "8", assist: "6", zeromatch: "0", lostgoals: "0", team: "8x8", time_in: "01.05.2024" },
        { number: "24", name: "Лешанков", matches: "98", goals: "8", assist: "9", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.10.2019" },
        { number: "25", name: "Матвеев", matches: "49", goals: "6", assist: "6", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.01.2024" },
        { number: "26", name: "Голованов", matches: "32", goals: "0", assist: "3", zeromatch: "2", lostgoals: "123", team: "pro", time_in: "01.10.2021" },
        { number: "27", name: "Филимонов", matches: "6", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.07.2024" },
        { number: "28", name: "Ларин", matches: "27", goals: "0", assist: "0", zeromatch: "4", lostgoals: "57", team: "pro", time_in: "01.07.2021" },
        { number: "29", name: "Свирщевский", matches: "15   ", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.06.2024" },
        { number: "30", name: "Салимгареев", matches: "23", goals: "0", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.02.2022" },
        { number: "NaN", name: "Дудочкин", matches: "3", goals: "0", assist: "0", zeromatch: "0", lostgoals: "3", team: "8x8", time_in: "" },
        { number: "32", name: "Волконский", matches: "67", goals: "20", assist: "13", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.04.2023" },
        { number: "35", name: "Сыпченко", matches: "28", goals: "0", assist: "1", zeromatch: "2", lostgoals: "48", team: "pro", time_in: "01.01.2024" },
        { number: "37", name: "Шеин", matches: "170", goals: "33", assist: "38", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.10.2020" },
        { number: "41", name: "Синицын", matches: "43", goals: "1", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.12.2022" },
        { number: "42", name: "Белов", matches: "116", goals: "40", assist: "35", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.06.2020" },
        { number: "47", name: "Языков", matches: "205", goals: "346", assist: "140", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.10.2018" },
        { number: "49", name: "Бутусов", matches: "5", goals: "0", assist: "0", zeromatch: "1", lostgoals: "0", team: "pro", time_in: "01.08.2024" },
        { number: "50", name: "Штепа", matches: "17", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.04.2024" },
        { number: "52", name: "Мкома", matches: "5", goals: "0", assist: "0", zeromatch: "0", lostgoals: "10", team: "pro", time_in: "01.07.2024" },
        { number: "55", name: "Нестор", matches: "40", goals: "0", assist: "8", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.12.2022" },
        { number: "63", name: "Скворцов", matches: "119", goals: "2", assist: "9", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.10.2019" },
        { number: "64", name: "Беров", matches: "39", goals: "39", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.12.2020" },
        { number: "66", name: "Королев", matches: "26", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.01.2024" },
        { number: "71", name: "Иванов", matches: "1", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.09.2024" },
        { number: "72", name: "Абянов", matches: "26", goals: "9", assist: "6", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.11.2023" },
        { number: "74", name: "Котов", matches: "28", goals: "3", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.10.2023" },
        { number: "77", name: "Иванов Никита", matches: "16", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.02.2024" },
        { number: "81", name: "Мытько", matches: "28", goals: "0", assist: "0", zeromatch: "1b", lostgoals: "80", team: "pro", time_in: "01.07.2023" },
        { number: "86", name: "Гусев", matches: "10", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.02.2023" },
        { number: "87", name: "Данишевский", matches: "12", goals: "3", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.08.2024" },
        { number: "88", name: "Стребков", matches: "6", goals: "0", assist: "0", zeromatch: "0", lostgoals: "11", team: "pro", time_in: "01.08.2024" },
        { number: "90", name: "Юсуф", matches: "20", goals: "1", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.07.2023" },
        { number: "101", name: "Грумынский", matches: "8", goals: "0", assist: "3", zeromatch: "2", lostgoals: "0", team: "pro", time_in: "01.03.2023" },
        // Добавьте остальных игроков
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


    // Функция для преобразования даты в формат YYYY-MM-DD
    function convertToISODate(dateStr) {
        const [day, month, year] = dateStr.split('.').map(Number);
        return `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    }

    // Функция для расчета разницы между двумя датами
    function calculateTimeInTeam(startDate) {
        const now = new Date();
        const start = new Date(convertToISODate(startDate));

        let years = now.getFullYear() - start.getFullYear();
        let months = now.getMonth() - start.getMonth();

        // Проверка на дни и корректировка месяцев
        if (now.getDate() < start.getDate()) {
            months--; // Если текущий день меньше дня старта, вычитаем месяц
        }

        // Если месяцев получилось меньше 0, корректируем количество лет и месяцев
        if (months < 0) {
            years--;
            months += 12;
        }

        // Уточняем разницу в годах и месяцах
        let totalMonths = years * 12 + months;
        let resultYears = Math.floor(totalMonths / 12);
        let resultMonths = totalMonths % 12;

        // Возвращаем результат
        return { years: resultYears, months: resultMonths };
    }

    // Пример использования
    const { years, months } = calculateTimeInTeam("30.05.2013");
    console.log(`${years} лет и ${months} месяцев`);

    // Функция для создания общего списка игроков
    function updateGeneralList() {
        const timeInTeamList = document.querySelector(".time_in_team_list");
        timeInTeamList.innerHTML = "";  // Очищаем список перед добавлением данных

        // Собираем игроков с датой прихода в команду
        const playersWithTimeIn = statisticsall
            .filter(player => player.time_in)  // Оставляем только тех, у кого указано время прихода в команду
            .map(player => {
                const { years, months } = calculateTimeInTeam(player.time_in);
                return { ...player, years, months };
            })
            .sort((a, b) => {  // Сортируем игроков по времени в команде от большего к меньшему
                if (b.years === a.years) {
                    return b.months - a.months;
                } else {
                    return b.years - a.years;
                }
            });

        let index = 1;

        // Добавляем игроков в список
        playersWithTimeIn.forEach(player => {
            const listItem = document.createElement("li");

            // Юбилей: если месяцев = 0 и лет >= 1
            if (player.months === 0 && player.years >= 1) {
                listItem.classList.add("anniversary");
            }

            // Создаем элементы: номер, имя и время в команде
            const playerNumber = document.createElement("span");
            playerNumber.classList.add("player-number");
            playerNumber.textContent = index;

            const playerName = document.createElement("span");
            playerName.classList.add("player-name");
            playerName.textContent = player.name;

            const playerTime = document.createElement("span");
            playerTime.classList.add("time-in-team");

            // Формируем текст времени в команде
            let timeInTeam = "";
            if (player.years > 0) timeInTeam += player.years + " " + (player.years === 1 ? "год" : player.years < 5 ? "года" : "лет");
            if (player.months > 0) timeInTeam += " " + player.months + " " + (player.months === 1 ? "месяц" : player.months < 5 ? "месяца" : "месяцев");

            playerTime.textContent = timeInTeam.trim() || "Менее месяца";

            // Добавляем элементы в элемент списка
            listItem.appendChild(playerNumber);
            listItem.appendChild(playerName);
            listItem.appendChild(playerTime);

            timeInTeamList.appendChild(listItem);
            index++;
        });
    }

    // Вызов функции для обновления общего списка
    updateGeneralList();

    // Цвет для подсветки, если количество матчей кратно 100
    const highlightColor = "#2D62B5";

    // Функция для обновления списка матчей за команду
    function updateGameInTeamList() {
        const gameInTeamList = document.querySelector(".game_in_team_list");
        gameInTeamList.innerHTML = ""; // Очищаем список перед добавлением данных

        // Сортируем игроков по количеству матчей (в порядке убывания)
        const sortedPlayers = statisticsall
            .map(player => ({ ...player, matches: Number(player.matches.trim()) })) // Убираем лишние пробелы и преобразуем строку в число
            .sort((a, b) => b.matches - a.matches);

        // Добавляем игроков в список
        sortedPlayers.forEach((player, index) => {
            const listItem = document.createElement("li");
            listItem.classList.add("player-item");

            // Подсветка, если количество матчей юбилейное (100, 200, 300 и т.д.)
            if (player.matches > 0 && player.matches % 100 === 0) {
                listItem.style.backgroundColor = highlightColor; // Задаем синий цвет для юбилейных матчей
            }

            // Создаем элементы: порядковый номер, имя и количество матчей
            const playerNumber = document.createElement("span");
            playerNumber.classList.add("player-number");
            playerNumber.textContent = index + 1;

            const playerName = document.createElement("span");
            playerName.classList.add("player-name");
            playerName.textContent = player.name;

            const playerMatches = document.createElement("span");
            playerMatches.classList.add("player-matches");
            playerMatches.textContent = player.matches + " матчей";

            // Добавляем элементы в элемент списка
            listItem.appendChild(playerNumber);
            listItem.appendChild(playerName);
            listItem.appendChild(playerMatches);

            gameInTeamList.appendChild(listItem);
        });
    }

    // Вызов функции для обновления списка
    updateGameInTeamList();

});
