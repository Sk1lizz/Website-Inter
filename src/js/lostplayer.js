// АРХИВ

const statisticsarchive = [
    { number: "20", name: "Ларин Илья", matches: "17", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro" },
    { number: "18", name: "Губский Никита", matches: "235", goals: "124", assist: "19", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.07.2014" },
    { number: "17", name: "Петрищев Андрей", matches: "52", goals: "4", assist: "7", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.03.2023" },
    { number: "89", name: "Пожидаев Дмитрий", matches: "4", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.02.2023" },
    { number: "21", name: "Костоев Али", matches: "3", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "01.09.2024" },
    { number: "8", name: "Устинов Илья", matches: "0", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "proand8x8", time_in: "01.08.2024" },
    { number: "1", name: "Исаев Матвей", matches: "87", goals: "0", assist: "1", zeromatch: "10", lostgoals: "237", team: "proand8x8", time_in: "01.05.2021" },
    { number: "20", name: "Власов Владлен", matches: "240", goals: "22", assist: "16", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "33", name: "Кравченко", matches: "2", goals: "4", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "69", name: "Долгов Данила", matches: "66", goals: "14", assist: "10", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "15", name: "Королев И.", matches: "11", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Афонетошин Георгий", matches: "22", goals: "11", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Губский Данила", matches: "58", goals: "34", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Дьякнонов Максим", matches: "69", goals: "2", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Сафин Дамир", matches: "61", goals: "13", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Бокатенко Егор", matches: "51", goals: "11", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Абдулин Марат", matches: "28", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Маркин Никита", matches: "24", goals: "5", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Семышев Вячеслав", matches: "28", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Оленькин Илья", matches: "22", goals: "29", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Торосян Арам", matches: "50", goals: "68", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Буджиашвили Давид", matches: "25", goals: "0", assist: "0", zeromatch: "0", lostgoals: "74", team: "pro", time_in: "" },
    { number: "0", name: "Царукян Захар", matches: "23", goals: "32", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Газарян Николай", matches: "21", goals: "5", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Мелконян Армен", matches: "101", goals: "27", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Буцкий Евгений", matches: "18", goals: "0", assist: "0", zeromatch: "0", lostgoals: "27", team: "pro", time_in: "" },
    { number: "0", name: "Минаев Кирилл", matches: "36", goals: "0", assist: "0", zeromatch: "0", lostgoals: "142", team: "pro", time_in: "" },
    { number: "0", name: "Царев Евгений", matches: "19", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Некрасов Дмитрий", matches: "18", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Хошбекян Вячеслав", matches: "29", goals: "8", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Гончаров Данила", matches: "42", goals: "2", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Васильев Валерий", matches: "15", goals: "6", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Макаров Максим", matches: "15", goals: "2", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Коркин Дмитрий", matches: "36", goals: "36", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Сытник Денис", matches: "19", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Соколов Кирилл", matches: "10", goals: "6", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Шипов Максим", matches: "7", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Исмоилбеков Саркобек", matches: "4", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Степанов Максим", matches: "4", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Жуков Дмитрий", matches: "22", goals: "13", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Молчанов Вадим", matches: "9", goals: "6", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Макушев Владимир", matches: "11", goals: "5", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Попов Данила", matches: "23", goals: "21", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Мартынов Артем", matches: "10", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Вартанян Артем", matches: "17", goals: "0", assist: "0", zeromatch: "0", lostgoals: "114", team: "pro", time_in: "" },
    { number: "0", name: "Нюдльчиев Санджи", matches: "4", goals: "2", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Тепляков Валерий", matches: "1", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Дудочкин Олег", matches: "12", goals: "2", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Хотеев Дмитрий", matches: "61", goals: "7", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Ким Петр", matches: "30", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Глембо Алексей", matches: "73", goals: "27", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Василина Владислав", matches: "55", goals: "8", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Англичанинов Герман", matches: "88", goals: "0", assist: "0", zeromatch: "0", lostgoals: "269", team: "pro", time_in: "" },
    { number: "0", name: "Авдеев Дмитрий", matches: "54", goals: "51", assist: "0", zeromatch: "1", lostgoals: "45", team: "pro", time_in: "" },
    { number: "0", name: "Кондратюк Дмитрий", matches: "6", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Бейлин Артем", matches: "2", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Крюков Семен", matches: "151", goals: "3", assist: "0", zeromatch: "0", lostgoals: "392", team: "pro", time_in: "" },
    { number: "0", name: "Шайдулин Эльдар", matches: "106", goals: "41", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Килба Станислав", matches: "104", goals: "162", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Белов Кирилл", matches: "26", goals: "2", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Шракс Александр", matches: "64", goals: "37", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Куликов Александр", matches: "29", goals: "16", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Деулин Семен", matches: "9", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Алферов Виталий", matches: "45", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Калинин Михаил", matches: "5", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Железнов Глеб", matches: "7", goals: "0", assist: "0", zeromatch: "0", lostgoals: "7", team: "pro", time_in: "" },
    { number: "0", name: "Вылегжанин Максим", matches: "17", goals: "3", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Лагута Михаил", matches: "16", goals: "6", assist: "7", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Константинов Павел", matches: "42", goals: "44", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Алимжанов Дастан", matches: "62", goals: "35", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Захаров Снислав", matches: "28", goals: "6", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Афонин Владимир", matches: "2", goals: "3", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Сергеев Александр", matches: "149", goals: "16", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Жуков Лев", matches: "16", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Баранов Антон", matches: "26", goals: "0", assist: "0", zeromatch: "0", lostgoals: "54", team: "pro", time_in: "" },
    { number: "0", name: "Портнов Николай", matches: "8", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Найденов Дмитрий", matches: "11", goals: "6", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Фадеев Александр", matches: "26", goals: "47", assist: "17", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Цветков Иван", matches: "30", goals: "3", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Радченко Михаил", matches: "27", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Мамедов Агил", matches: "4", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Горькаев Сергей", matches: "22", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Власов Александр", matches: "10", goals: "3", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Селиверст Мирон", matches: "2", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Плужников Тимофей", matches: "5", goals: "3", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Петров Дмитрий", matches: "3", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Саплин Кирилл", matches: "6", goals: "2", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Готовцев Иван", matches: "2", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Герасимов Владислав", matches: "58", goals: "26", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Тегунов Данила", matches: "15", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Кондрашов Никита", matches: "4", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Долинин Александр", matches: "3", goals: "0", assist: "0", zeromatch: "0", lostgoals: "8", team: "pro", time_in: "" },
    { number: "0", name: "Лысягин Андрей", matches: "55", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Власов Богдан", matches: "19", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Ботобеков Аслан", matches: "3", goals: "2", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Гасанов Баганд", matches: "4", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Афонин Григорий", matches: "5", goals: "0", assist: "0", zeromatch: "0", lostgoals: "19", team: "pro", time_in: "" },
    { number: "0", name: "Токарев Данила", matches: "10", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Казаков Александр", matches: "5", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Коломиец Алексей", matches: "7", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Лисицин Александр", matches: "9", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Пырсов Артур", matches: "4", goals: "0", assist: "0", zeromatch: "0", lostgoals: "10", team: "pro", time_in: "" },
    { number: "0", name: "Родионов Денис", matches: "12", goals: "4", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Лобанов Ярослав", matches: "4", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Баранов Антон", matches: "5", goals: "3", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Чистов Павел", matches: "9", goals: "17", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Свинцов Егор", matches: "11", goals: "11", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Куликов Евгений", matches: "10", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Соколов Максим", matches: "16", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Жембровский Кирилл", matches: "12", goals: "21", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Илюлин Илья", matches: "26", goals: "0", assist: "0", zeromatch: "0", lostgoals: "67", team: "pro", time_in: "" },
    { number: "0", name: "Сарапов Даниил", matches: "4", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Васьков Максим", matches: "55", goals: "9", assist: "3", zeromatch: "6", lostgoals: "75", team: "pro", time_in: "" },
    { number: "0", name: "Слепов Данила", matches: "54", goals: "0", assist: "0", zeromatch: "11", lostgoals: "132", team: "pro", time_in: "" },
    { number: "0", name: "Арясин Павел", matches: "3", goals: "2", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Ляхов Сергей", matches: "7", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Шандорин Дмитрий", matches: "22", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Рижский Никита", matches: "23", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Белиловский Никола", matches: "97", goals: "31", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Вершинин Артем", matches: "22", goals: "11", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Кузмицкий Александр", matches: "1", goals: "1", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Юрин Данила", matches: "12", goals: "3", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Остапенко Мирон", matches: "29", goals: "0", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Бульбаш Илья", matches: "47", goals: "7", assist: "16", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Лалаян Александр", matches: "41", goals: "6", assist: "9", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Сафи Миладжи", matches: "6", goals: "4", assist: "3", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Пинягин Семен", matches: "6", goals: "1", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Шипулин Глеб", matches: "69", goals: "2", assist: "5", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Комиссаров Кирилл", matches: "26", goals: "33", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "БЬойко Иван", matches: "3", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Глембо Антон", matches: "196", goals: "7", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Гаевский Павел", matches: "70", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Иконников Максим", matches: "44", goals: "5", assist: "6", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Кузнецов Илья", matches: "28", goals: "22", assist: "23", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Прыдывус Александр", matches: "67", goals: "24", assist: "12", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Дорохольский Дмитрий", matches: "43", goals: "54", assist: "19", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Нарватов Дмитрий", matches: "58", goals: "25", assist: "12", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Кузнецов Данила", matches: "26", goals: "9", assist: "7", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Гиско Георгий", matches: "7", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Файзиев Фархад", matches: "18", goals: "0", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Батуев Никита", matches: "3", goals: "0", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Светилов Виталий", matches: "198", goals: "259", assist: "197", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Нишанов Дауд", matches: "24", goals: "12", assist: "0", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Сосков Сергей", matches: "5", goals: "1", assist: "1", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Торо Камило", matches: "5", goals: "2", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Пашаев Исмаил", matches: "16", goals: "4", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Джумагалиев Алексей", matches: "7", goals: "2", assist: "2", zeromatch: "0", lostgoals: "0", team: "pro", time_in: "" },
    { number: "0", name: "Мищенко Сергей", matches: "18", goals: "1", assist: "0", zeromatch: "0", lostgoals: "12", team: "pro", time_in: "" },
    { number: "0", name: "Тапанайнен Данила", matches: "12", goals: "0", assist: "0", zeromatch: "0", lostgoals: "20", team: "pro", time_in: "" },
    { number: "0", name: "Косовских Арсений", matches: "1", goals: "0", assist: "1", zeromatch: "0", lostgoals: "12", team: "pro", time_in: "" },

];


function updateLostList() {
    const lostList = document.querySelector(".lost-list");
    lostList.innerHTML = "";  // Очищаем список перед добавлением данных

    // Создаем заголовок таблицы
    const header = document.createElement("li");
    header.classList.add("header");
    header.innerHTML = `
                <span>№</span>
                <span>Фамилия</span>
                <span>Матчи</span>
                <span>Голы</span>
                <span>Ассисты</span>
                <span>Матчи на 0</span>
                <span>Пропущенные голы</span>
            `;
    lostList.appendChild(header);

    // Сортируем данные по количеству сыгранных матчей
    const sortedPlayers = statisticsarchive.sort((a, b) => b.matches - a.matches);

    // Добавляем данные игроков в список
    sortedPlayers.forEach((player, index) => {
        const listItem = document.createElement("li");
        listItem.innerHTML = `
                    <span>${index + 1}</span>
                    <span>${player.name}</span>
                    <span>${player.matches}</span>
                    <span>${player.goals}</span>
                    <span>${player.assist}</span>
                    <span>${player.zeromatch}</span>
                    <span>${player.lostgoals}</span>
                `;
        lostList.appendChild(listItem);
    });
}

// Вызов функции для обновления списка
updateLostList();
