// dashboard/trainer/trainer_layout.js
const BASE = "../../";
const API  = BASE + "backend/";

function esc(s){
  return String(s ?? "")
    .replaceAll("&","&amp;")
    .replaceAll("<","&lt;")
    .replaceAll(">","&gt;");
}

async function guardTrainer(){
  const r = await fetch(API+"check_auth.php",{credentials:"include"});
  if(r.status===401){ location.href = BASE+"login.html"; return; }
  const data = await r.json();

  // show trainer name if available
  const nameEl = document.getElementById("trainerName");
  if(nameEl){
    nameEl.textContent = data.username || data.name || "trainer";
  }

  if((data.role||"")!=="trainer"){
    if(data.role==="admin") location.href = BASE+"dashboard/admin/dashboard.html";
    else location.href = BASE+"dashboard/member/dashboard.html";
    return;
  }

  const rolePill = document.getElementById("rolePill");
  if(rolePill) rolePill.textContent = "Role: Trainer";
}

async function doLogout(){
  try{
    await fetch(API+"logout.php",{method:"POST",credentials:"include"});
  }catch(e){}
  location.href = BASE+"login.html";
}

function setActiveFromBody(){
  const key = document.body.getAttribute("data-active") || "";
  if(!key) return;
  document.querySelectorAll(".nav-item").forEach(a=>{
    if((a.getAttribute("data-key")||"") === key) a.classList.add("active");
    else a.classList.remove("active");
  });
}

window.addEventListener("DOMContentLoaded", async () => {
  // active menu
  setActiveFromBody();

  // logout auto
  const btn = document.getElementById("btnLogout");
  if(btn){
    btn.addEventListener("click",(e)=>{ e.preventDefault(); doLogout(); });
  }

  // role guard auto
  await guardTrainer();
});