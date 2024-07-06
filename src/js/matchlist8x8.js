var matches = [
    {
        date: "21.01.2024",
        league: "Лига F, зимний чемпионат «Торпедо», 5-й тур",
        team1: "FC Inter Moscow 8х8",
        team2: "SALUT SNEAKERS",
        score: "1:5",
        goals: "Гол: Языков (Власов)"
    },
    {
        date: "14.01.2024",
        league: "Лига F, зимний чемпионат «Торпедо», 4-й тур",
        team1: "FC Inter Moscow 8х8",
        team2: "Citadella YRN",
        score: "1:8",
        goals: "Гол: Костич (Макарчев)"
    },
    {
        date: "28.01.2024",
        league: "Лига F, зимний чемпионат «Торпедо», 6-й тур",
        team1: "FC Inter Moscow 8х8",
        team2: "Аврора",
        score: "4:6",
        goals: "Гол: Макарчев (Языков), Костич, Языков (Белоножкин), Костич (Мищенко)"
    },
    {
        date: "04.02.2024",
        league: "Лига F, зимний чемпионат «Торпедо», 7-й тур",
        team1: "FC Inter Moscow 8х8",
        team2: "Удинезе",
        score: "2:10",
        goals: "Голы: Волконский (Шеин), Языков (Петрищев)"
    },
    {
        date: "11.02.2024",
        league: "Лига F, зимний чемпионат «Торпедо», 8-й тур",
        team1: "FC Inter Moscow 8х8",
        team2: "Яндекс",
        score: "8:4",
        goals: "Голы: Языков - 4, Шеин - 2, Костич, Макарчев"
    },
    {
        date: "18.02.2024",
        league: "Товарищеский матч",
        team1: "FC Inter Moscow 8х8",
        team2: "TZN",
        score: "3:5",
        goals: "Голы: Языков (Матвеев), Языков, Матвеев (Языков)"
    },
    {
        date: "24.02.2024",
        league: "Лига F, зимний чемпионат «Торпедо», 10-й тур",
        team1: "FC Inter Moscow 8х8",
        team2: "Альтерон",
        score: "0:4",
        goals: ""
    },
    {
        date: "03.03.2024",
        league: "Лига F, зимний чемпионат «Торпедо», 11-й тур",
        team1: "FC Inter Moscow 8х8",
        team2: "Скантроп",
        score: "1:4",
        goals: ""
    },
    {
        date: "24.03.2024",
        league: "Товарищеский матч",
        team1: "FC Inter Moscow 8х8",
        team2: "Т-Тайминг",
        score: "6:4",
        goals: "Голы: Языков - 4, Белкин, автогол"
    },
    {
        date: "31.03.2024",
        league: "Товарищеский матч",
        team1: "FC Inter Moscow 8х8",
        team2: "Орлан Москва",
        score: "9:2",
        goals: "Голы: Языков - 6, Голуб - 2, Волконский"
    },
    {
        date: "07.04.2024",
        league: "Лига F, первый дивизион «Севера», 1-й тур",
        team1: "FC Inter Moscow 8х8",
        team2: "Спарта",
        score: "15:1",
        goals: "Голы: Голуб - 6, Костич - 2, Языков - 2, Волконский, Власов, Касулин, Белоножкин, Макарчев"
    },
    {
        date: "14.04.2024",
        league: "Лига F, первый дивизион «Севера», 2-й тур",
        team1: "FC Inter Moscow 8х8",
        team2: "Страга",
        score: "2:2",
        goals: "Голы: Языков, Белоножкин"
    },
    {
        date: "21.04.2024",
        league: "Лига F, первый дивизион «Севера», 3-й тур",
        team1: "FC Inter Moscow 8х8",
        team2: "1331.REST",
        score: "2:0",
        goals: "Голы: Языков (Белоножкин), Абянов (Власов)"
    },
    {
        date: "28.04.2024",
        league: "Лига F, первый дивизион «Севера», 4-й тур",
        team1: "FC Inter Moscow (8x8)",
        team2: "FC Oden's",
        score: "0:5",
        goals: "Голы: Lf"
    }
];

document.addEventListener("DOMContentLoaded", function () {
    displayMatches(matches);
    displayLastMatches(matches[matches.length - 1]);

    function displayMatches(matches) {
        var matchesContainer = document.getElementById('matchesContainer');
        matchesContainer.innerHTML = '';

        matches.forEach(function (match) {
            var matchElement = document.createElement('div');
            matchElement.classList.add('match');
            matchElement.innerHTML = `
                <p>${match.date}</p>
                <p>${match.league}</p>
                <p>${match.team1} - ${match.team2} (${match.score})</p>
                <p>${match.goals}</p>
            `;
            matchesContainer.appendChild(matchElement);
        });
    }

});