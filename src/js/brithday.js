document.addEventListener('DOMContentLoaded', () => {
    fetch('http://localhost:3000/api/birthdays')
        .then(response => response.json())
        .then(data => {
            const blocks = document.querySelectorAll('.block_birthday_players');
            data.forEach((player, index) => {
                if (blocks[index]) {
                    blocks[index].querySelector('.name_player').textContent = player.name;
                    blocks[index].querySelector('.data_title').textContent = player.birthday;
                    blocks[index].querySelector('.timer').textContent = `Через ${player.days_left} дней`;
                }
            });
        })
        .catch(err => {
            console.error('Ошибка загрузки дней рождения:', err);
        });
});