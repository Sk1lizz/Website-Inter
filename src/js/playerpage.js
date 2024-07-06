
let now = new Date(); // Текущя дата
let today = new Date(now.getFullYear(), now.getMonth(), now.getDate()); // Текущя дата без времени
let birthdayText = document.getElementById('birthday').textContent; // Получаем текст даты рождения
let dobParts = birthdayText.split('.'); // Разделяем текст на компоненты
let dob = new Date(parseInt(dobParts[2]), parseInt(dobParts[1]) - 1, parseInt(dobParts[0])); // Создаем объект Date из компонентов даты рождения
let dobnow = new Date(today.getFullYear(), dob.getMonth(), dob.getDate()); // ДР в текущем году
let age; // Возраст

// Возраст = текущий год - год рождения
age = today.getFullYear() - dob.getFullYear();
// Если ДР в этом году ещё предстоит, то вычитаем из age один год
if (today < dobnow) {
    age = age - 1;
}

// Вставляем возраст в элемент с id="age"
document.getElementById("age").textContent = `${age} лет`;

let birthdayText1 = document.getElementById('datateamTime').textContent; // Получаем текст даты вступления в команду
let dobParts1 = birthdayText1.split('.'); // Разделяем текст на компоненты
let dob1 = new Date(parseInt(dobParts1[2]), parseInt(dobParts1[1]) - 1, parseInt(dobParts1[0])); // Создаем объект Date из компонентов даты вступления в команду
let dobnow1 = new Date(today.getFullYear(), dob1.getMonth(), dob1.getDate()); // Дата вступления в текущем году
let age1; // Возраст

// Текущий год = текущий год - вступления в команду
age1 = today.getFullYear() - dob1.getFullYear();
// Если ДР в этом году ещё предстоит, то вычитаем из age один год
if (today < dobnow1) {
    age1 = age1 - 1;
}

// Добавляем количество месяцев
let months = today.getMonth() - dob1.getMonth();
if (months < 0) {
    months = months + 12;
}
age1 = age1 + (months / 12);

// Форматируем возраст в соответствии с русским языком
let ageText = `${Math.floor(age1)}`;
if (ageText.endsWith('1')) {
    ageText += ' год';
} else if (ageText.endsWith('2') || ageText.endsWith('3') || ageText.endsWith('4')) {
    ageText += ' года';
} else {
    ageText += ' лет';
}

// Проверяем, нужно ли отображать количество месяцев
if (age1 === 0) {
    ageText = `Меньше месяца`;
}
else if (age1 < 1) {
    ageText = ``;
    let monthsText = `${Math.floor(age1 * 12 - Math.floor(age1) * 12)}`;
    if (monthsText === '1') {
        monthsText += ' месяц';
    } else if (monthsText >= '2' && monthsText <= '4') {
        monthsText += ' месяца';
    } else {
        monthsText += ' месяцев';
    }
    ageText += ` ${monthsText}`;
}
// если нужно
else {
    let monthsText = `${Math.floor(age1 * 12 - Math.floor(age1) * 12)}`;
    if (monthsText === '1') {
        monthsText += ' месяц';
    } else if (monthsText >= '2' && monthsText <= '4') {
        monthsText += ' месяца';
    } else {
        monthsText += ' месяцев';
    }
    ageText += ` ${monthsText}`;
}

document.getElementById("datateamTime").textContent = ageText;
'use strict';