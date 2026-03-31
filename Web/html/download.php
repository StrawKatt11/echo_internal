<?php
session_start();
require '../database/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>ECHO RUNNER - Download</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="../css/style.css"/>
  <script src="../javascript/main_js.js"></script>

  <style>
    .btn-play-neon {
      background: #0d1f1f;
      color: #00ffff;
      border: 2px solid #00ffff;
      border-radius: 8px;
      font-weight: bold;
      padding: 0.9rem 2.4rem;
      font-size: 1.3rem;
      text-transform: uppercase;
      letter-spacing: 2px;
      text-shadow: 0 0 10px #00ffff90;
      box-shadow: 
        0 0 18px #00ffff50,
        inset 0 0 14px #00ffff30;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .btn-play-neon:hover {
      background: #003838;
      color: #ffffff;
      text-shadow: 0 0 15px #ffffffcc;
      box-shadow: 
        0 0 35px #00ffffaa,
        inset 0 0 25px #00ffff60;
      transform: translateY(-3px);
    }

    .btn-play-neon::before {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(
        120deg,
        transparent 30%,
        rgba(0, 255, 255, 0.18) 50%,
        transparent 70%
      );
      transform: rotate(30deg);
      opacity: 0;
      transition: all 0.7s ease;
      z-index: -1;
    }

    .btn-play-neon:hover::before {
      opacity: 1;
      transform: rotate(30deg) translate(25%, 25%);
    }

    .btn-neon {
      background: #001f1f;
      color: #00ffff;
      border: 2px solid #00ffff55;
      transition: all 0.3s;
    }

    .btn-neon:hover {
      background: #003333;
      box-shadow: 0 0 25px #00ffff80;
    }

    .login-required {
      background: rgba(40, 20, 0, 0.5);
      border: 2px solid #ffaa0066;
      border-radius: 12px;
      padding: 2.2rem 3rem;
      min-width: 320px;
      max-width: 420px;
      box-shadow: 0 0 35px #ff880055;
      backdrop-filter: blur(4px);
    }

    .text-glow-warning {
      text-shadow: 0 0 12px #ffcc00, 0 0 24px #ff8800;
    }
  </style>
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
  <div class="row justify-content-center">

    <div class="col-12 col-lg-10">

      <div class="download-hero mb-5"
           style="background:rgba(0,0,0,0.7);box-shadow:0 0 30px #00ffff;">
        <h1 class="cyberpunk-glitch download-title mb-3" data-text="DOWNLOAD">DOWNLOAD</h1>
        <p class="version-text">v0.0.1 Beta · 33,9 MB · December 2025</p>

        <div class="d-flex flex-wrap gap-5 justify-content-center mt-5">

          <?php if (isset($_SESSION['user_id'])): ?>

            <a href="download-file.php" class="btn btn-neon px-5 py-3 fs-4">DOWNLOAD NOW</a>
            <a href="game.php" class="btn btn-play-neon px-5 py-3 fs-4">PLAY NOW</a>

          <?php else: ?>

            <!-- Bal oldal -->
            <div class="login-required text-center">
              <p class="text-warning fs-4 mb-4 fw-bold text-glow-warning">
                Login required to download
              </p>
              <a href="login.php" class="btn btn-neon px-5 py-3 fs-4">
                LOGIN TO DOWNLOAD
              </a>
            </div>

            <!-- Jobb oldal – ugyanaz -->
            <div class="login-required text-center">
              <p class="text-warning fs-4 mb-4 fw-bold text-glow-warning">
                Login required to download
              </p>
              <a href="login.php" class="btn btn-neon px-5 py-3 fs-4">
                LOGIN TO PLAY
              </a>
            </div>

          <?php endif; ?>

        </div>
      </div>

      <div class="row g-4 justify-content-center">

        <div class="col-12 col-md-6 col-lg-5">
          <div class="req-card text-center text-cyan p-4 rounded-4 border border-cyan"
               style="background:rgba(0,0,0,0.7);box-shadow:0 0 30px #00ffff;">
            <h4 class="mb-4 cyberpunk-glitch" data-text="MINIMUM">MINIMUM</h4>
            <ul class="list-unstyled text-start mx-auto" style="max-width:300px;">
              <li>Windows 10 64-bit</li>
              <li>i5-4460 / FX-6300</li>
              <li>8 GB RAM</li>
              <li>GTX 960 / RX 460</li>
              <li>800 MB storage</li>
            </ul>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-5">
          <div class="req-card text-center text-cyan p-4 rounded-4 border border-cyan"
               style="background:rgba(0,0,0,0.7);box-shadow:0 0 30px #00ffff;">
            <h4 class="mb-4 cyberpunk-glitch" data-text="RECOMMENDED">RECOMMENDED</h4>
            <ul class="list-unstyled text-start mx-auto" style="max-width:300px;">
              <li>Windows 11 64-bit</li>
              <li>i7-8700 / Ryzen 5 3600</li>
              <li>16 GB RAM</li>
              <li>RTX 2060 / RX 5700</li>
              <li>SSD recommended</li>
            </ul>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<div id="echoCursor"></div>
<div id="imageModal">
  <div class="modal-backdrop"></div>
  <div class="modal-img-wrapper">
    <img id="modalImage" src="" alt="">
  </div>
  <span id="modalClose">×</span>
</div>

</body>
</html>