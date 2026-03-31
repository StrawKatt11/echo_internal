<?php 
require '../database/db.php'; 
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>ECHO RUNNER - Story</title>
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
      <div class="col-md-10 p-4">
        
        <h3>ECHO RUNNER - 2177</h3>
        <hr>

        <div style="float:left; margin-right:20px; margin-bottom:10px;">
            <img class="story-img" src="../img/story/Echo.png" alt="Echo" 
                 style="width:160px; height:auto; border-radius:8px; border:3px solid #00ffff; box-shadow:0 0 15px #00ffff;">
        </div>

        <h5>The events unfold in the year 2177, in an era where humanity has already partially<strong>merged with machines</strong>.</h5>
        <h5>Beneath the towering, glowing skyscrapers, however, dark secrets and shadowy intrigues lurk, questioning the very nature of power and technology.</h5>

        <div style="clear:both;"></div>
        <br>

        <div style="float:right; width:40%; max-width:240px; margin-left:20px; margin-bottom:15px;">
            <img class="story-img" src="../img/story/Echomap.png" alt="Echo Map" 
                 style="width:100%; border-radius:8px; border:3px solid #00ffff; box-shadow:0 0 15px #00ffff;">
            <p class="text-muted text-center mt-2" style="font-size:14px;">
                <em>The neon-soaked city of 2177</em>
            </p>
        </div>

        <h5> The protagonist, <strong>Echo</strong>, is no ordinary ghost: they are a <em>digital imprint</em> of a human consciousness, trapped between the physical and virtual worlds after a tragic experiment.</h5>
        <h5>As Echo wanders through the corrupted digital landscape, fragments of their past slowly begin to resurface—memories, emotions, fears.</h5>

        <div style="clear:both;"></div>
        <br>

        <div style="float:left; width:40%; max-width:220px; margin-right:20px; margin-bottom:15px;">
            <img class="story-img" src="../img/story/Echomap2.png" alt="Echo Map" 
                 style="width:100%; border-radius:8px; border:3px solid #00ffff; box-shadow:0 0 15px #00ffff;">
            <p class="text-muted text-center mt-2" style="font-size:14px;">
                <em>The forgotten undercity where Echo searches for answers</em>
            </p>
        </div>

        <h5>On their journey, Echo must rediscover their identity and unveil the truth hidden deep within <strong>Helix Corp</strong>—the corporation responsible for their fractured existence.
        </h5>
        <h5>To survive, Echo must confront corrupted AI, collapsing memory sectors, and the truth of their own creation—before their remaining consciousness fades forever.</h5>

        <div style="clear:both;"></div>
      </div>
    </main>
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
