
// ... данные для списка "Бомбардиры" ...
const goalsData = [
    { number: "1.", name: "Костич", counter: "2" },
    { number: "2.", name: "Волконский", counter: "6" },
    { number: "3.", name: "Шеин", counter: "2" },
    { number: "4.", name: "Петрищев", counter: "2" },
    { number: "6.", name: "Пашаев", counter: "1" },
    { number: "7.", name: "Пожидаев", counter: "1" },
    { number: "8.", name: "Нишанов", counter: "2" },
    { number: "9.", name: "Шаропов", counter: "4" },
    // Добавьте остальных игроков
];

// ... данные для списка "Ассистенты" ...
const assistsData = [
    { number: "1.", name: "Власов", counter: "1" },
    { number: "2.", name: "Пашаев", counter: "1" },
    { number: "3.", name: "Долгов", counter: "1" },
    { number: "4.", name: "Костич", counter: "1" },
    { number: "5.", name: "Волконский", counter: "1" },
    { number: "6.", name: "Петрищев", counter: "3" },
    { number: "7.", name: "Шаропов", counter: "2" },
    { number: "8.", name: "Матвеев", counter: "1" },
    { number: "9.", name: "Штепа", counter: "1" },
    { number: "10.", name: "Сыпченка", counter: "1" },
    // Добавьте остальных игроков
];

// Статистика вратарей
const goalkeepersData = [
    { number: "1.", name: "Сыпченко", counter: "13" },
    { number: "3.", name: "Мищенко", counter: "4" },
    { number: "2.", name: "Исаев", counter: "9" },
    // Добавьте остальных вратарей
];

// Матчи на ноль
const goalkeepersZero = [
    { number: "1.", name: "Сыпченко", counter: "2" },
    { number: "2.", name: "Исаев", counter: "1" },
    // Добавьте остальных вратарей
];

// Посещаемость
const zanettiTopData = [
    { name: "Батуев", training: 16 },
    { name: "Белоножкин", training: 11 },
    { name: "Бутин", training: 11 },
    { name: "Власов", training: 16 },
    { name: "Волконский", training: 22 },
    { name: "Иванов", training: 23 },
    { name: "Долгов", training: 23 },
    { name: "Швамбергер", training: 18 },
    { name: "Исаев", training: 9 },
    { name: "Пашаев", training: 4 },
    { name: "Королев Д.", training: 25 },
    { name: "Королев И.", training: 15 },
    { name: "Костич", training: 14 },
    { name: "Ларин", training: 12 },
    { name: "Матвеев", training: 40 },
    { name: "Нишанов", training: 9 },
    { name: "Мищенко", training: 3 },
    { name: "Петрищев", training: 41 },
    { name: "Родионов", training: 0 },
    { name: "Тапчан", training: 0 },
    { name: "Савельев", training: 11 },
    { name: "Салимгареев", training: 31 },
    { name: "Свирщевский", training: 7 },
    { name: "Сыпченко", training: 19 },
    { name: "Амири", training: 34 },
    { name: "Шаропов", training: 35 },
    { name: "Шеин", training: 34 },
    { name: "Юсуф", training: 4 },
    { name: "Аралекян", training: 5 },
    { name: "Демидов", training: 1 },
    { name: "Пожидаев", training: 23 },
    { name: "Хамзин", training: 2 },
    { name: "Турнусов", training: 2 },
    { name: "Штепа", training: 5 },
    { name: "Полевой", training: 16 },
    { name: "Саидов", training: 4 },
    { name: "Малышев", training: 3 },
    { name: "Клеменой", training: 2 },

];

const sortByCounterDescending = (data) => {
    return data.sort((a, b) => {
        return parseInt(b.counter) - parseInt(a.counter);
    });
};

// Сортировка списка "Бомбардиры" по убыванию количества голов
sortByCounterDescending(goalsData);

// Сортировка списка "Ассистенты" по убыванию количества голов
sortByCounterDescending(assistsData);
// ... данные для списка "Бомбардиры" ...

// Обновление номеров игроков в зависимости от позиции в отсортированном списке
const updatePlayerNumbers = (data) => {
    data.forEach((player, index) => {
        player.number = `${index + 1}.`;
    });
};

updatePlayerNumbers(goalsData);
updatePlayerNumbers(assistsData);


const mergedData = {};

goalsData.forEach((player) => {
    mergedData[player.name] = {
        name: player.name,
        goals: parseInt(player.counter) || 0,
        assists: 0
    };
});

assistsData.forEach((player) => {
    if (mergedData[player.name]) {
        mergedData[player.name].assists = parseInt(player.counter) || 0;
    } else {
        mergedData[player.name] = {
            name: player.name,
            goals: 0,
            assists: parseInt(player.counter) || 0
        };
    }
});

const goalsAssistsArray = Object.values(mergedData);

// Сортировка по общему количеству голов и ассистов
goalsAssistsArray.sort((a, b) => {
    const totalA = a.goals + a.assists;
    const totalB = b.goals + b.assists;
    return totalB - totalA;
});

// Создаем элементы списка и добавляем их в разметку
const goalsList = document.querySelector('.goals-list');
const assistsList = document.querySelector('.assists-list');
const goalsAssistsList = document.querySelector('.goals-assists-list');

