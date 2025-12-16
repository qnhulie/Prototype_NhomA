<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>AI Buddy · Emotion Tracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root {
      --primary-dark: #01161e;
      --primary: #124559;
      --primary-light: #598392;
      --accent: #33c6e7;
      --light: #aec3b0;
      --background: #eff6e0;
      --white: #ffffff;
      --gray: #d9d9d9;
      --text: #353535;
      --shadow: 0 5px 15px rgba(0, 0, 0, .08);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Verdana, sans-serif;
    }

    body {
      background: var(--background);
      color: var(--text);
    }

    .container {
      width: 90%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    }

    /* ========= HEADER ========= */
    header {
      background: var(--white);
      padding: 15px 0;
      box-shadow: 0 2px 10px rgba(0, 0, 0, .1);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-size: 24px;
      font-weight: 700;
      color: var(--primary);
      display: flex;
      align-items: center;
      text-decoration: none;
    }

    .logo-icon {
      font-size: 28px;
      margin-right: 8px;
    }

    nav a {
      margin: 0 15px;
      text-decoration: none;
      color: var(--primary);
      font-weight: 500;
    }

    nav a:hover {
      color: var(--accent);
    }

    .signin-btn {
      background: var(--accent);
      color: white;
      border: none;
      padding: 8px 20px;
      border-radius: 20px;
      font-weight: 600;
      cursor: pointer;
    }

    /* ========= HERO ========= */
    .hero {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
      padding: 80px 0;
      text-align: center;
      margin: 20px auto;
      border-radius: 12px;
      box-shadow: var(--shadow);
    }

    .hero h1 {
      font-size: 2.8rem;
      margin-bottom: 15px;
    }

    .hero p {
      font-size: 1.2rem;
      opacity: .9
    }

    /* ========= MAIN ========= */
    main {
      display: flex;
      gap: 30px;
      margin: 40px auto;
    }

    .left {
      flex: 2;
    }

    .right {
      flex: 1;
    }

    .card {
      background: white;
      border-radius: 12px;
      padding: 25px;
      box-shadow: var(--shadow);
      margin-bottom: 25px;
    }

    /* ========= MOOD ========= */
    .mood-row {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 10px;
      margin-bottom: 15px;
    }

    .mood-btn {
      border: 1px solid var(--gray);
      border-radius: 10px;
      padding: 12px;
      font-weight: 600;
      cursor: pointer;
      background: white;
      text-align: center;
    }

    .mood-btn.active {
      outline: 2px solid var(--accent);
    }

    input,
    textarea {
      width: 100%;
      padding: 12px;
      border-radius: 8px;
      border: 1px solid var(--gray);
      margin-top: 12px;
    }

    textarea {
      min-height: 120px;
      resize: vertical;
    }

    .actions {
      display: flex;
      gap: 10px;
      margin-top: 15px;
      align-items: center;
    }

    .btn {
      padding: 10px 18px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
      border: none;
    }

    .btn-ghost {
      background: white;
      border: 1px solid var(--primary);
      color: var(--primary);
    }

    .entries {
      margin-top: 20px;
      display: grid;
      gap: 12px;
    }

    .entry {
      border: 1px solid var(--gray);
      border-radius: 10px;
      padding: 12px;
    }

    .entry-head {
      display: flex;
      justify-content: space-between;
      font-weight: 600;
    }

    canvas {
      width: 100%;
      height: 240px;
    }

    .chart-nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    /* ========= FOOTER ========= */
    footer {
      background: var(--primary-dark);
      color: white;
      padding: 60px 0 20px;
      margin-top: 60px;
    }

    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 40px;
      margin-bottom: 40px;
    }

    .footer-column h3 {
      color: var(--accent);
      margin-bottom: 20px;
    }

    .footer-column ul {
      list-style: none;
    }

    .footer-column ul li {
      margin-bottom: 10px;
    }

    .footer-column ul li a {
      color: var(--light);
      text-decoration: none;
    }

    .footer-column ul li a:hover {
      color: var(--accent);
    }

    .social-links a {
      color: var(--accent);
      margin-right: 10px;
      font-size: 1.2rem;
    }

    .copyright {
      text-align: center;
      border-top: 1px solid var(--primary);
      padding-top: 20px;
      color: var(--light);
      font-size: .9rem;
    }

    @media(max-width:768px) {
      main {
        flex-direction: column;
      }

      .mood-row {
        grid-template-columns: repeat(3, 1fr);
      }

      .header-content {
        flex-direction: column;
      }

      nav {
        margin: 10px 0;
      }
    }

    /* ===== CHART CARD BEAUTIFY ===== */
    .right .card {
      padding: 20px 22px 26px;
    }

    .chart-nav {
      gap: 10px;
    }

    .chart-nav button {
      min-width: 70px;
    }

    #weekLabel {
      font-weight: 600;
      color: var(--primary);
    }
  </style>
