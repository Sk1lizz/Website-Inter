document.addEventListener("DOMContentLoaded",(function(){const e=document.querySelector("#datateamTime").textContent.trim().split(" ");console.log("Слова в тексте:",e);let t=0,n=0;e.forEach(((o,r)=>{"год"===o||"года"===o||"лет"===o?t=parseInt(e[r-1]):"месяц"!==o&&"месяца"!==o&&"месяцев"!==o||(n=parseInt(e[r-1]))}));const o=12*t+n,r=document.querySelectorAll(".statisticplayersall .block"),l=100*o+50*parseInt(r[0].querySelector(".matchesall").textContent)+125*parseInt(r[1].querySelector(".goalall").textContent)+100*parseInt(r[3].querySelector(".assistall").textContent)+250*parseInt(r[7].querySelector(".zeromatchall").textContent)+1e3*(parseInt(document.querySelector(".Number_Awards").textContent)||0)+250*(parseInt(document.querySelector(".Number_Month_nomination").textContent)||0)+500*(parseInt(document.querySelector(".Number_Player_Month").textContent)||0)+500*(parseInt(document.querySelector(".Number_Year_nomination").textContent)||0)+2500*(parseInt(document.querySelector(".Number_Year_best").textContent)||0);let a="";l>=0&&l<500?a="Новичок":l>=500&&l<=1e3?a="Перспективный":l>=1001&&l<=2500?a="Футболист":l>=2501&&l<=5e3?a="Опытный":l>=5001&&l<=7500?a="Старожил":l>=7501&&l<=1e4?a="Мастер":l>=10001&&l<=12500?a="Герой":l>=12501&&l<=15e3?a="Магистр":l>=15001&&l<=2e4?a="Посвященный":l>=20001&&l<=25e3?a="Ветеран":l>=25001&&l<=3e4?a="Виртуоз":l>=30001&&l<=35e3?a="Элита":l>=35001&&l<=45e3?a="Чемпион":l>=45001&&l<=6e4?a="Хранитель":l>=60001&&l<=75e3?a="Вершитель":l>=75001&&l<=9e4?a="Избранный":l>=90001&&(a="Легенда");const c=document.querySelector("#experience"),u=document.querySelector("#title");c.textContent=l,u.textContent=a}));