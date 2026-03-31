<?php
require '../database/db.php';
session_start();

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
require_once 'mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

$message = '';
$step = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_code'])) {
    $username = trim($_POST['username'] ?? '');
    if (empty($username)) {
        $message = 'Please enter your username!';
    } else {
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $message = 'Username not found!';
        } else {
            $code = sprintf("%06d", mt_rand(0, 999999));
            $expires = time() + 600;

            try {
                $mail = createMailer();
                $mail->addAddress($user['email']);
                $mail->isHTML(true);
                $mail->Subject = 'ECHO RUNNER - Password Reset';
                $mail->Body    = "<h2 style='color:#00ffff;'>Your reset code:</h2>
                                 <h1 style='font-size:4rem;letter-spacing:20px;color:#00ffff;'>$code</h1>
                                 <p>Valid for 10 minutes</p>";

                $mail->send();

                $_SESSION['reset_temp'] = [
                    'user_id' => $user['id'],
                    'code'    => $code,
                    'expires' => $expires
                ];

                $step = 2;
                $message = "Code sent to your email! Check inbox (and spam folder)!";
            } catch (Exception $e) {
                $message = "Error sending email: " . $mail->ErrorInfo;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $code = trim($_POST['verification_code'] ?? '');

    if (!isset($_SESSION['reset_temp']) || time() > $_SESSION['reset_temp']['expires']) {
        $message = 'Code expired or invalid! Please start again.';
        unset($_SESSION['reset_temp']);
        $step = 1;
    } elseif ($code !== $_SESSION['reset_temp']['code']) {
        $message = 'Wrong code!';
    } else {
        $step = 3;
        $message = '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $p1 = $_POST['new_password'] ?? '';
    $p2 = $_POST['confirm_password'] ?? '';

    if ($p1 !== $p2) {
        $message = 'Passwords do not match!';
    } elseif (strlen($p1) < 6) {
        $message = 'Password must be at least 6 characters!';
    } else {
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['reset_temp']['user_id']]);
        $current_hash = $stmt->fetchColumn();

        if ($current_hash && password_verify($p1, $current_hash)) {
            $message = 'You cannot use the same password! Choose a new one.';
        } else {
            $hash = password_hash($p1, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $_SESSION['reset_temp']['user_id']]);

            unset($_SESSION['reset_temp']);
            $message = 'Password changed successfully! You can now log in.';
            $step = 4;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ECHO RUNNER - Forgot Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <script src="../javascript/main_js.js"></script>
</head>
<body class="login-register-page">
<div class="login-register-box">
  <h2 class="flicker">Forgot Password</h2>

  <?php if ($message): ?>
    <div class="alert alert-<?= (strpos($message, 'successfully') !== false || $step == 2) ? 'success' : 'danger' ?> text-center mt-3">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <?php if ($step === 1): ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required class="form-control mb-4" autofocus>
      <input type="hidden" name="send_code" value="1">
      <button type="submit" class="btn btn-login w-100">Send Code</button>
    </form>
  <?php endif; ?>

  <?php if ($step === 2): ?>
    <form method="POST">
      <p class="text-cyan text-center mb-4">Enter the 6-digit code:</p>
      <input type="text" name="verification_code" class="code-input form-control" maxlength="6" required inputmode="numeric" pattern="\d{6}" autofocus>
      <input type="hidden" name="verify_code" value="1">
      <button type="submit" class="btn btn-play w-100 mt-4">Verify</button>
    </form>
  <?php endif; ?>

  <?php if ($step === 3): ?>
    <form method="POST">
      <input type="password" name="new_password" placeholder="New password" required minlength="6" class="form-control mb-3" autofocus>
      <input type="password" name="confirm_password" placeholder="Confirm password" required class="form-control mb-4">
      <input type="hidden" name="change_password" value="1">
      <button type="submit" class="btn btn-login w-100">Change Password</button>
    </form>
  <?php endif; ?>

  <?php if ($step === 4): ?>
    <div class="text-center">
      <p class="text-success fs-4">Done!</p>
      <a href="login.php" class="btn btn-login w-100">Log In</a>
    </div>
  <?php endif; ?>

  <p class="mt-4 text-center"><a href="login.php">← Back to login</a></p>
</div>

<div id="echoCursor"></div>
</body>
</html>