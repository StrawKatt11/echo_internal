<?php require '../database/db.php'; session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ECHO RUNNER - Leaderboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="../css/style.css">
  <script src="../javascript/main_js.js"></script>
</head>
<body class="leaderboard-page">

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

<main class="pb-5 pt-4">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-8">

        <div class="card">
          <div class="card-header text-center border-0 bg-transparent pt-4">
            <h4 class="mb-2 text-cyan" style="font-size: 2.2rem; text-shadow: 0 0 15px #00ffff;">Leaderboard</h4>
            <small class="text-white opacity-75">Top 50 Players – minimum 5 minutes playtime</small>
          </div>

          <div class="card-body p-4">

            <?php if (isset($_SESSION['user_id'])): ?>
              <?php
                $my_stmt = $pdo->prepare("
                  SELECT u.id, u.username, u.profile_pic, s.playtime, s.deaths 
                  FROM game_stats s 
                  JOIN users u ON u.id = s.user_id 
                  WHERE s.user_id = ?
                ");
                $my_stmt->execute([$_SESSION['user_id']]);
                $me = $my_stmt->fetch(PDO::FETCH_ASSOC);

                if ($me) {
                  $m_min = floor($me['playtime'] / 60);
                  $m_sec = $me['playtime'] % 60;
                  $myTime = sprintf("%02d:%02d", $m_min, $m_sec);

                  $rank_stmt = $pdo->prepare("SELECT COUNT(*) + 1 FROM game_stats WHERE playtime < ? AND playtime >= 300");
                  $rank_stmt->execute([$me['playtime']]);
                  $my_rank = $me['playtime'] >= 300 ? $rank_stmt->fetchColumn() : 'Not qualified';

                  $my_avatar = '../img/avatars/default.png';
                  if ($me['profile_pic']) {
                    if (file_exists("../img/avatars/presets/{$me['profile_pic']}")) $my_avatar = "../img/avatars/presets/{$me['profile_pic']}";
                    elseif (file_exists("../img/avatars/{$me['profile_pic']}")) $my_avatar = "../img/avatars/{$me['profile_pic']}";
                  }
              ?>
              <div class="mb-5">
                <div class="leaderboard-row your-rank d-flex justify-content-between align-items-center p-4 rounded-3">
                  <div class="d-flex align-items-center gap-4">
                    <img src="<?= $my_avatar ?>?v=<?= time() ?>" class="rounded-circle" style="width:58px;height:58px;object-fit:cover;border:4px solid #00ffff;box-shadow:0 0 20px #00ffff;">
                    <div>
                      <strong class="text-cyan fs-3">YOU</strong><br>
                      <span class="text-white fs-4"><?= htmlspecialchars($me['username']) ?></span>
                    </div>
                  </div>
                  <div class="text-end">
                    <div class="text-cyan fs-2 fw-bold">
                      <?= $me['playtime'] >= 300 ? "#$my_rank" : "— (need 5:00)" ?>
                    </div>
                    <div class="mt-3">
                      <span class="badge bg-success fs-5 px-4 py-2"><?= $myTime ?></span>
                      <span class="badge bg-danger fs-5 px-4 py-2 ms-3"><?= $me['deaths'] ?> deaths</span>
                    </div>
                  </div>
                </div>
              </div>
              <?php } endif; ?>

            <div id="leaderboard-list">
              <?php
              $stmt = $pdo->query("
                SELECT u.id, u.username, u.profile_pic, s.playtime, s.deaths
                FROM game_stats s 
                JOIN users u ON s.user_id = u.id 
                WHERE s.playtime >= 300
                ORDER BY s.playtime ASC
                LIMIT 50
              ");

              $rank = 1;
              if ($stmt->rowCount() == 0) {
                echo '<div class="text-center py-5 text-muted fs-3">No players have reached 5 minutes yet!</div>';
              } else {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  $min = floor($row['playtime'] / 60);
                  $sec = $row['playtime'] % 60;
                  $timeFormatted = sprintf("%02d:%02d", $min, $sec);

                  $avatar = '../img/avatars/default.png';
                  if ($row['profile_pic']) {
                    if (file_exists("../img/avatars/presets/{$row['profile_pic']}")) $avatar = "../img/avatars/presets/{$row['profile_pic']}";
                    elseif (file_exists("../img/avatars/{$row['profile_pic']}")) $avatar = "../img/avatars/{$row['profile_pic']}";
                  }
              ?>
                  <div class="leaderboard-row d-flex justify-content-between align-items-center p-4 mb-3 rounded-3">
                    <div class="d-flex align-items-center gap-4">
                      <strong class="text-cyan fs-3" style="width:50px;"><?= $rank ?>.</strong>
                      <a href="user_profile.php?id=<?= $row['id'] ?>" class="text-decoration-none d-flex align-items-center gap-3">
                        <img src="<?= $avatar ?>?v=<?= time() ?>" class="rounded-circle" style="width:52px;height:52px;object-fit:cover;border:3px solid #00ffff;box-shadow:0 0 15px #00ffff;">
                        <span class="text-white fs-4"><?= htmlspecialchars($row['username']) ?></span>
                      </a>
                    </div>
                    <div class="text-end">
                      <span class="badge bg-success fs-5 px-4 py-2"><?= $timeFormatted ?></span>
                      <span class="badge bg-danger fs-5 px-4 py-2 ms-3"><?= $row['deaths'] ?> deaths</span>
                    </div>
                  </div>
              <?php
                  $rank++;
                }
              }
              ?>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</main>

<div id="echoCursor"></div>

</body>
</html>