!function(){const n=[{number:"1.",name:"Костич",counter:"3"},{number:"2.",name:"Волконский",counter:"7"},{number:"3.",name:"Шеин",counter:"3"},{number:"4.",name:"Петрищев",counter:"2"},{number:"6.",name:"Пашаев",counter:"1"},{number:"7.",name:"Пожидаев",counter:"1"},{number:"8.",name:"Нишанов",counter:"2"},{number:"9.",name:"Шаропов",counter:"5"},{number:"10.",name:"Штепа",counter:"1"}],e=[{number:"1.",name:"Власов",counter:"1"},{number:"2.",name:"Пашаев",counter:"1"},{number:"3.",name:"Долгов",counter:"1"},{number:"4.",name:"Костич",counter:"1"},{number:"5.",name:"Волконский",counter:"2"},{number:"6.",name:"Петрищев",counter:"4"},{number:"7.",name:"Шаропов",counter:"2"},{number:"8.",name:"Матвеев",counter:"2"},{number:"9.",name:"Штепа",counter:"1"},{number:"10.",name:"Сыпченко",counter:"1"},{number:"11.",name:"Клейменов",counter:"1"}],t=[{number:"1.",name:"Сыпченко",counter:"19"},{number:"3.",name:"Мищенко",counter:"4"},{number:"2.",name:"Исаев",counter:"9"}],a=[{number:"1.",name:"Сыпченко",counter:"2"},{number:"2.",name:"Исаев",counter:"1"}],r=[{name:"Батуев",training:16},{name:"Белоножкин",training:11},{name:"Бутин",training:11},{name:"Власов",training:16},{name:"Волконский",training:22},{name:"Иванов",training:23},{name:"Долгов",training:23},{name:"Швамбергер",training:18},{name:"Исаев",training:9},{name:"Пашаев",training:4},{name:"Королев Д.",training:25},{name:"Королев И.",training:15},{name:"Костич",training:14},{name:"Ларин",training:12},{name:"Матвеев",training:40},{name:"Нишанов",training:9},{name:"Мищенко",training:3},{name:"Петрищев",training:41},{name:"Родионов",training:0},{name:"Тапчан",training:0},{name:"Савельев",training:11},{name:"Салимгареев",training:31},{name:"Свирщевский",training:7},{name:"Сыпченко",training:19},{name:"Амири",training:34},{name:"Шаропов",training:35},{name:"Шеин",training:34},{name:"Юсуф",training:4},{name:"Аралекян",training:5},{name:"Демидов",training:1},{name:"Пожидаев",training:23},{name:"Хамзин",training:2},{name:"Турнусов",training:2},{name:"Штепа",training:5},{name:"Полевой",training:16},{name:"Саидов",training:4},{name:"Малышев",training:3},{name:"Клеменой",training:2}],i=n=>n.sort(((n,e)=>parseInt(e.counter)-parseInt(n.counter)));i(n),i(e);const s=n=>{n.forEach(((n,e)=>{n.number=`${e+1}.`}))};s(n),s(e);const o={};n.forEach((n=>{o[n.name]={name:n.name,goals:parseInt(n.counter)||0,assists:0}})),e.forEach((n=>{o[n.name]?o[n.name].assists=parseInt(n.counter)||0:o[n.name]={name:n.name,goals:0,assists:parseInt(n.counter)||0}}));const m=Object.values(o);m.sort(((n,e)=>{const t=n.goals+n.assists;return e.goals+e.assists-t}));const c=document.querySelector(".goals-list"),u=document.querySelector(".assists-list"),l=document.querySelector(".goals-assists-list");n.forEach((n=>{const e=document.createElement("li");e.innerHTML=`\n    <div class="number">${n.number}</div>\n    <div class="player">${n.name}</div>\n    <div class="counter">${n.counter}</div>\n    `,c.appendChild(e)})),e.forEach((n=>{const e=document.createElement("li");e.innerHTML=`\n    <div class="number">${n.number}</div>\n    <div class="player">${n.name}</div>\n    <div class="counter">${n.counter}</div>\n    `,u.appendChild(e)})),m.forEach(((n,e)=>{const t=document.createElement("li");t.innerHTML=`\n    <div class="number">${e+1}.</div>\n    <div class="player">${n.name}</div>\n    <div class="total">${n.goals+n.assists}</div>\n    `,l.appendChild(t)}));const d=document.querySelector(".goalkeepers-list");t.sort(((n,e)=>parseInt(e.counter)-parseInt(n.counter))),t.forEach((n=>{const e=document.createElement("li");e.innerHTML=`\n        <div class="number">${n.number}</div>\n        <div class="player">${n.name}</div>\n        <div class="counter">${n.counter}</div>\n    `,d.appendChild(e)}));const g=document.querySelector(".goalkeepers-list-zero");a.sort(((n,e)=>parseInt(e.counter)-parseInt(n.counter))),a.forEach((n=>{const e=document.createElement("li");e.innerHTML=`\n<div class="number">${n.number}</div>\n<div class="player">${n.name}</div>\n<div class="counter">${n.counter}</div>\n`,g.appendChild(e)}));const p=document.querySelector(".zanetti_top-list");r.sort(((n,e)=>e.training-n.training)),r.slice(0).forEach(((n,e)=>{const t=document.createElement("li");t.innerHTML=`\n<div class="position">${e+1+"."}</div>\n<div class="name">${n.name}</div>\n<div class="training">${n.training}</div>\n`,p.appendChild(t)}));const b=parseInt(document.querySelector(".goals .number").textContent)-parseInt(document.querySelector(".goals_conceded .number").textContent);document.querySelector(".difference .number").textContent=`${b>=0?"+":""}${b}`;const v=document.querySelector(".goals-list-top");createListItems(n,v);const $=document.querySelector(".assists-list-top");createListItems(e,$);const y=document.querySelector(".goals-assists-list-top");createListItems(m,y);const I=document.querySelector(".goalkeepers-list-top");createListItems(t,I);const E=document.querySelector(".goalkeepers-list-zero-top");createListItems(a,E);const h=document.querySelector(".zanetti_top-list-top");createListItems(r,h),document.addEventListener("DOMContentLoaded",(function(){}))}();