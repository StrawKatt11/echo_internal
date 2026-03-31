<?php
require '../database/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : $user_id;

function getUserProfile($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT username, email, profile_pic, bio, steam_link, discord_link, youtube_link, last_activity FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

$profile_user = getUserProfile($pdo, $profile_id);

$is_own_profile = ((int)$profile_id === (int)$user_id);

$last_activity = strtotime($profile_user['last_activity'] ?? 'now');
$minutes_ago = (time() - $last_activity) / 60;
$status = $minutes_ago < 5 ? 'online' : ($minutes_ago < 60 ? 'away' : 'offline');
$status_color = $status === 'online' ? 'text-success' : ($status === 'away' ? 'text-warning' : 'text-danger');

if ($is_own_profile) {
    $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?")->execute([$user_id]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preset_avatar']) && $is_own_profile) {
    $preset = $_POST['preset_avatar'];
    $allowed = ['avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg', 'avatar4.jpg', 
               'avatar5.jpg', 'avatar6.jpg', 'avatar7.jpg', 'avatar8.jpg'];

    if (in_array($preset, $allowed)) {
        $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $old = $stmt->fetchColumn();
        
        if ($old && $old !== 'default.png' && !in_array($old, $allowed)) {
            @unlink('../img/avatars/' . $old);
        }

        $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?")->execute([$preset, $user_id]);
        $_SESSION['profile_pic'] = $preset;
        $message = '<div class="alert alert-success">Avatar updated successfully!</div>';

        $url = $_SERVER['PHP_SELF'];
        if (isset($_GET['id'])) {
            $url .= '?id=' . (int)$_GET['id'];
        }
        header('Location: ' . $url);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar']) && $is_own_profile) {
    $file = $_FILES['avatar'];
    $uploadDir = '../img/avatars/';
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = '<div class="alert alert-danger">Error uploading file. Code: ' . $file['error'] . ' - ' . getUploadError($file['error']) . '</div>';
    } 
    elseif ($file['size'] > 3 * 1024 * 1024) {
        $message = '<div class="alert alert-danger">File is too large. Maximum size is 3MB.</div>';
    }
    else {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $message = '<div class="alert alert-danger">Invalid file type. Allowed types: ' . implode(', ', $allowed) . '</div>';
        } else {
            $new_name = 'user_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = $uploadDir . $new_name;
            
            $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $old_avatar = $stmt->fetchColumn();
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $presets = ['avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg', 'avatar4.jpg', 
                           'avatar5.jpg', 'avatar6.jpg', 'avatar7.jpg', 'avatar8.jpg'];
                
                if ($old_avatar && $old_avatar !== 'default.png' && !in_array($old_avatar, $presets)) {
                    @unlink($uploadDir . $old_avatar);
                }
                
                $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?")->execute([$new_name, $user_id]);
                $_SESSION['profile_pic'] = $new_name;
                
                clearstatcache();
                
                $url = $_SERVER['PHP_SELF'];
                if (isset($_GET['id'])) {
                    $url .= '?id=' . (int)$_GET['id'];
                }
                $url = $_SERVER['PHP_SELF'];
                if (isset($_GET['id'])) {
                  $url .= '?id=' . (int)$_GET['id'];
                }
                $url .= (strpos($url, '?') === false ? '?' : '&') . 't=' . time();
                header('Location: ' . $url);
                exit();
            } else {
                $message = '<div class="alert alert-danger">Error saving file. Please try again. Error: ' . error_get_last()['message'] . '</div>';
            }
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile']) && $is_own_profile) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $steam = trim($_POST['steam_link'] ?? '');
    $discord = trim($_POST['discord_link'] ?? '');
    $youtube = trim($_POST['youtube_link'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->rowCount() > 0) {
        $message = '<div class="text-danger text-center fw-bold fs-4">Username already exists!</div>';
    } 
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->rowCount() > 0) {
            $message = '<div class="text-danger text-center fw-bold fs-4">Email already in use!</div>';
        } else {
            $pdo->prepare("UPDATE users SET username = ?, email = ?, bio = ?, steam_link = ?, discord_link = ?, youtube_link = ? WHERE id = ?")
                ->execute([$username, $email, $bio, $steam, $discord, $youtube, $user_id]);
            
            $_SESSION['username'] = $username;
            
            if (!empty($password)) {
                if ($password === $confirm_password) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
                        ->execute([$hashed_password, $user_id]);
                    $message = '<div class="text-success text-center fw-bold fs-4">Profile and password updated successfully!</div>';
                } else {
                    $message = '<div class="text-danger text-center fw-bold fs-4">Passwords do not match!</div>';
                }
            } else {
                $message = '<div class="text-success text-center fw-bold fs-4">Profile updated successfully!</div>';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($is_own_profile || isset($_POST['preset_avatar']))) {
    $profile_user = getUserProfile($pdo, $profile_id);
}

$presets = ['avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg', 'avatar4.jpg', 'avatar5.jpg', 'avatar6.jpg', 'avatar7.jpg', 'avatar8.jpg'];
$avatar = $profile_user['profile_pic'] 
    ? (in_array($profile_user['profile_pic'], $presets) 
        ? '../img/avatars/presets/' . $profile_user['profile_pic'] 
        : '../img/avatars/' . $profile_user['profile_pic'])
    : '../img/avatars/default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ECHO RUNNER - Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="../css/style.css">
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


<div class="profile-page">
  <div class="profile-container">
    <h2 class="main-title text-center mb-5">User Profile</h2>
    <?php if ($message): ?><div class="text-center mb-4 fs-4"><?= $message ?></div><?php endif; ?>

    <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info">Profile Info</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#friends">Friends</button></li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane fade show active" id="info">
        <div class="row g-5 justify-content-center align-items-start">
          <div class="col-lg-5 text-center">

            <div class="avatar-container mb-5">
              <img src="<?= $avatar ?>?v=<?= time() ?>" class="avatar-big mb-4 rounded-circle" alt="Avatar" id="current-avatar">
              <?php if ($is_own_profile): ?>
                <div class="mb-4">
                  <h4 class="text-cyan mb-3">Upload Custom Avatar</h4>
                  <form method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . (isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : '')); ?>">
                    <div class="file-upload-wrapper">
                      <button type="button" class="btn btn-outline-cyan w-100 text-start" onclick="document.getElementById('avatar').click()">
                        <span id="file-button-text">Select file</span>
                      </button>
                      <input type="file" id="avatar" name="avatar" accept="image/*" style="display: none;" onchange="this.form.submit()">
                    </div>
                    <div class="form-text text-cyan">Max 3MB, JPG, PNG, GIF, or WebP</div>
                    <div id="file-name" class="text-cyan mt-2 p-2 border border-cyan rounded">
                      <span>No file selected</span>
                    </div>
                  </form>
                </div>

                <h4 class="text-cyan mb-3">Or choose from presets:</h4>
                <div class="row g-4 justify-content-center">
                  <?php
                  $presets = ['avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg', 'avatar4.jpg', 'avatar5.jpg', 'avatar6.jpg', 'avatar7.jpg', 'avatar8.jpg'];
                  foreach ($presets as $p) {
                      $path = "../img/avatars/presets/$p";
                      if (file_exists($path)) {
                          $isSelected = ($profile_user['profile_pic'] === $p) ? 'selected-avatar' : '';
                          echo '<div class="col-6 col-md-3">';
                          echo '<img src="../img/avatars/presets/'.$p.'?v='.time().'" 
                                     class="img-fluid rounded-circle preset-avatar shadow '.$isSelected.'" 
                                     style="cursor:pointer;height:100px;width:100px;object-fit:cover;"
                                     onclick="selectPreset(\''.$p.'\')"
                                     data-avatar="'.$p.'">';
                          echo '</div>';
                      }
                  }
                  ?>
                </div>

                <?php if (!glob("../img/avatars/presets/avatar*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE)): ?>
                  <div class="text-danger mt-3 text-center fw-bold">
                    Preset images are missing!<br>
                    Please add them to: <code>img/avatars/presets/avatar1.jpg</code> etc.
                  </div>
                <?php endif; ?>

                <form method="POST" id="presetForm" class="d-none">
                  <input type="hidden" name="preset_avatar" id="presetInput">
                </form>
              <?php endif; ?>
            </div>

          </div>

          <div class="col-lg-7">
            <form method="POST" enctype="multipart/form-data">
              <div class="mb-4">
                <label class="form-label fs-3 text-cyan">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($profile_user['username']) ?>" class="form-control form-control-lg" required>
              </div>
              <div class="mb-4">
                <label class="form-label fs-3 text-cyan">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($profile_user['email']) ?>" class="form-control form-control-lg" required>
              </div>

              <div class="mb-4">
                <label class="form-label fs-3 text-cyan">About Me</label>
                <textarea name="bio" class="form-control form-control-lg" rows="4"><?= htmlspecialchars($profile_user['bio'] ?? '') ?></textarea>
              </div>

              <div class="mb-4">
                <label class="form-label fs-3 text-cyan">Steam Link</label>
                <input type="text" name="steam_link" value="<?= htmlspecialchars($profile_user['steam_link'] ?? '') ?>" class="form-control form-control-lg">
              </div>
              <div class="mb-4">
                <label class="form-label fs-3 text-cyan">Discord Link</label>
                <input type="text" name="discord_link" value="<?= htmlspecialchars($profile_user['discord_link'] ?? '') ?>" class="form-control form-control-lg">
              </div>
              <div class="mb-4">
                <label class="form-label fs-3 text-cyan">YouTube Link</label>
                <input type="text" name="youtube_link" value="<?= htmlspecialchars($profile_user['youtube_link'] ?? '') ?>" class="form-control form-control-lg">
              </div>

              <hr class="my-5 border-cyan">
              <div class="row g-4">
                <div class="col-md-6">
                  <label class="form-label fs-3 text-cyan">New Password</label>
                  <input type="password" name="password" class="form-control form-control-lg">
                </div>
                <div class="col-md-6">
                  <label class="form-label fs-3 text-cyan">Confirm Password</label>
                  <input type="password" name="confirm_password" class="form-control form-control-lg">
                </div>
              </div>
              <div class="text-center mt-5">
                <div class="d-flex gap-4 justify-content-center flex-wrap">
                  <button type="submit" name="save_profile" class="btn btn-play btn-lg px-5 flex-fill flex-md-grow-0">Save</button>
                  <a href="gameinfo.php" class="btn btn-outline-cyan btn-lg px-5 flex-fill flex-md-grow-0">Back</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="friends">
        <div class="row g-4">
          <div class="col-lg-6">
            <h4 class="text-cyan mb-3">Your Friends</h4>
            <ul class="list-group" id="friend-list"><li class="list-group-item text-center text-secondary">Loading...</li></ul>
          </div>
          <div class="col-lg-6">
            <h4 class="text-cyan mb-3">Add Friend</h4>
            <form id="add-friend-form">
              <div class="input-group mb-3">
                <input type="text" id="friend-username" class="form-control" placeholder="Enter username" required>
                <button class="btn btn-outline-cyan" type="submit">Send Request</button>
              </div>
            </form>
            <h4 class="text-cyan mt-4 mb-3">Friend Requests</h4>
            <ul class="list-group" id="friend-requests"><li class="list-group-item text-center text-secondary">Loading...</li></ul>
          </div>
        </div>
        <div class="mb-3"><a href="gameinfo.php" class="btn btn-outline-cyan btn-lg">Back</a></div>
      </div>
    </div>
  </div>
</div>

<div id="echoCursor"></div>
</body>
</html>