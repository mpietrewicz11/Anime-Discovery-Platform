<?php
session_start();

//if user is not logged in send them back
if (!isset($_SESSION['username'])) {
  header("Location: login.html");
  exit;
}
// using htmlspecialchrs here just to be safe when showing username on page
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
      padding: 18px 0 50px;
    }

    .grid{
      display:grid;
      grid-template-columns: 260px 1fr;
      gap: 18px;
      margin-top: 18px;
    }

    @media (max-width: 760px){
      .grid{
        grid-template-columns: 1fr;
      }
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

    h1{
      margin: 0 0 6px;
      font-size: 26px;
      line-height: 1.2;
    }

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

    .muted{
      color: var(--muted);
    }

    .btnRow{
      display:flex;
      gap:10px;
      flex-wrap: wrap;
      margin-top: 10px;
    }

    .btn{
      border:1px solid rgba(255,255,255,0.14);
      background: rgba(255,255,255,0.06);
      color: var(--text);
      padding:10px 12px;
      border-radius:12px;
      cursor:pointer;
      font-size: 14px;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
    }

    .btn:hover{
      background: rgba(255,255,255,0.10);
    }

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

    .toast.show{
      display:block;
    }

    .sectionTitle{
      margin: 0 0 8px;
      font-size: 15px;
      font-weight: 800;
    }

    .list{
      border: 1px solid rgba(255,255,255,0.10);
      border-radius: 12px;
      overflow: hidden;
      background: rgba(255,255,255,0.03);
    }

    .rowItem{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      padding: 10px 12px;
      border-top: 1px solid rgba(255,255,255,0.08);
    }

    .rowItem:first-child{
      border-top: none;
    }

    .rowLeft{
      min-width: 0;
    }

    .rowLeft b{
      font-size: 13px;
    }

    .rowLeft .small{
      font-size: 12px;
      color: var(--muted);
      margin-top: 2px;
      overflow:hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 520px;
    }

    .miniBtn{
      border:1px solid rgba(255,255,255,0.14);
      background: rgba(255,255,255,0.06);
      color: var(--text);
      padding:8px 10px;
      border-radius:10px;
      cursor:pointer;
      font-size: 13px;
      text-decoration:none;
      white-space: nowrap;
    }

    .miniBtn:hover{
      background: rgba(255,255,255,0.10);
    }

    .miniBtn.primary{
      background: var(--accent);
      border-color: transparent;
      font-weight: 700;
    }

    .note{
      color: var(--muted);
      font-size: 12px;
      line-height: 1.35;
      margin-top: 8px;
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
    <div class="muted">Logged in as <b><?php echo $username; ?></b></div>

    <div class="grid">
      <div class="card">
        <img id="poster" class="poster" alt="Poster" />
        <div class="btnRow">
          <button id="watchlistBtn" class="btn primary">Add to Watchlist</button>
          <button id="notifyBtn" class="btn">Notify Me</button>
          <a id="jikanLink" class="btn" target="_blank" rel="noreferrer">Open on MAL</a>
        </div>
        <div class="muted" style="margin-top:10px;font-size:13px">
          Your rating: <b id="yourRating">—</b>
        </div>

        <div style="height:14px"></div>

        <div class="card" style="padding:14px;">
          <div class="sectionTitle">Where to watch</div>
          <div class="btnRow" style="margin-top:8px;">
            <a id="watchCrunchyroll" class="btn" target="_blank" rel="noreferrer">Crunchyroll</a>
            <a id="watchNetflix" class="btn" target="_blank" rel="noreferrer">Netflix</a>
            <a id="watchHulu" class="btn" target="_blank" rel="noreferrer">Hulu</a>
          </div>
          <div class="note">
            These links open the provider search page for this title.
          </div>
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
          <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
            <div class="sectionTitle" style="margin:0;">Episodes</div>
            <div class="btnRow" style="margin:0;">
              <button id="loadEpisodesBtn" class="btn">Load Episodes</button>
              <a id="episodesOnMAL" class="btn" target="_blank" rel="noreferrer">MAL Episodes</a>
            </div>
          </div>

          <div id="episodesNote" class="note">
            Click "Load Episodes" to see the list. Then use "Watch" to open a provider search for that title.
          </div>

          <div style="height:10px"></div>
          <div id="episodesWrap" class="list" style="display:none;"></div>
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
     // added a small popup message at the bottom
    function toast(msg){
      const el = document.getElementById("toast");
      el.textContent = msg;
      el.classList.add("show");
      setTimeout(() => el.classList.remove("show"), 2400);
    }

    //get animeid from URL 
    function getAnimeId(){
      const p = new URLSearchParams(window.location.search);
      return p.get("id");
    }

    const animeId = getAnimeId();

    function escapeHtml(str){
      return String(str)
        .replaceAll("&","&amp;")
        .replaceAll("<","&lt;")
        .replaceAll(">","&gt;")
        .replaceAll('"',"&quot;")
        .replaceAll("'","&#039;");
    }

    async function getJikan(path){
      const res = await fetch(JIKAN_BASE + path);
      if (!res.ok) throw new Error("Jikan request failed");
      return res.json();
    }

    async function postForm(url, dataObj){
      // using form data here since my php files are already expecting POST this way
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

    async function loadComments(){
      // gets all comments and reviews for current anime
      const wrap = document.getElementById("comments");
      wrap.innerHTML = '<div class="muted" style="font-size:13px;">Loading...</div>';

      try {
        const res = await fetch(`review_get.php?anime_id=${encodeURIComponent(animeId)}`);
        const json = await res.json();
        const list = json.reviews ?? [];

        if (list.length === 0) {
          wrap.innerHTML = '<div class="muted" style="font-size:13px;">No comments yet.</div>';
          return;
        }

        wrap.innerHTML = list.map(c => `
          <div class="comment">
            <div class="commentTop">
              <span><b>${escapeHtml(c.username)}</b></span>
              <span>${escapeHtml(c.created_at)}</span>
            </div>
            <div>${escapeHtml(c.review_text)}</div>
          </div>
        `).join("");
      } catch (e) {
        wrap.innerHTML = '<div class="muted" style="font-size:13px;">Could not load comments.</div>';
      }
    }
    //still working on the push notificcations they are not working now... emi
    async function loadNotificationStatus(){
      const btn = document.getElementById("notifyBtn");
      if (!btn) return;

      try {
        const res = await fetch("notification_get.php");
        const data = await res.json();

        if (data.ok === true && Array.isArray(data.data)) {
          const item = data.data.find(n => String(n.anime_id) === String(animeId) && String(n.enabled) === "1");
          if (item) {
            btn.textContent = "Notifications On";
            btn.classList.add("ok");
          } else {
            btn.textContent = "Notify Me";
            btn.classList.remove("ok");
          }
        }
      } catch (e) {
        console.error("Could not load notification status:", e);
      }
    }
    //adding the function to rate animes (its working so far)
    async function loadUserRating(){
      try {
        const res = await fetch(`review_get.php?anime_id=${encodeURIComponent(animeId)}`);
        const json = await res.json();

        if (!json.ok) return;

        if (json.data && json.data.rating) {
          document.getElementById("ratingSelect").value = String(json.data.rating);
          document.getElementById("yourRating").textContent = `${json.data.rating}/10`;
          return;
        }

        if (Array.isArray(json.reviews) && json.reviews.length > 0) {
          const myReview = json.reviews.find(r => r.username === username);
          if (myReview && (myReview.score || myReview.rating)) {
            const savedRating = myReview.score || myReview.rating;
            document.getElementById("ratingSelect").value = String(savedRating);
            document.getElementById("yourRating").textContent = `${savedRating}/10`;
          }
        }
      } catch (e) {
        console.error("Could not load rating:", e);
      }
    }

    let currentTitle = "";
    let malUrl = "";
    // added the option for where can i watch and sends people user to the other website
    //added this for the watch ability for our deliveries
    function setProviderLinks(title){
      const q = encodeURIComponent(title || "anime");
      document.getElementById("watchCrunchyroll").href = "https://www.crunchyroll.com/search?q=" + q;
      document.getElementById("watchNetflix").href = "https://www.netflix.com/search?q=" + q;
      document.getElementById("watchHulu").href = "https://www.hulu.com/search?q=" + q;
    }

    function setMALLinks(url){
      const a = document.getElementById("jikanLink");
      a.href = url ? url : "#";
      if (!url) a.style.display = "none";

      const ep = document.getElementById("episodesOnMAL");
      ep.href = url ? (url.replace("/anime/", "/anime/") + "/episode") : "#";
      if (!url) ep.style.display = "none";
    }
    //loads anime data from api like poster score and year so it looks pretty
    async function loadAnime(){
  let a = null;

  // try our own cache first, if not we got a fall back
  try {
    const res = await fetch(`get_anime_detail.php?anime_id=${encodeURIComponent(animeId)}`);
    const json = await res.json();

    if (res.ok && json.ok === true && json.data) {
      const d = json.data;
      // same filed we extracted from jikan
      a = {
        images: { jpg: { large_image_url: d.poster || "", image_url: d.poster || "" } },
        title: d.title || "Untitled",
        year: d.year || null,
        score: d.score ? Number(d.score) : null,
        episodes: d.episodes || null,
        type: d.type || "—",
        synopsis: d.synopsis || "",
        genres: d.genre
          ? d.genre.split(",").map(g => ({ name: g.trim() }))
          : [],
        url: d.url || "",
        aired: null
      };
    }
  } catch (e) {
    console.warn("Cache lookup failed, falling back to Jikan:", e);
  }

  // fall back to jikan if cache missed or failed
  if (!a) {
    const json = await jikanGet(`/anime/${encodeURIComponent(animeId)}`);
    a = json.data;
  }

  // everything below stays exactly the same as before
  document.getElementById("poster").src =
    a.images?.jpg?.large_image_url || a.images?.jpg?.image_url || "";

  currentTitle = a.title || "Untitled";
  document.getElementById("title").textContent = currentTitle;

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

  malUrl = a.url || "";
  setMALLinks(malUrl);
  setProviderLinks(currentTitle);

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
  } catch (e) { /* ignore */ }

  await loadComments();
  await loadUserRating();
  await loadNotificationStatus();
}

    function episodeRowHTML(epNum, epTitle){
      const safeTitle = currentTitle ? currentTitle : "anime";
      const q = encodeURIComponent(safeTitle);

      return `
        <div class="rowItem">
          <div class="rowLeft">
            <b>Episode ${escapeHtml(String(epNum))}</b>
            <div class="small">${escapeHtml(epTitle || "")}</div>
          </div>
          <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a class="miniBtn primary" target="_blank" rel="noreferrer"
               href="https://www.crunchyroll.com/search?q=${q}">Watch</a>
            <a class="miniBtn" target="_blank" rel="noreferrer"
               href="https://www.netflix.com/search?q=${q}">Netflix</a>
            <a class="miniBtn" target="_blank" rel="noreferrer"
               href="https://www.hulu.com/search?q=${q}">Hulu</a>
          </div>
        </div>
      `;
    }
    // little section for episodes this was one of the deluveries 
    async function loadEpisodes(){
      const btn = document.getElementById("loadEpisodesBtn");
      const wrap = document.getElementById("episodesWrap");
      const note = document.getElementById("episodesNote");

      btn.disabled = true;
      btn.textContent = "Loading...";
      wrap.style.display = "block";
      wrap.innerHTML = `<div class="rowItem"><div class="rowLeft"><b>Loading episodes...</b></div></div>`;

      try {
        let page = 1;
        let hasNext = true;
        let all = [];
        const MAX_PAGES = 6;

        while (hasNext && page <= MAX_PAGES) {
          const json = await jikanGet(`/anime/${encodeURIComponent(animeId)}/episodes?page=${page}`);
          const list = Array.isArray(json.data) ? json.data : [];
          all = all.concat(list);

          hasNext = Boolean(json.pagination && json.pagination.has_next_page);
          page += 1;
          //added a small delay so i dont hit the api too much
          await new Promise(r => setTimeout(r, 250));
        }

        if (all.length === 0) {
          wrap.innerHTML = `
            <div class="rowItem">
              <div class="rowLeft">
                <b>No episodes found</b>
                <div class="small">Some shows do not have episode data available in Jikan.</div>
              </div>
            </div>
          `;
          note.textContent = "If episodes do not load, use the watch buttons above or open MAL.";
          return;
        }

        wrap.innerHTML = all.map(ep => {
          const displayNum = ep.mal_id || ep.episode_id || ep.episode || "?";
          const title = ep.title || ep.title_romanji || ep.title_japanese || "";
          return episodeRowHTML(displayNum, title);
        }).join("");

        note.textContent = "Episodes loaded. Use Watch to open a provider search page for this title.";
      } catch (e) {
        wrap.innerHTML = `
          <div class="rowItem">
            <div class="rowLeft">
              <b>Could not load episodes</b>
              <div class="small">Try again in a minute (Jikan rate limits sometimes).</div>
            </div>
          </div>
        `;
        note.textContent = "If this keeps happening, use the provider buttons or open MAL.";
      } finally {
        btn.disabled = false;
        btn.textContent = "Load Episodes";
      }
    }

    document.getElementById("loadEpisodesBtn").addEventListener("click", () => {
      loadEpisodes();
    });

    document.getElementById("saveRatingBtn").addEventListener("click", async () => {
      const val = document.getElementById("ratingSelect").value;
      if (!val) return toast("Pick a rating first.");

      try {
        await postForm("review_add.php", {
          anime_id: animeId,
          title: currentTitle,
          rating: val,
          review_text: ""
        });

        document.getElementById("yourRating").textContent = `${val}/10`;
        toast("Rating saved.");
      } catch (e) {
        toast(e.message);
      }
    });

    document.getElementById("postCommentBtn").addEventListener("click", async () => {
      const text = document.getElementById("commentText").value.trim();
      if (!text) return toast("Write a comment first.");

      try {
        await postForm("review_add.php", {
          anime_id: animeId,
          title: currentTitle,
          rating: document.getElementById("ratingSelect").value || 0,
          review_text: text
        });

        document.getElementById("commentText").value = "";
        await loadComments();
        await loadUserRating();
        toast("Comment posted.");
      } catch (e) {
        toast(e.message);
      }
    });

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

        btn.textContent = "In Watchlist";
        btn.classList.add("ok");
        toast("Added to watchlist.");
      } catch (e) {
        toast(e.message);
      }
    });

    document.getElementById("notifyBtn").addEventListener("click", async () => {
      const btn = document.getElementById("notifyBtn");
      const enabled = btn.classList.contains("ok") ? 0 : 1;

      try {
        await postForm("notification_toggle.php", {
          anime_id: animeId,
          title: currentTitle,
          enabled: enabled
        });

        if (enabled === 1) {
          btn.textContent = "Notifications On";
          btn.classList.add("ok");
          toast("Episode notifications enabled.");
        } else {
          btn.textContent = "Notify Me";
          btn.classList.remove("ok");
          toast("Episode notifications turned off.");
        }
      } catch (e) {
        toast(e.message);
      }
    });

    if (!animeId) {
      document.getElementById("title").textContent = "Missing anime id.";
      toast("Missing anime id.");
    } else {
      loadAnime().catch(() => {
        document.getElementById("title").textContent = "Could not load anime details.";
        toast("Could not load anime details.");
      });
    }
  </script>
</body>
</html>
