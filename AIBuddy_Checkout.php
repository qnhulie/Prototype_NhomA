<?php
require_once 'config.php';
session_start();

/* User đã login */
$UserID = $_SESSION['user_id'] ?? 101;

/* Nhận PlanID từ Trial */
$PlanID = $_GET['plan_id'] ?? null;
if (!$PlanID) {
    die("Missing PlanID");
}

/* ================= LOAD PLAN ================= */
$stmt = $conn->prepare("
    SELECT PlanID, PlanName, PlanPrice, BillingCycle
    FROM plan
    WHERE PlanID = ?
");
$stmt->bind_param("i", $PlanID);
$stmt->execute();
$result = $stmt->get_result();
$plan = $result->fetch_assoc();

if (!$plan) {
    die("Plan not found");
}

/* ================= LOAD USER ================= */
$stmt = $conn->prepare("
    SELECT UserID, UserName, UserEmail, PhoneNumber
    FROM users
    WHERE UserID = ?
");
$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found");
}

$paymentSuccess = false;
$orderSummary = null;

/* ================= SUBMIT PAYMENT ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        /* MySQLi transaction */
        $conn->begin_transaction();

        /* INSERT userorder */
        $stmt = $conn->prepare("
            INSERT INTO userorder (UserID, PlanID, TotalAmount, OrderStatus)
            VALUES (?, ?, ?, 'Completed')
        ");
        $stmt->bind_param(
            "iid",
            $UserID,
            $plan['PlanID'],
            $plan['PlanPrice']
        );
        $stmt->execute();

        $OrderID = $conn->insert_id;

        /* INSERT transactions */
        $stmt = $conn->prepare("
            INSERT INTO transactions (OrderID, PaymentMethod, PaymentStatus)
            VALUES (?, ?, 'Completed')
        ");
        $stmt->bind_param(
            "is",
            $OrderID,
            $_POST['PaymentMethod']
        );
        $stmt->execute();

        /* LOAD DATA FOR MODAL */
        $stmt = $conn->prepare("
            SELECT
                o.OrderID,
                o.TotalAmount,
                p.PlanName,
                t.PaymentMethod,
                t.PaymentTime,
                u.UserName,
                u.UserEmail
            FROM userorder o
            JOIN plan p ON o.PlanID = p.PlanID
            JOIN transactions t ON o.OrderID = t.OrderID
            JOIN users u ON o.UserID = u.UserID
            WHERE o.OrderID = ?
        ");
        $stmt->bind_param("i", $OrderID);
        $stmt->execute();
        $result = $stmt->get_result();
        $orderSummary = $result->fetch_assoc();

        $conn->commit();
        $paymentSuccess = true;

    } catch (Exception $e) {
        $conn->rollback();
        die("Payment failed: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>AI Buddy - Checkout</title>
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

        /* Header */
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

        /* Checkout Information */
        .payment-section {
            background-color: var(--background);
            border-radius: 10px;
            padding: 40px;
            box-shadow: var(--card-shadow);
        }

        .payment-section h2 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 30px;
            text-align: center;
        }

        .payment-container {
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
        }

        .customer-form,
        .payment-method {
            flex: 1;
            min-width: 320px;
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .customer-form h3,
        .payment-method h3 {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        /* Reuse input style */
        .payment-section .form-group {
            margin-bottom: 20px;
        }

        .payment-section .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
        }

        .payment-section .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--gray);
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .payment-section .form-control:focus {
            outline: none;
            border-color: var(--accent);
        }

        .btn {
            background-color: #124559;
            color: var(--white);
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover {
            background-color: #598392;
        }

        /* Order Information*/
        .order-information {
            background-color: #aec3b0;
            border-radius: 10px;
            padding: 40px;
            color: var(--primary);
            width: 100%;
            box-sizing: border-box;
        }

        .order-information h2 {
            font-size: 2rem;
            margin-bottom: 15px;
            color: var(--primary);
        }

        .order-information p {
            font-size: 1.1rem;
            margin-bottom: 25px;
            color: var(--text);
        }

        .order-information ul {
            list-style: none;
            padding: 0;
            margin-bottom: 30px;
        }

        .order-information li {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .order-information li span {
            color: #598392;
            font-weight: bold;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .payment-timer {
            margin: 12px 0;
            text-align: center;
            font-size: 14px;
            color: #124559;
        }

        /* ================= MODAL (COMMON) ================= */

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(1, 22, 30, 0.6);
            /* dark overlay */
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            padding: 28px 26px;
            border-radius: 16px;
            width: 90%;
            max-width: 420px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.35s ease;
        }

        .modal-content h2 {
            font-size: 1.7rem;
            margin-bottom: 16px;
            color: var(--primary);
        }

        .modal-content p {
            font-size: 1rem;
            margin: 6px 0;
            color: var(--text);
        }

        .modal-content p strong {
            color: var(--primary-dark);
        }

        /* Button group */
        .modal-actions {
            margin-top: 22px;
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .modal-actions .btn,
        .modal-content .btn {
            min-width: 140px;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        /* ================= ANIMATION ================= */

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
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
        @media (max-width: 1024px) {
            nav a {
                margin: 0 8px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            nav {
                margin: 15px 0;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }

            nav a {
                margin: 0 8px 5px;
                font-size: 0.9rem;
            }

            .signup-card {
                padding: 30px 25px;
            }

            .signup-header h1 {
                font-size: 1.8rem;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .signin-btn {
                margin: 10px 0 0 0;
            }
        }

        @media (max-width: 480px) {
            .signup-card {
                padding: 25px 20px;
            }

            .signup-header h1 {
                font-size: 1.6rem;
            }

            .signup-icon {
                font-size: 2.5rem;
            }

            .form-input,
            .form-select {
                padding: 12px 15px 12px 45px;
            }

            nav a {
                margin: 0 5px 5px;
                font-size: 0.85rem;
            }
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
                <a href="Prototype_Homepage.html">Home</a>
                <a href="Prototype_Chatbot.html">Chatbot</a>
                <a href="Prototype_EmotionTracker.html">Emotion Tracker</a>
                <a href="Prototype_Trial.html">Trial</a>
                <a href="Prototype_Profile.html">Profile</a>
                <a href="Prototype_About.html">About</a>
                <a href="Prototype_Contact.html">Contact</a>
            </nav>
            <a href="AIBuddy_SignIn.html">
                <button class="signin-btn">Sign In</button>
            </a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="page-hero">
        <div class="container">
            <h1>Checkout</h1>
            <ul class="breadcrumb">
                <li><a href="Prototype_Trial.html">Trial</a></li>
                <li class="current">Checkout</li>
            </ul>
        </div>
    </section>

    <!-- Payment Timer -->
    <section class="payment-timer">
        <h2>
            Time left to complete payment:
            <strong id="timer-value">02:00</strong>
        </h2>
    </section>

    <section class="payment-section">
        <form method="post">
            <div class="payment-container">

                <div class="customer-form">
                    <h2>Customer Information</h2>

                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="FullName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="UserEmail" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="text" name="PhoneNumber" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Country *</label>
                        <input type="text" name="Country" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>City / Province *</label>
                        <input type="text" name="City" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Postal Code *</label>
                        <input type="text" name="PostalCode" class="form-control" required>
                    </div>
                </div>

                <div class="payment-method">
                    <h2>Payment Method Details</h2>

                    <div class="form-group">
                        <label>Payment Method *</label>
                        <select name="PaymentMethod" class="form-control" required>
                            <option value="">Select</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Debit Card">Debit Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="E-Wallet">E-Wallet</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Card / Account Number *</label>
                        <input type="text" name="CardNumber" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Cardholder / Account Name *</label>
                        <input type="text" name="CardHolder" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Expiration Date</label>
                        <input type="text" name="Expiry" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" name="CVV" class="form-control">
                    </div>

                    <button type="submit" class="btn">Submit Payment</button>
                </div>

            </div>
        </form>
    </section>



    <!-- Order Information -->
    <section class="order-information">
        <h2>Your order information</h2>
        <p><strong>Plan:</strong> <?= htmlspecialchars($plan['PlanName']) ?></p>
        <p><strong>Price:</strong> <?= number_format($plan['PlanPrice']) ?> VND</p>
        <p><strong>Billing:</strong> <?= htmlspecialchars($plan['BillingCycle']) ?></p>

    </section>

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
                        <li><a href="Prototype_Homepage.html">Home</a></li>
                        <li><a href="Prototype_Chatbot.html">Chatbot</a></li>
                        <li><a href="Prototype_EmotionTracker.html">Emotion Tracker</a></li>
                        <li><a href="Prototype_Trial.html">Trial</a></li>
                        <li><a href="Prototype_Contact.html">Contact</a></li>
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

    <!-- Modal Payment Failed -->
    <div class="modal-overlay" id="timeout-modal-overlay">
        <div class="modal-content">
            <h2>❌ Payment Failed</h2>
            <p>Your payment session has expired.</p>

            <div class="modal-actions">
                <button class="btn" id="restart-btn">
                    Start Again
                </button>
                <button class="btn" id="home-btn">
                    Back to Homepage
                </button>
            </div>
        </div>
    </div>

    <script>
        let timeLeft = 120;

        const timerValueEl = document.getElementById("timer-value");
        const timeoutModal = document.getElementById("timeout-modal-overlay");
        const restartBtn = document.getElementById("restart-btn");
        const homeBtn = document.getElementById("home-btn");

        console.log(timerValueEl, timeoutModal, restartBtn, homeBtn);

        if (!timerValueEl || !timeoutModal) {
            console.error("Missing timer or modal element");
        }

        const timerInterval = setInterval(updateTimer, 1000);

        function updateTimer() {
            console.log("Time left:", timeLeft);

            let min = Math.floor(timeLeft / 60);
            let sec = timeLeft % 60;

            min = min < 10 ? "0" + min : min;
            sec = sec < 10 ? "0" + sec : sec;

            timerValueEl.textContent = `${min}:${sec}`;

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                timeoutModal.classList.add("active");
            }

            timeLeft--;
        }

        restartBtn?.addEventListener("click", () => {
            location.reload();
        });

        homeBtn?.addEventListener("click", () => {
            window.location.assign("AIBuddy_Homepage.php");
        });
    </script>

    <?php if ($paymentSuccess && $orderSummary): ?>
        <div class="modal-overlay active">
            <div class="modal-content">
                <h2>✅ Payment Successful</h2>

                <p><strong>OrderID:</strong> <?= $orderSummary['OrderID'] ?></p>
                <p><strong>Plan:</strong> <?= htmlspecialchars($orderSummary['PlanName']) ?></p>
                <p><strong>Total:</strong> <?= number_format($orderSummary['TotalAmount']) ?> VND</p>
                <p><strong>Payment:</strong> <?= htmlspecialchars($orderSummary['PaymentMethod']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($orderSummary['UserEmail']) ?></p>

                <a href="AIBuddy_Homepage.php" class="btn">Back to Homepage</a>
            </div>
        </div>
    <?php endif; ?>

</body>


</html>
