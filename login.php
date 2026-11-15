<?php
session_start();

if (!empty($_SESSION['admin_user_id'])) {
  header('Location: admin/');
  exit;
}

require_once __DIR__ . '/db_connection.php';

$errorMessage = '';
// $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'admin/';
// if (strpos($redirect, '/glace-new/admin/') !== 0) {
//   $redirect = 'admin/';
// }

$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'admin/';

$redirect = trim($redirect);

if (
    strpos($redirect, 'admin/') !== 0 ||
    strpos($redirect, '..') !== false
) {
    $redirect = 'admin/';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $identity = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $columns = [];
  $columnResult = $conn->query('SHOW COLUMNS FROM users');

  if ($columnResult) {
    while ($column = $columnResult->fetch_assoc()) {
      $columns[] = $column['Field'];
    }
  }

  $identityColumn = in_array('user_name', $columns, true) ? 'user_name' : (in_array('username', $columns, true) ? 'username' : (in_array('email', $columns, true) ? 'email' : null));
  $passwordColumn = in_array('password_hash', $columns, true) ? 'password_hash' : (in_array('password', $columns, true) ? 'password' : null);

  if ($identityColumn && $passwordColumn && $identity !== '' && $password !== '') {
    $query = $conn->prepare("SELECT * FROM users WHERE `$identityColumn` = ? LIMIT 1");
    $query->bind_param('s', $identity);
    $query->execute();
    $user = $query->get_result()->fetch_assoc();
    $storedPassword = $user[$passwordColumn] ?? '';
    $passwordMatches = $storedPassword !== '' && (password_verify($password, $storedPassword) || hash_equals((string) $storedPassword, $password));

    if ($user && $passwordMatches) {
      session_regenerate_id(true);
      $_SESSION['admin_user_id'] = $user['id'] ?? $identity;
      $_SESSION['admin_user_identity'] = $user[$identityColumn];
      header('Location: ' . $redirect);
      exit;
    }
  }

  $errorMessage = 'Invalid email or password. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login | Media Agency</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --bg: #0a0d0d;
      --card: #101515;
      --card-light: #151c1c;
      --primary: #14a49a;
      --primary-dark: #0d7e77;
      --cream: #ebe1d3;
      --muted: #8b9491;
      --border: rgba(235, 225, 211, 0.12);
      --danger: #e56b6f;
    }

    body {
      min-height: 100vh;
      overflow: hidden;
      background: var(--bg);
      color: var(--cream);
      font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    .page { position: relative; display: flex; min-height: 100vh; overflow: hidden; }

    .glow {
      position: absolute;
      width: 550px;
      height: 550px;
      border-radius: 50%;
      background: var(--primary);
      filter: blur(180px);
      opacity: .08;
      pointer-events: none;
    }
    .glow.one { top: -250px; left: -180px; }
    .glow.two { right: -300px; bottom: -300px; }

    .brand-panel {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      width: 52%;
      min-height: 100vh;
      padding: 48px;
      border-right: 1px solid var(--border);
    }

    .logo { display: flex; align-items: center; gap: 12px; font-size: 18px; font-weight: 700; letter-spacing: -.02em; }
    .logo-mark {
      display: grid;
      place-items: center;
      width: 38px;
      height: 38px;
      border: 1px solid rgba(20, 164, 154, .5);
      border-radius: 10px;
      background: rgba(20, 164, 154, .06);
      color: var(--primary);
      font-size: 15px;
      font-weight: 800;
    }
    .brand-content { max-width: 580px; }
    .eyebrow { margin-bottom: 22px; color: var(--primary); font-size: 12px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; }
    .brand-content h1 { margin-bottom: 28px; font-size: clamp(48px, 5.2vw, 82px); line-height: .95; letter-spacing: -.06em; font-weight: 600; }
    .brand-content h1 span { color: var(--primary); }
    .brand-content p { max-width: 470px; color: var(--muted); font-size: 15px; line-height: 1.7; }
    .footer-text { color: #59625f; font-size: 12px; }

    .login-section {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 48%;
      min-height: 100vh;
      padding: 40px;
    }
    .login-container { width: 100%; max-width: 440px; }
    .login-header { margin-bottom: 34px; }
    .login-header h2 { margin-bottom: 10px; font-size: 34px; line-height: 1.1; letter-spacing: -.04em; font-weight: 600; }
    .login-header p { color: var(--muted); font-size: 14px; }

    .login-card {
      padding: 30px;
      border: 1px solid var(--border);
      border-radius: 18px;
      background: rgba(16, 21, 21, .88);
      box-shadow: 0 30px 80px rgba(0, 0, 0, .35);
      backdrop-filter: blur(20px);
    }
    .form-group { margin-bottom: 21px; }
    .form-group label { display: block; margin-bottom: 9px; color: var(--cream); font-size: 12px; font-weight: 600; }
    .input-wrapper { position: relative; }
    .input-icon { position: absolute; top: 50%; left: 15px; color: #66716e; pointer-events: none; transform: translateY(-50%); }

    input:not([type="checkbox"]) {
      width: 100%;
      height: 52px;
      padding: 0 15px 0 45px;
      border: 1px solid var(--border);
      border-radius: 10px;
      outline: none;
      background: #0c1111;
      color: var(--cream);
      font-size: 14px;
      transition: .2s ease;
    }
    input::placeholder { color: #505957; }
    input:not([type="checkbox"]):focus { border-color: rgba(20, 164, 154, .7); box-shadow: 0 0 0 3px rgba(20, 164, 154, .08); }

    .password-toggle { position: absolute; top: 50%; right: 14px; border: 0; background: none; color: #697370; cursor: pointer; font-size: 12px; transform: translateY(-50%); }
    .password-toggle:hover { color: var(--cream); }
    .form-options { display: flex; align-items: center; justify-content: space-between; margin: 5px 0 26px; }
    .remember { display: flex; align-items: center; gap: 8px; color: var(--muted); cursor: pointer; font-size: 12px; }
    .remember input { width: 15px; height: 15px; accent-color: var(--primary); }
    .forgot { color: var(--primary); font-size: 12px; font-weight: 600; text-decoration: none; }
    .forgot:hover { text-decoration: underline; }

    .login-btn {
      position: relative;
      width: 100%;
      height: 52px;
      border: 0;
      border-radius: 10px;
      background: var(--primary);
      color: #071111;
      cursor: pointer;
      font-size: 13px;
      font-weight: 800;
      letter-spacing: .01em;
      overflow: hidden;
      transition: .2s ease;
    }
    .login-btn:hover { background: #19b7ac; box-shadow: 0 12px 30px rgba(20, 164, 154, .18); transform: translateY(-1px); }
    .login-btn:active { transform: translateY(0); }
    .login-btn.loading { pointer-events: none; color: transparent; }
    .login-btn.loading::after {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 17px;
      height: 17px;
      border: 2px solid rgba(7, 17, 17, .3);
      border-top-color: #071111;
      border-radius: 50%;
      content: "";
      transform: translate(-50%, -50%);
      animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: translate(-50%, -50%) rotate(360deg); } }

    .error-message { display: none; padding: 11px 13px; margin-bottom: 20px; border: 1px solid rgba(229, 107, 111, .2); border-radius: 8px; background: rgba(229, 107, 111, .08); color: var(--danger); font-size: 12px; }
    .security-note { display: flex; align-items: center; justify-content: center; gap: 7px; margin-top: 20px; color: #58625f; font-size: 11px; }
    .security-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--primary); box-shadow: 0 0 8px rgba(20, 164, 154, .5); }

    @media (max-width: 850px) {
      body { overflow: auto; }
      .page { display: block; }
      .brand-panel { width: 100%; min-height: auto; padding: 28px; border-right: 0; }
      .brand-content { padding: 90px 0 45px; }
      .brand-content h1 { font-size: 52px; }
      .brand-content p, .footer-text { display: none; }
      .login-section { width: 100%; min-height: auto; padding: 10px 28px 45px; }
      .login-container { max-width: 500px; }
    }

    @media (max-width: 480px) {
      .brand-panel { padding: 22px; }
      .brand-content { padding: 70px 0 35px; }
      .brand-content h1 { font-size: 43px; }
      .login-section { padding: 5px 22px 35px; }
      .login-card { padding: 22px; }
      .login-header h2 { font-size: 29px; }
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="glow one"></div>
    <div class="glow two"></div>

    <section class="brand-panel">
      <div class="logo">
        <div class="logo-mark">M</div>
        <span>MEDIA / ADMIN</span>
      </div>
      <div class="brand-content">
        <div class="eyebrow">Control Center</div>
        <h1>Create.<br>Manage.<br><span>Grow.</span></h1>
        <p>Manage your agency's digital presence from one central workspace. Update your work, clients, reels, services and website content effortlessly.</p>
      </div>
      <div class="footer-text">© 2026 Media Agency. Internal use only.</div>
    </section>

    <section class="login-section">
      <div class="login-container">
        <div class="login-header">
          <h2>Welcome back.</h2>
          <p>Sign in to access your admin dashboard.</p>
        </div>
        <div class="login-card">
          <div class="error-message" id="errorMessage" style="<?= $errorMessage !== '' ? 'display:block' : '' ?>"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
          <form id="loginForm" method="post" action="login.php">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="username">Username</label>
              <div class="input-wrapper">
                <span class="input-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg></span>
                <input type="text" id="username" name="username" placeholder="Enter your username" autocomplete="username" required>
              </div>
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <div class="input-wrapper">
                <span class="input-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg></span>
                <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                <button type="button" class="password-toggle" id="passwordToggle">SHOW</button>
              </div>
            </div>
            <div class="form-options">
              <label class="remember"><input type="checkbox" id="remember"><span>Remember me</span></label>
              <a href="#" class="forgot" id="forgotPassword">Forgot password?</a>
            </div>
            <button type="submit" class="login-btn" id="loginButton">SIGN IN TO DASHBOARD</button>
          </form>
        </div>
        <div class="security-note"><span class="security-dot"></span>Secure admin access</div>
      </div>
    </section>
  </div>

  <script src="cursor.js"></script>
  <script>
    const form = document.getElementById('loginForm');
    const password = document.getElementById('password');
    const loginButton = document.getElementById('loginButton');
    const errorMessage = document.getElementById('errorMessage');
    const passwordToggle = document.getElementById('passwordToggle');

    passwordToggle.addEventListener('click', () => {
      const isPassword = password.type === 'password';
      password.type = isPassword ? 'text' : 'password';
      passwordToggle.textContent = isPassword ? 'HIDE' : 'SHOW';
    });

    form.addEventListener('submit', () => {
      errorMessage.style.display = 'none';
      loginButton.classList.add('loading');
    });

    document.getElementById('forgotPassword').addEventListener('click', (event) => {
      event.preventDefault();
      alert('Password reset functionality will be connected to your backend.');
    });
  </script>
</body>
</html>
