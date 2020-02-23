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

    public function __construct()
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

    private function sanitizeValue($value) {
        return trim(filter_var($value, FILTER_SANITIZE_STRING));
    }

    private function formValidator($value, $valueType)
    {
        switch ($valueType) {
            case 'name':
                return $this->nameValidator->validate($this->sanitizeValue($value));
            case 'email':
                return $this->emailValidator->validate($this->sanitizeValue($value));
            case 'message':
                return $this->messageValidator->validate($this->sanitizeValue($value));
            case 'rodo':
                return $this->rodoValidator->validate($this->sanitizeValue($value));
            case 'json':
                return $this->jsonValidator->validate($value);
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
