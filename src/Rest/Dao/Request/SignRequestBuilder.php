<?php
namespace Sk\Mid\Rest\Dao\Request;

use Sk\Mid\MobileIdAuthenticationHashToSign;
use Sk\Mid\Exception\MissingOrInvalidParameterException;
use Sk\Mid\Language\Language;
use Sk\Mid\HashType\HashType;
use Sk\Mid\Util\MidInputUtil;

class SignRequestBuilder
{
    /** @var string $relyingPartyName */
    private $relyingPartyName;

    /** @var string $relyingPartyUUID */
    private $relyingPartyUUID;

    /** @var string $phoneNumber */
    private $phoneNumber;

    /** @var string $nationalIdentityNumber */
    private $nationalIdentityNumber;

    /** @var MobileIdAuthenticationHashToSign $hashToSign */
    private $hashToSign;

    /** @var Language $language */
    private $language;

    /** @var string $displayText */
    private $displayText;

    /** @var string $displayTextFormat */
    private $displayTextFormat;


    public function withRelyingPartyUUID(?string $relyingPartyUUID) : SignRequestBuilder
    {
        $this->relyingPartyUUID = $relyingPartyUUID;
        return $this;
    }

    public function withRelyingPartyName(?string $relyingPartyName) : SignRequestBuilder
    {
        $this->relyingPartyName = $relyingPartyName;
        return $this;
    }

    public function withPhoneNumber(string $phoneNumber) : SignRequestBuilder
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function withNationalIdentityNumber(string $nationalIdentityNumber) : SignRequestBuilder
    {
        $this->nationalIdentityNumber = $nationalIdentityNumber;
        return $this;
    }

    public function withHashToSign(MobileIdAuthenticationHashToSign $hashToSign) : SignRequestBuilder
    {
        $this->hashToSign = $hashToSign;
        return $this;
    }

    public function withLanguage(Language $language) : SignRequestBuilder
    {
        $this->language = $language;
        return $this;
    }

    public function withDisplayText(string $displayText) : SignRequestBuilder
    {
        $this->displayText = $displayText;
        return $this;
    }

    public function withDisplayTextFormat(string $displayTextFormat) : SignRequestBuilder
    {
        $this->displayTextFormat = $displayTextFormat;
        return $this;
    }

    public function build() : SignRequest
    {
        $this->validateParameters();

        $request = new SignRequest();
        $request->setRelyingPartyUUID($this->getRelyingPartyUUID());
        $request->setRelyingPartyName($this->getRelyingPartyName());
        $request->setPhoneNumber($this->getPhoneNumber());
        $request->setNationalIdentityNumber($this->getNationalIdentityNumber());
        $request->setHash($this->getHashToSign()->getHashInBase64());
        $request->setHashType($this->getHashToSign()->getHashType()->getHashTypeName());
        $request->setLanguage($this->getLanguage());
        $request->setDisplayText($this->getDisplayText());
        $request->setDisplayTextFormat($this->getDisplayTextFormat());
        return $request;

    }


    private function validateParameters()
    {
        MidInputUtil::validateUserInput($this->phoneNumber, $this->nationalIdentityNumber);

        if (is_null($this->hashToSign)) {
            throw new MissingOrInvalidParameterException("hashToSign must be set");
        }

        if (is_null($this->language)) {
            throw new MissingOrInvalidParameterException("Language for user dialog in mobile phone must be set");
        }
    }

    private function getHashToSign() : MobileIdAuthenticationHashToSign {
        return $this->hashToSign;
    }

    private function getRelyingPartyName() : ?string
    {
        return $this->relyingPartyName;
    }

    private function getRelyingPartyUUID() : ?string
    {
        return $this->relyingPartyUUID;
    }

    private function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    private function getNationalIdentityNumber(): string
    {
        return $this->nationalIdentityNumber;
    }

    protected function getHashType() : HashType
    {
        return $this->getHashToSign()->getHashType();
    }

    protected function getHashInBase64() : string
    {
        return $this->getHashToSign()->getHashInBase64();
    }

    private function getLanguage() : Language
    {
        return $this->language;
    }

    private function getDisplayText() : ?string
    {
        return $this->displayText;
    }

    private function getDisplayTextFormat() : ?string
    {
        return $this->displayTextFormat;
    }

}
