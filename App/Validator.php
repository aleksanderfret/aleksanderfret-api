<?php

namespace App;

use Respect\Validation\Rules;
use \ReCaptcha\Recaptcha;

class Validator
{
    private $nameValidator;
    private $emailValidator;
    private $subjectValidator;
    private $messageValidator;
    private $jsonValidator;
    private $rodoValidator;
    private $emailCopyValidator;
    private $recaptchaValidator;

    public function __construct($recaptchaPrivateKey)
    {
        $this->nameValidator = new Rules\AllOf(
            new Rules\Regex('|^[a-zA-ZąćęłńóśżźĄĆĘŁŃÓŚŻŹ -\']+$|'),
            new Rules\Length(3, 60)
        );
        $this->emailValidator = new Rules\AllOf(
            new Rules\Email()
        );
        $this->subjectValidator = new Rules\AllOf(
            new Rules\Regex('|^[a-zA-ZąćęłńóśżźĄĆĘŁŃÓŚŻŹ0-9,.)-:(!? \']+$|'),
            new Rules\Length(5, 500)
        );
        $this->messageValidator = new Rules\AllOf(
            new Rules\Regex('|^[a-zA-ZąćęłńóśżźĄĆĘŁŃÓŚŻŹ0-9,.)-:(!? \']+$|'),
            new Rules\Length(5, 2000)
        );
        $this->jsonValidator = new Rules\AllOf(
            new Rules\Json()
        );
        $this->rodoValidator = new Rules\AllOf(
            new Rules\BoolType(),
            new Rules\TrueVal()
        );
        $this->emailCopyValidator = new Rules\AllOf(
            new Rules\BoolType()
        );
        $this->recaptchaValidator = new ReCaptcha($recaptchaPrivateKey);
    }

    public function validate($value, $valueType)
    {
        $error = '';
        if (!$value && $valueType !== 'emailcopy') {
            $error = 'required';
        } elseif (!$this->patternValidator($value, $valueType)) {
            $error = 'pattern';
        }
        return $error;
    }

    private function patternValidator($value, $valueType)
    {
        if (gettype($value) !== 'boolean') {
            $value = trim(filter_var($value, FILTER_SANITIZE_STRING));
        }
        switch ($valueType) {
            case 'name':
                return $this->nameValidator->validate($value);
            case 'email':
                return $this->emailValidator->validate($value);
            case 'subject':
                return $this->subjectValidator->validate($value);
            case 'message':
                return $this->messageValidator->validate($value);
            case 'rodo':
                return $this->rodoValidator->validate($value);
            case 'emailcopy':
                return $this->emailCopyValidator->validate($value);
            case 'captcha':
                return $this->captchaValidator($value);
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