</head>

<body>

  <header>
    <div class="container header-content">
      <a href="AIBuddy_Homepage.php" class="logo">
        <span class="logo-icon">🤖</span>AI Buddy
      </a>
      <nav>
        <a href="AIBuddy_Homepage.php">Home</a>
        <a href="AIBuddy_Chatbot.php">Chatbot</a>
        <a href="AIBuddy_EmotionTracker.php">Emotion Tracker</a>
        <a href="AIBuddy_Focus.php">Focus</a>
        <a href="AIBuddy_Profile.php">Profile</a>
        <a href="AIBuddy_About.php">About</a>
        <a href="AIBuddy_Contact.php">Contact</a>
      </nav>
      <button class="signin-btn">Sign In</button>
    </div>
  </header>

  <section class="hero container">
    <h1>Emotion Tracker</h1>
    <p>Reflect, journal, and understand your emotional patterns</p>
  </section>

  <div class="container">
    <main>

      <div class="left">
        <div class="card">
          <h3>Quick Mood</h3>

          <div class="mood-row">
            <div class="mood-btn" data-score="5">😊 Joyful</div>
            <div class="mood-btn" data-score="4">😌 Calm</div>
            <div class="mood-btn active" data-score="3">🙂 Okay</div>
            <div class="mood-btn" data-score="2">🥱 Tired</div>
            <div class="mood-btn" data-score="1">😔 Sad</div>
            <div class="mood-btn" data-score="0">😠 Angry</div>
          </div>

          <label>Select date</label>
          <input type="date" id="entryDate">

          <textarea id="note" placeholder="How are you feeling today? What happened..."></textarea>

          <div class="actions">
            <button class="btn btn-primary" id="saveBtn">Save</button>
            <button class="btn btn-ghost" id="clearBtn">Clear</button>
          </div>

          <div class="entries" id="entries"></div>
        </div>
      </div>

      <div class="right">
        <div class="card">
          <h3>Mood Chart (7 days)</h3>

          <div class="chart-nav">
            <button class="btn btn-ghost" id="prevWeek">← Prev</button>
            <strong id="weekLabel"></strong>
            <button class="btn btn-ghost" id="nextWeek">Next →</button>
          </div>

          <canvas id="moodChart" width="500" height="260"></canvas>
        </div>
      </div>

    </main>
  </div>

  <footer>
    <div class="container">
      <div class="footer-content">
        <div class="footer-column">
          <h3>AI Buddy</h3>
          <p>Your companion for mental wellness with intelligent AI support and personalized care.</p>
          <div class="social-links">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>

        <div class="footer-column">
          <h3>Quick Links</h3>
          <ul>
            <li><a href="AIBuddy_Homepage.php">Home</a></li>
            <li><a href="AIBuddy_Chatbot.php">Chatbot</a></li>
            <li><a href="AIBuddy_EmotionTracker.php">Emotion Tracker</a></li>
            <li><a href="AIBuddy_Contact.php">Contact</a></li>
          </ul>
        </div>

        <div class="footer-column">
          <h3>Legal</h3>
          <ul>
            <li><a href="#">Terms of Service</a></li>
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Cookie Policy</a></li>
            <li><a href="#">Disclaimer</a></li>
          </ul>
        </div>

        <div class="footer-column">
          <h3>Contact</h3>
          <ul>
            <li><i class="fas fa-map-marker-alt"></i> 123 Wellness Street, Mindful District, CA 90210</li>
            <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
            <li><i class="fas fa-envelope"></i> support@aibuddy.com</li>
            <li><i class="fas fa-clock"></i> Mon-Fri: 8:00 AM - 8:00 PM</li>
          </ul>
        </div>
      </div>

      <div class="copyright">
        © 2025 AI Buddy. All rights reserved. | Mental Health Companion
      </div>
    </div>
  </footer>

  <script>

    const moodEmoji = {
      5: '😊',
      4: '😌',
      3: '🙂',
      2: '🥱',
      1: '😔',
      0: '😠'
    };

    const moods = document.querySelectorAll('.mood-btn');
    const dateEl = document.getElementById('entryDate');
    const noteEl = document.getElementById('note');
    const entriesEl = document.getElementById('entries');
    const canvas = document.getElementById('moodChart');
    const ctx = canvas.getContext('2d');
    const weekLabel = document.getElementById('weekLabel');

    let selected = moods[2];
    let weekOffset = 0;
    dateEl.valueAsDate = new Date();

    moods.forEach(btn => {
      btn.onclick = () => {
        moods.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selected = btn;
      };
    });

    function loadData() {
      return JSON.parse(localStorage.getItem('aiBuddyEntries') || '[]');
    }
    function saveData(d) {
      localStorage.setItem('aiBuddyEntries', JSON.stringify(d));
    }

    saveBtn.onclick = () => {
      const d = loadData();
      d.push({
        date: entryDate.value,
        score: Number(selected.dataset.score),
        mood: selected.innerText,
        note: noteEl.value,
        time: Date.now()
      });
      saveData(d);
      noteEl.value = '';
      render();
    };

    clearBtn.onclick = () => noteEl.value = '';

    prevWeek.onclick = () => { weekOffset--; drawChart(); }
    nextWeek.onclick = () => { weekOffset++; drawChart(); }

    function render() {
      const data = loadData().slice().reverse();
      entriesEl.innerHTML = data.map(e => `
    <div class="entry">
      <div class="entry-head">
        <span>${e.mood} · ${'★'.repeat(e.score)}</span>
        <small>${e.date}</small>
      </div>
      <div>${e.note || ''}</div>
    </div>
  `).join('');
      drawChart();
    }

    function drawChart() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      const data = loadData();

      // ===== TÍNH TUẦN =====
      const base = new Date();
      base.setDate(base.getDate() + weekOffset * 7);
      base.setDate(base.getDate() - ((base.getDay() + 6) % 7));

      const days = [...Array(7)].map((_, i) => {
        const d = new Date(base);
        d.setDate(base.getDate() + i);
        return d.toISOString().split('T')[0];
      });

      weekLabel.textContent = days[0] + ' → ' + days[6];

      const values = days.map(day => {
        const items = data.filter(e => e.date === day);
        return items.length
          ? items.reduce((s, e) => s + e.score, 0) / items.length
          : null;
      });

      const pad = 45;
      const w = canvas.width - pad * 2;
      const h = canvas.height - pad * 2;

      /* ===== GRID Y ===== */
      ctx.strokeStyle = '#eef2f3';
      ctx.lineWidth = 1;
      for (let i = 0; i <= 5; i++) {
        const y = pad + h - (i / 5) * h;
        ctx.beginPath();
        ctx.moveTo(pad, y);
        ctx.lineTo(pad + w, y);
        ctx.stroke();

        ctx.fillStyle = '#9aa5ab';
        ctx.font = '11px Segoe UI';
        ctx.fillText(i, pad - 18, y + 4);
      }

      /* ===== AXIS ===== */
      ctx.strokeStyle = '#cfd8dc';
      ctx.beginPath();
      ctx.moveTo(pad, pad);
      ctx.lineTo(pad, pad + h);
      ctx.lineTo(pad + w, pad + h);
      ctx.stroke();

      /* ===== DAY LABEL ===== */
      ctx.fillStyle = '#607d8b';
      ctx.font = '12px Segoe UI';
      ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].forEach((d, i) => {
        ctx.fillText(d, pad + (w / 6) * i - 12, pad + h + 22);
      });

      /* ===== LINE ===== */
      ctx.strokeStyle = '#33c6e7';
      ctx.lineWidth = 3;
      ctx.lineJoin = 'round';
      ctx.lineCap = 'round';
      ctx.beginPath();

      values.forEach((v, i) => {
        if (v === null) return;
        const x = pad + (w / 6) * i;
        const y = pad + h - (v / 5) * h;
        i === 0 || values[i - 1] === null ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
      });
      ctx.stroke();

      /* ===== DOT ===== */
      values.forEach((v, i) => {
        if (v === null) return;

        const x = pad + (w / 6) * i;
        const y = pad + h - (v / 5) * h;

        ctx.font = '22px Segoe UI Emoji';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(moodEmoji[Math.round(v)], x, y);
      });

    }


    render();
  </script>

</body>

</html>