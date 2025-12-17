<!--
PAGE: Profile
DEPENDENCY:
table: orders, order_items
-->


<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Buddy - Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Color Variables */
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
            --card-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text);
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header Styles */
        header {
            background-color: var(--white);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            font-weight: bold;
            color: var(--primary);
            display: flex;
            align-items: center;
        }

        .logo-icon {
            margin-right: 8px;
            font-size: 28px;
        }

        nav a {
            margin: 0 15px;
            text-decoration: none;
            color: var(--primary);
            font-weight: 500;
            transition: color 0.3s;
        }

        nav a:hover {
            color: var(--accent);
        }

        .signin-btn {
            background-color: var(--accent);
            color: var(--white);
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .signin-btn:hover {
            background-color: #2ab4d1;
        }

        /* Hero Section */
        .page-hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: var(--white);
            padding: 60px 0;
            text-align: center;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }

        .page-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .breadcrumb {
            display: flex;
            justify-content: center;
            list-style: none;
        }

        .breadcrumb li {
            margin: 0 10px;
            position: relative;
        }

        .breadcrumb li:not(:last-child):after {
            content: ">";
            position: absolute;
            right: -15px;
        }

        .breadcrumb a {
            color: var(--light);
            text-decoration: none;
            transition: color 0.3s;
        }

        .breadcrumb a:hover {
            color: var(--accent);
        }

        .breadcrumb .current {
            color: var(--accent);
        }


        /*Sidebar*/
        .sidebar {
            background-color: #124559;
            border-radius: 20px;
            padding: 25px 20px;
            color: #fff;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 18px;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: 500;
            font-size: 18px;
        }

        .sidebar-menu li i {
            font-size: 20px;
            color: #598392;
            transition: color 0.3s ease;
        }

        .sidebar-menu li:hover {
            background-color: #124559;
        }

        .sidebar-menu li:hover i {
            color: #fff;
        }

        /* Dashboard Section */
        .dashboard-section {
            padding: 80px 0;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: 1fr 1.5fr 1fr;
            gap: 30px;
        }

        .dashboard-box {
            background-color: var(--white);
            border-radius: 10px;
            padding: 30px;
            box-shadow: var(--card-shadow);
            min-height: 350px;
        }

        .dashboard-box h2 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--primary);
            font-weight: 700;
        }

        /* Mood & Focus History */
        .mood-chart {
            text-align: center;
            background-color: #aec3b0;
            border-radius: 20px;
            padding: 25px 20px;
            margin-bottom: 25px;
        }

        .mood-chart p {
            margin-top: 8px;
            font-size: 16px;
            color: #3c6e71;
        }

        /* Focus log */
        .focus-log {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 15px 20px;
        }

        .focus-log h3 {
            font-size: 19px;
            color: #3c6e71;
            margin-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding-bottom: 5px;
        }

        .focus-log ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .focus-log li {
            font-size: 17px;
            color: #598392;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .focus-log li:last-child {
            border-bottom: none;
        }

        /* Badges & Achievements */
        .badge1 {

            width: 300px;
            text-align: center;
            background-color: #aec3b0;
            color: white;
            border-radius: 20px;
            padding: 15px 10px;
            margin-bottom: 25px;
        }

        .badge2 {

            width: 300px;
            text-align: center;
            background-color: #598392;
            border-radius: 20px;
            color: white;

            padding: 15px 10px;
            margin-bottom: 25px;
        }

        .badge3 {

            width: 300px;
            text-align: center;
            background-color: #124559;
            border-radius: 20px;
            color: white;

            padding: 15px 10px;
            margin-bottom: 25px;
        }

        .badge p {
            font-size: 14px;
            color: var(--text);
        }

        .btn-primary {
            background-color: #eff6e0;
            color: #124559;
            padding: 6px 15px;
            border: none;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: #598392;
        }

        /*Modal Failed Transaction*/
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999;
        }

        .modal-box {
            background: #ffffff;
            width: 90%;
            max-width: 600px;
            border-radius: 15px;
            padding: 25px 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.3s ease;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            color: #124559;
        }

        .close-modal {
            font-size: 26px;
            cursor: pointer;
            color: #999;
        }

        .close-modal:hover {
            color: #000;
        }

        /*Badge Requirement*/
        .badge-requirement {
            background: #eff6e0;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 15px;
        }

        .badge-requirement h3 {
            margin-bottom: 10px;
            color: #124559;
        }

        .badge-requirement ul {
            padding-left: 20px;
        }

        .badge-requirement li {
            font-size: 15px;
            margin-bottom: 6px;
            color: #353535;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }


        /* Footer */
        footer {
            background-color: var(--primary-dark);
            color: var(--white);
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
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: var(--accent);
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
            transition: color 0.3s;
        }

        .footer-column ul li a:hover {
            color: var(--accent);
        }

        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid var(--primary);
            color: var(--light);
            font-size: 0.9rem;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .social-links a {
            color: var(--light);
            font-size: 1.2rem;
            transition: color 0.3s;
        }

        .social-links a:hover {
            color: var(--accent);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            nav {
                margin: 15px 0;
            }

            nav a {
                margin: 0 10px;
            }

            .contact-container {
                grid-template-columns: 1fr;
            }

            .page-hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <div class="container header-content">
            <div class="logo">
                <span class="logo-icon">🤖</span>
                AI Buddy
            </div>
            <nav>
                <a href="AIBuddy_Homepage.php">Home</a>
                <a href="AIBuddy_Chatbot.php">Chatbot</a>
                <a href="AIBuddy_EmotionTracker.php">Emotion Tracker</a>
                <a href="AIBuddy_Trial.php">Trial</a>
                <a href="AIBuddy_Profile.php">Profile</a>
                <a href="AIBuddy_About.php">About</a>
                <a href="AIBuddy_Contact.php">Contact</a>
            </nav>
            <a href="AIBuddy_SignIn.php">
                <button class="signin-btn">Sign In</button>
            </a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="page-hero">
        <div class="container">
            <h1>Your Profile</h1>
            <ul class="breadcrumb">
                <li><a href="AIBuddy_Homepage.php">Home</a></li>
                <li class="current">Profile</li>
            </ul>
        </div>
    </section>

    <!--Dashboard Section-->
    <main>
        <section class="dashboard-section">
            <div class="dashboard-container">

                <!-- Cột 1: Sidebar Menu -->
                <div class="sidebar">
                    <ul class="sidebar-menu">
                        <li><i class="fas fa-user-circle"></i> Account Details </li>
                        <li><i class="fas fa-credit-card"></i> Manage Subscription</li>
                        <li><i class="fas fa-history"></i> Membership History </li>
                        <li id="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Log Out
                        </li>
                    </ul>
                </div>

                <!-- Cột 2: Account Details -->
                <div class="dashboard-box">
                    <h2>Account Details</h2>

                    <div class="mood-chart">
                        <p>[Image of last 7 days' mood-trend chart]</p>
                        <p><strong>Last 7 days' mood-trend</strong></p>
                    </div>

                    <div class="focus-log">
                        <h3>Recent Focus Sessions</h3>
                        <ul>
                            <li>🕓 45 mins • Deep Work • Oct 30</li>
                            <li>🕓 30 mins • Study • Oct 29</li>
                            <li>🕓 60 mins • Reading • Oct 28</li>
                        </ul>
                    </div>
                </div>

                <!-- Cột 3: Badges & Achievements -->
                <div class="dashboard-box">
                    <h2>Badges & Achievements</h2>
                    <div class="badge-grid">
                        <div class="badge1">
                            <p>&#127941; Calm Master</p>
                        </div>
                        <div class="badge2">
                            <p>&#129496; Focus Hero</p>
                        </div>
                        <div class="badge3">
                            <p>&#128172; Consistency Streak</p>
                        </div>
                    </div>
                    <button class="btn-primary">View Details</button>

                </div>

            </div>
        </section>
    </main>

    <!-- Footer -->
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
                        <li><a href="AIBuddy_Trial.php">Trial</a></li>
                        <li><a href="AIBuddy_Contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="AIBuddy_Terms of Service.php">Terms of Service</a></li>
                        <li><a href="AIBuddy_PrivacyPolicy.php">Privacy Policy</a></li>
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
                <p>&copy; 2025 AI Buddy. All rights reserved. | Mental Health Companion</p>
            </div>
        </div>
    </footer>

    <!-- Badge Requirement Modal -->
    <div class="modal-overlay" id="badgeModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>🏆 Badge Requirements</h2>
                <span class="close-modal" id="closeModal">&times;</span>
            </div>

            <div class="modal-content">

                <div class="badge-requirement">
                    <h3>🏅 Calm Master</h3>
                    <ul style="list-style: none; padding-left: 15px;">
                        <li>✔ Essential plan or higher</li>
                        <li>✔ Complete at least 5 sessions</li>
                        <li>✔ Use Breathe Animation 3 times</li>
                    </ul>
                </div>

                <div class="badge-requirement">
                    <h3>🧘 Focus Hero</h3>
                    <ul style="list-style: none; padding-left: 15px;">
                        <li>✔ Premium plan required</li>
                        <li>✔ Complete 15 focus sessions</li>
                        <li>✔ Enable automatic focus reminders</li>
                        <li>✔ 5 sessions longer than 25 minutes</li>
                    </ul>
                </div>

                <div class="badge-requirement">
                    <h3>🔥 Consistency Streak</h3>
                    <ul style="list-style: none; padding-left: 15px;">
                        <li>✔ Premium plan required</li>
                        <li>✔ Active 7 consecutive days</li>
                        <li>✔ Minimum 1 session per day</li>
                        <li>✔ Emotional trends analysis enabled</li>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <script>
        const viewDetailBtn = document.querySelector(".btn-primary");
        const badgeModal = document.getElementById("badgeModal");
        const closeModal = document.getElementById("closeModal");

        viewDetailBtn.addEventListener("click", () => {
            badgeModal.style.display = "flex";
        });

        closeModal.addEventListener("click", () => {
            badgeModal.style.display = "none";
        });

        badgeModal.addEventListener("click", (e) => {
            if (e.target === badgeModal) {
                badgeModal.style.display = "none";
            }
        });

        document.getElementById("logout-btn").addEventListener("click", () => {
            window.location.href = "AIBuddy_SignIn.php";
        });
    </script>

</body>

</html>
