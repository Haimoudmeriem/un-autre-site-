<?php
// send-mail.php - Backend pour l'envoi d'emails

// Configuration
$to = "contact@mivara.com"; // Remplace par ton email
$subject = "Nouveau message depuis le formulaire de contact Mivara";

// Récupération des données du formulaire
$name = htmlspecialchars($_POST['name'] ?? '');
$email = htmlspecialchars($_POST['email'] ?? '');
$phone = htmlspecialchars($_POST['phone'] ?? 'Non renseigné');
$service = htmlspecialchars($_POST['service'] ?? 'Non spécifié');
$message = htmlspecialchars($_POST['message'] ?? '');

// Validation des champs obligatoires
if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo "error:missing_fields";
    exit;
}

// Validation de l'email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "error:invalid_email";
    exit;
}

// Construction du message email
$email_message = "
<html>
<head>
    <title>Nouveau contact Mivara</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: #4361ee; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fa; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #4361ee; }
        .value { padding: 8px; background: white; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Nouveau message de contact</h1>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>Nom complet:</div>
                <div class='value'>$name</div>
            </div>
            <div class='field'>
                <div class='label'>Email:</div>
                <div class='value'>$email</div>
            </div>
            <div class='field'>
                <div class='label'>Téléphone:</div>
                <div class='value'>$phone</div>
            </div>
            <div class='field'>
                <div class='label'>Type de demande:</div>
                <div class='value'>$service</div>
            </div>
            <div class='field'>
                <div class='label'>Message:</div>
                <div class='value'>$message</div>
            </div>
        </div>
    </div>
</body>
</html>
";

// En-têtes de l'email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Mivara Contact <contact@mivara.com>" . "\r\n";
$headers .= "Reply-To: $name <$email>" . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Tentative d'envoi de l'email
if (mail($to, $subject, $email_message, $headers)) {
    // Envoi d'un accusé de réception automatique
    $auto_reply_subject = "Merci pour votre message - Mivara";
    $auto_reply_message = "
    <html>
    <body>
        <h2>Merci $name !</h2>
        <p>Nous avons bien reçu votre message et nous vous répondrons dans les 24 heures.</p>
        <p><strong>Résumé de votre demande:</strong></p>
        <p>Type: $service</p>
        <p>Message: " . substr($message, 0, 100) . "...</p>
        <hr>
        <p>Cordialement,<br>L'équipe Mivara Communication Digitale</p>
    </body>
    </html>
    ";
    
    $auto_headers = "MIME-Version: 1.0\r\n";
    $auto_headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $auto_headers .= "From: Mivara <contact@mivara.com>\r\n";
    
    mail($email, $auto_reply_subject, $auto_reply_message, $auto_headers);
    
    // Sauvegarde dans un fichier CSV (backup)
    $csv_data = [
        date('Y-m-d H:i:s'),
        $name,
        $email,
        $phone,
        $service,
        str_replace(["\r\n", "\n", "\r"], ' ', $message)
    ];
    
    $csv_line = '"' . implode('","', $csv_data) . '"' . PHP_EOL;
    file_put_contents('contacts.csv', $csv_line, FILE_APPEND);
    
    echo "success";
} else {
    http_response_code(500);
    echo "error:send_failed";
}
?>