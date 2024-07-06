const goalsData = [{
    number: "1.",
    name: "Костич",
    counter: "6"
}, {
    number: "2.",
    name: "Языков",
    counter: "27"
}, {
    number: "3.",
    name: "Макарчев",
    counter: "5"
}, {
    number: "4.",
    name: "Волконский",
    counter: "5"
}, {
    number: "5.",
    name: "Шеин",
    counter: "2"
}, {
    number: "6.",
    name: "Матвеев",
    counter: "6"
}, {
    number: "7.",
    name: "Белкин",
    counter: "1"
},

{
    number: "8.",
    name: "Голуб",
    counter: "9"
},

{
    number: "9.",
    name: "Власов",
    counter: "2"
},

{
    number: "10.",
    name: "Касулин",
    counter: "3"
},

{
    number: "11.",
    name: "Белоножкин",
    counter: "5"
},

{
    number: "12.",
    name: "Автогол",
    counter: "3"
},

{
    number: "13.",
    name: "Абянов",
    counter: "2"
},

{
    number: "14.",
    name: "Хакимов",
    counter: "10"
},

{
    number: "15.",
    name: "Бубнов",
    counter: "2"
},

{
    number: "15.",
    name: "Шаропов",
    counter: "2"
},



    // Добавьте остальных игроков
];
// ... данные для списка "Ассистенты" ...

const assistsData = [{
    number: "1.",
    name: "Макарчев",
    counter: "1"
}, {
    number: "1.",
    name: "Власов",
    counter: "8"
}, {
    number: "1.",
    name: "Мищенко",
    counter: "1"
}, {
    number: "1.",
    name: "Белоножкин",
    counter: "10"
}, {
    number: "1.",
    name: "Языков",
    counter: "11"
}, {
    number: "1.",
    name: "Шеин",
    counter: "6"
}, {
    number: "1.",
    name: "Петрищев",
    counter: "2"
}, {
    number: "1.",
    name: "Синицын",
    counter: "2"
}, {
    number: "1.",
    name: "Костич",
    counter: "4"
}, {
    number: "1.",
    name: "Матвеев",
    counter: "2"
}, {
    number: "1.",
    name: "Котов",
    counter: "2"
}, {
    number: "1.",
    name: "Голуб",
    counter: "4"
}, {
    number: "1.",
    name: "Волконский",
    counter: "4"
},
{
    number: "1.",
    name: "Тапчан",
    counter: "2"
},
{
    number: "1.",
    name: "Бубнов",
    counter: "2"
},
{
    number: "1.",
    name: "Хакимов",
    counter: "2"
},
{
    number: "1.",
    name: "Касулин",
    counter: "1"
},
{
    number: "1.",
    name: "Алексий",
    counter: "1"
},
{
    number: "1.",
    name: "Малышев А.",
    counter: "1"
},
{
    number: "1.",
    name: "Абянов",
    counter: "1"
},

    // Добавьте остальных игроков
];

const goalkeepersData = [{
    number: "3.",
    name: "Мищенко",
    counter: "13"
}, {
    number: "1.",
    name: "Мытько",
    counter: "24"
}, {
    number: "2.",
    name: "Исаев",
    counter: "19"
}, {
    number: "4.",
    name: "Сыпченко",
    counter: "8"
},
    // Добавьте остальных вратарей
];

// Матчи на ноль

const goalkeepersZero = [{
    number: "1.",
    name: "Сыпченко",
    counter: "1"
},

{
    number: "1.",
    name: "Мытько",
    counter: "1"
},
    // Добавьте остальных вратарей
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


