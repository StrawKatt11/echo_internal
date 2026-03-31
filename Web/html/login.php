<?php 
require '../database/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: gameinfo.php');
    exit;
}

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
require_once 'mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

$message = '';
$step = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $message = 'Enter username and password!';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, email, profile_pic FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $code = sprintf("%06d", mt_rand(0, 999999));
            $expires = time() + 600;

            try {
                $mail = createMailer();
                $mail->addAddress($user['email']);
                $mail->isHTML(true);
                $mail->Subject = 'ECHO RUNNER - Login Code';
                $mail->Body = "<h2 style='color:#00ffff;'>Login code:</h2>
                               <h1 style='font-size:3.5rem;letter-spacing:15px;color:#00ffff;'>$code</h1>
                               <p>Valid 10 minutes.</p>";

                $mail->send();

                $_SESSION['login_temp'] = [
                    'user_id'     => $user['id'],
                    'username'    => $user['username'],
                    'profile_pic' => $user['profile_pic'] ?? 'default.png',
                    'code'        => $code,
                    'expires'     => $expires
                ];

                $step = 2;
                $message = "Code sent to <strong>email</strong>! Check your inbox (and spam folder)!";
            } catch (Exception $e) {
                $message = "Mail error: {$mail->ErrorInfo}";
            }
        } else {
            $message = 'Wrong username or password!';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_code'])) {
    $input_code = trim($_POST['verification_code']);

    if (!isset($_SESSION['login_temp']) || time() > $_SESSION['login_temp']['expires']) {
        $message = 'Code expired!';
        unset($_SESSION['login_temp']);
        $step = 1;
    } elseif ($input_code !== $_SESSION['login_temp']['code']) {
        $message = 'Wrong code!';
    } else {
        $_SESSION['user_id']     = $_SESSION['login_temp']['user_id'];
        $_SESSION['username']    = $_SESSION['login_temp']['username'];
        $_SESSION['profile_pic'] = $_SESSION['login_temp']['profile_pic'];
        unset($_SESSION['login_temp']);
        header('Location: gameinfo.php');
        exit;
    }
}

if (isset($_POST['start_reset'])) {
    $username = trim($_POST['username']);
    if (empty($username)) {
        $message = 'Enter username!';
        $step = 5;
    } else {
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            $message = 'Username not found!';
            $step = 5;
        } else {
            $code = sprintf("%06d", mt_rand(0, 999999));
            $expires = time() + 600;

            try {
                $mail = createMailer();
                $mail->addAddress($user['email']);
                $mail->isHTML(true);
                $mail->Subject = 'ECHO RUNNER - Password Reset Code';
                $mail->Body = "<h2 style='color:#00ffff;'>Reset code:</h2>
                               <h1 style='font-size:3.5rem;letter-spacing:15px;color:#00ffff;'>$code</h1>
                               <p>Valid 10 minutes.</p>";
                $mail->send();

                $_SESSION['reset_temp'] = [
                    'user_id' => $user['id'],
                    'code'    => $code,
                    'expires' => $expires
                ];

                $step = 3;
                $message = "Reset code sent!";
            } catch (Exception $e) {
                $message = "Mail error: {$mail->ErrorInfo}";
            }
        }
    }
}

if (isset($_POST['verify_reset_code'])) {
    $input_code = trim($_POST['verification_code']);

    if (!isset($_SESSION['reset_temp']) || time() > $_SESSION['reset_temp']['expires']) {
        $message = 'Code expired!';
        unset($_SESSION['reset_temp']);
        $step = 1;
    } elseif ($input_code !== $_SESSION['reset_temp']['code']) {
        $message = 'Wrong code!';
    } else {
        $step = 4;
        $message = '';
    }
}

if (isset($_POST['change_password'])) {
    $p1 = $_POST['new_password'];
    $p2 = $_POST['confirm_password'];

    if ($p1 !== $p2) {
        $message = 'Passwords do not match!';
    } elseif (strlen($p1) < 6) {
        $message = 'Password must be at least 6 characters!';
    } else {
        $hash = password_hash($p1, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $_SESSION['reset_temp']['user_id']]);

        unset($_SESSION['reset_temp']);
        $message = 'Password changed! You can now log in.';
        $step = 1;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ECHO RUNNER - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <script src="../javascript/main_js.js"></script>
</head>
<body class="login-register-page">

<div class="login-register-box">
  <h2 class="flicker">Login</h2>

  <?php if ($message): ?>
    <div class="alert alert-<?= strpos($message,'sent')!==false || strpos($message,'changed')!==false ? 'success' : 'danger' ?> text-center mt-3">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <?php if ($step === 1 || $step === 5): ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Username" required maxlength="30" autocomplete="username" class="mb-3" autofocus>
      <?php if ($step === 1): ?>
        <input type="password" name="password" placeholder="Password" required autocomplete="current-password" class="mb-4">
        <input type="hidden" name="login" value="1">
        <button type="submit" class="btn btn-login w-100">Send Code</button>
      <?php else: ?>
        <input type="hidden" name="start_reset" value="1">
        <button type="submit" class="btn btn-login w-100">Send Reset Code</button>
      <?php endif; ?>
    </form>

    <?php if ($step === 1): ?>
      <div class="text-center mt-3">
        <a href="forgot_password.php" class="btn btn-link text-cyan p-0">Forgot password?</a>
      </div>
    <?php endif; ?> 
  <?php endif; ?>

  <?php if ($step === 2): ?>
    <form method="POST">
      <p class="text-cyan text-center mb-4">Enter 6-digit login code:</p>
      <input type="text" name="verification_code" class="code-input form-control" maxlength="6" required inputmode="numeric" autofocus>
      <input type="hidden" name="login_code" value="1">
      <button type="submit" class="btn btn-play w-100 mt-4">Login</button>
    </form>
  <?php endif; ?>

  <?php if ($step === 3): ?>
    <form method="POST">
      <p class="text-cyan text-center mb-4">Enter 6-digit reset code:</p>
      <input type="text" name="verification_code" class="code-input form-control" maxlength="6" required inputmode="numeric" autofocus>
      <input type="hidden" name="verify_reset_code" value="1">
      <button type="submit" class="btn btn-play w-100 mt-4">Verify</button>
    </form>
  <?php endif; ?>

  <?php if ($step === 4): ?>
    <form method="POST">
      <input type="password" name="new_password" placeholder="New password" required minlength="6" class="form-control mb-3" autofocus>
      <input type="password" name="confirm_password" placeholder="Confirm password" required class="form-control mb-4">
      <input type="hidden" name="change_password" value="1">
      <button type="submit" class="btn btn-login w-100">Change Password</button>
    </form>
  <?php endif; ?>

  <p class="mt-4 text-center">
    Don't have an account? <a href="register.php">Sign up</a>
  </p>
  <p class="text-center"><a href="gameinfo.php">← Back</a></p>
</div>

<div id="echoCursor"></div>
</body>
</html>