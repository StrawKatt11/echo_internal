<?php
require '../database/db.php';
session_start();

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header("Location: leaderboard.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.profile_pic, u.bio, 
           u.steam_link, u.discord_link, u.youtube_link,
           u.last_login, u.last_activity,
           s.points, s.playtime, s.deaths
    FROM users u
    LEFT JOIN game_stats s ON u.id = s.user_id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    echo "<div class='text-center text-danger py-5 fs-1'>User not found</div>";
    exit;
}

$pic = $profile['profile_pic'] ?? 'default.png';
if ($pic !== 'default.png' && file_exists("../img/avatars/presets/$pic")) {
    $avatar = "../img/avatars/presets/$pic";
} elseif ($pic !== 'default.png' && file_exists("../img/avatars/$pic")) {
    $avatar = "../img/avatars/$pic";
} else {
    $avatar = '../img/avatars/default.png';
}

$last_act = strtotime($profile['last_activity'] ?? $profile['last_login'] ?? 'now');
$minutes_ago = (time() - $last_act) / 60;
if ($minutes_ago < 5) {
    $status = 'online'; $status_text = 'Online'; $dot = 'bg-success';
} elseif ($minutes_ago < 60) {
    $status = 'away'; $status_text = 'Away'; $dot = 'bg-warning';
} else {
    $status = 'offline'; $status_text = 'Offline'; $dot = 'bg-secondary';
}

$rankStmt = $pdo->prepare("
    SELECT COUNT(*) + 1 AS rank
    FROM game_stats
    WHERE (points > ?) OR (points = ? AND playtime < ?)
");
$rankStmt->execute([
    $profile['points'] ?? 0,
    $profile['points'] ?? 0,
    $profile['playtime'] ?? 999999999
]);
$profile_rank = $rankStmt->fetchColumn() ?: 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($profile['username']) ?> - Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../javascript/main_js.js"></script>
</head>
<body data-current-user="<?= $_SESSION['user_id'] ?? 0 ?>" 
      data-other-user="<?= $profile['id'] ?>" 
      data-other-username="<?= htmlspecialchars($profile['username']) ?>">

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


<div class="profile-page">
    <div class="profile-container text-center position-relative">

        <div id="messageIcon" class="message-icon">
            <svg width="30" height="30" fill="#00eaff"><path d="M2 3h26v18H6l-4 4V3z"/></svg>
        </div>

        <div class="profile-header mt-4">
            <img src="<?= $avatar ?>?v=<?= time() ?>" class="avatar-big rounded-circle border border-5 border-cyan shadow-lg" width="180" height="180" style="object-fit:cover" alt="Avatar">
            
            <h2 class="text-cyan mt-4 mb-1 display-5"><?= htmlspecialchars($profile['username']) ?></h2>
            
            <p class="mb-3">
                <span class="status-dot <?= $dot ?>"></span>
                <span class="text-<?= $status === 'online' ? 'success' : ($status === 'away' ? 'warning' : 'secondary') ?> fw-bold">
                    <?= $status_text ?>
                </span>
                <span class="last-seen ms-3">
                    Last seen: <?= $profile['last_login'] ? date('M j, Y \a\t g:i A', strtotime($profile['last_login'])) : 'Never' ?>
                </span>
            </p>
        </div>

        <div id="messageBox" style="display:none;" class="p-3 border rounded bg-dark text-white mb-4">
            <div class="message-header mb-2">Chat with <b><?= htmlspecialchars($profile['username']) ?></b></div>
            <div class="chat-messages mb-2" style="max-height:250px; overflow-y:auto;"></div>
            <textarea id="messageText" class="form-control mb-2" placeholder="Write a message..."></textarea>
            <button id="sendMessageBtn" class="btn btn-primary w-100">Send</button>
            <div id="messageStatus" class="mt-2"></div>
        </div>

        <div id="profileContent">
            <div id="profileStats" class="profile-stats mb-5">
                <div class="stat-item"><span class="stat-label">Rank</span><span class="stat-value rank-value">#<?= $profile_rank ?></span></div>
                <div class="stat-item"><span class="stat-label">Points</span><span class="stat-value"><?= number_format($profile['points'] ?? 0) ?></span></div>
                <div class="stat-item"><span class="stat-label">Playtime</span><span class="stat-value"><?= number_format($profile['playtime'] ?? 0) ?>s</span></div>
                <div class="stat-item"><span class="stat-label">Deaths</span><span class="stat-value"><?= $profile['deaths'] ?? 0 ?></span></div>
            </div>

            <div class="info-box mx-auto" style="max-width: 800px;">
                <h4 class="text-cyan mb-4 text-center" style="text-shadow: 0 0 15px #00ffff;">About Me</h4>
                <p class="text-light fs-5 text-center mb-4">
                    <?= nl2br(htmlspecialchars($profile['bio'] ?: 'This user hasn\'t written anything yet...')) ?>
                </p>

                <?php if ($profile['steam_link'] || $profile['discord_link'] || $profile['youtube_link']): ?>
                <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                    <?php if ($profile['steam_link']): ?>
                        <a href="<?= htmlspecialchars($profile['steam_link']) ?>" target="_blank" class="social-btn text-success border-success">
                            Steam Profile
                        </a>
                    <?php endif; ?>
                    <?php if ($profile['discord_link']): ?>
                        <a href="<?= htmlspecialchars($profile['discord_link']) ?>" target="_blank" class="social-btn text-primary border-primary">
                            Discord
                        </a>
                    <?php endif; ?>
                    <?php if ($profile['youtube_link']): ?>
                        <a href="<?= htmlspecialchars($profile['youtube_link']) ?>" target="_blank" class="social-btn text-danger border-danger">
                            YouTube
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="mt-5">
                <a href="profile.php" class="btn btn-outline-cyan btn-lg px-5 fw-bold" style="border-width: 2px; text-shadow: 0 0 10px #00ffff;">
                    Back to My Profile
                </a>
            </div>
        </div>
    </div>
</div>

<div id="echoCursor"></div>

</body>
</html>