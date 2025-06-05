<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
// Configuration de base
error_reporting(0); // Désactiver en production
date_default_timezone_set('Africa/Dakar');

// Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}


// Inclure PHPMailer
$phpmailerPath = __DIR__ . '/phpmailer/src/';
require $phpmailerPath . 'Exception.php';
require $phpmailerPath . 'PHPMailer.php';
require $phpmailerPath . 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuration SMTP (à adapter avec vos identifiants)
$config = [
    'host' => 'smtp.gmail.com',
    'username' => 'votre_email@gmail.com',
    'password' => 'votre_mot_de_passe_app', // Mot de passe d'application
    'port' => 587,
    'encryption' => 'tls',
    'from_email' => 'no-reply@caefsr.com',
    'from_name' => 'CAEFSR',
    'admin_email' => 'admin@caefsr.com',
    'admin_name' => 'Administrateur CAEFSR'
];

// Validation des données
$data = [
    'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
    'email' => filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL),
    'service' => filter_input(INPUT_POST, 'service', FILTER_SANITIZE_STRING),
    'message' => filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING)
];

// Vérification des champs obligatoires
if (empty($data['name']) || !$data['email'] || empty($data['message'])) {
    echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs obligatoires']);
    exit;
}

$mail = new PHPMailer(true);

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['encryption'];
    $mail->Port = $config['port'];
    $mail->CharSet = 'UTF-8';

    // Activer le débogage SMTP (à désactiver en production)
    $mail->SMTPDebug = 3; // Niveau de débogage (1-4)
    $mail->Debugoutput = function($str, $level) {
        file_put_contents('smtp_debug.log', "SMTP: $str\n", FILE_APPEND);
    };
    // ...
    // Destinataires
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($config['admin_email'], $config['admin_name']);
    $mail->addReplyTo($data['email'], $data['name']);

    // Contenu
    $mail->Subject = "Nouveau message de contact: " . ($data['service'] ?: 'Sans objet');
    $mail->Body = sprintf(
        "Nom: %s\nEmail: %s\nService: %s\n\nMessage:\n%s",
        $data['name'],
        $data['email'],
        $data['service'] ?: 'Non spécifié',
        $data['message']
    );

    // Envoi
    if ($mail->send()) {
        echo json_encode(['success' => true, 'message' => 'Votre message a été envoyé avec succès']);
    } else {
        throw new Exception('Erreur lors de l\'envoi');
    }
} catch (Exception $e) {
    error_log('Erreur PHPMailer: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Désolé, notre serveur de messagerie ne répond pas. Veuillez réessayer plus tard ou nous contacter par téléphone.'
    ]);
}