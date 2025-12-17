<?php
session_start();
require_once 'config.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // VALIDATE
    if (!$email || !$old_password || !$new_password || !$confirm_password) {
        $error = "Vui lòng nhập đầy đủ thông tin";
    } else if ($new_password !== $confirm_password) {
        $error = "Mật khẩu mới không khớp";
    } else if (strlen($new_password) < 6) {
        $error = "Mật khẩu mới phải có ít nhất 6 ký tự";
    } else if ($new_password === $old_password) {
        $error = "Mật khẩu mới không được trùng với mật khẩu cũ";
    } else {
        // KIỂM TRA EMAIL VÀ MẬT KHẨU CŨ
        $stmt = $conn->prepare("SELECT UserID, UserPassword FROM users WHERE UserEmail = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($old_password, $user['UserPassword'])) {
            // KIỂM TRA MẬT KHẨU MỚI KHÔNG ĐƯỢC TRÙNG VỚI MẬT KHẨU HIỆN TẠI
            if (password_verify($new_password, $user['UserPassword'])) {
                $error = "Mật khẩu mới không được trùng với mật khẩu hiện tại";
            } else {
                // CẬP NHẬT MẬT KHẨU MỚI
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET UserPassword = ? WHERE UserID = ?");
                if ($stmt->execute([$hashed, $user['UserID']])) {
                    $success = "Đổi mật khẩu thành công! Vui lòng đăng nhập lại.";
                } else {
                    $error = "Đã xảy ra lỗi. Vui lòng thử lại!";
                }
            }
        } else {
            $error = "Email hoặc mật khẩu cũ không đúng";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Buddy - Change Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Color Variables từ Homepage */
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
            --error: #F44336;
            --success: #4CAF50;
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header Styles (giống SignIn) */
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
            text-decoration: none;
        }

        .logo-icon {
            margin-right: 8px;
            font-size: 28px;
        }

        nav {
            display: flex;
            align-items: center;
        }

        nav a {
            margin: 0 10px;
            text-decoration: none;
            color: var(--primary);
            font-weight: 500;
            transition: color 0.3s;
            white-space: nowrap;
            font-size: 0.95rem;
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
            margin-left: 10px;
        }

        .signin-btn:hover {
            background-color: #2ab4d1;
        }

        /* Change Password Section */
        .change-section {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
            padding: 40px 0;
            margin: 20px 0;
        }

        .change-container {
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .change-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 40px;
            width: 100%;
            box-shadow: var(--card-shadow);
            border-top: 5px solid var(--accent);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out;
        }

        .change-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--accent), var(--primary-light));
        }

        .change-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .change-header h1 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 2.2rem;
        }

        .change-header p {
            color: var(--primary-light);
            font-size: 1.1rem;
        }

        .change-icon {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary);
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-light);
            font-size: 1.2rem;
        }

        .form-input {
            width: 100%;
            padding: 14px 15px 14px 50px;
            border: 2px solid var(--gray);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: rgba(174, 195, 176, 0.05);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(51, 198, 231, 0.2);
            background-color: var(--white);
        }

        .form-input::placeholder {
            color: var(--primary-light);
            opacity: 0.7;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--primary-light);
            cursor: pointer;
            font-size: 1.2rem;
        }

        /* Alert Messages */
        .alert-message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            text-align: center;
            animation: slideIn 0.3s ease-out;
        }

        .alert-error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        /* Change Button */
        .change-form-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        .change-form-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(1, 22, 30, 0.2);
        }

        .change-form-btn:active {
            transform: translateY(0);
        }

        /* Back to Sign In Link */
        .back-link {
            text-align: center;
            font-size: 1rem;
            color: var(--primary-light);
            margin-top: 20px;
        }

        .back-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        /* Password Strength Indicator */
        .password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .strength-bar {
            flex: 1;
            height: 4px;
            background-color: var(--gray);
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }

        /* Field Error */
        .field-error {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }

        /* Footer (giống SignIn) */
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

            .change-card {
                padding: 30px 25px;
            }

            .change-header h1 {
                font-size: 1.8rem;
            }

            .signin-btn {
                margin: 10px 0 0 0;
            }
        }

        @media (max-width: 480px) {
            .change-card {
                padding: 25px 20px;
            }

            .change-header h1 {
                font-size: 1.6rem;
            }

            .change-icon {
                font-size: 2.5rem;
            }

            .form-input {
                padding: 12px 15px 12px 45px;
            }

            nav a {
                margin: 0 5px 5px;
                font-size: 0.85rem;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }

        /* Form validation styles */
        .form-input.invalid {
            border-color: var(--error);
            box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.1);
        }

        .form-input.valid {
            border-color: var(--success);
        }
    </style>
</head>

