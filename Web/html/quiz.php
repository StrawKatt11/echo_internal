<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$questions = [
    1 => ['text' => 'How many levels are there in total?', 'options' => ['3','4','5','6','7'], 'correct' => '5'],
    2 => ['text' => 'Can the character double jump?', 'options' => ['yes','no'], 'correct' => 'no'],
    3 => ['text' => 'How many skins/outfits are there in the game in total?', 'options' => ['5','8','10','12','15'], 'correct' => '10'],
    4 => ['text' => 'Is the game available on Itch.io?', 'options' => ['yes','no'], 'correct' => 'yes'],
    5 => ['text' => 'In which year will we win the GOTY award?', 'options' => ['2025','2026','2077','2177','2180'], 'correct' => '2177']
];

$max_points = count($questions);

$result = null;
$score = 0;
$percentage = 0;
$user_answers = [];

// POST feldolgozás csak egyszer fut le, majd redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $all_answered = true;
    $score = 0;
    foreach ($questions as $qid => $q) {
        $answer = $_POST['q'.$qid] ?? '';
        $user_answers[$qid] = $answer;
        if ($answer === '') $all_answered = false;
        if ($answer === $q['correct']) $score++;
    }

    if ($all_answered) {
        $percentage = round(($score / $max_points) * 100);

        $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, score, max_score, percentage, created_at) 
                               VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $score, $max_points, $percentage]);

        $_SESSION['quiz_result'] = [
            'score'      => $score,
            'percentage' => $percentage,
            'answers'    => $user_answers
        ];

        header("Location: quiz.php");
        exit;
    } else {
        $result = false;
    }
}

// Ha van session eredmény, betöltjük és töröljük
if (isset($_SESSION['quiz_result'])) {
    $result = true;
    $score = $_SESSION['quiz_result']['score'];
    $percentage = $_SESSION['quiz_result']['percentage'];
    $user_answers = $_SESSION['quiz_result']['answers'];
    unset($_SESSION['quiz_result']);
}

// History lekérdezés
$stmt = $pdo->prepare("SELECT score, max_score, percentage, created_at 
                       FROM quiz_results 
                       WHERE user_id = ? 
                       ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>ECHO RUNNER - Quiz</title>
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

        <div class="text-center mb-4">
          <h3 class="text-cyan cyberpunk-glitch d-inline-block" data-text="ECHO RUNNER QUIZ">ECHO RUNNER QUIZ</h3>
        </div>
        <hr class="border-cyan">

        <?php if ($result === null || $result === false): ?>
          <?php if ($result === false): ?>
            <div class="alert alert-warning text-center mb-4">Válaszolj minden kérdésre!</div>
          <?php endif; ?>

          <form method="post" action="quiz.php">
            <?php foreach ($questions as $qid => $q): ?>
              <div class="mb-4 p-3 border border-cyan rounded" style="background:rgba(0,255,255,0.05);">
                <h5 class="mb-3"><?= $qid ?>. <?= htmlspecialchars($q['text']) ?></h5>
                <?php foreach ($q['options'] as $option): ?>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="q<?= $qid ?>" id="q<?= $qid ?>_<?= $option ?>" value="<?= htmlspecialchars($option) ?>" required>
                    <label class="form-check-label" for="q<?= $qid ?>_<?= $option ?>">
                      <?= htmlspecialchars($option) ?>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>

            <div class="text-center mt-5">
              <button type="submit" name="submit_quiz" class="btn btn-play fw-bold px-5 py-3">SUBMIT</button>
            </div>
          </form>

        <?php else: ?>
          <div class="alert alert-cyan text-center py-4 mb-5">
            <h4>Your score: <strong><?= $score ?> / <?= $max_points ?></strong></h4>
            <h5><?= $percentage ?>% completion!</h5>
          </div>

          <h4 class="mb-4">Detailed results:</h4>
          <?php foreach ($questions as $qid => $q): ?>
            <div class="mb-4 p-4 border <?= ($user_answers[$qid] === $q['correct']) ? 'border-success' : 'border-danger' ?> rounded" style="background:rgba(0,255,255,0.05);">
              <h5><?= $qid ?>. <?= htmlspecialchars($q['text']) ?></h5>
              <p><strong>Your answer:</strong> <?= htmlspecialchars($user_answers[$qid] ?? '—') ?></p>
              <p><strong>Correct:</strong> <span class="text-success fw-bold"><?= htmlspecialchars($q['correct']) ?></span></p>
              <?php if ($user_answers[$qid] === $q['correct']): ?>
                <span class="badge bg-success">+1</span>
              <?php else: ?>
                <span class="badge bg-danger">0</span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>

          <div class="text-center mt-4 mb-5">
            <a href="quiz.php" class="btn btn-play fw-bold px-5 py-3">TRY AGAIN</a>
            <button class="btn btn-neon fw-bold px-4 py-2 ms-3 fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#historyCollapse">
              Show History
            </button>
          </div>

          <div class="collapse" id="historyCollapse">
            <div class="card bg-dark border-cyan text-cyan">
              <div class="card-body">
                <h5 class="card-title text-center mb-4">Previous Results</h5>
                <?php if (empty($history)): ?>
                  <p class="text-center">No attempts yet.</p>
                <?php else: ?>
                  <ul class="list-group list-group-flush">
                    <?php foreach ($history as $row): ?>
                      <li class="list-group-item bg-transparent text-cyan border-cyan">
                        <?= date('Y-m-d H:i', strtotime($row['created_at'])) ?>  
                        – <?= $row['score'] ?>/5 – <?= $row['percentage'] ?>%
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>

      </div>
    </main>
  </div>
</div>

<div id="echoCursor"></div>

</body>
</html>