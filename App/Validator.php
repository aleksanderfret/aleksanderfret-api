<?php

namespace App;

use Respect\Validation\Rules;

class Validator
{
    private $nameValidator;
    private $emailValidator;
    private $messageValidator;
    private $jsonValidator;
    private $rodoValidator;

    public function __construct($recaptchaPrivateKey)
    {
        $this->nameValidator = new Rules\AllOf(
            new Rules\Length(3, 60)
        );
        $this->emailValidator = new Rules\AllOf(
            new Rules\Email()
        );
        $this->messageValidator = new Rules\AllOf(
            new Rules\Length(5, 2000)
        );
        $this->jsonValidator = new Rules\AllOf(
            new Rules\Json()
        );
        $this->rodoValidator = new Rules\AllOf(
            new Rules\Equals('accepted')
        );
    }

    public function validate($value, $valueType)
    {
        return !(!$value || !$this->formValidator($value, $valueType));
    }

    private function formValidator($value, $valueType)
    {

        $sanitizedValue = trim(filter_var($value, FILTER_SANITIZE_STRING));

        switch ($valueType) {
            case 'name':
                return $this->nameValidator->validate($sanitizedValue);
            case 'email':
                return $this->emailValidator->validate($sanitizedValue);
            case 'message':
                return $this->messageValidator->validate($sanitizedValue);
            case 'rodo':
                return $this->rodoValidator->validate($sanitizedValue);
            case 'json':
                return $this->jsonValidator->validate($sanitizedValue);
        }
    }

    private function captchaValidator($gRecaptchaResponse)
    {
        $error = '';
        $remoteIp = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
        $response = $this->recaptchaValidator
            ->setExpectedHostname('recaptcha-demo.appspot.com')
            ->verify($gRecaptchaResponse, $remoteIp);
        if (!$response->isSuccess()) {
            $error = 'pattern';
        }
        return $error;
    }
}
