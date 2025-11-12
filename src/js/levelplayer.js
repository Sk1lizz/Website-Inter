document.addEventListener("DOMContentLoaded", async () => {
    const playerId = new URLSearchParams(window.location.search).get("id");
    if (!playerId) return console.error("ID игрока не указан в URL");

    function formatDate(dateStr, format = "full") {
        const d = new Date(dateStr);
        if (format === "short") return d.getFullYear().toString();
        if (format === "numeric") return `${String(d.getDate()).padStart(2, '0')}.${String(d.getMonth() + 1).padStart(2, '0')}.${d.getFullYear()}`;
        return d.toLocaleDateString('ru-RU', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function declension(num, forms) {
        const mod10 = num % 10, mod100 = num % 100;
        if (mod100 >= 11 && mod100 <= 14) return forms[2];
        if (mod10 === 1) return forms[0];
        if (mod10 >= 2 && mod10 <= 4) return forms[1];
        return forms[2];
    }

    function calculateAge(dateStr) {
        const birthDate = new Date(dateStr), today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
        return age;
    }

    try {
        const playerRes = await fetch(`/api/get_player.php?id=${playerId}`);
        if (!playerRes.ok) throw new Error("Игрок не найден");
        const player = await playerRes.json();

        // === Подтягиваем очки Fantasy и прибавляем их к опыту ===
        try {
            const fantasyRes = await fetch(`/api/get_fantasy_points.php?player_id=${player.id}`);
            if (fantasyRes.ok) {
                const data = await fantasyRes.json();
                if (data && data.success && typeof data.points === "number") {
                    player.fantasy_points = data.points; // сохраняем отдельно
                    console.log(`Fantasy XP добавлено: +${data.points}`);
                }
            }
        } catch (e) {
            console.warn("Ошибка загрузки fantasy points:", e);
        }

        const bg = document.querySelector('.bg-fixed');
        if (bg && player.full_image_path) {
            bg.style.backgroundImage = `url('${player.full_image_path}')`;
        }

        if (player.full_image_path) {
            const page = document.querySelector('.player_page');
            if (page) {
                page.style.backgroundImage = `url('${player.full_image_path}')`;
                page.style.backgroundSize = 'cover';
                page.style.backgroundRepeat = 'no-repeat';
                page.style.backgroundPosition = 'center';
                page.style.backgroundAttachment = 'fixed';
                page.style.backgroundColor = 'transparent';
                page.style.minHeight = '100vh';
            }
        }

        let years = 0, months = 0;

        if (!player || !player.id) throw new Error("Данные игрока неполные");
        document.title = `${player.name} ${player.patronymic || ''} | FC Inter Moscow`.trim();

        const img = document.querySelector(".player-card img");

        try {
            const frRes = await fetch(`/api/get_player_frame.php?player_id=${player.id}`);
            const fr = await frRes.json();
            const key = (fr && fr.frame_key) ? fr.frame_key : '';

            // удаляем все возможные рамки, чтобы не было наложений
            img.classList.remove(
                'frame-gold', 'frame-gold-glow',
                'frame-green', 'frame-green-glow',
                'frame-blue', 'frame-blue-glow',
                'frame-purple', 'frame-purple-glow'
            );

            // добавляем новую рамку по ключу
            switch (key) {
                case 'gold':
                    img.classList.add('frame-gold');
                    break;
                case 'gold_glow':
                    img.classList.add('frame-gold-glow');
                    break;
                case 'green':
                    img.classList.add('frame-green');
                    break;
                case 'green_glow':
                    img.classList.add('frame-green-glow');
                    break;
                case 'blue':
                    img.classList.add('frame-blue');
                    break;
                case 'blue_glow':
                    img.classList.add('frame-blue-glow');
                    break;
                case 'purple':
                    img.classList.add('frame-purple');
                    break;
                case 'purple_glow':
                    img.classList.add('frame-purple-glow');
                    break;
                default:
                    // без рамки
                    break;
            }
        } catch (e) {
            console.warn('frame fetch fail', e);
        }

        img.src = `/img/player/player_${player.id}.png`;
        img.alt = `${player.name} ${player.patronymic || ''}`.trim();
        img.onerror = () => { img.src = "/img/player/player_0.png"; };

        const infoEl = document.querySelector(".player-info");
        const joinDate = new Date(player.join_date), now = new Date();
        years = now.getFullYear() - joinDate.getFullYear();
        months = now.getMonth() - joinDate.getMonth();
        if (months < 0) { years--; months += 12; }

        const teamDuration = [];
        if (years > 0) teamDuration.push(`${years} ${declension(years, ['год', 'года', 'лет'])}`);
        if (months > 0) teamDuration.push(`${months} ${declension(months, ['месяц', 'месяца', 'месяцев'])}`);

        document.querySelector(".player-name").textContent = `${player.name} ${player.patronymic || ''}`.trim();
        infoEl.innerHTML = `
            <p><strong>Номер:</strong> ${player.number}</p>
            <p><strong>Позиция:</strong> ${player.position}</p>
            <p><strong>Рост:</strong> ${player.height_cm || '-'}</p>
            <p><strong>Вес:</strong> ${player.weight_kg || '-'}</p>
            <p><strong>Возраст:</strong> ${calculateAge(player.birth_date)}</p>
            <p><strong>Присоединился:</strong> ${formatDate(player.join_date, 'short')}</p>
            <p><strong>Дата Рождения:</strong> ${formatDate(player.birth_date, 'numeric')}</p>
            <p><strong>Время в команде:</strong> ${teamDuration.join(' ') || 'менее месяца'}</p>
        `;

        const statsRes = await fetch(`/api/player_statistics_all.php?id=${player.id}`);
        if (!statsRes.ok) throw new Error("Ошибка загрузки статистики");
        const statsJson = await statsRes.json();
        const seasonStats = statsJson.season || {};
        const allStats = statsJson.all || {};

        const useSeasonOnly = ['matches', 'goals', 'assists', 'zeromatch', 'lostgoals', 'zanetti_priz']
            .every(key => !allStats[key] || Number(allStats[key]) === 0);

        const safeStats = (stats) => ({
            matches: Number(stats.matches) || 0,
            goals: Number(stats.goals) || 0,
            assists: Number(stats.assists) || 0,
            zeromatch: Number(stats.zeromatch) || 0,
            lostgoals: Number(stats.lostgoals) || 0,
            zanetti_priz: Number(stats.zanetti_priz) || 0
        });

        // Подсчет месяцев в команде в текущем году
        const joined = new Date(player.join_date);
        // используем уже существующий now
        // Подсчет ПОЛНЫХ месяцев в команде в текущем году
        let monthsInThisYear = 0;
        const joinYear = joined.getFullYear();
        const joinMonth = joined.getMonth(); // 0 = январь
        const currentYear = now.getFullYear();
        const currentMonth = now.getMonth();
        const currentDay = now.getDate();

        // Если игрок присоединился до текущего года — считаем месяцы до текущего месяца
        if (joinYear < currentYear) {
            monthsInThisYear = currentMonth; // полные месяцы: январь...предыдущий
        }
        // Если в этом же году
        else if (joinYear === currentYear) {
            // Если месяц присоединения раньше текущего — разница
            if (joinMonth < currentMonth) {
                monthsInThisYear = currentMonth - joinMonth;
            }
            // Если в этом месяце — проверим, прошёл ли он полностью
            else if (joinMonth === currentMonth && now.getDate() >= 28) {
                monthsInThisYear = 1; // только если почти конец месяца
            } else {
                monthsInThisYear = 0;
            }
        }

        monthsInThisYear = Math.max(0, monthsInThisYear); // защита от отрицательных значений

        const yearExperience =
            monthsInThisYear * 100 +
            (seasonStats.matches || 0) * 50 +
            (seasonStats.goals || 0) * 100 +
            (seasonStats.assists || 0) * 100 +
            (seasonStats.zeromatch || 0) * 250;

        const yearExpEl = document.getElementById('year-exp');
        if (yearExpEl) {
            yearExpEl.textContent = yearExperience;
        }

        const season = safeStats(seasonStats);
        const all = safeStats(allStats);

        const totalStats = useSeasonOnly ? season : {
            matches: season.matches + all.matches,
            goals: season.goals + all.goals,
            assists: season.assists + all.assists,
            zeromatch: season.zeromatch + all.zeromatch,
            lostgoals: season.lostgoals + all.lostgoals,
            zanetti_priz: season.zanetti_priz + all.zanetti_priz
        };

        document.querySelector(".season-stats").innerHTML = `
            <div><div class="number">${seasonStats.matches || 0}</div>Матчей</div>
            <div><div class="number">${seasonStats.goals || 0}</div>Голов</div>
            <div><div class="number">${seasonStats.assists || 0}</div>Ассистов</div>
            <div><div class="number">${(seasonStats.goals || 0) + (seasonStats.assists || 0)}</div>Гол+пас</div>
            <div><div class="number">${seasonStats.lostgoals || 0}</div>Голов пропущено</div>
            <div><div class="number">${seasonStats.zeromatch || 0}</div>Матчей на 0</div>
        `;

        document.querySelector(".all-stats").innerHTML = `
            <div><div class="number number2 matches">${totalStats.matches}</div>Матчей</div>
            <div><div class="number number2 goals">${totalStats.goals}</div>Голов</div>
            <div><div class="number number2 assists">${totalStats.assists}</div>Ассистов</div>
            <div><div class="number">${totalStats.goals + totalStats.assists}</div>Гол+пас</div>
            <div><div class="number number2 lostgoals">${totalStats.lostgoals}</div>Голов пропущено</div>
            <div><div class="number number2 zeromatch">${totalStats.zeromatch}</div>Матчей на 0</div>
        `;

        function calculateExperience(achievementPoints = 0) {
            const { matches, goals, assists, zeromatch, zanetti_priz } = totalStats;
            const totalMonths = years * 12 + months;
            const trainingXP = zanetti_priz * 25;
            return totalMonths * 100 +
                matches * 50 +
                goals * 100 +
                assists * 100 +
                zeromatch * 250 +
                trainingXP +
                achievementPoints;
        }

        function updateExperienceBar(exp) {
            const levels = [
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
                { limit: 100000, name: 'Избранный' },
                { limit: 125000, name: 'Мудрец' },
                { limit: 150000, name: 'Наставник' },
                { limit: 175000, name: 'Архонт' },
                { limit: 200000, name: 'Маэстро' },
                { limit: 225000, name: 'Хранитель огня' },
                { limit: 250000, name: 'Лидер эпохи' },
                { limit: 275000, name: 'Идеал' },
                { limit: 300000, name: 'Миф' },
                { limit: 350000, name: 'Символ клуба' },
                { limit: 400000, name: 'Бессмертный' },
                { limit: 450000, name: 'Наследие' },
                { limit: 500000, name: 'Полубог' },
                { limit: Infinity, name: 'Легенда' }
            ];


            const current = levels.find(l => exp <= l.limit) || levels.at(-1);
            const prev = levels[levels.indexOf(current) - 1]?.limit || 0;
            const percent = current.limit === Infinity ? 100 : Math.min(100, ((exp - prev) / (current.limit - prev)) * 100);

            document.getElementById("experience-bar-fill").style.width = `${percent}%`;
            document.getElementById("experience-bar-text").textContent = `${exp} / ${current.limit === Infinity ? '∞' : current.limit}`;
            document.getElementById("title").textContent = current.name;

            const imgEl = document.querySelector(".player-card img");

            const playerStarEl = document.querySelector(".player-star");
            const playerNameEl = document.querySelector(".player-name");

            // Управляем звездочкой ⭐
            if (current.limit >= 2500) {
                playerStarEl.style.display = 'block';
            } else {
                playerStarEl.style.display = 'none';
            }

            // Всегда обновляем фамилию (чтобы не зависеть от прошлых innerHTML)
            playerNameEl.textContent = `${player.name} ${player.patronymic || ''}`.trim();

            const levelPrizes = [
                "Страница на сайте",
                "Поздравление в соцсетях с Днем Рождения",
                "Эмодзи звезда в профиле",
                "Золотая рамка фотографии на сайте",
                "Книга от руководителя команды",
                "Интервью + пост в соцсетях",
                "Фон профиля на выбор",
                "Подписка на Okko на 1 месяц",
                "Разбор игры с ТТД",
                "Футболка гостевая/тренировочная",
                "Telegram Premium подписка",
                "Матч в роли капитана",
                "Футболка гостевая/тренировочная",
                "1 месяц тренировок или 3 мес без взносов (8x8)",
                "Футболка-поло с логотипом",
                "Зал Славы + футболка с золотым номером",
                "Ветровка Kappa",
                "В разработке"
            ];

        }

        // Загружаем АЧИВКИ
        const successWrapper = document.querySelector(".card.success");
        const successList = document.querySelector(".success-list");
        const successCountEl = document.getElementById("success-count");

        const [successListRes, ownedRes] = await Promise.all([
            fetch("/api/get_success_list.php"),
            fetch(`/api/get_player_success.php?player_id=${player.id}`)
        ]);
        let totalAchievementPoints = 0;
        const allSuccess = await successListRes.json();
        const ownedIds = await ownedRes.json(); // массив id
        const ownedSuccess = allSuccess.filter(s => ownedIds.includes(s.id));
        const achievementPoints = ownedSuccess.reduce((sum, s) => sum + (s.points || 0), 0);
        totalAchievementPoints = achievementPoints;

        // Добавляем очки из Fantasy к общему опыту перед отрисовкой
        const fantasyXP = player.fantasy_points || 0;
        const totalExp = calculateExperience(totalAchievementPoints) + fantasyXP;
        updateExperienceBar(totalExp);

        if (fantasyXP > 0) {
            const bar = document.querySelector("#experience-bar-text");
            if (bar && !document.querySelector(".xp-fantasy")) {
                bar.insertAdjacentHTML(
                    "afterend",
                    `<div class="xp-fantasy" style="font-size:14px;color:#FDC500;margin-top:4px;">
        +${fantasyXP} XP (Fantasy)
      </div>`
                );
            }
        }


        successWrapper.style.display = ownedSuccess.length > 0 ? 'block' : 'none';
        successCountEl.textContent = `${ownedSuccess.length} / ${allSuccess.length} • ${achievementPoints} очков`;
        successList.innerHTML = '';

        ownedSuccess.forEach(s => {
            const iconPath = `/img/success/success-${s.id}.png`;
            const fallback = `/img/success/success-0.png`;

            const wrapper = document.createElement('div');
            wrapper.className = 'success-item';
            wrapper.style.marginBottom = '14px';
            wrapper.style.paddingTop = '10px';

            wrapper.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <img src="${iconPath}" onerror="this.src='${fallback}'" width="50" height="50" style="border-radius: 6px; flex-shrink: 0;">
                    <div style="flex: 1;">
                        <div style="font-weight: bold;">${s.title}</div>
                        <div style="color: #c5c2c2; font-size: 14px;">${s.description}</div>
                    </div>
                    <div style="color: #2D62B5; font-weight: bold; font-size: 14px;">${s.points} очков</div>
                </div>
            `;
            successList.appendChild(wrapper);
        });


        // Загружаем ДОСТИЖЕНИЯ (награды)
        const achievementsCard = document.querySelector(".achievements-card");
        const listEl = achievementsCard?.querySelector(".achievements-list");

        try {
            const achRes = await fetch(`/api/achievements.php?player_id=${player.id}`);
            const text = await achRes.text();
            const achievements = JSON.parse(text);
            const achievementsPoints = (achievements?.length || 0) * 1000;
            totalAchievementPoints += achievementsPoints;

            achievementsCard.style.display = 'block';
            listEl.innerHTML = '';

            if (Array.isArray(achievements) && achievements.length > 0) {
                achievements.forEach(a => {
                    const div = document.createElement('div');
                    div.classList.add('career-item');
                    div.innerHTML = `
                        ${a.award_title}<span>${a.award_year}</span>
                        <small>${a.team_name}</small>
                    `;
                    listEl.appendChild(div);
                });
            } else {
                listEl.innerHTML = '<div class="empty-achievements">Нет достижений</div>';
            }
        } catch (err) {
            console.error("Ошибка при загрузке достижений:", err);
            if (achievementsCard && listEl) {
                achievementsCard.style.display = 'block';
                listEl.innerHTML = '<div class="error-msg">Не удалось загрузить достижения</div>';
            }
        }

    } catch (error) {
        console.error("Ошибка при загрузке данных игрока:", error);
    }
});