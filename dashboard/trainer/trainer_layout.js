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
  if(r.status===401){ location.href = BASE+"login.html"; return null; }
  const data = await r.json().catch(()=>null);

  if(!data){ location.href = BASE+"login.html"; return null; }

  // show trainer name
  const nameEl = document.getElementById("trainerName");
  if(nameEl){
    nameEl.textContent = data.username || data.name || "trainer";
  }

  // role redirect
  if((data.role||"")!=="trainer"){
    if(data.role==="admin") location.href = BASE+"dashboard/admin/dashboard.html";
    else location.href = BASE+"dashboard/member/dashboard.html";
    return null;
  }

  const rolePill = document.getElementById("rolePill");
  if(rolePill) rolePill.textContent = "Role: Trainer";

  return data;
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

/* ✅ Load unread notifications count for trainer */
async function loadTrainerNotifCount(){
  const badge = document.getElementById("navNotifBadge");
  if(!badge) return;

  try{
    const r = await fetch(API+"trainer_get_notifications.php?mode=count", { credentials:"include" });
    if(!r.ok) return;
    const data = await r.json();
    if(!data.ok) return;

    const n = Number(data.unread || 0);
    if(n > 0){
      badge.textContent = n > 99 ? "99+" : String(n);
      badge.style.display = "inline-block";
    }else{
      badge.style.display = "none";
    }
  }catch(e){
    // silently ignore
  }
}

window.addEventListener("DOMContentLoaded", async () => {
  setActiveFromBody();

  // logout
  const btn = document.getElementById("btnLogout");
  if(btn){
    btn.addEventListener("click",(e)=>{ e.preventDefault(); doLogout(); });
  }

  const me = await guardTrainer();
  if(me){
    await loadTrainerNotifCount();
    // optional: refresh every 15s
    setInterval(loadTrainerNotifCount, 15000);
  }
});