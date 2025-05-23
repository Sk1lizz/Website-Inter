document.addEventListener("DOMContentLoaded", async () => {
    const params = new URLSearchParams(window.location.search);
    const playerNumber = params.get("id");

    if (!playerNumber) {
        console.error("ID игрока не указан в URL");
        return;
    }

    function formatDate(dateStr, format = "full") {
        const d = new Date(dateStr);
        if (format === "short") {
            // Возвращаем только год
            return d.getFullYear().toString();
        } else if (format === "numeric") {
            // Возвращаем в формате ДД.ММ.ГГГГ
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            return `${day}.${month}.${year}`;
        } else {
            return d.toLocaleDateString('ru-RU', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    }

    function declension(num, forms) {
        const mod10 = num % 10;
        const mod100 = num % 100;
        if (mod100 >= 11 && mod100 <= 14) return forms[2];
        if (mod10 === 1) return forms[0];
        if (mod10 >= 2 && mod10 <= 4) return forms[1];
        return forms[2];
    }

    function calculateAge(dateStr) {
        const birthDate = new Date(dateStr);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

    try {
        // Получаем данные игрока по номеру
        const playerRes = await fetch(`/api/players/number/${playerNumber}`);
        if (!playerRes.ok) throw new Error("Игрок не найден");
        const player = await playerRes.json();
        if (!player || !player.id) throw new Error("Данные игрока неполные");

        // Устанавливаем фото игрока
        const playerPhoto = `/img/player/player_${player.id}.png`;
        const playerImgEl = document.querySelector(".player-card img");
        if (playerImgEl) {
            playerImgEl.src = playerPhoto;
            playerImgEl.alt = `${player.name} ${player.patronymic || ''}`.trim();
            playerImgEl.onerror = () => {
                playerImgEl.src = "/img/player/player_0.png"; // Заглушка
            };
        }

        // Получаем статистику текущего сезона
        const seasonStatsRes = await fetch(`/api/admin/players/${player.id}/statistics`);
        if (!seasonStatsRes.ok) throw new Error("Ошибка при загрузке статистики сезона");
        const seasonStats = await seasonStatsRes.json();

        // Получаем общую статистику
        const allStatsRes = await fetch(`/api/admin/players/${player.id}/statistics/all`);
        if (!allStatsRes.ok) throw new Error("Ошибка при загрузке общей статистики");
        const allStatsRaw = await allStatsRes.json();
        const allStats = (allStatsRaw && allStatsRaw.length > 0) ? allStatsRaw[0] : {};

        // Обновляем основную информацию игрока
        const nameEl = document.querySelector(".player-name");
        const infoEl = document.querySelector(".player-info");

        if (nameEl) {
            nameEl.textContent = `${player.name} ${player.patronymic || ''}`.trim();
        }

        if (infoEl) {
            // Расчёт времени в команде
            const joinDate = new Date(player.join_date);
            const now = new Date();
            let years = now.getFullYear() - joinDate.getFullYear();
            let months = now.getMonth() - joinDate.getMonth();
            if (months < 0) {
                years--;
                months += 12;
            }

            const teamDuration = [];
            if (years > 0) teamDuration.push(`${years} ${declension(years, ['год', 'года', 'лет'])}`);
            if (months > 0) teamDuration.push(`${months} ${declension(months, ['месяц', 'месяца', 'месяцев'])}`);

            infoEl.innerHTML = `
              <p><strong>Номер:</strong> ${player.number}</p>
            <p><strong>Позиция:</strong> ${player.position}</p>
            <p><strong>Рост:</strong> ${player.height_cm || '-'} см</p>
            <p><strong>Вес:</strong> ${player.weight_kg || '-'} кг</p>
            <p><strong>Возраст:</strong> ${calculateAge(player.birth_date)}</p>
            <p><strong>Присоединился:</strong> ${formatDate(player.join_date, 'short')}</p>
             <p><strong>Дата Рождения:</strong> ${formatDate(player.birth_date, 'numeric')}</p>
            <p><strong>Время в команде:</strong> ${teamDuration.join(' ') || 'менее месяца'}</p>
        `;
        }

        // Вывод статистики сезона
        const seasonStatsGrid = document.querySelector(".season-stats");
        if (seasonStatsGrid) {
            seasonStatsGrid.innerHTML = `
                <div><div class="number">${seasonStats.matches || 0}</div>Матчей</div>
                <div><div class="number">${seasonStats.goals || 0}</div>Голов</div>
                <div><div class="number">${seasonStats.assists || 0}</div>Ассистов</div>
                <div><div class="number">${(seasonStats.goals || 0) + (seasonStats.assists || 0)}</div>Гол+пас</div>
                <div><div class="number">${seasonStats.lostgoals || 0}</div>Голов пропущено</div>
                <div><div class="number">${seasonStats.zeromatch || 0}</div>Матчей на 0</div>
            `;
        }

        // Вывод общей статистики
        const allStatsGrid = document.querySelector(".all-stats");
        if (allStatsGrid) {
            // Суммируем поля сезона и общей статистики
            const sum = (key) => (seasonStats[key] || 0) + (allStats[key] || 0);

            allStatsGrid.innerHTML = `
                <div><div class="number number2 matches">${sum('matches')}</div>Матчей</div>
                <div><div class="number number2 goals">${sum('goals')}</div>Голов</div>
                <div><div class="number number2 assists">${sum('assists')}</div>Ассистов</div>
                <div><div class="number">${sum('goals') + sum('assists')}</div>Гол+пас</div>
                <div><div class="number number2 lostgoals">${sum('lostgoals')}</div>Голов пропущено</div>
                <div><div class="number number2 zeromatch">${sum('zeromatch')}</div>Матчей на 0</div>
            `;

            // Функция для подсчёта опыта
            function calculateExperience() {
                const matches = sum('matches');
                const goals = sum('goals');
                const assists = sum('assists');
                const zeroMatches = sum('zeromatch');

                // Извлекаем время в команде из infoEl
                let years = 0, months = 0;
                if (infoEl) {
                    const timeInTeamP = [...infoEl.querySelectorAll('p')].find(p => p.querySelector('strong')?.textContent.includes('Время в команде'));
                    if (timeInTeamP) {
                        const text = timeInTeamP.textContent;
                        const matchYears = text.match(/(\d+)\s(год|года|лет)/);
                        const matchMonths = text.match(/(\d+)\s(месяц|месяца|месяцев)/);
                        if (matchYears) years = parseInt(matchYears[1]);
                        if (matchMonths) months = parseInt(matchMonths[1]);
                    }
                }
                const totalMonths = years * 12 + months;

                // Формула опыта
                const experience = totalMonths * 100 + matches * 50 + goals * 125 + assists * 100 + zeroMatches * 250;
                return experience;
            }

            function updateExperienceBar(experience) {
                const titles = [
                    { limit: 500, name: 'Новичок' },
                    { limit: 1000, name: 'Перспективный' },
                    { limit: 2500, name: 'Футболист' },
                    { limit: 5000, name: 'Опытный' },
                    { limit: 7500, name: 'Старожил' },
                    { limit: 10000, name: 'Мастер' },
                    { limit: 12500, name: 'Герой' },
                    { limit: 15000, name: 'Магистр' },
                    { limit: 20000, name: 'Посвященный' },
                    { limit: 25000, name: 'Ветеран' },
                    { limit: 30000, name: 'Виртуоз' },
                    { limit: 35000, name: 'Элита' },
                    { limit: 45000, name: 'Чемпион' },
                    { limit: 60000, name: 'Хранитель' },
                    { limit: 75000, name: 'Вершитель' },
                    { limit: 90000, name: 'Избранный' },
                    { limit: Infinity, name: 'Легенда' }
                ];

                let currentLevelIndex = 0;
                for (let i = 0; i < titles.length; i++) {
                    if (experience <= titles[i].limit) {
                        currentLevelIndex = i;
                        break;
                    }
                }

                const currentLevel = titles[currentLevelIndex];
                const prevLimit = currentLevelIndex === 0 ? 0 : titles[currentLevelIndex - 1].limit;
                const nextLimit = currentLevel.limit === Infinity ? currentLevel.limit : currentLevel.limit;

                const expInLevel = experience - prevLimit;
                const expRange = nextLimit - prevLimit;
                const percent = nextLimit === Infinity ? 100 : Math.min(100, (expInLevel / expRange) * 100);

                const barFill = document.getElementById('experience-bar-fill');
                const barText = document.getElementById('experience-bar-text');
                const titleEl = document.getElementById('title');

                if (barFill) {
                    barFill.style.width = percent + '%';
                }

                if (barText) {
                    if (nextLimit === Infinity) {
                        barText.textContent = `${experience} / ∞`;
                    } else {
                        barText.textContent = `${experience} / ${nextLimit}`;
                    }
                }

                if (titleEl) {
                    titleEl.textContent = currentLevel.name;
                }
            }

            // Подсчитываем опыт и обновляем UI
            const experience = calculateExperience();
            updateExperienceBar(experience);
        }

    } catch (error) {
        console.error("Ошибка при загрузке данных игрока:", error);
    }
});
