<?php
require 'functions.php';

$message      = '';
$email        = '';
$showMessage  = false;
$codeSent     = false;

// Replace with your real check—in functions.php or a database
function isEmailRegistered($email) {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return false;
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return in_array($email, $lines);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $code  = trim($_POST['verification_code'] ?? '');

    // === Step 1: sending code ===
    if ($email !== '' && $code === '') {
        if (isEmailRegistered($email)) {
            $message     = "<div class='flash-message error'>⚠️ This email <strong>$email</strong> is already registered.</div>";
            $showMessage = true;
            $email       = '';       // clear field
        } else {
            // generate & send
            $vcode    = generateVerificationCode();
            sendVerificationEmail($email, $vcode);
            file_put_contents(__DIR__ . "/codes/$email.txt", $vcode);
            $codeSent = true;        // reveal the code box
            // no message here
        }
    }

    // === Step 2: verifying code ===
    if ($email !== '' && $code !== '') {
        $saved = @file_get_contents(__DIR__ . "/codes/$email.txt");
        if (verifyCode($code, $saved)) {
            if (isEmailRegistered($email)) {
                $message     = "<div class='flash-message error'>⚠️ This email <strong>$email</strong> is already registered.</div>";
            } else {
                registerEmail($email);
                @unlink(__DIR__ . "/codes/$email.txt");
                $message     = "<div class='flash-message success'>✅ Email verified and registered successfully!</div>";
            }
            $showMessage = true;
            $email       = '';
            $codeSent    = false;    // hide code box again
        } else {
            $message     = "<div class='flash-message error'>❌ Invalid verification code. Please try again.</div>";
            $showMessage = true;
            $codeSent    = true;     // keep code box visible for retry
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Email Verification</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background: #f2f2f2;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    height: 100vh; margin: 0;
  }
  h1 {
    margin-bottom: 20px; color: #333; font-weight: 600;
  }
  .flash-message {
    position: fixed; top: 20px; left: 50%;
    transform: translateX(-50%);
    padding: 15px 25px; border-radius: 8px;
    font-size: 14px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    animation: fadeSlide 0.6s ease; text-align: center;
    z-index: 1000;
  }
  .flash-message.success {
    background-color: #e6f4ea; color: #2d7a46; border: 1px solid #a3d5b1;
  }
  .flash-message.error {
    background-color: #fcebea; color: #c0392b; border: 1px solid #f5c6cb;
  }
  @keyframes fadeSlide {
    from { opacity: 0; top: 0; }
    to   { opacity: 1; top: 20px; }
  }
  form {
    background: #fff; padding: 30px;
    border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    width: 320px; display: flex; flex-direction: column; gap: 15px;
  }
  input {
    padding: 10px; border: 1px solid #ccc;
    border-radius: 6px; font-size: 14px;
  }
  button {
    padding: 10px; background-color: #007bff;
    border: none; color: white; font-weight: bold;
    border-radius: 6px; cursor: pointer;
    transition: background 0.3s;
  }
  button:hover { background-color: #0056b3; }

  /* hide code section by default */
  #code-section { display: none; }
  /* when .visible is added, show it */
  #code-section.visible { display: block; }
</style>
</head>
<body>

<h1>Email Verification</h1>

<?php if ($showMessage && $message): ?>
  <?php echo $message; ?>
<?php endif; ?>

<form method="POST" novalidate>
  <!-- always show email field -->
  <label>
    Your Email
    <input type="email" name="email" required
           placeholder="Enter email"
           value="<?php echo htmlspecialchars($email); ?>">
  </label>

  <button id="send-code" type="submit"
          <?php if ($codeSent) echo 'disabled'; ?>>
    <?php echo $codeSent ? 'Code Sent' : 'Submit'; ?>
  </button>

  <!-- verification section -->
  <div id="code-section" class="<?php echo $codeSent ? 'visible' : ''; ?>">
    <label>
      Verification Code
      <input type="text" name="verification_code"
             placeholder="Enter verification code">
    </label>
    <button id="verify" type="submit">Verify</button>
  </div>
</form>

<script>
// auto-hide flash after 5s
window.addEventListener('DOMContentLoaded', () => {
  const flash = document.querySelector('.flash-message');
  if (flash) {
    setTimeout(() => {
      flash.style.transition = 'opacity 0.5s ease';
      flash.style.opacity = '0';
      setTimeout(() => flash.remove(), 500);
    }, 5000);
  }
});
</script>

</body>
</html>
