
<?php
if (!is_dir(__DIR__ . '/codes')) {
    mkdir(__DIR__ . '/codes', 0777, true);
}

function generateVerificationCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
    if (!in_array($email, $emails)) {
        file_put_contents($file, "$email\n", FILE_APPEND);
    }
}

function unsubscribeEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file($file, FILE_IGNORE_NEW_LINES);
    $emails = array_filter($emails, fn($e) => trim($e) !== trim($email));
    file_put_contents($file, implode("\n", $emails) . "\n");
}

function sendVerificationEmail($email, $code) {
    $subject = "Your Verification Code";
    $body = "<p>Your verification code is: <strong>$code</strong></p>";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: no-reply@example.com\r\n";
    mail($email, $subject, $body, $headers);
}

function sendUnsubscribeCode($email, $code) {
    $subject = "Confirm Un-subscription";
    $body = "<p>To confirm un-subscription, use this code: <strong>$code</strong></p>";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: no-reply@example.com\r\n";
    mail($email, $subject, $body, $headers);
}

function verifyCode($input, $stored) {
    return trim($input) === trim($stored);
}

function fetchAndFormatXKCDData() {
    $random = random_int(1, 2800); // safe limit
    $json = @file_get_contents("https://xkcd.com/$random/info.0.json");
    if (!$json) return false;

    $data = json_decode($json, true);
    $img = $data['img'];
    return "<h2>XKCD Comic</h2><img src=\"$img\" alt=\"XKCD Comic\"><p><a href=\"#\" id=\"unsubscribe-button\">Unsubscribe</a></p>";
}

function sendXKCDUpdatesToSubscribers() {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;

    $content = fetchAndFormatXKCDData();
    if (!$content) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES);
    foreach ($emails as $email) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: no-reply@example.com\r\n";
        mail(trim($email), "Your XKCD Comic", $content, $headers);
    }
}
