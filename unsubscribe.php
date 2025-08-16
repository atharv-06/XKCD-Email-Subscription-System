<?php
require 'functions.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['unsubscribe_email'] ?? '';
    $code = $_POST['verification_code'] ?? '';

    if (!empty($email) && empty($code)) {
        $vcode = generateVerificationCode();
        sendUnsubscribeCode($email, $vcode);
        file_put_contents(__DIR__ . "/codes/unsub_$email.txt", $vcode);
        $msg = "Unsubscribe code sent.";
    }

    if (!empty($email) && !empty($code)) {
        $savedCode = @file_get_contents(__DIR__ . "/codes/unsub_$email.txt");
        if (verifyCode($code, $savedCode)) {
            unsubscribeEmail($email);
            $msg = "You have been unsubscribed.";
            @unlink(__DIR__ . "/codes/unsub_$email.txt");
        } else {
            $msg = "Invalid code.";
        }
    }
}
?>

<form method="POST">
    <input type="email" name="unsubscribe_email" required placeholder="Enter email">
    <button id="submit-unsubscribe">Unsubscribe</button><br><br>
    <input type="text" name="verification_code" maxlength="6" placeholder="Enter code">
    <button id="submit-verification">Verify</button>
</form>
<p><?= $msg ?></p>
