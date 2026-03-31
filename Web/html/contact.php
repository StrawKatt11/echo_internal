<?php
session_start();
require '../database/db.php';

// PHPMailer requires BEFORE use statement
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
require_once 'mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

$status = '';
$status_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_contact'])) {
    $sender_name  = trim($_POST['name']    ?? '');
    $sender_email = trim($_POST['email']   ?? '');
    $msg_body     = trim($_POST['message'] ?? '');

    if (empty($sender_name) || empty($sender_email) || empty($msg_body)) {
        $status = 'Please fill in all fields!';
        $status_type = 'danger';
    } elseif (!filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
        $status = 'Invalid email address!';
        $status_type = 'danger';
    } else {
        try {
            $mail = createMailer();
            $mail->addAddress(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addReplyTo($sender_email, $sender_name);
            $mail->isHTML(true);
            $mail->Subject = 'ECHO RUNNER Contact: ' . htmlspecialchars($sender_name);
            $mail->Body    = "
                <h2 style='color:#00ffff;'>New contact message</h2>
                <p><strong>Name:</strong> " . htmlspecialchars($sender_name) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($sender_email) . "</p>
                <hr>
                <p>" . nl2br(htmlspecialchars($msg_body)) . "</p>
            ";
            $mail->AltBody = "Name: $sender_name\nEmail: $sender_email\n\n$msg_body";
            $mail->send();
            $status = 'Message sent! We will get back to you soon.';
            $status_type = 'success';
        } catch (MailException $e) {
            $status = 'Could not send message. Error: ' . $mail->ErrorInfo;
            $status_type = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Contact - Echo Runner</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="../css/style.css"/>
<script src="../javascript/main_js.js"></script>
</head>
<body>

<header class="py-3">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-12 d-flex flex-column position-relative">

        <div class="logo text-center mb-4">
          <h3 class="cyberpunk-glitch mb-0" data-text="ECHO RUNNER" style="font-size: 4rem; line-height: 1;">
            ECHO RUNNER
          </h3>
          <p style="color:#00ffff; letter-spacing: 12px; font-size: 1.4rem; text-shadow: 0 0 20px #00ffff; margin-top: -5px;">
            2177
          </p>
        </div>

        <div class="d-flex align-items-center justify-content-between w-100 px-3">

          <div class="menu-left">
            <button class="hamburger-btn" type="button" data-bs-toggle="offcanvas" 
                    data-bs-target="#echoMainMenu" aria-controls="echoMainMenu">
              <span></span><span></span><span></span>
            </button>
          </div>

          <div class="d-flex align-items-center gap-3 flex-wrap justify-content-end">
            <?php if (isset($_SESSION['user_id'])): ?>
              <a href="download.php" class="btn btn-play btn-sm fw-bold px-4">PLAY NOW</a>
              <form action="logout.php" method="post" class="d-inline">
                <button class="btn btn-logout btn-sm fw-bold px-4">Logout</button>
              </form>
              <span class="text-cyan fw-bold fs-5">HI, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
              <a href="profile.php" class="avatar-circle">
                <img src="<?php
                    $pic = $_SESSION['profile_pic'] ?? 'default.png';
                    if ($pic !== 'default.png' && file_exists("../img/avatars/presets/$pic")) echo "../img/avatars/presets/$pic";
                    elseif (file_exists("../img/avatars/$pic")) echo "../img/avatars/$pic";
                    else echo '../img/avatars/default.png';
                ?>?v=<?= time() ?>" alt="Avatar">
              </a>
            <?php else: ?>
              <a href="login.php" class="btn btn-login btn-sm">Login</a>
              <a href="register.php" class="btn btn-register btn-sm">Register</a>
              <a href="download.php" class="btn btn-play btn-sm fw-bold px-4">PLAY NOW</a>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</header>

<div class="offcanvas offcanvas-start" tabindex="-1" id="echoMainMenu" aria-labelledby="echoMainMenuLabel">
  <div class="offcanvas-header border-bottom border-cyan">
    <h3 class="offcanvas-title cyberpunk-glitch text-cyan" id="echoMainMenuLabel" data-text="MENU">MENU</h3>
  </div>
  <div class="offcanvas-body pt-5">
    <ul class="list-unstyled menu-list">
      <li><a href="gameinfo.php">GAME INFO</a></li>
      <li><a href="story.php">STORY</a></li>
      <li><a href="update.php">UPDATE</a></li>
      <li><a href="forum.php">FORUM</a></li>
      <li><a href="leader.php">LEADERBOARD</a></li>
      <li><a href="contact.php">CONTACT</a></li>
      <li><a href="quiz.php">QUIZ</a></li>
    </ul>
  </div>
</div>


<div class="container my-5">
  <div class="d-flex justify-content-center">
    <main class="row justify-content-center">
      <div class="col-md-8">
        <div class="position-relative p-5">
          <h2 class="text-center text-cyan flicker mb-5">CONTACT ECHO RUNNER TEAM</h2>
          <hr>
          <form id="contactForm" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="send_contact" value="1">
            <div class="mb-4">
              <label for="name" class="form-label text-cyan">Your Name</label>
              <input type="text" class="form-control form-control-lg" id="name" name="name"
                     value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>

            <div class="mb-4">
              <label for="email" class="form-label text-cyan">Your Email</label>
              <input type="email" class="form-control form-control-lg" id="email" name="email"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="mb-4">
              <label for="message" class="form-label text-cyan">Message</label>
              <textarea class="form-control form-control-lg" id="message" name="message"
                        rows="7" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>

            <div class="text-center">
              <button type="submit" class="btn btn-play btn-lg px-5 fw-bold">SEND MESSAGE</button>
            </div>
          </form>

          <?php if ($status): ?>
            <div class="alert alert-<?= $status_type ?> mt-4 text-center fs-5">
              <?= htmlspecialchars($status) ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>
</div>

<div id="echoCursor"></div>
</body>
</html>