<?php
// Activation des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Inclure PHPMailer MANUELLEMENT (sans autoload)
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

// Utilisation des classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Configuration admin
$admin_email = 'wourypoullo04@gmail.com';
$admin_name = 'Administrateur CAEFSR';

// Paramètres SMTP Gmail
$smtp_host = 'smtp.gmail.com';
$smtp_username = 'wourypoullo04@gmail.com';
$smtp_password = 'qmig rurr bzbz sulw';
$smtp_secure = PHPMailer::ENCRYPTION_SMTPS;
$smtp_port = 465;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $avis = htmlspecialchars(trim($_POST['avis'] ?? ''));
    
    if (empty($avis)) {
        $_SESSION['form_error'] = "Veuillez saisir un avis";
        header('Location: formulaire.php'); // Remplacez par votre page de formulaire
        exit;
    }

    $mail = new PHPMailer(true);
    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = $smtp_secure;
        $mail->Port = $smtp_port;
        $mail->CharSet = 'UTF-8';

        // Destinataire uniquement (l'admin)
        $mail->setFrom($smtp_username, 'Formulaire Avis');
        $mail->addAddress($admin_email, $admin_name);

        // Message simple
        $mail->Subject = 'Nouvel avis - CAEFSR';
        $mail->Body = "Avis reçu:\n\n" . $avis;
        $mail->AltBody = "Avis reçu:\n\n" . $avis;

        $mail->send();
        
        // Message de succès
        $_SESSION['form_success'] = "Votre avis a été envoyé avec succès!";
        header('Location: index.html'); // Redirection vers la page du formulaire
        exit;
        
    } catch (Exception $e) {
        $_SESSION['form_error'] = "Erreur d'envoi: " . $e->getMessage();
        header('Location: index.html');
        exit;
    }
} else {
    header('Location: formulaire.php');
    exit;
}