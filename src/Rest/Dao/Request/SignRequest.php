<?php

namespace Sk\Mid\Rest\Dao\Request;

use JsonSerializable;
use \sk\mid\DisplayTextFormat;
use Sk\Mid\Language\Language;

class SignRequest extends AbstractRequest implements JsonSerializable
{
    /**
     * @var string $phoneNumber
     */
    private $phoneNumber;

    /**
     * @var string $nationalIdentityNumber
     */
    private $nationalIdentityNumber;

    /** @var string hash */
    private $hash;

    /** @var string $hashType */
    private $hashType;

    /** @var Language $language */
    private $language;

    /** @var string $displayText */
    private $displayText;

    /** @var DisplayTextFormat $displayTextFormat */
    private $displayTextFormat;

    public function __construct()
    {
        parent::__construct();
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getNationalIdentityNumber(): string
    {
        return $this->nationalIdentityNumber;
    }

    public function setNationalIdentityNumber(string $nationalIdentityNumber): void
    {
        $this->nationalIdentityNumber = $nationalIdentityNumber;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getHashType(): string
    {
        return $this->hashType;
    }

    public function setHashType(string $hashType): void
    {
        $this->hashType = $hashType;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): void
    {
        $this->language = $language;
    }

    public function getDisplayText(): ?string
    {
        return $this->displayText;
    }

    public function setDisplayText(?string $displayText): void
    {
        $this->displayText = $displayText;
    }

    public function getDisplayTextFormat(): ?string
    {
        return $this->displayTextFormat;
    }

    public function setDisplayTextFormat(?string $displayTextFormat): void
    {
        $this->displayTextFormat = $displayTextFormat;
    }

    public static function newBuilder() : SignRequestBuilder
    {
        return new SignRequestBuilder();
    }


    public function jsonSerialize() : array {
        $params = [
                'phoneNumber' => $this->getPhoneNumber(),
                'nationalIdentityNumber' => $this->getNationalIdentityNumber(),
                'relyingPartyUUID' => $this->getRelyingPartyUUID(),
                'relyingPartyName' => $this->getRelyingPartyName(),
                'hash' => $this->getHash(),
                'hashType' => $this->getHashType(),
                'language' => (string) $this->getLanguage()
        ];

        if (null !== $this->getDisplayText()) {
            $params['displayText'] = $this->getDisplayText();

            if (null !== $this->getDisplayTextFormat()) {
                $params['displayTextFormat'] = $this->getDisplayTextFormat();
            }
        }
        return $params;
    }

}
