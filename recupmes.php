<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Autoriser les requêtes cross-origin si nécessaire

// Activer le reporting d'erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration SMTP (à adapter)
$config = [
    'host' => 'smtp.gmail.com',
    'username' => 'wourypoullo04@gmail.com',
    'password' => 'qmig rurr bzbz sulw', // Mot de passe d'application
    'port' => 587,
    'encryption' => 'tls',
    'from_email' => 'wourypoullo04@gmail.com',
    'from_name' => 'CAEFSR',
    'admin_email' => 'wourypoullo04@gmail.com'
];

// Récupération et validation des données
$data = [
    'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
    'email' => filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL),
    'service' => filter_input(INPUT_POST, 'service', FILTER_SANITIZE_STRING),
    'message' => filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING)
];

// Validation des champs obligatoires
if (empty($data['name']) || !$data['email'] || empty($data['message'])) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis.']);
    exit;
}

// Tentative d'envoi d'email
try {
    // Inclure PHPMailer
    require 'phpmailer/src/Exception.php';
    require 'phpmailer/src/PHPMailer.php';
    require 'phpmailer/src/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    // Configuration SMTP
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['encryption'];
    $mail->Port = $config['port'];
    $mail->CharSet = 'UTF-8';

    // Destinataires
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($config['admin_email']);
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

    if ($mail->send()) {
        echo json_encode(['success' => true, 'message' => 'Votre message a été envoyé avec succès !']);
    } else {
        throw new Exception('L\'envoi du message a échoué');
    }
} catch (Exception $e) {
    error_log('Erreur PHPMailer: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer plus tard.'
    ]);
}