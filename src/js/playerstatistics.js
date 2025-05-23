document.addEventListener('DOMContentLoaded', function () {

    // статистика текущего сезона
    const statistics2025 = [
        // Номер, фамилия, матчей, голов забито, ассистов, матчей на 0, голов пропущено
        { number: "2", name: "Хакимов", matches: "16", goals: "22", assist: "4", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "3", name: "Самарин", matches: "8", goals: "1", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "4", name: "Якушин", matches: "10", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "5", name: "Волокитин", matches: "5", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "6", name: "Касулин", matches: "9", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "7", name: "Тапчан", matches: "15", goals: "1", assist: "7", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "8", name: "Нарватов", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "9", name: "Белоножкин", matches: "19", goals: "6", assist: "4", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "10", name: "Костич", matches: "14", goals: "5", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "11", name: "Полевой", matches: "2", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "12", name: "Шаропов", matches: "10", goals: "3", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "14", name: "Голуб", matches: "1", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "16", name: "Степанян", matches: "4", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "17", name: "Шеин", matches: "10", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "18", name: "Корнилов", matches: "5", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "19", name: "Агатов", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "20", name: "Ларин И.", matches: "1", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "21", name: "Макаров", matches: "11", goals: "2", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "22", name: "Макарчев", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "23", name: "Бубнов", matches: "5", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "24", name: "Лешанков", matches: "12", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "25", name: "Матвеев", matches: "13", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "26", name: "Голованов", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "27", name: "Портнов", matches: "4", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "28", name: "Ларин Д.", matches: "2", goals: "0", assist: "0", zeromatch: "0", lostgoals: "4", team: "pro" },
        { number: "29", name: "Свирщевский", matches: "12", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "30", name: "Салимгареев", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "31", name: "Дудочкин", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "32", name: "Волконский", matches: "6", goals: "3", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "33", name: "Долгополов", matches: "16", goals: "5", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "35", name: "Сыпченко", matches: "5", goals: "0", assist: "0", zeromatch: "0", lostgoals: "14", team: "pro" },
        { number: "37", name: "Иванов", matches: "9", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "41", name: "Синицын", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "42", name: "Изосимов", matches: "5", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "45", name: "Амири", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "47", name: "Языков", matches: "16", goals: "12", assist: "11", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "49", name: "Бутусов", matches: "6", goals: "0", assist: "0", zeromatch: "0", lostgoals: "17", team: "pro" },
        { number: "50", name: "Штепа", matches: "6", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "51", name: "Кардаш", matches: "10", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "52", name: "Теплов", matches: "6", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "55", name: "Нестор", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "63", name: "Скворцов", matches: "12", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "64", name: "Беров", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "65", name: "Шевченко", matches: "1", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "66", name: "Королев", matches: "12", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "69", name: "Шамсутдинов", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "70", name: "Тошев", matches: "13", goals: "3", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "71", name: "Гусев", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "73", name: "Белов", matches: "11", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "74", name: "Котов", matches: "9", goals: "1", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "75", name: "Дюков", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "77", name: "Палий", matches: "1", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "81", name: "Мытько", matches: "16", goals: "0", assist: "0", zeromatch: "3", lostgoals: "36", team: "pro" },
        { number: "86", name: "Гусев", matches: "7", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "87", name: "Данишевский", matches: "2", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "88", name: "Стребков", matches: "6", goals: "0", assist: "0", zeromatch: "0", lostgoals: "24", team: "pro" },
        { number: "89", name: "Пожидаев", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "90", name: "Корчагин", matches: "7", goals: "4", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "95", name: "Сокирко", matches: "5", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "97", name: "Москалев", matches: "2", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "99", name: "Петров", matches: "8", goals: "0", assist: "2", zeromatch: "0", lostgoals: "0", team: "8x8" },
        { number: "101", name: "Грумынский", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro" },
        // Добавьте остальных игроков Долгополов 
    ];


    // Статистика за все время 
    let statisticsall = [
        // Номер, фамилия, матчей, голов забито, ассистов, матчей на 0, голов пропущено
        { number: "coach", name: "Пешехонов", matches: "540", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.05.2013" },
        { number: "2", name: "Хакимов", matches: "19", goals: "23", assist: "8", zeromatch: "0", lostgoals: "0", team: "8x8", time_in: "" },
        { number: "4", name: "Якушин", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.01.2025" },
        { number: "5", name: "Волокитин", matches: "72", goals: "30", assist: "17", zeromatch: "0", lostgoals: "0", team: "8x8", time_in: "01.09.2020" },
        { number: "6", name: "Касулин", matches: "18", goals: "3", assist: "4", zeromatch: "0", lostgoals: "0", team: "8x8", time_in: "01.03.2024" },
        { number: "7", name: "Тапчан", matches: "309", goals: "166", assist: "50", zeromatch: "0", lostgoals: "0", team: "8x8", time_in: "01.08.2015" },
        { number: "8", name: "Нарватов", matches: "61", goals: "25", assist: "13", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.07.2021" },
        { number: "9", name: "Белоножкин", matches: "508", goals: "161", assist: "112", zeromatch: "0", lostgoals: "0", team: "proand8x8", time_in: "01.07.2014" },
        { number: "11", name: "Полевой", matches: "15", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.04.2024" },
        { number: "12", name: "Шаропов", matches: "27", goals: "11", assist: "4", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.07.2023" },
        { number: "14", name: "Голуб", matches: "44", goals: "25", assist: "21", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.02.2023" },
        { number: "NaN", name: "Петросян", matches: "1", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "8x8", time_in: "" },
        { number: "16", name: "Степанян", matches: "3", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.08.2024" },
        { number: "17", name: "Шеин", matches: "170", goals: "33", assist: "38", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.10.2020" },
        { number: "18", name: "Губский", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.07.2014" },
        { number: "19", name: "Агатов", matches: "15", goals: "1", assist: "8", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.11.2022" },
        { number: "21", name: "Макаров", matches: "4", goals: "2", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.08.2024" },
        { number: "22", name: "Макарчев", matches: "59", goals: "51", assist: "24", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.10.2022" },
        { number: "23", name: "Бубнов", matches: "19", goals: "8", assist: "6", zeromatch: "0", lostgoals: "0", team: "8x8", time_in: "01.05.2024" },
        { number: "24", name: "Лешанков", matches: "100", goals: "8", assist: "9", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.10.2019" },
        { number: "25", name: "Матвеев", matches: "49", goals: "6", assist: "6", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.01.2024" },
        { number: "26", name: "Голованов", matches: "32", goals: "0", assist: "3", zeromatch: "2", lostgoals: "123", team: "pro", time_in: "01.10.2021" },
        { number: "27", name: "Филимонов", matches: "6", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.07.2024" },
        { number: "28", name: "Ларин", matches: "27", goals: "0", assist: "0", zeromatch: "4", lostgoals: "61", team: "pro", time_in: "01.07.2021" },
        { number: "29", name: "Свирщевский", matches: "15", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.06.2024" },
        { number: "30", name: "Салимгареев", matches: "23", goals: "0", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.02.2022" },
        { number: "NaN", name: "Дудочкин", matches: "3", goals: "0", assist: "0", zeromatch: "0", lostgoals: "3", team: "8x8", time_in: "" },
        { number: "32", name: "Волконский", matches: "67", goals: "20", assist: "13", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.04.2023" },
        { number: "35", name: "Сыпченко", matches: "28", goals: "0", assist: "1", zeromatch: "4", lostgoals: "62", team: "pro", time_in: "01.01.2024" },
        { number: "37", name: "Иванов Никита", matches: "16", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.02.2024" },
        { number: "41", name: "Синицын", matches: "43", goals: "1", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.12.2022" },
        { number: "42", name: "Изосимов", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.06.2020" },
        { number: "44", name: "Борзенков", matches: "55", goals: "1", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.01.2020" },
        { number: "45", name: "Амири", matches: "15", goals: "0", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro" },
        { number: "47", name: "Языков", matches: "205", goals: "346", assist: "140", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.10.2018" },
        { number: "49", name: "Бутусов", matches: "5", goals: "0", assist: "0", zeromatch: "1", lostgoals: "0", team: "pro", time_in: "01.08.2024" },
        { number: "50", name: "Штепа", matches: "17", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.04.2024" },
        { number: "51", name: "Кардаш", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.01.2025" },
        { number: "52", name: "Мкома", matches: "5", goals: "0", assist: "0", zeromatch: "0", lostgoals: "10", team: "pro", time_in: "01.07.2024" },
        { number: "55", name: "Нестор", matches: "40", goals: "0", assist: "8", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.12.2022" },
        { number: "63", name: "Скворцов", matches: "119", goals: "2", assist: "9", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.10.2019" },
        { number: "64", name: "Беров", matches: "39", goals: "39", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.12.2020" },
        { number: "66", name: "Королев", matches: "26", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.01.2024" },
        { number: "70", name: "Тошев", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.01.2025" },
        { number: "71", name: "Иванов", matches: "1", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.09.2024" },
        { number: "72", name: "Абянов", matches: "26", goals: "9", assist: "6", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.11.2023" },
        { number: "73", name: "Белов", matches: "116", goals: "40", assist: "35", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.06.2020" },
        { number: "74", name: "Котов", matches: "28", goals: "3", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.10.2023" },
        { number: "81", name: "Мытько", matches: "28", goals: "0", assist: "0", zeromatch: "3", lostgoals: "115", team: "pro", time_in: "01.07.2023" },
        { number: "86", name: "Гусев", matches: "10", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.02.2023" },
        { number: "87", name: "Данишевский", matches: "12", goals: "3", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.08.2024" },
        { number: "88", name: "Стребков", matches: "6", goals: "0", assist: "0", zeromatch: "0", lostgoals: "19", team: "pro", time_in: "01.08.2024" },
        { number: "90", name: "Юсуф", matches: "20", goals: "1", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.07.2023" },
        { number: "95", name: "Сокирко", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.01.2025" },
        { number: "97", name: "Москалев", matches: "1", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.01.2025" },
        { number: "99", name: "Петров", matches: "4", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "8x8", time_in: "01.01.2025" },
        { number: "101", name: "Грумынский", matches: "8", goals: "0", assist: "3", zeromatch: "2", lostgoals: "0", team: "pro", time_in: "01.03.2023" },
        // Добавьте остальных игроков
    ];

    // Шаг 1: Получить номер игрока со страницы
    const numberElement = document.querySelector('.player-info p:nth-child(1)');
    if (!numberElement) return;

    const number = numberElement.textContent.replace(/\D/g, ''); // Извлечь только цифры

    // Шаг 2: Найти объект игрока по номеру
    const playerStats = statisticsall.find(player => player.number === number);
    if (!playerStats) return;

    // Шаг 3: Получить текущую статистику из DOM
    const getNumber = (selector) => parseInt(document.querySelector(selector)?.textContent || '0');

    const currentMatches = getNumber('.stats-grid .number:nth-child(1)');
    const currentGoals = getNumber('.stats-grid .number:nth-child(2)');
    const currentAssists = getNumber('.stats-grid .number:nth-child(3)');
    const currentZeromatch = getNumber('.stats-grid .number:nth-child(6)');
    const currentLostGoals = getNumber('.stats-grid .number:nth-child(5)');

    // Шаг 4: Просуммировать статистику
    const totalMatches = currentMatches + parseInt(playerStats.matches);
    const totalGoals = currentGoals + parseInt(playerStats.goals);
    const totalAssists = currentAssists + parseInt(playerStats.assist);
    const totalZeromatch = currentZeromatch + parseInt(playerStats.zeromatch);
    const totalLostGoals = currentLostGoals + parseInt(playerStats.lostgoals);
    const totalGoalPlusAssist = totalGoals + totalAssists;

    // Шаг 5: Вставить в блок "Статистика общая"
    const statsGrid = document.querySelectorAll('.card:nth-of-type(3) .number');
    if (statsGrid.length >= 6) {
        statsGrid[0].textContent = totalMatches;
        statsGrid[1].textContent = totalGoals;
        statsGrid[2].textContent = totalAssists;
        statsGrid[3].textContent = totalGoalPlusAssist;
        statsGrid[4].textContent = totalLostGoals;
        statsGrid[5].textContent = totalZeromatch;
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

