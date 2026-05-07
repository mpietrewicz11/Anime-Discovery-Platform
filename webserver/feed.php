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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ADEM Project - Feed</title>
  <style>
    :root{
      --bg:#0f1220;
      --panel:rgba(255,255,255,0.06);
      --border:rgba(255,255,255,0.12);
      --text:#e9ecff;
      --muted:rgba(233,236,255,0.75);
      --accent:#7c5cff;
      --ok:#19d3a2;
      --danger:#ff4d6d;
    }

    *{ box-sizing:border-box; }

    body{
      margin:0;
      font-family:Arial,sans-serif;
      background:var(--bg);
      color:var(--text);
      min-height:100vh;
    }

    .navbar{
      position:sticky;top:0;z-index:10;
      display:flex;justify-content:space-between;align-items:center;
      padding:14px 22px;
      background:rgba(15,18,32,0.88);
      backdrop-filter:blur(8px);
      border-bottom:1px solid var(--border);
    }
    .brand{ font-weight:800; }
    .nav-links{ display:flex;gap:14px;align-items:center; }
    .nav-links a{ color:var(--text);text-decoration:none;font-size:14px; }
    .nav-links a:hover{ color:var(--accent); }
    .nav-links a.active{ color:var(--accent);font-weight:700; }

    .container{
      max-width:620px;
      margin:0 auto;
      padding:24px 16px 60px;
    }

    .compose{
      background:var(--panel);
      border:1px solid var(--border);
      border-radius:14px;
      padding:16px;
      margin-bottom:22px;
    }

    .compose textarea{
      width:100%;
      min-height:80px;
      background:rgba(0,0,0,0.25);
      border:1px solid var(--border);
      border-radius:10px;
      color:var(--text);
      font-size:14px;
      font-family:Arial,sans-serif;
      padding:10px 12px;
      outline:none;
      resize:vertical;
    }
    .compose textarea:focus{ border-color:rgba(124,92,255,0.6); }

    .compose-footer{
      display:flex;
      justify-content:flex-end;
      margin-top:10px;
    }

    .btn-post{
      background:var(--accent);
      color:white;
      border:none;
      border-radius:10px;
      padding:9px 22px;
      font-size:14px;
      font-weight:700;
      cursor:pointer;
    }
    .btn-post:hover{ filter:brightness(1.1); }
    .btn-post:disabled{ opacity:0.5;cursor:default; }

    .feed{ display:flex;flex-direction:column;gap:14px; }

    .post{
      background:var(--panel);
      border:1px solid var(--border);
      border-radius:14px;
      padding:16px;
    }

    .post-header{
      display:flex;
      align-items:center;
      gap:10px;
      margin-bottom:10px;
    }

    .avatar{
      width:36px;height:36px;border-radius:50%;
      background:#800080;
      display:flex;align-items:center;justify-content:center;
      font-size:14px;font-weight:700;color:white;
      flex-shrink:0;
    }

    .post-meta{ flex:1;min-width:0; }
    .post-author{ font-weight:700;font-size:14px; }
    .user-link{ color:inherit;text-decoration:none;font-weight:700; }
    .user-link:hover{ color:var(--accent);text-decoration:underline; }
    .post-time{ font-size:12px;color:var(--muted); }

    .repost-label{
      font-size:12px;
      color:var(--muted);
      margin-bottom:8px;
      display:flex;
      align-items:center;
      gap:5px;
    }

    .post-body{
      font-size:14px;
      line-height:1.55;
      word-break:break-word;
      white-space:pre-wrap;
    }

    .original-post{
      margin-top:10px;
      padding:10px 14px;
      border:1px solid rgba(255,255,255,0.10);
      border-radius:10px;
      background:rgba(0,0,0,0.20);
    }
    .original-post .orig-author{
      font-size:12px;
      font-weight:700;
      color:var(--muted);
      margin-bottom:4px;
    }
    .original-post .orig-body{
      font-size:13px;
      line-height:1.5;
      word-break:break-word;
      white-space:pre-wrap;
    }

    .post-actions{
      display:flex;
      gap:10px;
      margin-top:14px;
      flex-wrap:wrap;
    }

    .action-btn{
      display:flex;
      align-items:center;
      gap:5px;
      background:rgba(255,255,255,0.05);
      border:1px solid rgba(255,255,255,0.10);
      border-radius:8px;
      color:var(--muted);
      font-size:13px;
      padding:6px 12px;
      cursor:pointer;
    }
    .action-btn:hover{ background:rgba(255,255,255,0.10);color:var(--text); }
    .action-btn.liked{
      color:#ff4d6d;
      border-color:rgba(255,77,109,0.35);
      background:rgba(255,77,109,0.08);
    }
    .action-btn.liked:hover{ background:rgba(255,77,109,0.14); }

    .empty{
      text-align:center;
      color:var(--muted);
      padding:40px 0;
      font-size:14px;
    }

    .loading{
      text-align:center;
      color:var(--muted);
      padding:30px 0;
      font-size:13px;
    }

    .toast{
      position:fixed;
      left:50%;transform:translateX(-50%);
      bottom:18px;
      background:#111427;
      border:1px solid var(--border);
      padding:10px 16px;
      border-radius:12px;
      display:none;
      font-size:13px;
      max-width:92vw;
      z-index:100;
    }
    .toast.show{ display:block; }

    @media(max-width:500px){
      .navbar{ padding:12px 14px; }
      .nav-links{ gap:10px; }
      .nav-links a{ font-size:13px; }
    }
  </style>
</head>
<body>

<div class="navbar">
  <div class="brand">ADEM Project</div>
  <div class="nav-links">
    <a href="home.php">Home</a>
    <a href="watchlists.php">Watchlists</a>
    <a href="feed.php" class="active">Feed</a>
    <a href="my_profile.php">Profile</a>
    <a href="logout.php">Logout</a>
  </div>
