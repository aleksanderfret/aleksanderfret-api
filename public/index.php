<?php
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Headers: origin, x-requested-with, content-type');
// header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Content-Type: application/json');

require dirname(__DIR__) . DIRECTORY_SEPARATOR. 'Config' . DIRECTORY_SEPARATOR . 'config.php';
require dirname(__DIR__) . DIRECTORY_SEPARATOR. 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Validator;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

$statusCode = 200;
$validator = new Validator($privateKey);
$json = file_get_contents('php://input');
if ($validator->validate($json, 'json')) {
    $isContactDataValid = true;
    $errors = [];
    $data = json_decode(file_get_contents('php://input'), true);
    $dataKeys = array_keys($data);
    foreach ($dataKeys as $key) {
        $errors[$key] = $validator->validate($data[$key]['value'], $key);
        if ($errors[$key] !== '') {
            $isContactDataValid = false;
        }
    }
    if (!$isContactDataValid) {
        print json_encode($errors);
        $statusCode = 406;
    } else {
        $mail = new PHPMailer(true);
        try {
            $mail->setFrom($senderAddress, 'website');
            $mail->addReplyTo($data['email'], $data['name']);
            $mail->addAddress($recipientAddress, $recipientName);
            $mail->addAddress($senderAddress, 'website');
            if ($data['emailcopy']) {
                $mail->addCC($data['email']);
            }
            $mail->Subject = $data['subject'];

            $messageBody = "Wiadomość od "
                . $data['name']
                . "\r\nWyrażono zgodę na użycie danych osobowych w celu udzielenia odpowiedzi na zgłoszone zapytanie."
                . "\r\n\r\n".$data['message'];

            $mail->AltBody = $messageBody;

            $mail->send();
            $statusCode = 200;
        } catch (Exception $e) {
            $message = $mail->ErrorInfo;
            $statusCode = 500;
        }
    }
} else {
    print ($json);
    $statusCode = 400;
}

http_response_code($statusCode);
