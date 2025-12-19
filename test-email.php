<?php
/**
 * Test Email Script for dkella.com
 * Use this to test if PHP mail() function works on your server
 * Access: https://dkella.com/test-email.php
 */

$to = 'kella.douzoune@gmail.com';
$subject = 'Test Email from dkella.com';
$message = 'This is a test email to verify that PHP mail() function works on your Namecheap hosting.';
$domain = $_SERVER['HTTP_HOST'];
$from_email = 'no-reply@' . $domain;

$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
$headers .= 'From: ' . $from_email . "\r\n";
$headers .= 'Reply-To: ' . $from_email . "\r\n";
$headers .= 'Return-Path: ' . $from_email . "\r\n";
$headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
$headers .= 'Message-ID: <' . time() . '.' . md5($to . time()) . '@' . $domain . '>' . "\r\n";
$headers .= 'Date: ' . date('r') . "\r\n";

echo "<h2>Email Test for dkella.com</h2>";
echo "<p>Testing email functionality...</p>";

if (mail($to, $subject, $message, $headers)) {
    echo "<p style='color: green;'><strong>✓ Email sent successfully!</strong></p>";
    echo "<p>Please check your inbox (and spam folder) at: <strong>$to</strong></p>";
} else {
    echo "<p style='color: red;'><strong>✗ Email sending failed!</strong></p>";
    echo "<p>This could mean:</p>";
    echo "<ul>";
    echo "<li>PHP mail() function is disabled on your server</li>";
    echo "<li>SMTP is not configured</li>";
    echo "<li>Contact Namecheap support to enable mail() function</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>Server Information:</strong></p>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Domain: " . $_SERVER['HTTP_HOST'] . "</li>";
echo "</ul>";

echo "<p><a href='index.html'>← Back to Portfolio</a></p>";
?>

