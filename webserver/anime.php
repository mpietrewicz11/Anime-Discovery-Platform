<?php
session_start();

if (!isset($_SESSION['username'])) {
  header("Location: login.html");
  exit;
}

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ADEM Project - Anime Details</title>

  <style>
    :root{
      --bg:#0f1220;
      --panel:rgba(255,255,255,0.06);
      --border:rgba(255,255,255,0.12);
      --text:#e9ecff;
      --muted:rgba(233,236,255,0.75);
      --accent:#7c5cff;
      --danger:#ff4d6d;
      --ok:#19d3a2;
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

    .brand{ font-weight: 800; letter-spacing: .3px; }

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

    .grid{
      display:grid;
      grid-template-columns: 260px 1fr;
      gap: 18px;
      margin-top: 18px;
    }

    @media (max-width: 760px){
      .grid{ grid-template-columns: 1fr; }
    }

    .card{
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 16px;
    }

    .poster{
      width:100%;
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,0.10);
      object-fit: cover;
      aspect-ratio: 3 / 4;
      background: rgba(0,0,0,0.2);
    }

    h1{ margin: 0 0 6px; font-size: 26px; line-height: 1.2; }

    .meta{
      display:flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items:center;
      color: var(--muted);
      font-size: 13px;
      margin-bottom: 10px;
    }

    .pill{
      display:inline-block;
      font-size: 12px;
      padding: 4px 10px;
      border-radius: 999px;
      border: 1px solid rgba(124,92,255,0.30);
      background: rgba(124,92,255,0.14);
      color: #dcd6ff;
      white-space: nowrap;
    }

    .muted{ color: var(--muted); }

    .btnRow{ display:flex; gap:10px; flex-wrap: wrap; margin-top: 10px; }

    .btn{
      border:1px solid rgba(255,255,255,0.14);
      background: rgba(255,255,255,0.06);
      color: var(--text);
      padding:10px 12px;
      border-radius:12px;
      cursor:pointer;
      font-size: 14px;
    }
    .btn:hover{ background: rgba(255,255,255,0.10); }

    .btn.primary{
      border-color: transparent;
      background: var(--accent);
      font-weight: 700;
    }

    .btn.ok{
      border-color: rgba(25,211,162,0.35);
      background: rgba(25,211,162,0.12);
      color: #b9ffe6;
    }

    .field{
      width: 140px;
      padding: 10px 12px;
      border-radius: 12px;
      border: 1px solid var(--border);
      background: rgba(0,0,0,0.25);
      color: var(--text);
      outline: none;
    }

    textarea{
      width:100%;
      min-height: 90px;
      padding: 10px 12px;
      border-radius: 12px;
      border: 1px solid var(--border);
      background: rgba(0,0,0,0.25);
      color: var(--text);
      outline: none;
      resize: vertical;
    }

    .comments{
      display:flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 12px;
    }

    .comment{
      padding: 10px 12px;
      border-radius: 12px;
      border: 1px solid rgba(255,255,255,0.12);
      background: rgba(255,255,255,0.04);
    }

    .commentTop{
      display:flex;
      justify-content: space-between;
      gap: 10px;
      color: var(--muted);
      font-size: 12px;
      margin-bottom: 6px;
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
      <a href="logout.php">Logout</a>
    </div>
  </div>

  <div class="container">
    <div class="muted">Logged in as <b><?php echo $username; ?></b></div>

    <div class="grid">
      <div class="card">
        <img id="poster" class="poster" alt="Poster" />
        <div class="btnRow">
          <button id="watchlistBtn" class="btn primary">Add to Watchlist</button>
          <a id="jikanLink" class="btn" target="_blank" rel="noreferrer">Open on MAL</a>
        </div>
        <div class="muted" style="margin-top:10px;font-size:13px">
          Your rating: <b id="yourRating">—</b>
        </div>
      </div>

      <div class="card">
        <h1 id="title">Loading...</h1>
        <div class="meta" id="meta"></div>

        <div class="muted" style="margin-top:8px">
          <b>Synopsis</b>
          <div id="synopsis" style="margin-top:6px; line-height: 1.45;"></div>
        </div>

        <div style="height:14px"></div>

        <div class="card" style="padding:14px;">
          <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <div>
              <div class="muted" style="font-size:13px; margin-bottom:6px;">Rate (1–10)</div>
              <select id="ratingSelect" class="field">
                <option value="">Select</option>
                <option>1</option><option>2</option><option>3</option><option>4</option><option>5</option>
                <option>6</option><option>7</option><option>8</option><option>9</option><option>10</option>
              </select>
            </div>
            <button id="saveRatingBtn" class="btn ok">Save Rating</button>
          </div>

          <div style="height:12px"></div>

          <div class="muted" style="font-size:13px; margin-bottom:6px;">Leave a comment</div>
          <textarea id="commentText" placeholder="Write something..."></textarea>
          <div class="btnRow">
            <button id="postCommentBtn" class="btn">Post Comment</button>
          </div>

          <div style="height:10px"></div>
          <div class="muted" style="font-size:13px;"><b>Comments</b></div>
          <div id="comments" class="comments"></div>
        </div>
      </div>
    </div>
  </div>

  <div id="toast" class="toast"></div>

  <script>
    const JIKAN_BASE = "https://api.jikan.moe/v4";
    const username = <?php echo json_encode($username); ?>;

    function toast(msg){
      const el = document.getElementById("toast");
      el.textContent = msg;
      el.classList.add("show");
      setTimeout(() => el.classList.remove("show"), 2400);
    }

    function getId(){
      const p = new URLSearchParams(window.location.search);
      return p.get("id");
    }

    function escapeHtml(str){
      return String(str)
        .replaceAll("&","&amp;")
        .replaceAll("<","&lt;")
        .replaceAll(">","&gt;")
        .replaceAll('"',"&quot;")
        .replaceAll("'","&#039;");
    }

    async function jikanGet(path){
      const res = await fetch(JIKAN_BASE + path);
      if (!res.ok) throw new Error("Jikan request failed");
      return res.json();
    }

    async function postForm(url, dataObj){
      const form = new URLSearchParams();
      Object.entries(dataObj).forEach(([k,v]) => form.append(k, v));

      const res = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: form.toString()
      });

      const json = await res.json().catch(() => ({}));
      if (!res.ok || json.ok !== true) {
        throw new Error(json.error || "Request failed");
      }
      return json;
    }

    function ratingsKey(){ return "ratings_" + username; }
    function commentsKey(){ return "comments_" + username; }

    function loadJson(key){
      try { return JSON.parse(localStorage.getItem(key)) || {}; }
      catch { return {}; }
    }
    function saveJson(key, obj){
      localStorage.setItem(key, JSON.stringify(obj));
    }

    function renderComments(animeId){
      const wrap = document.getElementById("comments");
      const all = loadJson(commentsKey());
      const list = all[animeId] || [];

      if (list.length === 0){
        wrap.innerHTML = `<div class="muted" style="font-size:13px;">No comments yet.</div>`;
        return;
      }

      wrap.innerHTML = list
        .slice().reverse()
        .map(c => `
          <div class="comment">
            <div class="commentTop">
              <span><b>${escapeHtml(c.user)}</b></span>
              <span>${escapeHtml(c.date)}</span>
            </div>
            <div>${escapeHtml(c.text)}</div>
          </div>
        `).join("");
    }

    const animeId = getId();
    if (!animeId){
      toast("Missing anime id.");
    }

    async function loadAnime(){
      const json = await jikanGet(`/anime/${encodeURIComponent(animeId)}`);
      const a = json.data;

      document.getElementById("poster").src =
        a.images?.jpg?.large_image_url || a.images?.jpg?.image_url || "";
      document.getElementById("title").textContent = a.title || "Untitled";

      const year = a.year || (a.aired?.from ? new Date(a.aired.from).getFullYear() : "—");
      const score = a.score ? `${a.score}/10` : "N/A";
      const eps = a.episodes ? `${a.episodes} eps` : "—";
      const type = a.type || "—";

      const genres = (a.genres || []).map(g => `<span class="pill">${escapeHtml(g.name)}</span>`).join(" ");
      document.getElementById("meta").innerHTML = `
        <span class="pill">⭐ ${score}</span>
        <span class="pill">${escapeHtml(String(year))}</span>
        <span class="pill">${escapeHtml(type)}</span>
        <span class="pill">${escapeHtml(eps)}</span>
        <span style="flex-basis: 100%; height: 1px;"></span>
        ${genres}
      `;

      document.getElementById("synopsis").textContent =
        a.synopsis ? a.synopsis : "No synopsis available.";

      const malUrl = a.url || "";
      const link = document.getElementById("jikanLink");
      link.href = malUrl ? malUrl : "#";
      if (!malUrl) link.style.display = "none";

      const ratings = loadJson(ratingsKey());
      const saved = ratings[animeId];
      document.getElementById("yourRating").textContent = saved ? `${saved}/10` : "—";
      document.getElementById("ratingSelect").value = saved ? String(saved) : "";

      // Watchlist button state (read from backend)
      const wlBtn = document.getElementById("watchlistBtn");
      wlBtn.textContent = "Add to Watchlist";
      wlBtn.classList.remove("ok");

      try {
        const res = await fetch("watchlist_get.php");
        const data = await res.json();

        if (data.ok === true && Array.isArray(data.data)) {
          const inList = data.data.some(item => String(item.anime_id) === String(animeId));
          if (inList) {
            wlBtn.textContent = "In Watchlist ✓";
            wlBtn.classList.add("ok");
          }
        }
      } catch (e) {
        // backend might be down; ignore
      }

      renderComments(animeId);
    }

    document.getElementById("saveRatingBtn").addEventListener("click", () => {
      const val = document.getElementById("ratingSelect").value;
      if (!val) return toast("Pick a rating first.");

      const ratings = loadJson(ratingsKey());
      ratings[animeId] = Number(val);
      saveJson(ratingsKey(), ratings);

      document.getElementById("yourRating").textContent = `${val}/10`;
      toast("Rating saved.");
    });

    document.getElementById("postCommentBtn").addEventListener("click", () => {
      const text = document.getElementById("commentText").value.trim();
      if (!text) return toast("Write a comment first.");

      const all = loadJson(commentsKey());
      if (!all[animeId]) all[animeId] = [];
      all[animeId].push({
        user: username,
        text: text,
        date: new Date().toLocaleString()
      });
      saveJson(commentsKey(), all);

      document.getElementById("commentText").value = "";
      renderComments(animeId);
      toast("Comment posted.");
    });

    // Watchlist: add only (backend doesn't support remove yet)
    document.getElementById("watchlistBtn").addEventListener("click", async () => {
      const btn = document.getElementById("watchlistBtn");

      if (btn.classList.contains("ok")) {
        return toast("Already in your watchlist.");
      }

      try {
        const title = document.getElementById("title").textContent.trim();
        await postForm("watchlist_add.php", {
          anime_id: animeId,
          title: title
        });

        btn.textContent = "In Watchlist ✓";
        btn.classList.add("ok");
        toast("Added to watchlist.");
      } catch (e) {
        toast(e.message);
      }
    });

    loadAnime().catch(() => {
      document.getElementById("title").textContent = "Could not load anime details.";
      toast("Could not load anime details.");
    });
  </script>
</body>
</html>
