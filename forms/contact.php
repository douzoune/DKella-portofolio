<?php
/**
 * Contact Form Handler with SMTP for dkella.com
 * Configured for Namecheap Shared Hosting
 * 
 * IMPORTANT: Remplacez VOTRE_MOT_DE_PASSE par le mot de passe de no-reply@dkella.com
 */

// ============================================
// CONFIGURATION SMTP - MODIFIEZ LE MOT DE PASSE
// ============================================
$smtp_host = 'mail.dkella.com';  // Serveur SMTP Namecheap
$smtp_username = 'no-reply@dkella.com';  // Votre email créé
$smtp_password = 'VOTRE_MOT_DE_PASSE';  // ⚠️ REMPLACEZ PAR VOTRE MOT DE PASSE
$smtp_port = 587;  // Port SMTP pour TLS
$smtp_encryption = 'tls';  // TLS (recommandé) ou 'ssl' pour le port 465

$receiving_email_address = 'kella.douzoune@gmail.com';

// Mode debug (mettre à false en production)
$debug_mode = true;

// Headers pour permettre les requêtes AJAX
header('Content-Type: text/plain; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Fonction pour logger les erreurs (utile pour le débogage)
function logError($message) {
    if (isset($GLOBALS['debug_mode']) && $GLOBALS['debug_mode']) {
        error_log("Contact Form Error: " . $message);
    }
}

// Vérifier que la requête est en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

// Fonction pour nettoyer le texte tout en préservant le contenu
function cleanText($text) {
    if (empty($text)) {
        return '';
    }
    // Convertir les entités HTML en caractères normaux d'abord
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Supprimer seulement les vraies balises HTML complètes (format <tag> ou </tag>)
    // Cette regex ne supprime que les balises HTML valides (commençant par une lettre ou /)
    $text = preg_replace('/<\/?[a-zA-Z][^>]*>/', '', $text);
    // Nettoyer les caractères de contrôle sauf les retours à la ligne (\n) et tabulations (\t)
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
    return trim($text);
}

// Récupérer et nettoyer les données du formulaire
$name = isset($_POST['name']) ? cleanText($_POST['name']) : '';
$email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
$subject = isset($_POST['subject']) ? cleanText($_POST['subject']) : '';
$message = isset($_POST['message']) ? cleanText($_POST['message']) : '';

// Debug: logger les données reçues (désactiver en production)
if ($debug_mode) {
    logError("Received POST data: " . print_r($_POST, true));
    logError("Name: '$name', Email: '$email', Subject: '$subject', Message length: " . strlen($message));
}

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

// Si des erreurs, retourner les erreurs avec plus de détails en mode debug
if (!empty($errors)) {
    http_response_code(400);
    $error_message = implode('. ', $errors);
    if ($debug_mode) {
        $error_message .= " [Debug: Name='$name', Email='$email', Subject='$subject', Message length=" . strlen($message) . "]";
    }
    die($error_message);
}

// Préparer le contenu de l'email
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
$email_body .= "IP Address: " . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown') . "\n";

// Essayer d'utiliser PHPMailer si disponible
$phpmailer_path = __DIR__ . '/../assets/vendor/phpmailer/src/PHPMailer.php';
$phpmailer_available = false;

if (file_exists($phpmailer_path)) {
    $phpmailer_available = true;
} else {
    // Essayer un autre chemin
    $phpmailer_path = __DIR__ . '/../assets/vendor/PHPMailer/src/PHPMailer.php';
    if (file_exists($phpmailer_path)) {
        $phpmailer_available = true;
    }
}

if ($phpmailer_available) {
    // Utiliser PHPMailer avec SMTP
    try {
        require_once $phpmailer_path;
        require_once str_replace('PHPMailer.php', 'SMTP.php', $phpmailer_path);
        require_once str_replace('PHPMailer.php', 'Exception.php', $phpmailer_path);
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = $smtp_encryption;
        $mail->Port = $smtp_port;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64'; // Améliore la compatibilité avec UTF-8
        
        // Options pour Namecheap
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Debug (désactiver en production)
        if ($debug_mode) {
            $mail->SMTPDebug = 0; // 0 = off, 1 = client, 2 = client and server
        }
        
        // Expéditeur et destinataire
        $mail->setFrom($smtp_username, 'dkella.com Contact Form');
        $mail->addAddress($receiving_email_address);
        $mail->addReplyTo($email, $name);
        
        // En-têtes anti-spam pour améliorer la délivrabilité
        $mail->addCustomHeader('X-Mailer', 'PHPMailer (dkella.com)');
        $mail->addCustomHeader('X-Priority', '3');
        $mail->addCustomHeader('X-MSMail-Priority', 'Normal');
        $mail->addCustomHeader('Importance', 'Normal');
        
        // Message-ID personnalisé avec le domaine
        $domain = 'dkella.com';
        $message_id = '<' . time() . '.' . md5($email . time()) . '@' . $domain . '>';
        $mail->MessageID = $message_id;
        
        // Headers supplémentaires pour améliorer la réputation
        $mail->addCustomHeader('X-Originating-IP', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown');
        
        // Contenu
        $mail->isHTML(false);
        $mail->Subject = $email_subject;
        $mail->Body = $email_body;
        
        // Améliorer le formatage du texte
        $mail->WordWrap = 72;
        
        $mail->send();
        echo "OK";
        exit;
        
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        logError("PHPMailer Error: " . $mail->ErrorInfo);
        
        // Si PHPMailer échoue, essayer avec mail() natif
        if (!$debug_mode) {
            // En production, essayer mail() comme fallback
        } else {
            http_response_code(500);
            die("SMTP Error: " . $mail->ErrorInfo . ". Please check SMTP credentials.");
        }
    }
}

// Fallback: utiliser mail() natif de PHP
$domain = $_SERVER['HTTP_HOST'];
$from_email = "no-reply@" . $domain;

// Headers optimisés pour éviter les spams
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

// Envoyer l'email
$mail_sent = @mail($receiving_email_address, $email_subject, $email_body, $headers);

if ($mail_sent) {
    echo "OK";
} else {
    logError("mail() function failed");
    
    if ($debug_mode) {
        // En mode debug, donner plus d'informations
        $error_msg = "Failed to send email. ";
        $error_msg .= "Please check: 1) PHP mail() is enabled, 2) Server configuration, ";
        $error_msg .= "3) Or contact support. Error logged in server logs.";
        http_response_code(500);
        die($error_msg);
    } else {
        http_response_code(500);
        die('Failed to send email. Please try again later or contact directly at ' . $receiving_email_address);
    }
}
?>
