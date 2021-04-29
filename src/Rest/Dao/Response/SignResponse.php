<?php
namespace Sk\Mid\Rest\Dao\Response;

class SignResponse
{

    /** @var string $sessionId */
    private $sessionId;

    public function __construct(array $responseJson)
    {
        $this->setSessionId($responseJson['sessionID'] ?? $responseJson['sessionId']);
    }


    public function getSessionId() : string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId) : void
    {
        $this->sessionId = $sessionId;
    }

    public function toString() : string
    {
        return  "SignResponse{sessionID='" . $this->sessionId . '}';
    }

}
