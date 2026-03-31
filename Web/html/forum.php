<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_post'])) {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $image = '';

        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp']) && $_FILES['image']['size'] <= 5*1024*1024) {
                $image = $user_id . '_post_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], "../img/posts/$image");
            }
        }

        if ($title && $content) {
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, image) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $content, $image]);
        }
        header("Location: forum.php");
        exit;
    }

    if (isset($_POST['new_comment'])) {
        $post_id = (int)$_POST['post_id'];
        $comment = trim($_POST['comment'] ?? '');
        if ($comment && $post_id > 0) {
            $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$post_id, $user_id, $comment]);
        }
        header("Location: forum.php");
        exit;
    }

    if (isset($_POST['delete_post'])) {
        $post_id = (int)$_POST['delete_post'];
        $stmt = $pdo->prepare("SELECT user_id, image FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $row = $stmt->fetch();
        if ($row && $row['user_id'] == $user_id) {
            if ($row['image'] && file_exists("../img/posts/".$row['image'])) {
                @unlink("../img/posts/".$row['image']);
            }
            $pdo->prepare("DELETE FROM comments WHERE post_id = ?")->execute([$post_id]);
            $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$post_id]);
        }
        header("Location: forum.php");
        exit;
    }

    if (isset($_POST['delete_comment'])) {
        $comment_id = (int)$_POST['delete_comment'];
        $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        if ($stmt->fetchColumn() == $user_id) {
            $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$comment_id]);
        }
        header("Location: forum.php");
        exit;
    }

    if (isset($_POST['vote'])) {
        $post_id = (int)$_POST['post_id'];
        $type = $_POST['vote'] === 'up' ? 'up' : 'down';
        $pdo->prepare("INSERT INTO post_votes (post_id, user_id, vote_type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE vote_type = ?")
            ->execute([$post_id, $user_id, $type, $type]);
        header("Location: forum.php");
        exit;
    }
}

