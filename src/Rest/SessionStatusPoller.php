<?php
/*-
 * #%L
 * Mobile ID sample PHP client
 * %%
 * Copyright (C) 2018 - 2021 SK ID Solutions AS
 * %%
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * #L%
 */
namespace Sk\Mid\Rest;
use Sk\Mid\Util\Logger;
use Sk\Mid\Rest\Dao\SessionStatus;
use Sk\Mid\Rest\Dao\Request\SessionStatusRequest;
use Sk\Mid\Exception\MidInternalErrorException;
use Sk\Mid\Exception\MidNotMidClientException;
use Sk\Mid\Exception\MidUserCancellationException;
use Sk\Mid\Exception\MidPhoneNotAvailableException;
use Sk\Mid\Exception\MidDeliveryException;
use Sk\Mid\Exception\MidInvalidUserConfigurationException;
use Sk\Mid\Exception\MidSessionTimeoutException;

class SessionStatusPoller
{

    const DEFAULT_POLLING_SLEEP_TIMEOUT_SECONDS = 3;

    /** @var MobileIdRestConnector $connector */
    private $connector;

    /** @var int $pollingSleepTimeoutSeconds */
    private int $pollingSleepTimeoutSeconds;

    /** @var int $longPollingTimeoutSeconds */
    private int $longPollingTimeoutSeconds;

    private Logger $logger;


    public function __construct(SessionStatusPollerBuilder $builder)
    {
        $this->connector = $builder->getConnector();
        $this->pollingSleepTimeoutSeconds = $builder->getPollingSleepTimeoutSeconds();
        $this->longPollingTimeoutSeconds = $builder->getLongPollingTimeoutSeconds();

        if ($this->pollingSleepTimeoutSeconds == 0 && $this->longPollingTimeoutSeconds == 0) {
            $this->pollingSleepTimeoutSeconds = self::DEFAULT_POLLING_SLEEP_TIMEOUT_SECONDS;
        }

        $this->logger = new Logger('SessionStatusPoller');

    }

    public function fetchFinalSignatureSessionStatus(string $sessionId, int $longPollSeconds = 20) : SessionStatus
    {
        return $this->fetchFinalSessionStatus($sessionId, $longPollSeconds);
    }

    public function fetchFinalAuthenticationSession(string $sessionId, int $longPollSeconds = 20) : SessionStatus
    {
        return $this->fetchFinalSessionStatus($sessionId, $longPollSeconds);
    }

    public function fetchFinalSessionStatus(string $sessionId, int $longPollSeconds = null) : SessionStatus
    {
        $sessionStatus = $this->pollForFinalSessionStatus($sessionId, $longPollSeconds);
        $this->validateResult($sessionStatus);
        return $sessionStatus;
    }

    private function pollForFinalSessionStatus(string $sessionId, ?int $longPollSeconds = 20) : SessionStatus
    {
        $sessionStatus = null;

        while ($sessionStatus == null || strcasecmp($sessionStatus->getState(), 'RUNNING') == 0) {
            $sessionStatus = $this->pollSessionStatus($sessionId, $longPollSeconds);
            if ($sessionStatus->isComplete()) {
                return $sessionStatus;
            }

            $this->logger->debug('Sleeping for ' . $this->pollingSleepTimeoutSeconds . ' seconds');
            sleep($this->pollingSleepTimeoutSeconds);
        }

        $this->logger->debug('Got session final session status response');
        return $sessionStatus;
    }

    private function pollSessionStatus(string $sessionId, ?int $longPollSeconds = null) : SessionStatus
    {
        $this->logger->debug('Polling session status');
        $request = $this->createSessionStatusRequest($sessionId, $longPollSeconds);
        return $this->connector->pullSessionStatus($request);
    }

    private function createSessionStatusRequest(string $sessionId, ?int $longPollSeconds) : SessionStatusRequest
    {
        return new SessionStatusRequest($sessionId, $longPollSeconds);
    }

    private function validateResult(SessionStatus $sessionStatus) : void
    {
        $result = $sessionStatus->getResult();
        if ($result == null) {
            $this->logger->error('Result is missing in the session status response');
            throw new MidInternalErrorException('Result is missing in the session status response');
        } else {
            $this->validateResultOfString($result);
        }
    }

    /**
     * @param string $result
     * @throws MidDeliveryException
     * @throws MidInvalidUserConfigurationException
     * @throws MidInternalErrorException
     * @throws MidSessionTimeoutException
     * @throws MidNotMidClientException
     * @throws MidPhoneNotAvailableException
     * @throws MidUserCancellationException
     */
    private function validateResultOfString(string $result) : void
    {
        $result = strtoupper($result);

        switch ($result) {
            case 'OK':
                return;
            case 'TIMEOUT':
            case 'EXPIRED_TRANSACTION':
                $this->logger->error('Session timeout');
                throw new MidSessionTimeoutException();
            case 'NOT_MID_CLIENT':
                $this->logger->error('User is not Mobile-ID client');
                throw new MidNotMidClientException();
            case 'USER_CANCELLED':
                $this->logger->error('User cancelled the operation');
                throw new MidUserCancellationException();
            case 'PHONE_ABSENT':
                $this->logger->error('Sim not available');
                throw new MidPhoneNotAvailableException();
            case 'SIGNATURE_HASH_MISMATCH':
                $this->logger->error('Hash does not match with certificate type');
                throw new MidInvalidUserConfigurationException();
            case 'SIM_ERROR':
            case 'DELIVERY_ERROR':
                $this->logger->error('SMS sending or SIM error');
                throw new MidDeliveryException();
            default:
                throw new MidInternalErrorException("MID returned error code '" . $result . "'");

        }
    }

    public static function newBuilder() : SessionStatusPollerBuilder
    {
        return new SessionStatusPollerBuilder();
    }

}