<body>
    <!-- Header với navigation -->
    <header>
        <div class="container header-content">
            <a href="AIBuddy_Homepage.php" class="logo">
                <span class="logo-icon">🤖</span>
                AI Buddy
            </a>
            <nav>
                <a href="AIBuddy_Homepage.php">Home</a>
                <a href="AIBuddy_Chatbot.php">Chatbot</a>
                <a href="AIBuddy_EmotionTracker.php">Emotion Tracker</a>
                <a href="AIBuddy_Trial.php">Trial</a>
                <a href="AIBuddy_Profile.php">Profile</a>
                <a href="AIBuddy_About.php">About</a>
                <a href="AIBuddy_Contact.php">Contact</a>
            </nav>
            <?php if (isset($_SESSION['user_name'])): ?>
                <a href="AIBuddy_Profile.php" class="user-account">
                    <i class="fa-regular fa-user"></i>
                    <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                </a>
            <?php else: ?>
                <a href="AIBuddy_SignIn.php">
                    <button class="signin-btn">Sign In</button>
                </a>
            <?php endif; ?>

        </div>
    </header>

    <!-- Change Password Section -->
    <section class="change-section">
        <div class="container change-container">
            <div class="change-card">
                <div class="change-header">
                    <div class="change-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h1>Change Password</h1>
                    <p>Enter your email and old password to set a new password</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert-message alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert-message alert-success">
                        <?php echo htmlspecialchars($success); ?>
                        <br><small>Redirecting to login page in <span id="countdown">5</span> seconds...</small>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="changeForm">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                placeholder="you@example.com"
                                required
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="old_password">Old Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input
                                type="password"
                                id="old_password"
                                name="old_password"
                                class="form-input"
                                placeholder="Enter your old password"
                                required
                            >
                            <button type="button" class="password-toggle" id="toggleOldPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password">New Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="form-input"
                                placeholder="Enter your new password"
                                required
                            >
                            <button type="button" class="password-toggle" id="toggleNewPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <span>Strength:</span>
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <span id="strengthText">None</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-input"
                                placeholder="Confirm your new password"
                                required
                            >
                            <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span id="passwordMatch" class="field-error"></span>
                    </div>

                    <button type="submit" class="change-form-btn" id="submitBtn">
                        <i class="fas fa-sync-alt"></i> Change Password
                    </button>
                </form>

                <div class="back-link">
                    Remember your password?
                    <a href="AIBuddy_SignIn.php">Back to Sign In</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer với Legal Section -->
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
                        <li><a href="AIBuddy_Terms of Service.html">Terms of Service</a></li>
                        <li><a href="AIBuddy_Privacy Policy.html">Privacy Policy</a></li>
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

    <script>
        // Toggle hiển thị mật khẩu
        const toggleOldPassword = document.getElementById('toggleOldPassword');
        const toggleNewPassword = document.getElementById('toggleNewPassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const oldPasswordInput = document.getElementById('old_password');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        function setupPasswordToggle(toggleBtn, inputField) {
            if (toggleBtn && inputField) {
                toggleBtn.addEventListener('click', function() {
                    const type = inputField.getAttribute('type') === 'password' ? 'text' : 'password';
                    inputField.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }
        }
        
        setupPasswordToggle(toggleOldPassword, oldPasswordInput);
        setupPasswordToggle(toggleNewPassword, newPasswordInput);
        setupPasswordToggle(toggleConfirmPassword, confirmPasswordInput);

        // Password Strength Indicator
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        
        function checkPasswordStrength(password) {
            let strength = 0;
            
            // Length check
            if (password.length >= 6) strength += 25;
            if (password.length >= 8) strength += 10;
            if (password.length >= 12) strength += 10;
            
            // Character variety checks
            if (/[a-z]/.test(password)) strength += 15;
            if (/[A-Z]/.test(password)) strength += 15;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 10;
            
            // Update UI
            strengthFill.style.width = Math.min(strength, 100) + '%';
            
            // Set color and text based on strength
            if (strength < 30) {
                strengthFill.style.backgroundColor = '#F44336';
                strengthText.textContent = 'Weak';
                strengthText.style.color = '#F44336';
            } else if (strength < 60) {
                strengthFill.style.backgroundColor = '#FF9800';
                strengthText.textContent = 'Fair';
                strengthText.style.color = '#FF9800';
            } else if (strength < 80) {
                strengthFill.style.backgroundColor = '#2196F3';
                strengthText.textContent = 'Good';
                strengthText.style.color = '#2196F3';
            } else {
                strengthFill.style.backgroundColor = '#4CAF50';
                strengthText.textContent = 'Strong';
                strengthText.style.color = '#4CAF50';
            }
        }
        
        newPasswordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            validatePasswordMatch();
            checkSamePassword();
        });

        // Password Match Validation
        function validatePasswordMatch() {
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const matchElement = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchElement.textContent = '';
                confirmPasswordInput.classList.remove('invalid', 'valid');
                return false;
            }
            
            if (password === confirmPassword) {
                matchElement.textContent = '✓ Passwords match';
                matchElement.style.color = '#4CAF50';
                confirmPasswordInput.classList.remove('invalid');
                confirmPasswordInput.classList.add('valid');
                return true;
            } else {
                matchElement.textContent = '✗ Passwords do not match';
                matchElement.style.color = '#F44336';
                confirmPasswordInput.classList.remove('valid');
                confirmPasswordInput.classList.add('invalid');
                return false;
            }
        }
        
        confirmPasswordInput.addEventListener('input', validatePasswordMatch);

        // Check if new password is same as old password
        function checkSamePassword() {
            const oldPassword = oldPasswordInput.value;
            const newPassword = newPasswordInput.value;
            
            if (oldPassword && newPassword && oldPassword === newPassword) {
                newPasswordInput.classList.add('invalid');
                // Show error message
                let errorElement = newPasswordInput.parentNode.querySelector('.same-password-error');
                if (!errorElement) {
                    errorElement = document.createElement('div');
                    errorElement.className = 'field-error same-password-error';
                    errorElement.textContent = 'New password cannot be the same as old password';
                    errorElement.style.color = '#F44336';
                    errorElement.style.marginTop = '5px';
                    newPasswordInput.parentNode.appendChild(errorElement);
                }
                return true;
            } else {
                newPasswordInput.classList.remove('invalid');
                // Remove error message if exists
                const errorElement = newPasswordInput.parentNode.querySelector('.same-password-error');
                if (errorElement) {
                    errorElement.remove();
                }
                return false;
            }
        }
        
        oldPasswordInput.addEventListener('input', checkSamePassword);
        newPasswordInput.addEventListener('input', checkSamePassword);

        // Form Validation
        const changeForm = document.getElementById('changeForm');
        const submitBtn = document.getElementById('submitBtn');
        
        function validateForm() {
            let isValid = true;
            
            // Check required fields
            const requiredFields = changeForm.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('invalid');
                    isValid = false;
                } else {
                    field.classList.remove('invalid');
                }
            });
            
            // Check password match
            if (!validatePasswordMatch()) {
                isValid = false;
            }
            
            // Check password length
            if (newPasswordInput.value.length < 6) {
                newPasswordInput.classList.add('invalid');
                isValid = false;
            }
            
            // Check if new password is same as old password
            if (checkSamePassword()) {
                isValid = false;
            }
            
            // Update button state
            submitBtn.disabled = !isValid;
            
            return isValid;
        }
        
        // Validate on input
        changeForm.querySelectorAll('input').forEach(field => {
            field.addEventListener('input', validateForm);
        });
        
        // Initial validation
        validateForm();

        // Form submission
        changeForm.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                showAlert('Please fill in all fields correctly', 'error');
                return;
            }
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing Password...';
            submitBtn.disabled = true;
        });

        // Countdown for redirect after success
        <?php if ($success): ?>
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        const countdownInterval = setInterval(() => {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                window.location.href = 'AIBuddy_SignIn.php';
            }
        }, 1000);
        <?php endif; ?>

        // Show alert function
        function showAlert(message, type) {
            const alertEl = document.createElement('div');
            alertEl.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 1000;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                animation: slideIn 0.3s ease-out;
            `;
            
            if (type === 'success') {
                alertEl.style.backgroundColor = '#4CAF50';
            } else if (type === 'error') {
                alertEl.style.backgroundColor = '#F44336';
            } else {
                alertEl.style.backgroundColor = 'var(--primary)';
            }
            
            alertEl.textContent = message;
            document.body.appendChild(alertEl);
            
            // Add animation CSS
            if (!document.querySelector('#alertAnimation')) {
                const style = document.createElement('style');
                style.id = 'alertAnimation';
                style.textContent = `
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                    @keyframes slideOut {
                        from { transform: translateX(0); opacity: 1; }
                        to { transform: translateX(100%); opacity: 0; }
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                alertEl.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => {
                    if (alertEl.parentNode) {
                        alertEl.parentNode.removeChild(alertEl);
                    }
                }, 300);
            }, 3000);
        }

        // Responsive navigation
        function adjustNavigation() {
            const nav = document.querySelector('nav');
            if (window.innerWidth <= 768) {
                if (nav) {
                    nav.style.display = 'flex';
                    nav.style.flexWrap = 'wrap';
                    nav.style.justifyContent = 'center';
                }
            }
        }
        
        window.addEventListener('load', adjustNavigation);
        window.addEventListener('resize', adjustNavigation);
    </script>
</body>
</html>
