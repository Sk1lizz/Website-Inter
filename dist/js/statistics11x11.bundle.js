!function(){const n=[{number:"1.",name:"Костич",counter:"3"},{number:"2.",name:"Волконский",counter:"12"},{number:"3.",name:"Шеин",counter:"2"},{number:"4.",name:"Петрищев",counter:"3"},{number:"6.",name:"Пашаев",counter:"1"},{number:"7.",name:"Пожидаев",counter:"1"},{number:"8.",name:"Нишанов",counter:"2"},{number:"9.",name:"Шаропов",counter:"7"},{number:"10.",name:"Штепа",counter:"3"},{number:"11.",name:"Кпавченко",counter:"1"},{number:"13.",name:"Филимонов",counter:"1"},{number:"13.",name:"Данишевский",counter:"2"},{number:"14.",name:"Степанян",counter:"1"},{number:"15.",name:"Макаров",counter:"2"}],e=[{number:"1.",name:"Власов",counter:"1"},{number:"2.",name:"Пашаев",counter:"1"},{number:"3.",name:"Долгов",counter:"1"},{number:"4.",name:"Костич",counter:"2"},{number:"5.",name:"Волконский",counter:"4"},{number:"6.",name:"Петрищев",counter:"4"},{number:"7.",name:"Шаропов",counter:"4"},{number:"8.",name:"Матвеев",counter:"1"},{number:"9.",name:"Штепа",counter:"1"},{number:"10.",name:"Сыпченко",counter:"1"},{number:"11.",name:"Клейменов",counter:"1"},{number:"12.",name:"Королев И.",counter:"1"},{number:"13.",name:"Данишевский",counter:"1"},{number:"14.",name:"Белов",counter:"2"},{number:"15.",name:"Шеин",counter:"1"},{number:"16.",name:"Амири",counter:"2"},{number:"17.",name:"Макаров",counter:"1"}],t=[{number:"1.",name:"Сыпченко",counter:"13"},{number:"3.",name:"Мищенко",counter:"4"},{number:"2.",name:"Исаев",counter:"9"}],a=[{number:"1.",name:"Сыпченко",counter:"2"},{number:"2.",name:"Исаев",counter:"1"},{number:"3.",name:"Кулигин",counter:"1"},{number:"4.",name:"Бутусов",counter:"1"}],r=[{name:"Батуев",training:17},{name:"Белов",training:18},{name:"Белоножкин",training:15},{name:"Бутин",training:11},{name:"Бутусов",training:12},{name:"Власов",training:16},{name:"Волконский",training:38},{name:"Данишевский",training:20},{name:"Иванов",training:38},{name:"Долгов",training:23},{name:"Швамбергер",training:19},{name:"Исаев",training:9},{name:"Пашаев",training:4},{name:"Клеменой",training:9},{name:"Королев Д.",training:53},{name:"Королев И.",training:24},{name:"Кравченко",training:4},{name:"Костич",training:17},{name:"Кулигин",training:10},{name:"Нарванов",training:4},{name:"Ларин",training:25},{name:"Лешанков",training:18},{name:"Макаров",training:6},{name:"Матвеев",training:69},{name:"Мкома",training:3},{name:"Норванов",training:1},{name:"Нишанов",training:9},{name:"Мищенко",training:3},{name:"Петрищев",training:58},{name:"Пожидаев",training:31},{name:"Полевой",training:38},{name:"Родионов",training:0},{name:"Тапчан",training:0},{name:"Савельев",training:11},{name:"Салимгареев",training:47},{name:"Свирщевский",training:35},{name:"Стребков",training:22},{name:"Сыпченко",training:33},{name:"Устинов",training:9},{name:"Филимонов",training:8},{name:"Амири",training:53},{name:"Шаропов",training:67},{name:"Шеин",training:66},{name:"Штепа",training:18},{name:"Эрик",training:12},{name:"Юсуф",training:9},{name:"Майоров",training:5},{name:"Сафаров",training:5},{name:"Аралекян",training:5},{name:"Демидов",training:1},{name:"Хамзин",training:2},{name:"Турнусов",training:2},{name:"Саидов",training:4},{name:"Малышев",training:3}],i=n=>n.sort(((n,e)=>parseInt(e.counter)-parseInt(n.counter)));i(n),i(e);const m=n=>{n.forEach(((n,e)=>{n.number=`${e+1}.`}))};m(n),m(e);const o={};n.forEach((n=>{o[n.name]={name:n.name,goals:parseInt(n.counter)||0,assists:0}})),e.forEach((n=>{o[n.name]?o[n.name].assists=parseInt(n.counter)||0:o[n.name]={name:n.name,goals:0,assists:parseInt(n.counter)||0}}));const c=Object.values(o);c.sort(((n,e)=>{const t=n.goals+n.assists;return e.goals+e.assists-t}));const s=document.querySelector(".goals-list"),u=document.querySelector(".assists-list"),l=document.querySelector(".goals-assists-list");n.forEach((n=>{const e=document.createElement("li");e.innerHTML=`\n    <div class="number">${n.number}</div>\n    <div class="player">${n.name}</div>\n    <div class="counter">${n.counter}</div>\n    `,s.appendChild(e)})),e.forEach((n=>{const e=document.createElement("li");e.innerHTML=`\n    <div class="number">${n.number}</div>\n    <div class="player">${n.name}</div>\n    <div class="counter">${n.counter}</div>\n    `,u.appendChild(e)})),c.forEach(((n,e)=>{const t=document.createElement("li");t.innerHTML=`\n    <div class="number">${e+1}.</div>\n    <div class="player">${n.name}</div>\n    <div class="total">${n.goals+n.assists}</div>\n    `,l.appendChild(t)}));const d=document.querySelector(".goalkeepers-list");t.sort(((n,e)=>parseInt(e.counter)-parseInt(n.counter))),t.forEach((n=>{const e=document.createElement("li");e.innerHTML=`\n        <div class="number">${n.number}</div>\n        <div class="player">${n.name}</div>\n        <div class="counter">${n.counter}</div>\n    `,d.appendChild(e)}));const g=document.querySelector(".goalkeepers-list-zero");a.sort(((n,e)=>parseInt(e.counter)-parseInt(n.counter))),a.forEach((n=>{const e=document.createElement("li");e.innerHTML=`\n<div class="number">${n.number}</div>\n<div class="player">${n.name}</div>\n<div class="counter">${n.counter}</div>\n`,g.appendChild(e)}));const b=document.querySelector(".zanetti_top-list");r.sort(((n,e)=>e.training-n.training)),r.slice(0).forEach(((n,e)=>{const t=document.createElement("li");t.innerHTML=`\n<div class="position">${e+1+"."}</div>\n<div class="name">${n.name}</div>\n<div class="training">${n.training}</div>\n`,b.appendChild(t)}));const p=parseInt(document.querySelector(".goals .number").textContent)-parseInt(document.querySelector(".goals_conceded .number").textContent);document.querySelector(".difference .number").textContent=`${p>=0?"+":""}${p}`;const v=document.querySelector(".goals-list-top");createListItems(n,v);const $=document.querySelector(".assists-list-top");createListItems(e,$);const y=document.querySelector(".goals-assists-list-top");createListItems(c,y);const I=document.querySelector(".goalkeepers-list-top");createListItems(t,I);const E=document.querySelector(".goalkeepers-list-zero-top");createListItems(a,E);const h=document.querySelector(".zanetti_top-list-top");createListItems(r,h),document.addEventListener("DOMContentLoaded",(function(){}))}();