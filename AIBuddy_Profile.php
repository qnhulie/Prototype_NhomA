<?php
require_once 'config.php';
session_start();
$UserID = $_SESSION['user_id'] ?? 101;

$tab = $_GET['tab'] ?? 'account'; // mặc định Account Details

$stmt = $conn->prepare("
    SELECT UserID, UserName, UserEmail, PhoneNumber
    FROM users
    WHERE UserID = ?
");
$stmt->bind_param("i", $UserID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("
    SELECT 
        p.PlanName,
        o.OrderStatus
    FROM userorder o
    JOIN plan p ON o.PlanID = p.PlanID
    WHERE o.UserID = ?
    ORDER BY o.OrderID DESC
    LIMIT 1
");

$stmt->bind_param("i", $UserID);
$stmt->execute();
$membership = $stmt->get_result()->fetch_assoc();

$membershipStatus = 'No Active Plan';

/* ================================
   SUBSCRIPTION: CURRENT PLAN INFO
================================ */
$stmt = $conn->prepare("
    SELECT 
        o.OrderID,
        o.PlanID,
        o.OrderStatus,
        p.PlanName,
        p.PlanDescription,
        p.PlanPrice
    FROM userorder o
    JOIN plan p ON o.PlanID = p.PlanID
    WHERE o.UserID = ?
    ORDER BY o.OrderID DESC
    LIMIT 1
");
$stmt->bind_param("i", $UserID);
$stmt->execute();
$currentPlan = $stmt->get_result()->fetch_assoc();

/* ================================
   BADGE LOGIC (DEMO VERSION)
================================ */
$currentBadge = null;

if ($currentPlan && in_array($currentPlan['PlanName'], ['Essential', 'Premium'])) {
    $currentBadge = [
        'BadgeID' => 1,
        'BadgeName' => 'Calm Master',
        'BadgeSymbol' => '🏅',
        'BadgeColor' => 'badge1'
    ];
}

/* ================================
   ACTION: REQUEST REFUND
================================ */
if (isset($_POST['action']) && $_POST['action'] === 'request_refund') {

    $refundType = $_POST['refund_type'] ?? null;
    $refundAmount = $_POST['refund_amount'] ?? null;
    $refundDetails = $_POST['refund_details'] ?? null;

    if (!$refundType || !$refundAmount || !$refundDetails) {
        die("Missing refund information");
    }

    // Lấy OrderID mới nhất
    $stmt = $conn->prepare("
        SELECT OrderID
        FROM userorder
        WHERE UserID = ?
        ORDER BY OrderID DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        die("No transaction found.");
    }

    $transactionID = $order['OrderID'];

    // Insert refund request
    $stmt = $conn->prepare("
        INSERT INTO refundrequest
        (TransactionID, RefundType, RefundAmount, RefundDetails, RefundStatus)
        VALUES (?, ?, ?, ?, 'Pending')
    ");
    $stmt->bind_param(
        "isds",
        $transactionID,
        $refundType,
        $refundAmount,
        $refundDetails
    );
    $stmt->execute();

    header("Location: AIBuddy_Profile.php?tab=subscription");
    exit;
}



if (isset($_POST['update_single'])) {

    $field = $_POST['field'];   // UserName | UserEmail | PhoneNumber | Password
    $value = $_POST['value'];

    $allowedFields = ['UserName', 'UserEmail', 'PhoneNumber', 'Password'];

    if (!in_array($field, $allowedFields)) {
        die("Invalid field");
    }

    if ($field === 'Password') {
        $value = password_hash($value, PASSWORD_DEFAULT);
    }

    $sql = "UPDATE users SET $field = ? WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $value, $UserID);
    $stmt->execute();

    header("Location: AIBuddy_Profile.php?tab=account");
    exit;
}

if (isset($_POST['change_password'])) {

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        die("New password confirmation does not match");
    }

    // Lấy password hiện tại trong DB
    $stmt = $conn->prepare("SELECT Password FROM users WHERE UserID = ?");
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result || !password_verify($current, $result['Password'])) {
        die("Current password is incorrect");
    }

    // Update password mới
    $hashed = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET Password = ? WHERE UserID = ?");
    $stmt->bind_param("si", $hashed, $UserID);
    $stmt->execute();

    header("Location: AIBuddy_Profile.php?tab=account");
    exit;
}

/* ================================
   ACTION: CANCEL SUBSCRIPTION
================================ */
if (isset($_POST['action']) && $_POST['action'] === 'cancel_subscription') {

    $cancelType = $_POST['cancel_type'] ?? null;
    $cancelReason = $_POST['cancel_reason'] ?? null;

    if (!$cancelType) {
        die("Missing cancellation type");
    }

    // Lấy MembershipID (OrderID mới nhất)
    $stmt = $conn->prepare("
        SELECT OrderID
        FROM userorder
        WHERE UserID = ?
        ORDER BY OrderID DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        die("No active membership found.");
    }

    $membershipID = $order['OrderID'];

    // Insert vào subscriptioncancel
    $stmt = $conn->prepare("
        INSERT INTO subscriptioncancel
        (MembershipID, CancellationType, CancellationReason, CancellationStatus)
        VALUES (?, ?, ?, 'Pending')
    ");
    $stmt->bind_param(
        "iss",
        $membershipID,
        $cancelType,
        $cancelReason
    );
    $stmt->execute();

    // Update trạng thái order
    $stmt = $conn->prepare("
        UPDATE userorder
        SET OrderStatus = 'Cancelled'
        WHERE OrderID = ?
    ");
    $stmt->bind_param("i", $membershipID);
    $stmt->execute();

    header("Location: AIBuddy_Profile.php?tab=subscription");
    exit;
}



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

        .user-account {
            display: flex;
            align-items: center;
            gap: 8px;

            padding: 8px 16px;
            border-radius: 20px;

            background-color: var(--background);
            color: var(--primary);

            font-weight: 600;
            text-decoration: none;

            transition: all 0.25s ease;
        }

        .user-account i {
            font-size: 18px;
            color: var(--primary);
        }

        .user-account:hover {
            background-color: var(--accent);
            color: var(--white);
        }

        .user-account:hover i {
            color: var(--white);
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



        .account-form .form-group {
            margin-bottom: 22px;
        }

        .account-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary);
            font-size: 16px;
        }

        .account-form .form-control {
            width: 100%;
            padding: 14px 16px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
            background: #fff;
        }

        .field-row {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .edit-btn {
            background-color: var(--primary);
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .edit-btn:hover {
            background-color: var(--primary-light);
        }

        /* ===== EDIT LINK (FOR PASSWORD) ===== */
        .edit-link {
            background-color: var(--primary);
            color: #fff;
            border: none;

            /* QUAN TRỌNG: ép <a> giống <button> */
            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 10px 16px;
            /* y chang edit-btn */
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;

            text-decoration: none;
            /* bỏ gạch chân */
            line-height: normal;
        }

        .edit-link:hover {
            background-color: var(--primary-light);
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

        /* ================= MODAL – DASHBOARD STYLE ================= */

        /* Overlay */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(1, 22, 30, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999;
        }

        /* Box giống dashboard-box */
        .modal-box {
            background: var(--white);
            width: 100%;
            max-width: 520px;
            border-radius: 20px;
            padding: 32px 36px;
            box-shadow: var(--card-shadow);
            animation: modalFade 0.25s ease;
        }

        /* Header */
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 26px;
        }

        .modal-header h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
        }

        /* Close */
        .close-modal {
            font-size: 28px;
            cursor: pointer;
            color: #aaa;
        }

        .close-modal:hover {
            color: var(--primary);
        }

        /* Form layout */
        .modal-box .form-group {
            margin-bottom: 20px;
        }

        .modal-box label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--primary);
        }

        /* Input / Select / Textarea */
        .modal-box input,
        .modal-box select,
        .modal-box textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #dcdcdc;
            font-size: 15px;
            background: #fff;
        }

        .modal-box input:focus,
        .modal-box select:focus,
        .modal-box textarea:focus {
            border-color: var(--primary-light);
            outline: none;
        }

        /* Buttons */
        .modal-box button {
            width: 100%;
            margin-top: 14px;
            padding: 14px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
        }

        /* Animation */
        @keyframes modalFade {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
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

    <?php if (!empty($_SESSION['user_name'])): ?>
        <a href="AIBuddy_Profile.php" class="user-account">
            <i class="fa-regular fa-user"></i>
            <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
        </a>
    <?php endif; ?>

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
                        <li onclick="location.href='AIBuddy_Profile.php?tab=account'">
                            <i class="fas fa-user-circle"></i> Account Details
                        </li>
                        <li onclick="location.href='AIBuddy_Profile.php?tab=subscription'">
                            <i class="fas fa-credit-card"></i> Manage Subscription
                        </li>
                        <li onclick="location.href='AIBuddy_Profile.php?tab=membership'">
                            <i class="fas fa-history"></i> Membership History
                        </li>
                        <li id="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Log Out
                        </li>
                    </ul>
                </div>



                <!-- Cột 2: Dashboard Box -->
                <div class="dashboard-box">
                    <?php if ($tab === 'account'): ?>

                        <h2>Account Details</h2>

                        <div class="account-form">

                            <div class="form-group">
                                <label>Full Name *</label>
                                <div class="field-row">
                                    <input type="text" value="<?= htmlspecialchars($user['UserName']) ?>"
                                        class="form-control" disabled>
                                    <button type="button" class="edit-btn"
                                        onclick="openEditModal('UserName','Full Name','<?= htmlspecialchars($user['UserName']) ?>')">
                                        Edit
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Email *</label>
                                <div class="field-row">
                                    <input type="email" value="<?= htmlspecialchars($user['UserEmail']) ?>"
                                        class="form-control" disabled>
                                    <button type="button" class="edit-btn"
                                        onclick="openEditModal('UserEmail','Email','<?= htmlspecialchars($user['UserEmail']) ?>')">
                                        Edit
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Phone Number *</label>
                                <div class="field-row">
                                    <input type="text" value="<?= htmlspecialchars($user['PhoneNumber']) ?>"
                                        class="form-control" disabled>
                                    <button type="button" class="edit-btn"
                                        onclick="openEditModal('PhoneNumber','Phone Number','<?= htmlspecialchars($user['PhoneNumber']) ?>')">
                                        Edit
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Password</label>
                                <div class="field-row">
                                    <input type="password" value="********" class="form-control" disabled>

                                    <a href="AIBuddy_ChangePassword.php" class="edit-btn edit-link">
                                        Edit
                                    </a>

                                </div>
                            </div>


                            <hr>
                            <br>
                            <p><strong>Membership Status:</strong>
                                <span class="status active"><?= $membershipStatus ?></span>
                            </p>

                            <p><strong>Current Plan:</strong>
                                <?= $membership['PlanName'] ?? 'None' ?>
                            </p>

                        </div>


                    <?php endif; ?>
                    <?php if ($tab === 'subscription'): ?>

                        <!-- DASHBOARD: MANAGE SUBSCRIPTION -->
                        <h2>Manage Subscription</h2>

                        <?php if (!$currentPlan): ?>
                            <p>You do not have any active subscription.</p>
                        <?php else: ?>

                            <div class="account-form">

                                <div class="form-group">
                                    <label>Plan ID</label>
                                    <input class="form-control" value="<?= $currentPlan['PlanID'] ?>" disabled>
                                </div>

                                <div class="form-group">
                                    <label>Plan Name</label>
                                    <input class="form-control" value="<?= htmlspecialchars($currentPlan['PlanName']) ?>"
                                        disabled>
                                </div>

                                <div class="form-group">
                                    <label>Plan Description</label>
                                    <textarea class="form-control" rows="3"
                                        disabled><?= htmlspecialchars($currentPlan['PlanDescription']) ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Price</label>
                                    <input class="form-control" value="<?= number_format($currentPlan['PlanPrice']) ?> VND"
                                        disabled>
                                </div>

                                <div style="display:flex; gap:15px; margin-top:25px;">
                                    <button class="edit-btn" onclick="openCancelModal()">Cancel Subscription</button>
                                    <button class="btn-primary" onclick="openRefundModal()">Request Refund</button>
                                </div>

                            </div>

                        <?php endif; ?>
                    <?php endif; ?>


                </div>


                <!-- Cột 3: Badges & Achievements -->
                <div class="dashboard-box">
                    <h2>Badges & Achievements</h2>

                    <?php if ($currentBadge): ?>
                        <div class="<?= $currentBadge['BadgeColor'] ?>">
                            <p><?= $currentBadge['BadgeSymbol'] ?>     <?= $currentBadge['BadgeName'] ?></p>
                            <small>Your current badge</small>
                        </div>
                    <?php else: ?>
                        <p>No badge earned yet.</p>
                    <?php endif; ?>
                    <br>

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
                    <button class="btn-primary" id="openBadgeModal">
                        View Details
                    </button>
                </div>

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

    <!-- Edit Account Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2 id="modalTitle">Edit</h2>
                <span class="close-modal" onclick="closeEditModal()">&times;</span>
            </div>

            <form method="post">
                <input type="hidden" name="field" id="editField">

                <div class="form-group">
                    <label id="editLabel"></label>
                    <input type="text" name="value" id="editValue" class="form-control">
                </div>

                <button type="submit" name="update_single" class="btn-primary">
                    Save
                </button>
            </form>
        </div>
    </div>

    <!-- Badge Requirement Modal -->
    <div class="modal-overlay" id="badgeModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>🏆 Badge Requirements</h2>
                <span class="close-modal" id="closeModal">&times;</span>
            </div>

            <div class="modal-content">

                <!-- LEVEL 1 -->
                <div class="badge-requirement">
                    <h3>🏅 Calm Master</h3>
                    <ul style="list-style:none; padding-left:15px;">
                        <li>✔ Essential plan or higher</li>
                        <li>✔ Active subscription</li>
                    </ul>
                </div>

                <!-- LEVEL 2 -->
                <div class="badge-requirement">
                    <h3>🧘 Focus Hero</h3>
                    <ul style="list-style:none; padding-left:15px;">
                        <li>✔ Premium plan required</li>
                        <li>✔ Complete 15 focus sessions</li>
                    </ul>
                </div>

                <!-- LEVEL 3 -->
                <div class="badge-requirement">
                    <h3>🔥 Consistency Streak</h3>
                    <ul style="list-style:none; padding-left:15px;">
                        <li>✔ Premium plan required</li>
                        <li>✔ Active 7 consecutive days</li>
                    </ul>
                </div>

            </div>

        </div>
    </div>

    <!-- MODAL: CANCEL SUBSCRIPTION -->
    <div class="modal-overlay" id="cancelModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Cancel Subscription</h2>
                <span class="close-modal" onclick="closeCancelModal()">&times;</span>
            </div>

            <form method="post">
                <input type="hidden" name="action" value="cancel_subscription">

                <div class="form-group">
                    <label>Cancellation Type</label>
                    <select name="cancel_type" class="form-control" required>
                        <option value="immediate">Cancel Immediately</option>
                        <option value="end_period">Cancel at End of Period</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Reason (optional)</label>
                    <select name="cancel_reason" class="form-control">
                        <option value="">-- Select reason --</option>
                        <option>I don’t use the service much</option>
                        <option>Service is not suitable</option>
                        <option>Pricing issue</option>
                        <option>Technical problems</option>
                        <option>Other</option>
                    </select>
                </div>

                <button type="submit" class="edit-btn">Confirm Cancellation</button>
            </form>
        </div>
    </div>

    <!-- MODAL: REQUEST REFUND -->
    <div class="modal-overlay" id="refundModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Request Refund</h2>
                <span class="close-modal" onclick="closeRefundModal()">&times;</span>
            </div>

            <form method="post">
                <input type="hidden" name="action" value="request_refund">

                <div class="form-group">
                    <label>Refund Type</label>
                    <select name="refund_type" class="form-control" required>
                        <option value="Partial">Partial Refund</option>
                        <option value="Full">Full Refund</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Refund Amount (VND)</label>
                    <input name="refund_amount" type="number" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Refund Details *</label>
                    <textarea name="refund_details" class="form-control" rows="4" required></textarea>
                </div>

                <button type="submit" class="btn-primary">
                    Submit Request
                </button>
            </form>
        </div>
    </div>





    <script>
        /* ===== SUBSCRIPTION MODALS ===== */
        function openCancelModal() {
            document.getElementById("cancelModal").style.display = "flex";
        }
        function closeCancelModal() {
            document.getElementById("cancelModal").style.display = "none";
        }

        function openRefundModal() {
            document.getElementById("refundModal").style.display = "flex";
        }
        function closeRefundModal() {
            document.getElementById("refundModal").style.display = "none";
        }

        const badgeBtn = document.getElementById("openBadgeModal");
        const badgeModal = document.getElementById("badgeModal");
        const closeModal = document.getElementById("closeModal");

        badgeBtn.addEventListener("click", () => {
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

        function openEditModal(field, label, value) {
            document.getElementById("editModal").style.display = "flex";
            document.getElementById("editField").value = field;
            document.getElementById("editLabel").innerText = label;
            document.getElementById("editValue").value = value;
        }

        function closeEditModal() {
            document.getElementById("editModal").style.display = "none";
        }

        document.getElementById("logout-btn").addEventListener("click", () => {
            window.location.href = "AIBuddy_SignIn.php";
        });


    </script>

</body>

</html>
