document.addEventListener("DOMContentLoaded",(function(){const a=document.querySelector(".number").textContent,s=document.querySelector(".statisticplayersall"),t=document.querySelector(".statisticthisyears"),e=[{number:"coach",name:"Пешехонов",matches:"480",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"1",name:"Исаев",matches:"87",goals:"0",assist:"1",zeromatch:"10",lostgoals:"237",team:"proand8x8"},{number:"2",name:"Хакимов",matches:"17",goals:"16",assist:"6",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"3",name:"Фирдавс",matches:"14",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"4",name:"Малышев",matches:"23",goals:"1",assist:"6",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"5",name:"Волокитин",matches:"70",goals:"24",assist:"13",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"6",name:"Касулин",matches:"17",goals:"3",assist:"4",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"7",name:"Тапчан",matches:"301",goals:"164",assist:"47",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"8",name:"Устинов",matches:"0",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"proand8x8"},{number:"9",name:"Белоножкин",matches:"490",goals:"156",assist:"108",zeromatch:"0",lostgoals:"0",team:"proand8x8"},{number:"10",name:"Костич",matches:"58",goals:"15",assist:"6",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"11",name:"Полевой",matches:"9",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"12",name:"Шаропов",matches:"20",goals:"6",assist:"3",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"14",name:"Голуб",matches:"230",goals:"23",assist:"17",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"15",name:"Королев",matches:"11",goals:"0",assist:"1",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"16",name:"Петросян",matches:"1",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"17",name:"Петрищев",matches:"47",goals:"3",assist:"7",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"18",name:"Губский",matches:"235",goals:"124",assist:"19",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"19",name:"Агатов",matches:"15",goals:"1",assist:"8",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"20",name:"Власов",matches:"240",goals:"22",assist:"16",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"21",name:"Клейменов",matches:"3",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"22",name:"Макарчев",matches:"59",goals:"51",assist:"24",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"23",name:"Бубнов",matches:"18",goals:"8",assist:"5",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"24",name:"Лешанков",matches:"93",goals:"8",assist:"9",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"25",name:"Грумынский",matches:"8",goals:"0",assist:"3",zeromatch:"2",lostgoals:"0",team:"pro"},{number:"26",name:"Голованов",matches:"32",goals:"0",assist:"3",zeromatch:"2",lostgoals:"123",team:"pro"},{number:"27",name:"Филимонов",matches:"4",goals:"1",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"28",name:"Ларин",matches:"27",goals:"0",assist:"0",zeromatch:"4",lostgoals:"57",team:"pro"},{number:"29",name:"Свирщевский",matches:"9",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"30",name:"Салимгареев",matches:"23",goals:"0",assist:"2",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"31",name:"Дудочкин",matches:"3",goals:"0",assist:"0",zeromatch:"0",lostgoals:"3",team:"8x8"},{number:"32",name:"Волконский",matches:"57",goals:"18",assist:"10",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"33",name:"Кравченко",matches:"2",goals:"4",assist:"2",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"35",name:"Сыпченко",matches:"22",goals:"0",assist:"1",zeromatch:"2",lostgoals:"48",team:"pro"},{number:"37",name:"Шеин",matches:"164",goals:"33",assist:"37",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"41",name:"Синицын",matches:"43",goals:"1",assist:"3",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"47",name:"Языков",matches:"193",goals:"325",assist:"134",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"50",name:"Штепа",matches:"11",goals:"2",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"52",name:"Мкома",matches:"5",goals:"0",assist:"0",zeromatch:"0",lostgoals:"10",team:"pro"},{number:"55",name:"Нестор",matches:"40",goals:"0",assist:"8",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"63",name:"Скворцов",matches:"118",goals:"2",assist:"9",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"64",name:"Беров",matches:"39",goals:"39",assist:"3",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"66",name:"Королев",matches:"19",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"69",name:"Долгов",matches:"66",goals:"14",assist:"10",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"72",name:"Абянов",matches:"18",goals:"9",assist:"4",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"73",name:"Белов",matches:"112",goals:"40",assist:"34",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"74",name:"Котов",matches:"23",goals:"2",assist:"2",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"75",name:"Матвеев",matches:"29",goals:"5",assist:"3",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"77",name:"Иванов",matches:"12",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"81",name:"Мытько",matches:"25",goals:"0",assist:"0",zeromatch:"1b",lostgoals:"71",team:"pro"},{number:"86",name:"Гусев",matches:"10",goals:"0",assist:"1",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"87",name:"Данишевский",matches:"3",goals:"1",assist:"1",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"89",name:"Пожидаев",matches:"4",goals:"1",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"90",name:"Юсуф",matches:"18",goals:"1",assist:"3",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"99",name:"Кулигин",matches:"2",goals:"0",assist:"0",zeromatch:"1",lostgoals:"3",team:"pro"}].find((s=>s.number===a)),o=[{number:"1",name:"Исаев",matches:"9",goals:"0",assist:"0",zeromatch:"1",lostgoals:"33",team:"proand8x8"},{number:"2",name:"Хакимов",matches:"17",goals:"16",assist:"6",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"3",name:"Фирдавс",matches:"14",goals:"0",assist:"1",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"4",name:"Малышев",matches:"4",goals:"0",assist:"1",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"5",name:"Волокитин",matches:"12",goals:"1",assist:"1",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"6",name:"Касулин",matches:"17",goals:"3",assist:"3",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"7",name:"Тапчан",matches:"15",goals:"0",assist:"5",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"8",name:"Устинов",matches:"0",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"proand8x8"},{number:"9",name:"Белоножкин",matches:"39",goals:"7",assist:"14",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"10",name:"Костич",matches:"29",goals:"10",assist:"6",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"11",name:"Полевой",matches:"9",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"12",name:"Шаропов",matches:"20",goals:"6",assist:"3",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"14",name:"Голуб",matches:"14",goals:"10",assist:"6",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"15",name:"Королев",matches:"11",goals:"0",assist:"1",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"16",name:"Петросян",matches:"1",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"17",name:"Петрищев",matches:"23",goals:"2",assist:"5",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"18",name:"Губский",matches:"0",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"19",name:"Агатов",matches:"0",goals:"0",assist:"2",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"20",name:"Власов",matches:"38",goals:"5",assist:"11",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"21",name:"Клейменов",matches:"3",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"22",name:"Макарчев",matches:"10",goals:"7",assist:"1",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"23",name:"Бубнов",matches:"18",goals:"8",assist:"4",zeromatch:"0",lostgoals:"0",team:"8x8"},{number:"24",name:"Лешанков",matches:"3",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"25",name:"Грумынский",matches:"0",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"26",name:"Голованов",matches:"0",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"27",name:"Филимонов",matches:"4",goals:"1",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"28",name:"Ларин",matches:"2",goals:"0",assist:"0",zeromatch:"0",lostgoals:"5",team:"pro"},{number:"29",name:"Свирщевский",matches:"10",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"30",name:"Салимгареев",matches:"3",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"31",name:"Дудочкин",matches:"3",goals:"0",assist:"0",zeromatch:"0",lostgoals:"3",team:"8x8"},{number:"32",name:"Волконский",matches:"39",goals:"17",assist:"9",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"33",name:"Кравченко",matches:"2",goals:"1",assist:"1",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"35",name:"Сыпченко",matches:"22",goals:"0",assist:"1",zeromatch:"2",lostgoals:"50",team:"pro"},{number:"37",name:"Шеин",matches:"30",goals:"5",assist:"7",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"41",name:"Синицын",matches:"26",goals:"1",assist:"2",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"47",name:"Языков",matches:"29",goals:"40",assist:"18",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"50",name:"Штепа",matches:"10",goals:"2",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"52",name:"Мкома",matches:"5",goals:"0",assist:"0",zeromatch:"0",lostgoals:"10",team:"pro"},{number:"55",name:"Нестор",matches:"3",goals:"0",assist:"1",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"63",name:"Скворцов",matches:"37",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"64",name:"Беров",matches:"0",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"66",name:"Королев",matches:"19",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"69",name:"Долгов",matches:"3",goals:"1",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"72",name:"Абянов",matches:"13",goals:"12",assist:"3",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"73",name:"Белов",matches:"3",goals:"0",assist:"1",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"74",name:"Котов",matches:"12",goals:"1",assist:"1",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"75",name:"Матвеев",matches:"30",goals:"5",assist:"3",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"77",name:"Иванов",matches:"12",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"81",name:"Мытько",matches:"16",goals:"0",assist:"0",zeromatch:"1b",lostgoals:"34",team:"pro"},{number:"86",name:"Гусев",matches:"0",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"87",name:"Данишевский",matches:"3",goals:"1",assist:"1",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"89",name:"Пожидаев",matches:"5",goals:"1",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"90",name:"Юсуф",matches:"2",goals:"0",assist:"0",zeromatch:"0",lostgoals:"0",team:"pro"},{number:"99",name:"Кулигин",matches:"2",goals:"0",assist:"0",zeromatch:"1",lostgoals:"3",team:"pro"}].find((s=>s.number===a)),m=a=>Math.round(100*a)/100;if(e){s.querySelector(".matchesall").textContent=e.matches,s.querySelector(".goalall").textContent=e.goals,s.querySelector(".assistall").textContent=e.assist,s.querySelector(".zeromatchall").textContent="0"!==e.zeromatch?e.zeromatch:"0",s.querySelector(".goallostall").textContent="0"!==e.lostgoals?e.lostgoals:"0";const a=m(e.goals/e.matches),t=m(e.assist/e.matches),o=m(parseFloat(e.goals)+parseFloat(e.assist));isNaN(a)&&(a=0),isNaN(t)&&(t=0),isNaN(o)&&(o=0),s.querySelector(".goalallOnaverage").textContent=a,s.querySelector(".assistallOnaverage").textContent=t,s.querySelector(".assistgoalsall").textContent=o}if(o){t.querySelector(".matches").textContent=o.matches,t.querySelector(".goal").textContent=o.goals,t.querySelector(".assist").textContent=o.assist,t.querySelector(".zeromatch").textContent="0"!==o.zeromatch?o.zeromatch:"0",t.querySelector(".goallost").textContent="0"!==o.lostgoals?o.lostgoals:"0";const a=m(o.goals/o.matches),s=m(o.assist/o.matches),e=m(parseFloat(o.goals)+parseFloat(o.assist));isNaN(a)&&(a=0),isNaN(s)&&(s=0),isNaN(e)&&(e=0),t.querySelector(".goalOnaverage").textContent=a,t.querySelector(".assistOnaverage").textContent=s,t.querySelector(".assistgoals").textContent=e}}));