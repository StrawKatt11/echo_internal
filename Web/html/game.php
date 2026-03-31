<?php
session_start();

// Ha szeretnéd, hogy CSAK bejelentkezettek játszhassanak:
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=game.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>ECHO RUNNER - Play</title>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body, html {
      height: 100%;
      background: #000;
      overflow: hidden;
      font-family: 'Courier New', Courier, monospace;
    }
    .game-wrapper {
      position: relative;
      width: 100vw;
      height: 100vh;
    }
    iframe {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      border: none;
    }

    /* Középre igazított fejléc */
    .game-header {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: rgba(0, 0, 0, 0.65);
      color: #00ffff;
      z-index: 100;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 40px;                    /* a két elem közötti távolság */
      font-size: 1.3rem;
      text-shadow: 0 0 10px #00ffff80;
      pointer-events: none;
    }

    .game-title {
      font-size: 1.6rem;
      letter-spacing: 3px;
      text-transform: uppercase;
    }

    .btn-exit {
      pointer-events: auto;
      background: rgba(255, 0, 68, 0.8);
      color: white;
      border: 2px solid #ff0044;
      padding: 8px 24px;
      border-radius: 6px;
      font-weight: bold;
      text-decoration: none;
      font-size: 1.1rem;
      transition: all 0.2s;
    }

    .btn-exit:hover {
      background: #ff0044;
      box-shadow: 0 0 20px #ff004480;
      transform: translateY(-1px);
    }
  </style>
</head>
<body>

<div class="game-wrapper">
  <div class="game-header">
    <h1 class="game-title cyberpunk-glitch">ECHO RUNNER</h1>
    <a href="download.php" class="btn-exit">EXIT GAME</a>
  </div>

  <iframe 
    src="https://itch.io/embed-upload/16434223?color=252525" 
    frameborder="0" 
    allowfullscreen>
  </iframe>
</div>

</body>
</html>