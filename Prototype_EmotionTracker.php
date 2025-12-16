<?php
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: AIBuddy_SignIn.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AI Buddy · Emotion Tracker</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-dark: #01161e;
      --primary: #124559;
      --primary-light: #598392;
      --sage: #aec3b0;
      --background: #eff6e0;
      --accent: #33c6e7;
      --ink: #353535;
      --slate: #284b63;
      --white: #ffffff;
      --gray: #d9d9d9;
      --shadow: 0 8px 30px rgba(0,0,0,.08);
      --radius: 14px;
    }

    * { box-sizing: border-box; }
    html, body {
      margin: 0;
      padding: 0;
      font-family: Inter, system-ui, sans-serif;
      background: var(--background);
      color: var(--ink);
    }

    .container { width: 92%; max-width: 1200px; margin: 0 auto; }

    /* Header */
    header {
      background: var(--white);
      position: sticky;
      top: 0;
      z-index: 50;
      box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }
    .header-inner {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 0;
    }
    .brand {
      display: flex;
      gap: 10px;
      align-items: center;
      font-weight: 700;
      font-size: 20px;
      color: var(--primary);
    }
    .brand-badge {
      width: 34px;
      height: 34px;
      display: grid;
      place-items: center;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
    }
    nav a {
      margin: 0 12px;
      text-decoration: none;
      font-weight: 600;
      color: var(--primary);
    }
    nav a:hover { color: var(--accent); }

    .user {
      font-weight: 600;
      color: var(--primary-dark);
    }

    /* Hero */
    .hero {
      margin: 22px 0;
      border-radius: var(--radius);
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
      box-shadow: var(--shadow);
    }
    .hero-inner {
      padding: 64px 22px;
      text-align: center;
    }

    main {
      display: grid;
      grid-template-columns: 1.6fr .9fr;
      gap: 26px;
      margin-bottom: 60px;
    }

    .card {
      background: white;
      border-radius: var(--radius);
      padding: 22px;
      box-shadow: var(--shadow);
    }

    .mood-row {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 10px;
    }
    .mood-btn {
      border: 1px solid var(--gray);
      border-radius: 12px;
      padding: 12px;
      cursor: pointer;
      text-align: center;
    }
    .mood-btn.active {
      outline: 2px solid var(--accent);
    }

    textarea, input {
      width: 100%;
      padding: 12px;
      border-radius: 10px;
      border: 1px solid var(--gray);
      font-family: inherit;
    }

    .actions {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }

    .btn {
      padding: 11px 18px;
      border-radius: 10px;
      border: 0;
      font-weight: 700;
      cursor: pointer;
    }
    .btn-primary {
      background: var(--primary);
      color: white;
    }
    .btn-ghost {
      background: transparent;
      border: 1px solid var(--primary);
      color: var(--primary);
    }

    .entries { margin-top: 16px; display: grid; gap: 12px; }
    .entry {
      background: white;
      border-radius: 12px;
      border: 1px solid var(--gray);
      padding: 12px;
    }

    footer {
      text-align: center;
      padding: 30px 0 50px;
      color: var(--slate);
    }

    @media (max-width: 900px) {
      main { grid-template-columns: 1fr; }
      .mood-row { grid-template-columns: repeat(3,1fr); }
    }
  </style>
</head>

<body>

<header>
  <div class="container header-inner">
    <div class="brand">
      <div class="brand-badge">🤖</div>
      AI Buddy
    </div>
    <nav>
      <a href="AIBuddy_Homepage.php">Home</a>
      <a href="AIBuddy_Chatbot.php">Chatbot</a>
      <a href="AIBuddy_EmotionTracker.php">Emotion Tracker</a>
      <a href="AIBuddy_Profile.php">Profile</a>
      <a href="AIBuddy_About.php">About</a>
    </nav>
    <div class="user">
      Hello, <?= htmlspecialchars($_SESSION['user_name']) ?>
    </div>
  </div>
</header>

<section class="container hero">
  <div class="hero-inner">
    <h1>Track your emotions with kindness</h1>
    <p>Reflect, journal, and visualize your daily mood.</p>
  </div>
</section>

<main class="container">
  <section class="card">
    <h3>Quick Mood</h3>

    <div class="mood-row">
      <div class="mood-btn" data-score="5">😊 Joyful</div>
      <div class="mood-btn" data-score="4">😌 Calm</div>
      <div class="mood-btn" data-score="3">🙂 Okay</div>
      <div class="mood-btn" data-score="2">🥱 Tired</div>
      <div class="mood-btn" data-score="1">😔 Sad</div>
      <div class="mood-btn" data-score="0">😠 Angry</div>
    </div>

    <textarea id="note" placeholder="How are you feeling today?"></textarea>
    <input id="tags" placeholder="tags (comma separated)">

    <div class="actions">
      <button class="btn btn-primary" id="saveBtn">Save</button>
      <button class="btn btn-ghost" id="clearBtn">Clear</button>
    </div>

    <div class="entries" id="entries"></div>
  </section>

  <aside class="card">
    <h3>Mood Chart (Local)</h3>
    <canvas id="moodChart" width="400" height="240"></canvas>
  </aside>
</main>

<footer>
  © 2025 AI Buddy · Mental Wellness Companion
</footer>

<script>
  const moods = document.querySelectorAll('.mood-btn');
  let selected = moods[2];
  selected.classList.add('active');

  moods.forEach(btn => {
    btn.onclick = () => {
      moods.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      selected = btn;
    }
  });

  const entriesEl = document.getElementById('entries');

  function load() {
    const data = JSON.parse(localStorage.getItem('aiBuddyMood') || '[]');
    entriesEl.innerHTML = data.reverse().map(e => `
      <div class="entry">
        <strong>${e.mood}</strong> · ${new Date(e.time).toLocaleString()}
        <div>${e.note}</div>
      </div>
    `).join('');
  }

  document.getElementById('saveBtn').onclick = () => {
    const data = JSON.parse(localStorage.getItem('aiBuddyMood') || '[]');
    data.push({
      mood: selected.innerText,
      score: selected.dataset.score,
      note: note.value,
      time: Date.now()
    });
    localStorage.setItem('aiBuddyMood', JSON.stringify(data));
    note.value = '';
    tags.value = '';
    load();
  };

  document.getElementById('clearBtn').onclick = () => {
    note.value = '';
    tags.value = '';
  };

  load();
</script>

</body>
</html>

