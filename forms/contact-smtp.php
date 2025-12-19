<?php
/**
 * Contact Form Handler with SMTP for dkella.com
 * Use this version if you want to use SMTP instead of mail()
 * 
 * REQUIREMENTS:
 * 1. Create email account: no-reply@dkella.com in cPanel
 * 2. Install PHPMailer via Composer or download from https://github.com/PHPMailer/PHPMailer
 * 3. Update SMTP credentials below
 */

// ============================================
// CONFIGURATION SMTP - MODIFIEZ CES VALEURS
// ============================================
$smtp_host = 'mail.dkella.com';  // ou smtp.namecheap.com
$smtp_username = 'no-reply@dkella.com';  // Email créé dans cPanel
$smtp_password = 'VOTRE_MOT_DE_PASSE';  // Mot de passe de l'email
$smtp_port = 587;  // 587 pour TLS, 465 pour SSL
$smtp_encryption = 'tls';  // 'tls' ou 'ssl'

$receiving_email_address = 'kella.douzoune@gmail.com';

// Headers pour permettre les requêtes AJAX
header('Content-Type: text/plain; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Vérifier que la requête est en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

// Récupérer et nettoyer les données du formulaire
$name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
$email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
$subject = isset($_POST['subject']) ? trim(strip_tags($_POST['subject'])) : '';
$message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';

// Validation des champs
$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Name is required and must be at least 2 characters';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($subject) || strlen($subject) < 3) {
    $errors[] = 'Subject is required and must be at least 3 characters';
}

if (empty($message) || strlen($message) < 10) {
    $errors[] = 'Message is required and must be at least 10 characters';
}

// Si des erreurs, retourner les erreurs
if (!empty($errors)) {
    http_response_code(400);
    die(implode('. ', $errors));
}

// Vérifier si PHPMailer est disponible
$phpmailer_path = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
if (!file_exists($phpmailer_path)) {
    // Essayer un autre chemin
    $phpmailer_path = __DIR__ . '/../vendor/PHPMailer/PHPMailer/src/PHPMailer.php';
}

if (file_exists($phpmailer_path)) {
    // Utiliser PHPMailer avec SMTP
    require_once $phpmailer_path;
    require_once str_replace('PHPMailer.php', 'SMTP.php', $phpmailer_path);
    require_once str_replace('PHPMailer.php', 'Exception.php', $phpmailer_path);
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    
    $mail = new PHPMailer(true);
    
    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = $smtp_encryption;
        $mail->Port = $smtp_port;
        $mail->CharSet = 'UTF-8';
        
        // Expéditeur et destinataire
        $mail->setFrom($smtp_username, 'dkella.com Contact Form');
        $mail->addAddress($receiving_email_address);
        $mail->addReplyTo($email, $name);
        
        // Contenu de l'email
        $mail->isHTML(false);
        $mail->Subject = "[dkella.com] Contact Form: " . $subject;
        $mail->Body = "You have received a new message from your website contact form.\n\n";
        $mail->Body .= "===========================================\n";
        $mail->Body .= "CONTACT FORM MESSAGE\n";
        $mail->Body .= "===========================================\n\n";
        $mail->Body .= "Name: " . $name . "\n";
        $mail->Body .= "Email: " . $email . "\n";
        $mail->Body .= "Subject: " . $subject . "\n\n";
        $mail->Body .= "Message:\n";
        $mail->Body .= str_repeat("-", 50) . "\n";
        $mail->Body .= $message . "\n";
        $mail->Body .= str_repeat("-", 50) . "\n\n";
        $mail->Body .= "Sent from: " . $_SERVER['HTTP_HOST'] . "\n";
        $mail->Body .= "Date: " . date('Y-m-d H:i:s') . "\n";
        $mail->Body .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
        
        $mail->send();
        echo "OK";
    } catch (Exception $e) {
        http_response_code(500);
        die("Failed to send email: {$mail->ErrorInfo}");
    }
} else {
    // Fallback: utiliser mail() si PHPMailer n'est pas disponible
    $email_subject = "[dkella.com] Contact Form: " . $subject;
    $email_body = "You have received a new message from your website contact form.\n\n";
    $email_body .= "===========================================\n";
    $email_body .= "CONTACT FORM MESSAGE\n";
    $email_body .= "===========================================\n\n";
    $email_body .= "Name: " . $name . "\n";
    $email_body .= "Email: " . $email . "\n";
    $email_body .= "Subject: " . $subject . "\n\n";
    $email_body .= "Message:\n";
    $email_body .= str_repeat("-", 50) . "\n";
    $email_body .= $message . "\n";
    $email_body .= str_repeat("-", 50) . "\n\n";
    $email_body .= "Sent from: " . $_SERVER['HTTP_HOST'] . "\n";
    $email_body .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $email_body .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
    
    $domain = $_SERVER['HTTP_HOST'];
    $from_email = "no-reply@" . $domain;
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "From: " . $name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "Return-Path: " . $from_email . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= "X-MSMail-Priority: Normal\r\n";
    $headers .= "Message-ID: <" . time() . "." . md5($email . time()) . "@" . $domain . ">\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    
    $mail_sent = @mail($receiving_email_address, $email_subject, $email_body, $headers);
    
    if ($mail_sent) {
        echo "OK";
    } else {
        http_response_code(500);
        die('Failed to send email. Please try again later or contact directly at ' . $receiving_email_address);
    }
}
?>