$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.profile_pic, u.id AS author_id,
           COALESCE(SUM(CASE WHEN pv.vote_type = 'up' THEN 1 ELSE 0 END), 0) AS upvotes,
           COALESCE(SUM(CASE WHEN pv.vote_type = 'down' THEN 1 ELSE 0 END), 0) AS downvotes,
           uv.vote_type AS user_vote
    FROM posts p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN post_votes pv ON p.id = pv.post_id
    LEFT JOIN post_votes uv ON p.id = uv.post_id AND uv.user_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ECHO RUNNER - Forum</title>
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

        <h2 class="text-cyan mb-4 text-center">Forum</h2>
        <hr>
        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input type="text" name="title" class="form-control" placeholder="Post Title" required>
                    </div>
                    <div class="mb-3">
                        <textarea name="content" class="form-control" placeholder="Write your post..." rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="image" id="image-upload" class="d-none" accept="image/*">
                        <label for="image-upload" class="custom-file-upload">Upload Image (optional)</label>
                        <div class="file-name" id="file-name-display">No file selected</div>
                    </div>
                    <button name="new_post" class="btn btn-play" type="submit">Create Post</button>
                </form>
            </div>
        </div>

        <?php foreach($posts as $post):
            $score = $post['upvotes'] - $post['downvotes'];
        ?>
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <img src="<?php
                            $pic = $post['profile_pic'] ?? 'default.png';
                            if ($pic !== 'default.png' && file_exists("../img/avatars/presets/$pic")) {
                                echo "../img/avatars/presets/$pic";
                            } elseif (file_exists("../img/avatars/$pic")) {
                                echo "../img/avatars/$pic";
                            } else {
                                echo '../img/avatars/default.png';
                            }
                        ?>?v=<?= time() ?>"
                            class="rounded-circle me-2" 
                            style="width:40px;height:40px;object-fit:cover;"
                            alt="Avatar">
                        <div>
                            <a href="user_profile.php?id=<?= $post['author_id'] ?>" class="text-cyan text-decoration-none fw-bold"><?=htmlspecialchars($post['username'])?></a>
                            <div class="light-cyan small"><?= $post['created_at'] ?></div>
                        </div>
                    </div>
                    <form method="POST" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button name="vote" value="up" class="btn btn-sm <?= $post['user_vote'] === 'up' ? 'btn-success' : 'btn-outline-success' ?>">↑</button>
                        <span class="badge bg-secondary"><?= $score ?></span>
                        <button name="vote" value="down" class="btn btn-sm <?= $post['user_vote'] === 'down' ? 'btn-danger' : 'btn-outline-danger' ?>">↓</button>
                    </form>
                    <?php if ($post['user_id'] == $user_id): ?>
                        <form method="POST" onsubmit="return confirm('Delete this post and all comments permanently?');">
                            <input type="hidden" name="delete_post" value="<?= $post['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <h5 class="text-cyan"><?=htmlspecialchars($post['title'])?></h5>
                    <p class="text-light"><?=nl2br(htmlspecialchars($post['content']))?></p>
                    <?php if (!empty($post['image']) && file_exists("../img/posts/".$post['image'])): ?>
                        <a href="../img/posts/<?= $post['image'] ?>" class="post-image-link">
                            <img src="../img/posts/<?= $post['image'] ?>?v=<?= time() ?>" class="img-fluid rounded mb-3" style="max-height:600px; object-fit:contain; cursor:pointer;">
                        </a>
                    <?php endif; ?>

                </div>

                <div class="card-footer p-0">
                    <?php
                    $stmt = $pdo->prepare("SELECT c.*, u.username, u.profile_pic, u.id AS comment_user_id
                                          FROM comments c JOIN users u ON c.user_id = u.id
                                          WHERE c.post_id = ? ORDER BY c.created_at ASC");
                    $stmt->execute([$post['id']]);
                    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php if (!empty($comments)): ?>
                        <div class="border-top" style="border-top: 1px solid rgba(0,255,255,0.4);">
                            <div class="text-center py-2 bg-dark">
                                <small class="text-cyan opacity-75 fw-bold">COMMENTS</small>
                            </div>

                            <?php foreach($comments as $c): ?>
                                <div class="comment-header d-flex align-items-center justify-content-between px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php
                                            $pic = $c['profile_pic'] ?? 'default.png';
                                            if ($pic !== 'default.png' && file_exists("../img/avatars/presets/$pic")) {
                                                echo "../img/avatars/presets/$pic";
                                            } elseif (file_exists("../img/avatars/$pic")) {
                                                echo "../img/avatars/$pic";
                                            } else {
                                                echo '../img/avatars/default.png';
                                            }
                                        ?>?v=<?= time() ?>"
                                            class="rounded-circle me-3" 
                                            style="width:38px;height:38px;object-fit:cover;"
                                            alt="Avatar">
                                        <div>
                                            <a href="user_profile.php?id=<?= $c['comment_user_id'] ?>" class="text-cyan text-decoration-none fw-bold">
                                                <?=htmlspecialchars($c['username'])?>
                                            </a>
                                            <div class="light-cyan small"><?= $c['created_at'] ?></div>
                                        </div>
                                    </div>
                                    <?php if ($c['comment_user_id'] == $user_id): ?>
                                        <form method="POST" onsubmit="return confirm('Delete this comment?');">
                                            <input type="hidden" name="delete_comment" value="<?= $c['id'] ?>">
                                            <button type="submit" class="btn btn-sm text-danger border-danger" style="background:none;">×</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <div class="comment-body px-4 py-3">
                                    <div class="comment-content">
                                    <?=nl2br(htmlspecialchars($c['content']))?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="p-3 bg-dark">
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="new_comment" value="1">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <input type="text" name="comment" class="form-control" placeholder="Write a comment..." required>
                            <button class="btn btn-outline-cyan" type="submit">Send</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
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