<?php 
require '../database/db.php'; 
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: gameinfo.php');
    exit;
}

$message = '';
$step = 1;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
require_once 'mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['verification_code'])) {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $message = 'All fields are required!';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@gmail.com')) {
        $message = 'Only Gmail addresses allowed! (@gmail.com)';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        if ($check->rowCount() > 0) {
            $message = 'Username or email already taken!';
        } else {
            $code = sprintf("%06d", mt_rand(0, 999999));
            $expires = time() + 600;

            try {
                $mail = createMailer();
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Your ECHO RUNNER Verification Code';
                $mail->Body    = "<h2 style='color:#00ffff;'>Your code is:</h2>
                                 <h1 style='font-size:3rem;letter-spacing:10px;color:#00ffff;'>$code</h1>
                                 <p>Valid for 10 minutes.</p>";
                $mail->AltBody = "Your code: $code (valid 10 min)";

                $mail->send();

                $_SESSION['reg_temp'] = [
                    'username' => $username,
                    'email'    => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'code'     => $code,
                    'expires'  => $expires
                ];

                $step = 2;
                $message = "Code sent to <strong>$email</strong>! Check your inbox (and spam folder)!";

            } catch (Exception $e) {
                $message = "Email could not be sent. Error: {$mail->ErrorInfo}";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verification_code'])) {
    $input_code = trim($_POST['verification_code'] ?? '');

    if (!isset($_SESSION['reg_temp']) || time() > $_SESSION['reg_temp']['expires']) {
        $message = 'Code expired or invalid. Try again.';
        unset($_SESSION['reg_temp']);
        $step = 1;
    } elseif ($input_code !== $_SESSION['reg_temp']['code']) {
        $message = 'Wrong code!';
    } else {
        $data = $_SESSION['reg_temp'];
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$data['username'], $data['email'], $data['password']]);
        $user_id = $pdo->lastInsertId();

        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $data['username'];
        $_SESSION['profile_pic'] = 'default.png';

        $pdo->prepare("INSERT INTO game_stats (user_id) VALUES (?)")->execute([$user_id]);

        unset($_SESSION['reg_temp']);
        header('Location: gameinfo.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ECHO RUNNER - Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <script src="../javascript/main_js.js"></script>

  <style>
    .code-input {
      font-size: 2.5rem;
      letter-spacing: 15px;
      text-align: center;
      font-weight: bold;
    }
  </style>
</head>

<body class="login-register-page">

<div class="login-register-box">
  <h2 class="flicker">Register</h2>

  <?php if ($message): ?>
    <div class="alert alert-<?= strpos($message, 'sent') !== false ? 'success' : 'danger' ?> text-center mt-3">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <?php if ($step === 1): ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required maxlength="30" autocomplete="username" class="mb-3" autofocus>
      <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email" class="mb-3">
      <input type="password" name="password" placeholder="Password (min. 6)" required minlength="6" autocomplete="new-password" class="mb-4">
      <button type="submit" class="btn btn-register w-100">Send Code</button>
    </form>

    <p class="mt-4 text-center">
      Already have an account? <a href="login.php">Login</a>
    </p>
  <?php endif; ?>

  <?php if ($step === 2): ?>
    <form method="POST">
      <p class="text-cyan text-center mb-4">Enter 6-digit code:</p>
      <input type="text" name="verification_code" class="code-input form-control" maxlength="6" required inputmode="numeric" autofocus>
      <button type="submit" class="btn btn-play w-100 mt-4">Complete Registration</button>
    </form>

    <p class="mt-4 text-center">
      Already have an account? <a href="login.php">Login</a>
    </p>
  <?php endif; ?>

  <p class="text-center"><a href="gameinfo.php">← Back</a></p>
</div>

<div id="echoCursor"></div>

</body>
</html>