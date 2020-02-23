<?php
header('Content-Type: application/json');

require dirname(__DIR__) . DIRECTORY_SEPARATOR. 'Config' . DIRECTORY_SEPARATOR . 'config.php';
require dirname(__DIR__) . DIRECTORY_SEPARATOR. 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Validator;
use App\Logger;

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
        $errors[$key] = !$validator->validate($data[$key]['value'], $key);
        if ($errors[$key]) {
            $isContactDataValid = false;
        }
    }
    if (!$isContactDataValid) {
        print json_encode($errors);
        $statusCode = 406;
    } else {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        try {
            $mail->setFrom($senderAddress, $data['name']['value'] . ' from website');
            $mail->addReplyTo($data['email']['value'], $data['name']['value']);
            $mail->addAddress($recipientAddress, $recipientName);
            $mail->Subject = 'Wiadomość ze strony www.aleksander.fret.com.pl';

            $messageBody = "Wiadomość od "
                . $data['name']['value']
                . "\r\n\r\nWyrażono zgodę na użycie danych osobowych"
                . " w celu udzielenia odpowiedzi na zgłoszone zapytanie."
                . "\r\n\r\n"
                . $data['message']['value'];

            $mail->Body = $messageBody;
            
            $mail->send();
            $statusCode = 200;
        } catch (Exception $e) {
            $logger = new Logger();
            $logger->saveLog($e, ['JSON'=> $json, 'DATA' => $data, 'ERRORS' => $errors]);
            $statusCode = 500;
        }
    }
} else {
    $logger->saveLog('Invalid JSON', ['JSON'=> $json, 'DATA' => $data, 'ERRORS' => $errors]);
    print ($json);
    $statusCode = 400;
}

http_response_code($statusCode);
