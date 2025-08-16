<?php
$to      = 'your-other-email@gmail.com';
$subject = 'Test Mail from XAMPP';
$message = 'Hello! This is a test.';
$headers = 'From: your-email@gmail.com';

if (mail($to, $subject, $message, $headers)) {
    echo "Mail sent!";
} else {
    echo "Mail failed!";
}
?>
