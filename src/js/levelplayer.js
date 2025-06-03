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

        let years = 0, months = 0;

        const img = document.querySelector(".player-card img");
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
            lostgoals: Number(stats.lostgoals) || 0
        });

        const season = safeStats(seasonStats);
        const all = safeStats(allStats);

        const totalStats = useSeasonOnly ? season : {
            matches: season.matches + all.matches,
            goals: season.goals + all.goals,
            assists: season.assists + all.assists,
            zeromatch: season.zeromatch + all.zeromatch,
            lostgoals: season.lostgoals + all.lostgoals
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
            const { matches, goals, assists, zeromatch } = totalStats;
            const totalMonths = years * 12 + months;
            return totalMonths * 100 + matches * 50 + goals * 125 + assists * 100 + zeromatch * 250 + achievementPoints;
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
                { limit: 90000, name: 'Избранный' },
                { limit: 110000, name: 'Мудрец' },
                { limit: 130000, name: 'Наставник' },
                { limit: 150000, name: 'Вдохновитель' },
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
        }

        const successWrapper = document.querySelector(".card.success");
        const successList = document.querySelector(".success-list");
        const successCountEl = document.getElementById("success-count");

        const [successListRes, ownedRes] = await Promise.all([
            fetch("/api/get_success_list.php"),
            fetch(`/api/get_player_success.php?player_id=${player.id}`)
        ]);

        const allSuccess = await successListRes.json();
        const ownedIds = await ownedRes.json(); // массив id
        const ownedSuccess = allSuccess.filter(s => ownedIds.includes(s.id));
        const achievementPoints = ownedSuccess.reduce((sum, s) => sum + (s.points || 0), 0);

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

        updateExperienceBar(calculateExperience(achievementPoints));

    } catch (error) {
        console.error("Ошибка при загрузке данных игрока:", error);
    }
});