goalsData.forEach((player) => {
    const listItem = document.createElement('li');
    listItem.innerHTML = `
    <div class="number">${player.number}</div>
    <div class="player">${player.name}</div>
    <div class="counter">${player.counter}</div>
    `;
    goalsList.appendChild(listItem);
});

assistsData.forEach((player) => {
    const listItem = document.createElement('li');
    listItem.innerHTML = `
    <div class="number">${player.number}</div>
    <div class="player">${player.name}</div>
    <div class="counter">${player.counter}</div>
    `;
    assistsList.appendChild(listItem);
});

goalsAssistsArray.forEach((player, index) => {
    const listItem = document.createElement('li');
    listItem.innerHTML = `
    <div class="number">${index + 1}.</div>
    <div class="player">${player.name}</div>
    <div class="total">${player.goals + player.assists}</div>
    `;
    goalsAssistsList.appendChild(listItem);
});


// Находим элемент списка вратарей
const goalkeepersList = document.querySelector('.goalkeepers-list');

// Сортируем вратарей по значению счетчика (counter)
goalkeepersData.sort((a, b) => {
    return parseInt(b.counter) - parseInt(a.counter);
});

// Создаем элементы списка и добавляем их в разметку
goalkeepersData.forEach((goalkeeper) => {
    const listItem = document.createElement('li');
    listItem.innerHTML = `
        <div class="number">${goalkeeper.number}</div>
        <div class="player">${goalkeeper.name}</div>
        <div class="counter">${goalkeeper.counter}</div>
    `;
    goalkeepersList.appendChild(listItem);
});


// Находим элемент списка вратарей
const goalkeepersListZero = document.querySelector('.goalkeepers-list-zero');

// Сортируем вратарей по значению счетчика (counter)
goalkeepersZero.sort((a, b) => {
    return parseInt(b.counter) - parseInt(a.counter);
});

// Создаем элементы списка и добавляем их в разметку
goalkeepersZero.forEach((goalkeeper) => {
    const listItem = document.createElement('li');
    listItem.innerHTML = `
<div class="number">${goalkeeper.number}</div>
<div class="player">${goalkeeper.name}</div>
<div class="counter">${goalkeeper.counter}</div>
`;
    goalkeepersListZero.appendChild(listItem);
});

//считаем посещаемость

// Находим элемент списка топ игроков
const zanettiTopList = document.querySelector('.zanetti_top-list');

// Сортируем игроков по количеству тренировок (training) в обратном порядке
zanettiTopData.sort((a, b) => b.training - a.training);

// Создаем элементы списка и добавляем их в разметку
zanettiTopData.slice(0).forEach((player, index) => {
    const listItem = document.createElement('li');
    listItem.innerHTML = `
<div class="position">${index + 1 + "."}</div>
<div class="name">${player.name}</div>
<div class="training">${player.training}</div>
`;
    zanettiTopList.appendChild(listItem);
});

// Получаем элементы для голов забито и голов пропущено
const goalsScored = parseInt(document.querySelector('.goals .number').textContent);
const goalsConceded = parseInt(document.querySelector('.goals_conceded .number').textContent);

// Вычисляем разницу
const difference = goalsScored - goalsConceded;

// Получаем элемент разницы
const differenceElement = document.querySelector('.difference .number');

// Обновляем значение разницы
differenceElement.textContent = `${difference >= 0 ? '+' : ''}${difference}`;



// Создать элементы списка для "Бомбардиров" и добавить их в соответствующий блок
const goalsListTop = document.querySelector('.goals-list-top');
createListItems(goalsData, goalsListTop);

// Создать элементы списка для "Ассистентов" и добавить их в соответствующий блок
const assistsListTop = document.querySelector('.assists-list-top');
createListItems(assistsData, assistsListTop);

// Создать элементы списка для "Голов+Пасов" и добавить их в соответствующий блок
const goalsAssistsListTop = document.querySelector('.goals-assists-list-top');
createListItems(goalsAssistsArray, goalsAssistsListTop);

// Создать элементы списка для "Вратарей" и добавить их в соответствующий блок
const goalkeepersListTop = document.querySelector('.goalkeepers-list-top');
createListItems(goalkeepersData, goalkeepersListTop);

// Создать элементы списка для "Матчей на ноль" и добавить их в соответствующий блок
const goalkeepersListZeroTop = document.querySelector('.goalkeepers-list-zero-top');
createListItems(goalkeepersZero, goalkeepersListZeroTop);

// Создать элементы списка для "Топ-3 приз Дзанетти" и добавить их в соответствующий блок
const zanettiTopListTop = document.querySelector('.zanetti_top-list-top');
createListItems(zanettiTopData, zanettiTopListTop);


document.addEventListener('DOMContentLoaded', function () {
    // Функция для создания элементов списка
    const createListItems = (data, parentElement) => {
        // Отсортировать данные
        data.sort((a, b) => parseInt(b.counter) - parseInt(a.counter));
        // Создать элементы списка для 5 лучших игроков
        for (let i = 0; i < Math.min(data.length, 5); i++) {
            const player = data[i];
            const listItem = document.createElement('li');
            listItem.innerHTML = `
                <div class="number">${player.number}</div>
                <div class="player">${player.name}</div>
                <div class="counter">${player.counter}</div>
            `;
            parentElement.appendChild(listItem);
        }
    };

});