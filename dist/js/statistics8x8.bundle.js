!function(){const e=[{number:"1.",name:"Костич",counter:"6"},{number:"2.",name:"Языков",counter:"39"},{number:"3.",name:"Макарчев",counter:"7"},{number:"4.",name:"Волконский",counter:"5"},{number:"5.",name:"Шеин",counter:"2"},{number:"6.",name:"Матвеев",counter:"6"},{number:"7.",name:"Белкин",counter:"1"},{number:"8.",name:"Голуб",counter:"9"},{number:"9.",name:"Власов",counter:"4"},{number:"10.",name:"Касулин",counter:"4"},{number:"11.",name:"Белоножкин",counter:"7"},{number:"12.",name:"Автогол",counter:"4"},{number:"13.",name:"Абянов",counter:"4"},{number:"14.",name:"Хакимов",counter:"16"},{number:"15.",name:"Бубнов",counter:"7"},{number:"15.",name:"Шаропов",counter:"2"},{number:"16.",name:"Котов",counter:"1"},{number:"17.",name:"Волокитин",counter:"1"},{number:"17.",name:"Синицын",counter:"1"}],n=[{number:"1.",name:"Макарчев",counter:"1"},{number:"1.",name:"Власов",counter:"11"},{number:"1.",name:"Мищенко",counter:"1"},{number:"1.",name:"Белоножкин",counter:"13"},{number:"1.",name:"Языков",counter:"16"},{number:"1.",name:"Шеин",counter:"6"},{number:"1.",name:"Петрищев",counter:"2"},{number:"1.",name:"Синицын",counter:"3"},{number:"1.",name:"Костич",counter:"4"},{number:"1.",name:"Матвеев",counter:"2"},{number:"1.",name:"Котов",counter:"2"},{number:"1.",name:"Голуб",counter:"4"},{number:"1.",name:"Волконский",counter:"5"},{number:"1.",name:"Тапчан",counter:"2"},{number:"1.",name:"Бубнов",counter:"4"},{number:"1.",name:"Хакимов",counter:"5"},{number:"1.",name:"Касулин",counter:"4"},{number:"1.",name:"Алексий",counter:"1"},{number:"1.",name:"Малышев А.",counter:"1"},{number:"1.",name:"Абянов",counter:"1"},{number:"1.",name:"Волокитин",counter:"1"},{number:"1.",name:"Дубовицкий",counter:"1"}],r=[{number:"3.",name:"Мищенко",counter:"13"},{number:"1.",name:"Мытько",counter:"26"},{number:"2.",name:"Исаев",counter:"19"},{number:"4.",name:"Сыпченко",counter:"13"}],u=[{number:"1.",name:"Сыпченко",counter:"1"},{number:"1.",name:"Мытько",counter:"1"}],t=e=>e.sort(((e,n)=>parseInt(n.counter)-parseInt(e.counter)));t(e),t(n);const m=e=>{e.forEach(((e,n)=>{e.number=`${n+1}.`}))};m(e),m(n);const a={};e.forEach((e=>{a[e.name]={name:e.name,goals:parseInt(e.counter)||0,assists:0}})),n.forEach((e=>{a[e.name]?a[e.name].assists=parseInt(e.counter)||0:a[e.name]={name:e.name,goals:0,assists:parseInt(e.counter)||0}}));const c=Object.values(a);c.sort(((e,n)=>{const r=e.goals+e.assists;return n.goals+n.assists-r}));const o=document.querySelector(".goals-list"),s=document.querySelector(".assists-list"),i=document.querySelector(".goals-assists-list");e.forEach((e=>{const n=document.createElement("li");n.innerHTML=`\n            <div class="number">${e.number}</div>\n            <div class="player">${e.name}</div>\n            <div class="counter">${e.counter}</div>\n        `,o.appendChild(n)})),n.forEach((e=>{const n=document.createElement("li");n.innerHTML=`\n            <div class="number">${e.number}</div>\n            <div class="player">${e.name}</div>\n            <div class="counter">${e.counter}</div>\n        `,s.appendChild(n)})),c.forEach(((e,n)=>{const r=document.createElement("li");r.innerHTML=`\n            <div class="number">${n+1}.</div>\n            <div class="player">${e.name}</div>\n            <div class="total">${e.goals+e.assists}</div>\n        `,i.appendChild(r)}));const b=document.querySelector(".goalkeepers-list");r.sort(((e,n)=>parseInt(n.counter)-parseInt(e.counter))),r.forEach((e=>{const n=document.createElement("li");n.innerHTML=`\n        <div class="number">${e.number}</div>\n        <div class="player">${e.name}</div>\n        <div class="counter">${e.counter}</div>\n    `,b.appendChild(n)}));const l=document.querySelector(".goalkeepers-list-zero");u.sort(((e,n)=>parseInt(n.counter)-parseInt(e.counter))),u.forEach((e=>{const n=document.createElement("li");n.innerHTML=`\n<div class="number">${e.number}</div>\n<div class="player">${e.name}</div>\n<div class="counter">${e.counter}</div>\n`,l.appendChild(n)}))}();