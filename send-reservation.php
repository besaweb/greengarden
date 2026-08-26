<?php
/**
 * send-reservation.php
 * Přijme data z rezervačního formuláře na webu Green Garden a odešle je e-mailem
 * přes SMTP schránku nastavenou v config.php.
 *
 * Očekává POST požadavek (multipart/form-data nebo application/x-www-form-urlencoded)
 * s poli: zone, name, phone, email, date, time, guests, notes, website (honeypot).
 *
 * Vrací JSON: {"success": true} nebo {"success": false, "error": "..."}
 */

header('Content-Type: application/json; charset=utf-8');

// --- Povolit jen POST požadavky ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

$config = require __DIR__ . '/config.php';

/**
 * Pomocná funkce: ořízne, odstraní HTML tagy a znaky použitelné pro
 * "header injection" (nová řádka), pak převede na bezpečný HTML výstup.
 */
function clean_input(string $value): string
{
    $value = trim(strip_tags($value));
    $value = str_replace(["\r", "\n"], ' ', $value);
    return $value;
}

// --- Honeypot: pokud je vyplněné skryté pole "website", jde o bota -> tváříme se úspěšně, ale nic neposíláme ---
if (!empty($_POST['website'])) {
    echo json_encode(['success' => true]);
    exit;
}

// --- Načtení a validace povinných polí ---
$requiredFields = ['zone', 'name', 'phone', 'email', 'date', 'time', 'guests'];
$errors = [];

foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $errors[] = "Missing field: $field";
    }
}

$email = trim($_POST['email'] ?? '');
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
    exit;
}

// --- Sanitizace vstupů ---
$zone   = clean_input($_POST['zone']);
$name   = clean_input($_POST['name']);
$phone  = clean_input($_POST['phone']);
$date   = clean_input($_POST['date']);
$time   = clean_input($_POST['time']);
$guests = clean_input($_POST['guests']);
$notes  = trim(strip_tags($_POST['notes'] ?? ''));

$zoneLabels = [
    'restaurant' => 'Restoranti Kryesor & Steakhouse',
    'pool'       => 'Shezlongë pranë Pishinës & Pool Bar',
    'hall'       => 'Salloni i Veçantë (Dasma & Festa Familjare)',
    'patio'      => 'Tarraca Verore e Kopshtit',
];
$zoneLabel = $zoneLabels[$zone] ?? $zone;

// --- Sestavení e-mailu ---
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $config['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_username'];
    $mail->Password   = $config['smtp_password'];
    $mail->SMTPSecure = $config['smtp_secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = $config['smtp_port'];
    $mail->CharSet    = 'UTF-8';

    // Sender / recipient
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($config['to_email'], $config['to_name']);
    // Odpověď z e-mailového klienta půjde přímo hostovi, který rezervaci odeslal
    $mail->addReplyTo($email, $name);
    // Kopie potvrzení jde i hostovi na email, který vyplnil ve formuláři
    $mail->addCC($email, $name);

    // Content
    $mail->isHTML(true);
    $mail->Subject = "Rezervim i ri nga faqja - {$name}";

    $bodyRows = [
        'Zona'    => $zoneLabel,
        'Emri'    => $name,
        'Telefon' => $phone,
        'Email'   => $email,
        'Data'    => $date,
        'Ora'     => $time,
        'Persona' => $guests,
        'Shënime' => $notes !== '' ? nl2br(htmlspecialchars($notes)) : '-',
    ];

    $html = '<h2 style="font-family:sans-serif;color:#2f4f3a;">Rezervim i ri nga faqja Green Garden</h2>';
    $html .= '<table style="font-family:sans-serif;font-size:14px;border-collapse:collapse;">';
    foreach ($bodyRows as $label => $value) {
        $html .= '<tr>'
            . '<td style="padding:6px 12px 6px 0;font-weight:bold;vertical-align:top;">' . htmlspecialchars($label) . '</td>'
            . '<td style="padding:6px 0;">' . ($label === 'Shënime' ? $value : htmlspecialchars($value)) . '</td>'
            . '</tr>';
    }
    $html .= '</table>';
    $html .= '<p style="font-family:sans-serif;font-size:12px;color:#888;margin-top:16px;">Dërguar automatikisht nga formulari i rezervimeve në greengarden.al më ' . date('d.m.Y H:i') . '</p>';

    $mail->Body    = $html;
    $mail->AltBody = implode("\n", array_map(
        fn($label, $value) => "$label: " . strip_tags($value),
        array_keys($bodyRows),
        $bodyRows
    ));

    $mail->send();
    echo json_encode(['success' => true]);
} catch (PHPMailerException $e) {
    http_response_code(500);
    // V produkci doporučuji do JSON odpovědi $e->getMessage() neposílat (únik interních detailů),
    // jen ho zalogovat. Zde je pro snazší ladění během nasazení ponechaný.
    error_log('Reservation mail error: ' . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'error' => 'Mail could not be sent.']);
}
