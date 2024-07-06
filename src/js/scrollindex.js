let isDown = false;
let startX;
let scrollLeft;

const slider = document.querySelector('.scrolling-wrapper');

slider.addEventListener('mousedown', (e) => {
    isDown = true;
    slider.classList.add('active');
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
    e.preventDefault(); // отменяем действие по умолчанию, чтобы предотвратить выделение текста
});
slider.addEventListener('mouseleave', () => {
    isDown = false;
    slider.classList.remove('active');
});
slider.addEventListener('mouseup', () => {
    isDown = false;
    slider.classList.remove('active');
});
slider.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    const x = e.pageX - slider.offsetLeft;
    const walk = (x - startX) * 3; // Adjust scrolling speed here
    slider.scrollLeft = scrollLeft - walk;
});