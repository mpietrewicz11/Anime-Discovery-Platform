<?php
session_start();

// if user is not logged in send them back
if (!isset($_SESSION['username'])) {
  header("Location: login.html");
  exit;
}

// just being safe before printing username into the page
$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ADEM Project - Watchlists</title>

  <style>
    :root{
      --bg:#0f1220;
      --panel:rgba(255,255,255,0.06);
      --border:rgba(255,255,255,0.12);
      --text:#e9ecff;
      --muted:rgba(233,236,255,0.75);
      --accent:#7c5cff;
      --danger:#ff4d6d;
    }

    body{
      margin:0;
      font-family: Arial, sans-serif;
      background: var(--bg);
      color: var(--text);
    }

    .navbar{
      position: sticky;
      top: 0;
      z-index: 10;
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:14px 22px;
      background: rgba(15,18,32,0.88);
      backdrop-filter: blur(8px);
      border-bottom:1px solid var(--border);
    }

    .brand{ font-weight:800; letter-spacing:.3px; }

    .nav-links{ display:flex; gap:14px; align-items:center; }
    .nav-links a{
      color: var(--text);
      text-decoration:none;
      font-size: 14px;
    }
    .nav-links a:hover{ color: var(--accent); }

    .container{
      width: min(1100px, 92vw);
      margin: 0 auto;
      padding: 18px 0 50px;
    }

    h1{ margin: 14px 0 6px; font-size: 24px; }
    .sub{ margin: 0 0 18px; color: var(--muted); font-size: 13px; }

    .grid{
      display:grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 14px;
    }
    @media (max-width: 1100px){ .grid{ grid-template-columns: repeat(4, 1fr); } }
    @media (max-width: 720px){ .grid{ grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 420px){ .grid{ grid-template-columns: 1fr; } }

    .card{
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 10px;
    }

    .poster{
      width:100%;
      aspect-ratio: 3 / 4;
      border-radius: 12px;
      border: 1px solid rgba(255,255,255,0.10);
      object-fit: cover;
      display:block;
      background: rgba(0,0,0,0.2);
    }

    .title{
      margin: 8px 0 4px;
      font-size: 14px;
      font-weight: 700;
      line-height: 1.25;
      min-height: 34px;
    }

    .meta{
      display:flex;
      justify-content:space-between;
      gap: 8px;
      color: var(--muted);
      font-size: 12px;
    }

    .pill{
      display:inline-block;
      font-size: 11px;
      padding: 3px 8px;
      border-radius: 999px;
      border: 1px solid rgba(124,92,255,0.30);
      background: rgba(124,92,255,0.14);
      color: #dcd6ff;
      white-space: nowrap;
    }

    .btnRow{ display:flex; gap:8px; margin-top: 8px; }

    .btn{
      flex:1;
      border:1px solid rgba(255,255,255,0.14);
      background: rgba(255,255,255,0.06);
      color: var(--text);
      padding:8px 10px;
      border-radius:10px;
      cursor:pointer;
      font-size: 13px;
    }
    .btn:hover{ background: rgba(255,255,255,0.10); }

    .btn.primary{
      border-color: transparent;
      background: var(--accent);
      font-weight: 700;
    }

    .empty{
      color: var(--muted);
      font-size: 13px;
      padding: 12px;
      border: 1px dashed rgba(255,255,255,0.18);
      border-radius: 12px;
    }

    .toast{
      position: fixed;
      left: 50%;
      transform: translateX(-50%);
      bottom: 18px;
      background: #111427;
      border: 1px solid var(--border);
      padding: 10px 12px;
      border-radius: 12px;
      display:none;
      max-width: 92vw;
    }
    .toast.show{ display:block; }
  </style>
</head>

<body>
  <div class="navbar">
    <div class="brand">ADEM Project</div>
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="watchlists.php">Watchlists</a>
      <a href="feed.php">Feed</a>
      <a href="my_profile.php">Profile</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>

  <div class="container">
    <h1>Your Watchlist</h1>
    <p class="sub">Saved anime will show up here. Click details to open the anime page.</p>

    <div id="content"></div>
  </div>

  <div id="toast" class="toast"></div>

  <script>
    const JIKAN_BASE = "https://api.jikan.moe/v4";
    const username = <?php echo json_encode($username); ?>;

    function toast(msg){
      // small popup message at the bottom
      const el = document.getElementById("toast");
      el.textContent = msg;
      el.classList.add("show");
      setTimeout(() => el.classList.remove("show"), 2400);
    }

    function escapeHtml(str){
      return String(str)
        .replaceAll("&","&amp;")
        .replaceAll("<","&lt;")
        .replaceAll(">","&gt;")
        .replaceAll('"',"&quot;")
        .replaceAll("'","&#039;");
    }

    async function fetchWatchlist(){
      const res = await fetch("watchlist_get.php");
      const json = await res.json().catch(() => ({}));

      if (!res.ok || json.ok !== true) {
        throw new Error(json.error || "Could not load watchlist");
      }

      // expecting backend to send an array of saved anime ids
      if (!Array.isArray(json.data)) return [];
      return json.data;
    }

    async function fetchAnime(id){
      const res = await fetch(`${JIKAN_BASE}/anime/${encodeURIComponent(id)}`);
      if (!res.ok) throw new Error("Failed fetch");
      const json = await res.json();
      return json.data;
    }

    function normalize(a){
      // formats jikan response into what this page needs
      const year = a.year || (a.aired?.from ? new Date(a.aired.from).getFullYear() : "—");
      const score = a.score ? a.score : "N/A";
      const poster = a.images?.jpg?.image_url || "";
      return { id: a.mal_id, title: a.title, year, score, poster };
    }

    function cardHTML(item){
      const scoreText = item.score === "N/A" ? "N/A" : `${item.score}/10`;
      return `
        <div class="card" data-id="${item.id}">
          <img class="poster" src="${item.poster}" alt="${escapeHtml(item.title)}" loading="lazy">
          <div class="title">${escapeHtml(item.title)}</div>
          <div class="meta">
            <span class="pill">⭐ ${scoreText}</span>
            <span>${escapeHtml(String(item.year))}</span>
          </div>
          <div class="btnRow">
            <button class="btn primary" data-action="details">Details</button>
          </div>
        </div>
      `;
    }

    async function render(){
      const content = document.getElementById("content");
      content.innerHTML = `<div class="empty">Loading your watchlist...</div>`;

      let list = [];
      try {
        list = await fetchWatchlist();
      } catch (e) {
        content.innerHTML = `<div class="empty">${escapeHtml(e.message)}</div>`;
        return;
      }

      const ids = list.map(x => x.anime_id).filter(Boolean);

      if (ids.length === 0){
        content.innerHTML = `<div class="empty">Your watchlist is empty. Go to Home and add a few anime.</div>`;
        return;
      }

      const items = [];
      for (const id of ids){
        try{
          const data = await fetchAnime(id);
          items.push(normalize(data));

          // small delay so i do not hit jikan too fast
          await new Promise(r => setTimeout(r, 250));
        } catch(e){
          // skip one item if it fails and keep loading the rest
        }
      }

      if (items.length === 0){
        content.innerHTML = `<div class="empty">Could not load watchlist items right now.</div>`;
        return;
      }

      content.innerHTML = `<div class="grid">${items.map(cardHTML).join("")}</div>`;

      content.querySelectorAll("button[data-action='details']").forEach(btn => {
        btn.addEventListener("click", () => {
          const card = btn.closest(".card");
          const id = card.getAttribute("data-id");
          window.location.href = "anime.php?id=" + encodeURIComponent(id);
        });
      });
    }

    render();
  </script>
</body>
</html>