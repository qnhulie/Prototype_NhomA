<?php
session_start();
require_once 'db.php';

$successMsg = null;
$errorMsg = null;

/* BẮT BUỘC PHẢI ĐĂNG NHẬP */
if (!isset($_SESSION['userid'])) {
    header("Location: AIBuddy_SignIn.php");
    exit;
}

$userID = $_SESSION['userid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $topic   = trim($_POST['topic'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($topic === '' || $content === '') {
        $errorMsg = "Please fill in all required fields.";
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO form
            (UserID, AdminID, FormTopic, FormContent, FormStatus, FormCreationTime)
            VALUES (?, NULL, ?, ?, 'Pending', NOW())
        ");

        $stmt->execute([$userID, $topic, $content]);

        $successMsg = "Your request has been sent to Customer Support.";
    }
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - AI Buddy</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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


    /* Page Hero */
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

    /* Contact Section */
    .contact-section {
      padding: 80px 0;
    }

    .contact-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
    }

    .contact-info {
      background-color: var(--white);
      border-radius: 10px;
      padding: 40px;
      box-shadow: var(--card-shadow);
    }

    .contact-info h2 {
      font-size: 2rem;
      margin-bottom: 20px;
      color: var(--primary);
    }

    .contact-info p {
      margin-bottom: 30px;
      color: var(--text);
    }

    .contact-details {
      margin-bottom: 40px;
    }

    .contact-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 25px;
    }

    .contact-icon {
      width: 50px;
      height: 50px;
      background-color: var(--light);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      color: var(--primary);
      font-size: 20px;
      flex-shrink: 0;
    }

    .contact-text h3 {
      font-size: 1.2rem;
      margin-bottom: 5px;
      color: var(--primary);
    }

    .contact-text p {
      margin-bottom: 0;
      color: var(--text);
    }

    .social-links {
      display: flex;
      gap: 15px;
    }

    .social-links a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background-color: var(--primary);
      border-radius: 50%;
      color: var(--white);
      text-decoration: none;
      transition: background-color 0.3s;
    }

    .social-links a:hover {
      background-color: var(--accent);
    }

    /* Contact Form */
    .contact-form {
      background-color: var(--white);
      border-radius: 10px;
      padding: 40px;
      box-shadow: var(--card-shadow);
    }

    .contact-form h2 {
      font-size: 2rem;
      margin-bottom: 20px;
      color: var(--primary);
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--primary);
    }

    .form-control {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid var(--gray);
      border-radius: 5px;
      font-size: 1rem;
      transition: border-color 0.3s;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--accent);
    }

    textarea.form-control {
      min-height: 150px;
      resize: vertical;
    }

    .btn-primary {
      background-color: var(--accent);
      color: var(--white);
      padding: 12px 30px;
      border: none;
      border-radius: 5px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-primary:hover {
      background-color: #2ab4d1;
      transform: translateY(-2px);
    }

    /* FAQ Section */
    .faq {
      padding: 80px 0;
      background-color: var(--white);
    }

    .section-title {
      text-align: center;
      margin-bottom: 50px;
    }

    .section-title h2 {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 15px;
    }

    .section-title p {
      color: var(--text);
      max-width: 700px;
      margin: 0 auto;
    }

    .faq-container {
      max-width: 800px;
      margin: 0 auto;
    }

    .faq-item {
      margin-bottom: 15px;
      border: 1px solid var(--gray);
      border-radius: 5px;
      overflow: hidden;
    }

    .faq-question {
      padding: 20px;
      background-color: var(--light);
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: 600;
      color: var(--primary);
    }

    .faq-answer {
      padding: 0 20px;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease, padding 0.3s ease;
      color: var(--text);
    }

    .faq-item.active .faq-answer {
      padding: 20px;
      max-height: 500px;
    }

    /* Map Section */
    .map-section {
      padding: 0 0 80px;
    }

    .map-container {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: var(--card-shadow);
      height: 400px;
    }

    .map-placeholder {
      width: 100%;
      height: 100%;
      background-color: var(--light);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      font-size: 1.2rem;
    }

    /* Footer */
    footer {
      background-color: var(--primary-dark);
      color: var(--white);
      padding: 60px 0 20px;
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
            <?php if (!empty($_SESSION['user_name'])): ?>
  <a href="AIBuddy_Profile.php" class="user-account">
    <i class="fa-regular fa-user"></i>
    <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
  </a>
<?php endif; ?>
        </div>
    </header>

  <!-- Page Hero -->
  <section class="page-hero">
    <div class="container">
      <h1>Contact Us</h1>
      <ul class="breadcrumb">
        <li><a href="AIBuddy_Homepage.php">Home</a></li>
        <li class="current">Contact</li>
      </ul>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="contact-section">
    <div class="container contact-container">
      <div class="contact-info">
        <h2>Get in Touch</h2>
        <p>We're always here to listen and support you. Don't hesitate to reach out to us through any of the methods
          below.</p>

        <div class="contact-details">
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="contact-text">
              <h3>Address</h3>
              <p>123 Wellness Street, Mindful District, CA 90210</p>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fas fa-phone"></i>
            </div>
            <div class="contact-text">
              <h3>Phone</h3>
              <p>+1 (555) 123-4567</p>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fas fa-envelope"></i>
            </div>
            <div class="contact-text">
              <h3>Email</h3>
              <p>support@aibuddy.com</p>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fas fa-clock"></i>
            </div>
            <div class="contact-text">
              <h3>Working Hours</h3>
              <p>Monday - Friday: 8:00 AM - 8:00 PM<br>Saturday: 9:00 AM - 5:00 PM</p>
            </div>
          </div>
        </div>

        <h3>Follow Us</h3>
        <div class="social-links">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>

      <div class="contact-form">
  <h2>Customer Support Form</h2>

  <?php if (!empty($successMsg)): ?>
    <p style="color: green; font-weight: 600; margin-bottom: 15px;">
      <?= htmlspecialchars($successMsg) ?>
    </p>
  <?php endif; ?>

<?php if (!empty($errorMsg)): ?>
  <p style="color: red; font-weight: 600; margin-bottom: 15px;">
    <?= htmlspecialchars($errorMsg) ?>
  </p>
<?php endif; ?>


  <form method="POST">

  <div class="form-group">
    <label>Subject *</label>
    <select name="topic" class="form-control" required>
      <option value="General Inquiry">General Inquiry</option>
      <option value="Technical Support">Technical Support</option>
      <option value="Feedback">Feedback</option>
      <option value="Other">Other</option>
    </select>
  </div>

  <div class="form-group">
    <label>Message *</label>
    <textarea name="content" class="form-control" required></textarea>
  </div>

  <button type="submit" class="btn-primary">Send Message</button>
</form>
      </div>
    </div>
  </section>

  <!-- FAQ Section -->
  <section class="faq">
    <div class="container">
      <div class="section-title">
        <h2>Frequently Asked Questions</h2>
        <p>Find answers to common questions about our services</p>
      </div>

       <div class="faq-item">
      <div class="faq-question">
        How quickly can I expect a response?
        <i class="fas fa-chevron-down"></i>
      </div>
        <div class="faq-answer">
          <p>
            Our customer support team typically responds within <strong>24 hours</strong>
            on business days. During peak periods, responses may take slightly longer,
            but we always strive to assist you as quickly as possible.
          </p>
        </div>
      </div>

          <div class="faq-item">
        <div class="faq-question">
          <span>Is AI Buddy suitable for crisis situations?</span>
          <i class="fas fa-chevron-down"></i>
        </div>
        <div class="faq-answer">
          <p>
            AI Buddy is designed to provide emotional support and self-reflection tools.
            However, it is <strong>not a replacement for professional medical or emergency care</strong>.
            If you are in immediate danger, please contact local emergency services or a licensed professional.
          </p>
        </div>
      </div>

      <div class="faq-item">
        <div class="faq-question">
          <span>Can I use AI Buddy without creating an account?</span>
          <i class="fas fa-chevron-down"></i>
        </div>
        <div class="faq-answer">
          <p>
            Yes, you can explore some basic features without an account.
            However, creating an account allows you to access personalized features
            such as emotion tracking, session history, and subscription benefits.
          </p>
        </div>
      </div>

      <div class="faq-item">
        <div class="faq-question">
          <span>Is my data and privacy protected?</span>
          <i class="fas fa-chevron-down"></i>
        </div>
        <div class="faq-answer">
          <p>
            Absolutely. We take privacy seriously.
            Your personal data is securely stored and processed in accordance with our
            privacy policy. We never share your information with third parties without consent.
          </p>
        </div>
      </div>

    </div>
  </div>
</section>

  <!-- Map Section -->
  <section class="map-section">
    <div class="container">
      <div class="map-container">
        <div class="map-placeholder">
          <i class="fas fa-map-marked-alt"></i> AI Buddy Headquarters Location
        </div>
      </div>
    </div>
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


  <script>
  document.querySelectorAll('.faq-question').forEach(question => {
    question.addEventListener('click', () => {
      const faqItem = question.parentElement;
      faqItem.classList.toggle('active');
    });
  });
</script>

</body>

</html>