</div>

<div class="container">

  <div class="compose">
    <textarea id="composeBox" placeholder="What's on your mind?"></textarea>
    <div class="compose-footer">
      <button class="btn-post" id="postBtn">Post</button>
    </div>
  </div>

  <div class="feed" id="feed">
    <div class="loading">Loading feed...</div>
  </div>

</div>

<div class="toast" id="toast"></div>

<script>
  const ME = <?php echo json_encode($username); ?>;

  function toast(msg) {
    const el = document.getElementById("toast");
    el.textContent = msg;
    el.classList.add("show");
    setTimeout(() => el.classList.remove("show"), 2600);
  }

  function esc(str) {
    return String(str ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
    if (diff < 60)   return `${diff}s ago`;
    if (diff < 3600) return `${Math.floor(diff/60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
    return `${Math.floor(diff/86400)}d ago`;
  }

  async function postForm(url, data) {
    const body = new URLSearchParams(data);
    const res  = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: body.toString()
    });
    return res.json().catch(() => ({}));
  }

  function renderPost(p) {
    const initials = esc(p.author).slice(0, 2).toUpperCase();
    const isRepost = p.repost_of !== null;

    const repostLabel = isRepost
      ? `<div class="repost-label">&#8617; reposted by <a class="user-link" href="anime_profile.html?username=${encodeURIComponent(p.author)}">${esc(p.author)}</a></div>`
      : "";

    const bodyHtml = isRepost
      ? (p.body ? `<div class="post-body" style="margin-bottom:8px;">${esc(p.body)}</div>` : "")
      : `<div class="post-body">${esc(p.body)}</div>`;

    const originalHtml = isRepost && p.original_body
      ? `<div class="original-post">
           <div class="orig-author"><a class="user-link" href="anime_profile.html?username=${encodeURIComponent(p.original_author)}">@${esc(p.original_author)}</a></div>
           <div class="orig-body">${esc(p.original_body)}</div>
         </div>`
      : "";

    const headerAuthor = isRepost ? esc(p.author) : esc(p.author);

    const likedClass = p.user_liked ? " liked" : "";
    const likedIcon  = p.user_liked ? "♥" : "♡";

    return `
      <div class="post" data-id="${p.id}">
        ${repostLabel}
        <div class="post-header">
          <div class="avatar">${initials}</div>
          <div class="post-meta">
            <div class="post-author"><a class="user-link" href="anime_profile.html?username=${encodeURIComponent(isRepost ? (p.original_author ?? p.author) : p.author)}">@${isRepost ? esc(p.original_author ?? p.author) : esc(p.author)}</a></div>
            <div class="post-time">${timeAgo(p.created_at)}</div>
          </div>
        </div>
        ${bodyHtml}
        ${originalHtml}
        <div class="post-actions">
          <button class="action-btn like-btn${likedClass}" data-id="${p.id}" data-count="${p.like_count}">
            <span class="like-icon">${likedIcon}</span>
            <span class="like-count">${p.like_count}</span>
          </button>
          <button class="action-btn repost-btn" data-id="${p.id}" data-body="${esc(p.original_body ?? p.body)}">
            &#8617; Repost
          </button>
        </div>
      </div>
    `;
  }

  async function loadFeed() {
    const feedEl = document.getElementById("feed");
    try {
      const res  = await fetch("post_feed.php");
      const data = await res.json();

      if (!data.ok || !Array.isArray(data.posts) || data.posts.length === 0) {
        feedEl.innerHTML = '<div class="empty">No posts yet. Be the first to post!</div>';
        return;
      }

      feedEl.innerHTML = data.posts.map(renderPost).join("");
      attachActions();
    } catch (e) {
      feedEl.innerHTML = '<div class="empty">Could not load feed.</div>';
    }
  }

  function attachActions() {
    document.querySelectorAll(".like-btn").forEach(btn => {
      btn.addEventListener("click", async () => {
        const postId = btn.dataset.id;
        btn.disabled = true;

        const res = await postForm("post_like.php", { post_id: postId });

        if (res.ok) {
          const icon  = btn.querySelector(".like-icon");
          const count = btn.querySelector(".like-count");
          const cur   = parseInt(count.textContent, 10);

          if (res.liked) {
            btn.classList.add("liked");
            icon.textContent  = "♥";
            count.textContent = cur + 1;
          } else {
            btn.classList.remove("liked");
            icon.textContent  = "♡";
            count.textContent = Math.max(0, cur - 1);
          }
        } else {
          toast("Could not update like.");
        }

        btn.disabled = false;
      });
    });

    document.querySelectorAll(".repost-btn").forEach(btn => {
      btn.addEventListener("click", async () => {
        const postId = parseInt(btn.dataset.id, 10);
        btn.disabled = true;
        btn.textContent = "Reposting...";

        const res = await postForm("post_create.php", { body: "", repost_of: postId });

        if (res.ok) {
          toast("Reposted!");
          await loadFeed();
        } else {
          toast(res.error || "Could not repost.");
          btn.disabled = false;
          btn.textContent = "↩ Repost";
        }
      });
    });
  }

  document.getElementById("postBtn").addEventListener("click", async () => {
    const box  = document.getElementById("composeBox");
    const btn  = document.getElementById("postBtn");
    const text = box.value.trim();

    if (!text) return toast("Write something first.");

    btn.disabled = true;
    btn.textContent = "Posting...";

    const res = await postForm("post_create.php", { body: text });

    if (res.ok) {
      box.value = "";
      await loadFeed();
    } else {
      toast(res.error || "Could not post.");
    }

    btn.disabled = false;
    btn.textContent = "Post";
  });

  loadFeed();
</script>
</body>
</html>
