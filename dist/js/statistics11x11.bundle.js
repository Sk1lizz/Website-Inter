!function(){const e=[{number:"1.",name:"Матчей не было",counter:"0"}],n=[{number:"1.",name:"Матчей не было",counter:"0"}],t=[{number:"1.",name:"Матчей не было",counter:"0"}],s=[{number:"1.",name:"Матчей не было",counter:"0"}],r=[{name:"Тренировок не было",training:0}],o=e=>e.sort(((e,n)=>parseInt(n.counter)-parseInt(e.counter)));o(e),o(n);const c=e=>{e.forEach(((e,n)=>{e.number=`${n+1}.`}))};c(e),c(n);const a={};e.forEach((e=>{a[e.name]={name:e.name,goals:parseInt(e.counter)||0,assists:0}})),n.forEach((e=>{a[e.name]?a[e.name].assists=parseInt(e.counter)||0:a[e.name]={name:e.name,goals:0,assists:parseInt(e.counter)||0}}));const i=Object.values(a);i.sort(((e,n)=>{const t=e.goals+e.assists;return n.goals+n.assists-t}));const l=document.querySelector(".goals-list"),u=document.querySelector(".assists-list"),d=document.querySelector(".goals-assists-list");e.forEach((e=>{const n=document.createElement("li");n.innerHTML=`\n    <div class="number">${e.number}</div>\n    <div class="player">${e.name}</div>\n    <div class="counter">${e.counter}</div>\n    `,l.appendChild(n)})),n.forEach((e=>{const n=document.createElement("li");n.innerHTML=`\n    <div class="number">${e.number}</div>\n    <div class="player">${e.name}</div>\n    <div class="counter">${e.counter}</div>\n    `,u.appendChild(n)})),i.forEach(((e,n)=>{const t=document.createElement("li");t.innerHTML=`\n    <div class="number">${n+1}.</div>\n    <div class="player">${e.name}</div>\n    <div class="total">${e.goals+e.assists}</div>\n    `,d.appendChild(t)}));const m=document.querySelector(".goalkeepers-list");t.sort(((e,n)=>parseInt(n.counter)-parseInt(e.counter))),t.forEach((e=>{const n=document.createElement("li");n.innerHTML=`\n        <div class="number">${e.number}</div>\n        <div class="player">${e.name}</div>\n        <div class="counter">${e.counter}</div>\n    `,m.appendChild(n)}));const p=document.querySelector(".goalkeepers-list-zero");s.sort(((e,n)=>parseInt(n.counter)-parseInt(e.counter))),s.forEach((e=>{const n=document.createElement("li");n.innerHTML=`\n<div class="number">${e.number}</div>\n<div class="player">${e.name}</div>\n<div class="counter">${e.counter}</div>\n`,p.appendChild(n)}));const v=document.querySelector(".zanetti_top-list");r.sort(((e,n)=>n.training-e.training)),r.slice(0).forEach(((e,n)=>{const t=document.createElement("li");t.innerHTML=`\n<div class="position">${n+1+"."}</div>\n<div class="name">${e.name}</div>\n<div class="training">${e.training}</div>\n`,v.appendChild(t)}));const $=parseInt(document.querySelector(".goals .number").textContent)-parseInt(document.querySelector(".goals_conceded .number").textContent);document.querySelector(".difference .number").textContent=`${$>=0?"+":""}${$}`;const g=document.querySelector(".goals-list-top");createListItems(e,g);const y=document.querySelector(".assists-list-top");createListItems(n,y);const b=document.querySelector(".goals-assists-list-top");createListItems(i,b);const I=document.querySelector(".goalkeepers-list-top");createListItems(t,I);const E=document.querySelector(".goalkeepers-list-zero-top");createListItems(s,E);const h=document.querySelector(".zanetti_top-list-top");createListItems(r,h),document.addEventListener("DOMContentLoaded",(function(){}))}();