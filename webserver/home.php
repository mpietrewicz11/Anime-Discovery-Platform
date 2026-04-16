<?php
session_start();

// if user is not logged in send them back to login
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}
// just to be safe before showing username on the page
$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ADEM Project1111 - Home</title>

  <style>
    :root{
      --bg:#0f1220;
      --panel:rgba(255,255,255,0.06);
      --border:rgba(255,255,255,0.12);
      --text:#e9ecff;
      --muted:rgba(233,236,255,0.75);
      --accent:#7c5cff;
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

    .brand{
      font-weight: 800;
      letter-spacing: .3px;
    }

    .nav-links{
      display:flex;
      gap:14px;
      align-items:center;
    }

    .nav-links a{
      color: var(--text);
      text-decoration:none;
      font-size: 14px;
    }

    .nav-links a:hover{
      color: var(--accent);
    }

    .container{
      width: min(1100px, 92vw);
      margin: 0 auto;
      padding: 18px 0 40px;
    }

    .topline{
      display:flex;
      justify-content:space-between;
      align-items:flex-end;
      gap: 14px;
      margin-top: 12px;
    }

    .welcome{
      margin:0;
      font-size: 18px;
    }

    .sub{
      margin: 6px 0 0;
      color: var(--muted);
      font-size: 13px;
      line-height: 1.35;
    }

    .search{
      width: min(420px, 100%);
    }

    .search input{
      width:100%;
      padding:10px 12px;
      border-radius: 12px;
      border:1px solid var(--border);
      background: rgba(0,0,0,0.25);
      color: var(--text);
      outline:none;
    }

    .search input:focus{
      border-color: rgba(124,92,255,0.7);
    }

    .section{
      margin-top: 22px;
    }

    .section-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom: 10px;
    }

    .section-title{
      margin:0;
      font-size: 18px;
    }

    .hint{
      color: var(--muted);
      font-size: 12px;
    }

    .row{
      display:flex;
      gap:12px;
      overflow-x:auto;
      padding-bottom: 8px;
      scroll-behavior: smooth;
    }

    .row::-webkit-scrollbar{
      height: 8px;
    }

    .row::-webkit-scrollbar-thumb{
      background: rgba(255,255,255,0.18);
      border-radius: 999px;
    }

    .card{
      flex: 0 0 165px;
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 10px;
      transition: transform .12s ease;
    }

    .card:hover{
      transform: translateY(-2px);
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
      margin:0;
      font-size: 12px;
      color: var(--muted);
      display:flex;
      justify-content:space-between;
      gap: 8px;
      align-items:center;
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

    .btn{
      width:100%;
      margin-top: 8px;
      padding:8px 10px;
      border-radius:10px;
      border:1px solid rgba(255,255,255,0.14);
      background: rgba(255,255,255,0.06);
      color: var(--text);
      cursor:pointer;
      font-size: 13px;
    }

    .btn:hover{
      background: rgba(255,255,255,0.10);
    }

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

    .loader{
      color: var(--muted);
      font-size: 13px;
      padding: 10px 0;
    }

    @media (max-width: 520px){
      .topline{
        flex-direction: column;
        align-items: stretch;
      }

      .card{
        flex-basis: 150px;
      }
    }
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
    <div class="topline">
      <div>
        <p class="welcome">Hi <b><?php echo $username; ?></b> :)</p>
        <p class="sub">Browse by genre, find new shows, and hit details to rate + comment.</p>
      </div>

      <div class="search">
        <input id="searchInput" type="text" placeholder="Search anime..." />
      </div>
    </div>

    <div class="section">
      <div class="section-header">
        <h3 class="section-title">Recommended for you</h3>
        <div class="hint" id="recHint">Based on what you click</div>
      </div>
      <div class="row" id="recommendedRow"></div>
      <div id="recommendedEmpty" class="empty" style="display:none;">
        Click a few anime in different rows to build recommendations.
      </div>
    </div>

    <div id="sections"></div>
  </div>

  <script>
    const JIKAN_BASE = "https://api.jikan.moe/v4"; //api connection
    const SFW = true;
    const LIMIT = 12;
    const TASTE_KEY = "taste_genres_v1"; // saving clicked genres so i can build simple recommendations

    const ROW_CONFIG = [
      { key: "top", title: "Top Anime", type: "top" },
      { key: "shounen", title: "Shonen Picks", type: "demographic", name: "Shounen" },
      { key: "romance", title: "Romance", type: "genre", name: "Romance" },
      { key: "comedy", title: "Comedy", type: "genre", name: "Comedy" },
      { key: "mystery", title: "Mystery", type: "genre", name: "Mystery" },
      { key: "sports", title: "Sports", type: "genre", name: "Sports" },
      { key: "fantasy", title: "Fantasy", type: "genre", name: "Fantasy" },
    ];

    function sleep(ms){
      // small delay between api calls so i do not spam requests
      return new Promise(r => setTimeout(r, ms));
    }

    async function fetchJson(url, opts = {}, timeoutMs = 8000){
      const controller = new AbortController();
      const timer = setTimeout(() => controller.abort(), timeoutMs);

      try{
         // using timeout here so the page does not hang forever if something fails
        const res = await fetch(url, { ...opts, signal: controller.signal });
        const json = await res.json().catch(() => ({}));
        return { res, json };
      } finally {
        clearTimeout(timer);
      }
    }

    async function jikanGet(path, params = {}) {
      const url = new URL(JIKAN_BASE + path);

      Object.entries(params).forEach(([k,v]) => {
        if (v !== undefined && v !== null && v !== "") {
          url.searchParams.set(k, v);
        }
      });

      const res = await fetch(url.toString());
      if (!res.ok) throw new Error("Jikan request failed");
      return res.json();
    }

    function normalizeAnime(a){
      // makes jikan response easier to use in the cards
      const year = a.year || (a.aired && a.aired.from ? new Date(a.aired.from).getFullYear() : null);
      const score = (a.score && a.score !== 0) ? a.score : null;

      return {
        id: a.mal_id,
        title: a.title || "Untitled",
        poster: (a.images && a.images.jpg && a.images.jpg.image_url) ? a.images.jpg.image_url : "",
        year: year,
        score: score
      };
    }

    function normalizeFromDbRow(r){
      // same idea as normalizeAnime but for my backend/db response
      return {
        id: r.mal_id ?? r.id ?? r.anime_id,
        title: r.title || "Untitled",
        poster: r.poster || "",
        year: r.year || null,
        score: (r.score !== undefined && r.score !== null) ? Number(r.score) : null
      };
    }

    function getTaste(){
      try {
        return JSON.parse(localStorage.getItem(TASTE_KEY)) || {};
      } catch {
        return {};
      }
    }

    function saveTaste(t){
      localStorage.setItem(TASTE_KEY, JSON.stringify(t));
    }

    function bumpTaste(tag){
      // every time user clicks details i count that genre tag
      const t = getTaste();
      t[tag] = (t[tag] || 0) + 1;
      saveTaste(t);
    }

    function topTasteTags(limit = 2){
      const t = getTaste();
      return Object.entries(t)
        .sort((a,b) => b[1] - a[1])
        .slice(0, limit)
        .map(([k]) => k);
    }

    const sectionsEl = document.getElementById("sections");

    function sectionHTML(key, title){
      return `
        <div class="section" id="section-${key}">
          <div class="section-header">
            <h3 class="section-title">${title}</h3>
            <div class="hint">Scroll →</div>
          </div>
          <div class="loader" id="loader-${key}">Loading...</div>
          <div class="row" id="row-${key}"></div>
        </div>
      `;
    }

    function cardHTML(item, tasteTag){
      const scoreText = item.score ? `${item.score}/10` : "N/A";
      const yearText = item.year ? item.year : "—";

      return `
        <div class="card">
          <img class="poster" src="${item.poster}" alt="${escapeHtml(item.title)}" loading="lazy">
          <div class="title">${escapeHtml(item.title)}</div>
          <p class="meta">
            <span class="pill">⭐ ${scoreText}</span>
            <span>${yearText}</span>
          </p>
          <button class="btn primary" data-id="${item.id}" data-taste="${escapeHtml(tasteTag)}">Details</button>
        </div>
      `;
    }

    function renderRow(rowEl, list, tasteTag){
      rowEl.innerHTML = list.map(item => cardHTML(item, tasteTag)).join("");

      rowEl.querySelectorAll("button[data-id]").forEach(btn => {
        btn.addEventListener("click", () => {
          const id = btn.getAttribute("data-id");
          const tag = btn.getAttribute("data-taste") || "Other";
          bumpTaste(tag);
          window.location.href = "anime.php?id=" + encodeURIComponent(id);
        });
      });
    }

    function escapeHtml(str){
      return String(str)
        .replaceAll("&","&amp;")
        .replaceAll("<","&lt;")
        .replaceAll(">","&gt;")
        .replaceAll('"',"&quot;")
        .replaceAll("'","&#039;");
    }

    async function getGenreIdByName(name){
      const cacheKey = "jikan_genres_cache_v1";
      let cache = {};
      try {
        // cache it so i do not keep asking jikan for the same genre ids
        cache = JSON.parse(sessionStorage.getItem(cacheKey)) || {};
      } catch {}

      if (cache[name]) return cache[name];

      const json = await jikanGet("/genres/anime", { filter: "genres" });
      const found = (json.data || []).find(g => (g.name || "").toLowerCase() === name.toLowerCase());
      const id = found ? found.mal_id : null;

      cache[name] = id;
      sessionStorage.setItem(cacheKey, JSON.stringify(cache));
      return id;
    }

    async function getDemographicIdByName(name){
      const cacheKey = "jikan_demo_cache_v1";
      let cache = {};
      try {
        cache = JSON.parse(sessionStorage.getItem(cacheKey)) || {};
      } catch {}

      if (cache[name]) return cache[name];

      const json = await jikanGet("/genres/anime", { filter: "demographics" });
      const found = (json.data || []).find(d => (d.name || "").toLowerCase() === name.toLowerCase());
      const id = found ? found.mal_id : null;

      cache[name] = id;
      sessionStorage.setItem(cacheKey, JSON.stringify(cache));
      return id;
    }

    async function loadTopRow(){
      try{
        const { res, json } = await fetchJson("get_top_anime.php?limit=" + encodeURIComponent(LIMIT), {}, 8000);
        if (res.ok && json && json.ok === true && Array.isArray(json.data)) {
          return json.data.map(normalizeFromDbRow).filter(x => x.id);
        }
      } catch(e){
        console.error("Top anime backend fetch failed:", e);
      }

      const j = await jikanGet("/top/anime", { limit: LIMIT, sfw: SFW });
      return (j.data || []).map(normalizeAnime);
    }

    async function loadGenreRow(key, genreName){
      const genreId = await getGenreIdByName(genreName);
      if (!genreId) return [];

      const json = await jikanGet("/anime", {
        genres: genreId,
        order_by: "score",
        sort: "desc",
        limit: LIMIT,
        sfw: SFW
      });

      return (json.data || []).map(normalizeAnime);
    }

    async function loadDemographicRow(key, demoName){
      const demoId = await getDemographicIdByName(demoName);
      if (!demoId) return [];

      const json = await jikanGet("/anime", {
        demographics: demoId,
        order_by: "score",
        sort: "desc",
        limit: LIMIT,
        sfw: SFW
      });

      return (json.data || []).map(normalizeAnime);
    }

    async function buildRecommended(){
      const recRow = document.getElementById("recommendedRow");
      const empty = document.getElementById("recommendedEmpty");
      const hint = document.getElementById("recHint");

      const prefs = topTasteTags(2);

      //if user has not clicked anything yet just show top anime
      if (prefs.length === 0) {
        hint.textContent = "Popular picks to get you started";

        try {
          const fallback = await loadTopRow();
          recRow.innerHTML = "";
          empty.style.display = "none";
          renderRow(recRow, fallback.slice(0, LIMIT), "Top");
          return;
        } catch (e) {
          console.error("Fallback recommendations failed:", e);
          recRow.innerHTML = "";
          empty.textContent = "Could not load recommendations right now.";
          empty.style.display = "block";
          return;
        }
      }

      empty.style.display = "none";
      hint.textContent = "Because you seem to like " + prefs.join(" + ");

      let combined = [];

      for (const tag of prefs) {
        try {
          let list = [];

          if (tag.toLowerCase() === "shounen" || tag.toLowerCase() === "shonen picks") {
            list = await loadDemographicRow("rec", "Shounen");
          } else if (tag.toLowerCase() === "top") {
            list = await loadTopRow();
          } else {
            list = await loadGenreRow("rec", tag);
          }

          combined = combined.concat(list);
          await sleep(500);
        } catch (e) {
          console.error("Recommended load failed for tag:", tag, e);
        }
      }

      const seen = new Set();
      const unique = [];

      for (const a of combined) {
        if (!seen.has(a.id)) {
          seen.add(a.id);
          unique.push(a);
        }
      }

      if (unique.length === 0) {
        recRow.innerHTML = "";
        empty.textContent = "Could not load recommendations right now.";
        empty.style.display = "block";
        return;
      }

      recRow.innerHTML = "";
      empty.style.display = "none";
      renderRow(recRow, unique.slice(0, LIMIT), prefs[0]);
    }

    let searchTimer = null;

    async function doSearch(q){
      const hint = document.getElementById("recHint");
      const empty = document.getElementById("recommendedEmpty");
      const recRow = document.getElementById("recommendedRow");

      if (!q) {
        hint.textContent = "Based on what you click";
        empty.style.display = "none";
        recRow.innerHTML = "";
        sectionsEl.style.display = "block";
        await buildRecommended();
        return;
      }

      sectionsEl.style.display = "none";
      hint.textContent = `Search results for "${q}"`;

      try {
        const json = await jikanGet("/anime", {
          q: q,
          order_by: "score",
          sort: "desc",
          limit: 18,
          sfw: SFW
        });

        const list = (json.data || []).map(normalizeAnime);

        if (list.length === 0) {
          recRow.innerHTML = "";
          empty.textContent = "No results found.";
          empty.style.display = "block";
          return;
        }

        empty.style.display = "none";
        renderRow(recRow, list.slice(0, 18), "Search");
      } catch (e) {
        console.error("Search failed:", e);
        recRow.innerHTML = "";
        empty.textContent = "Search failed (try again).";
        empty.style.display = "block";
      }
    }

    document.getElementById("searchInput").addEventListener("input", (e) => {
      const q = e.target.value.trim();
      clearTimeout(searchTimer);
      //small delay so it searches when they type at least couple letters
      searchTimer = setTimeout(() => doSearch(q), 450);
    });

    async function init(){
      // builds all homepage rows first, then recommendations
      sectionsEl.innerHTML = ROW_CONFIG.map(r => sectionHTML(r.key, r.title)).join("");

      for (const r of ROW_CONFIG) {
        const rowEl = document.getElementById("row-" + r.key);
        const loaderEl = document.getElementById("loader-" + r.key);

        try {
          let list = [];

          if (r.type === "top") {
            list = await loadTopRow();
            renderRow(rowEl, list, "Top");
          } else if (r.type === "genre") {
            list = await loadGenreRow(r.key, r.name);
            renderRow(rowEl, list, r.name);
          } else if (r.type === "demographic") {
            list = await loadDemographicRow(r.key, r.name);
            renderRow(rowEl, list, r.name);
          }
        } catch (e) {
          console.error("Row load failed:", r.key, e);
          rowEl.innerHTML = `<div class="empty">Could not load this row right now. try again later</div>`;
        } finally {
          loaderEl.style.display = "none";
        }

        await sleep(400);
      }

      await buildRecommended();
    }

    init();
  </script>
</body>
</html>
