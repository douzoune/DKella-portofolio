<?php
/**
 * Test SMTP Connection for dkella.com
 * Access: https://dkella.com/forms/test-smtp.php
 * 
 * ⚠️ SUPPRIMEZ CE FICHIER APRÈS LES TESTS pour des raisons de sécurité
 */

// Configuration SMTP
$smtp_host = 'mail.dkella.com';
$smtp_username = 'no-reply@dkella.com';
$smtp_password = 'VOTRE_MOT_DE_PASSE';  // ⚠️ REMPLACEZ PAR VOTRE MOT DE PASSE
$smtp_port = 587;
$smtp_encryption = 'tls';

echo "<h2>Test SMTP Connection - dkella.com</h2>";
echo "<p>Testing SMTP connection...</p>";

// Test 1: Vérifier si PHPMailer est disponible
$phpmailer_path = __DIR__ . '/../assets/vendor/phpmailer/src/PHPMailer.php';
if (!file_exists($phpmailer_path)) {
    $phpmailer_path = __DIR__ . '/../assets/vendor/PHPMailer/src/PHPMailer.php';
}

if (!file_exists($phpmailer_path)) {
    echo "<p style='color: orange;'><strong>⚠️ PHPMailer not found at:</strong></p>";
    echo "<ul>";
    echo "<li>" . __DIR__ . "/../assets/vendor/phpmailer/src/PHPMailer.php</li>";
    echo "<li>" . __DIR__ . "/../assets/vendor/PHPMailer/src/PHPMailer.php</li>";
    echo "</ul>";
    echo "<p><strong>Solution:</strong> Download PHPMailer from <a href='https://github.com/PHPMailer/PHPMailer' target='_blank'>GitHub</a> and place it in assets/vendor/phpmailer/</p>";
    echo "<hr>";
    echo "<p><strong>Testing native PHP mail() function instead...</strong></p>";
    
    // Test mail() natif
    $test_email = 'kella.douzoune@gmail.com';
    $test_subject = 'Test Email from dkella.com';
    $test_message = 'This is a test email to verify PHP mail() function.';
    $test_headers = 'From: no-reply@dkella.com';
    
    if (mail($test_email, $test_subject, $test_message, $test_headers)) {
        echo "<p style='color: green;'><strong>✓ PHP mail() function works!</strong></p>";
        echo "<p>Check your email at: <strong>$test_email</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>✗ PHP mail() function failed!</strong></p>";
    }
    exit;
}

// Test 2: PHPMailer est disponible, tester la connexion SMTP
require_once $phpmailer_path;
require_once str_replace('PHPMailer.php', 'SMTP.php', $phpmailer_path);
require_once str_replace('PHPMailer.php', 'Exception.php', $phpmailer_path);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    echo "<p><strong>Testing SMTP connection...</strong></p>";
    
    // Configuration SMTP
    $mail->isSMTP();
    $mail->Host = $smtp_host;
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;
    $mail->SMTPSecure = $smtp_encryption;
    $mail->Port = $smtp_port;
    $mail->SMTPDebug = 2; // Afficher les détails de connexion
    $mail->Debugoutput = function($str, $level) {
        echo "<pre style='background: #f0f0f0; padding: 10px; border-left: 3px solid #6366f1;'>$str</pre>";
    };
    
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Test de connexion (sans envoyer d'email)
    echo "<p>Attempting to connect to <strong>$smtp_host:$smtp_port</strong>...</p>";
    
    if (!$mail->smtpConnect()) {
        throw new Exception("SMTP connection failed");
    }
    
    echo "<p style='color: green;'><strong>✓ SMTP Connection successful!</strong></p>";
    
    // Tester l'envoi d'un email
    echo "<hr>";
    echo "<p><strong>Testing email send...</strong></p>";
    
    $mail->setFrom($smtp_username, 'dkella.com Test');
    $mail->addAddress('kella.douzoune@gmail.com');
    $mail->Subject = 'Test SMTP from dkella.com';
    $mail->Body = 'This is a test email sent via SMTP from dkella.com. If you receive this, SMTP is working correctly!';
    $mail->isHTML(false);
    
    $mail->send();
    
    echo "<p style='color: green;'><strong>✓ Email sent successfully!</strong></p>";
    echo "<p>Check your inbox at: <strong>kella.douzoune@gmail.com</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>✗ Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>PHPMailer Error Info:</strong> " . $mail->ErrorInfo . "</p>";
    
    echo "<hr>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Verify SMTP credentials (username and password)</li>";
    echo "<li>Check if SMTP port $smtp_port is open</li>";
    echo "<li>Try port 465 with SSL instead of 587 with TLS</li>";
    echo "<li>Verify that email account no-reply@dkella.com exists in cPanel</li>";
    echo "<li>Check Namecheap documentation for correct SMTP settings</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='../index.html'>← Back to Portfolio</a></p>";
echo "<p style='color: red; font-size: 12px;'><strong>⚠️ IMPORTANT:</strong> Delete this file after testing for security reasons!</p>";
?>

