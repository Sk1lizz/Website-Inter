document.addEventListener('DOMContentLoaded', function () {
    const teamTimeElement = document.querySelector('#datateamTime');
    const teamTimeText = teamTimeElement.textContent.trim();
    const teamTimeWords = teamTimeText.split(' ');

    console.log("Слова в тексте:", teamTimeWords);

    let teamTimeYears = 0;
    let teamTimeMonths = 0;

    teamTimeWords.forEach((word, index) => {
        if (word === 'год' || word === 'года' || word === 'лет') {
            teamTimeYears = parseInt(teamTimeWords[index - 1]);
        } else if (word === 'месяц' || word === 'месяца' || word === 'месяцев') {
            teamTimeMonths = parseInt(teamTimeWords[index - 1]);
        }
    });


    // Вычисляем общее количество месяцев в команде
    const monthsInTeam = teamTimeYears * 12 + teamTimeMonths;

    // Получаем статистику всего сезона
    const statisticElements = document.querySelectorAll('.statisticplayersall .block');
    const matchesPlayed = parseInt(statisticElements[0].querySelector('.matchesall').textContent);
    const goalsScored = parseInt(statisticElements[1].querySelector('.goalall').textContent);
    const assists = parseInt(statisticElements[3].querySelector('.assistall').textContent);
    const zeroMatches = parseInt(statisticElements[7].querySelector('.zeromatchall').textContent);

    // Вычисляем опыт игрока
    const experience = monthsInTeam * 100 + // Опыт за каждый месяц пребывания в команде
        matchesPlayed * 50 + // Опыт за сыгранные матчи
        goalsScored * 125 + // Опыт за забитые голы
        assists * 100 + // Опыт за голевые передачи
        zeroMatches * 250; // Опыт за матчи на 0

    // Определяем титул игрока
    let title = '';
    if (experience >= 0 && experience < 500) {
        title = 'Новичок';
    } else if (experience >= 500 && experience < 1000) {
        title = 'Перспективный';
    } else if (experience >= 1001 && experience < 2500) {
        title = 'Футболист';
    } else if (experience >= 2501 && experience < 5000) {
        title = 'Опытный';
    } else if (experience >= 5001 && experience < 7500) {
        title = 'Старожил';
    } else if (experience >= 7501 && experience < 10000) {
        title = 'Мастер';
    } else if (experience >= 10001 && experience < 12500) {
        title = 'Герой';
    } else if (experience >= 12501 && experience < 15000) {
        title = 'Магистр';
    } else if (experience >= 15001 && experience < 20000) {
        title = 'Посвященный';
    } else if (experience >= 20001 && experience < 25000) {
        title = 'Ветеран';
    } else if (experience >= 25001 && experience < 30000) {
        title = 'Виртуоз';
    } else if (experience >= 30001 && experience < 35000) {
        title = 'Элита';
    } else if (experience >= 35001 && experience < 45000) {
        title = 'Чемпион';
    } else if (experience >= 45001 && experience < 60000) {
        title = 'Хранитель';
    } else if (experience >= 60001 && experience < 75000) {
        title = 'Вершитель';
    } else if (experience >= 75001 && experience < 90000) {
        title = 'Избранный';
    } else if (experience >= 90001) {
        title = 'Легенда';
    }

    // Обновляем информацию на странице
    const experienceElement = document.querySelector('#experience');
    const titleElement = document.querySelector('#title');
    experienceElement.textContent = experience;
    titleElement.textContent = title;
});
