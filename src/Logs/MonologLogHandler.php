<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Logs;

use Hengebytes\WebserviceCoreAsyncBundle\Provider\ParamsProviderInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Hengebytes\WebserviceCoreAsyncBundle\Response\ParsedResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class MonologLogHandler
{
    protected ?bool $maskSensitiveData = null;
    protected ?bool $maskSensitiveMemberPII = null;

    public function __construct(
        protected LoggerInterface $logger,
        protected ?ParamsProviderInterface $paramsProvider,
        protected RequestStack $requestStack
    ) {
    }

    public function writeLog(ParsedResponse $parsedResponse): void
    {
        $WSRequest = $parsedResponse->mainAsyncResponse->WSRequest;

        if (
            !$this->paramsProvider->getLogParameterValue('store', '1')
            || !$this->paramsProvider->getLogParameterValue(
                'store/' . $WSRequest->getCustomAction(), '1'
            )
        ) {
            return;
        }

        $currentRequest = $this->requestStack->getCurrentRequest();

        if ($this->maskSensitiveData === null) {
            $this->maskSensitiveData = (bool)$this->paramsProvider->getLogParameterValue('mask_sensitive_data');
        }
        if ($this->maskSensitiveMemberPII === null) {
            $this->maskSensitiveMemberPII = (bool)$this->paramsProvider->getLogParameterValue('mask_sensitive_member_pii');
        }

        $requestString = $WSRequest->getLogString();
        $responseString = $parsedResponse->getLogString(
            (bool)$this->paramsProvider->getLogParameterValue('log_response_headers'),
            $this->maskSensitiveData
        );
        if ($this->maskSensitiveData) {
            MaskLogHelper::maskSensitiveVar($requestString, $this->maskSensitiveMemberPII);
            MaskLogHelper::maskSensitiveVar($responseString, true);
        }
        $responseMaxLength = $this->paramsProvider->getLogParameterValue('max_length', 900000);
        if ($responseString && strlen($responseString) > $responseMaxLength) {
            $response = substr($responseString, 0, $responseMaxLength);
            $responseString = $response;
        }

        $logContext = [
            'service' => $this->getServiceName($WSRequest),
            'action' => $WSRequest->getCustomAction(),
            'clientip' => $currentRequest?->getClientIp(),
            'request' => $requestString,
            'response' => $responseString,
            'error' => $parsedResponse->exception?->getMessage(),
            'duration' => $parsedResponse->mainAsyncResponse->WSResponse->getInfo('total_time'),
            'uri' => $WSRequest->action,
        ];

        if ($logContext['error'] !== null) {
            $this->logger->error('CL', $logContext);
        } else {
            $this->logger->info('CL', $logContext);
        }
    }

    /**
     * Waiting for a cache lock leaves no request log (the response is served from cache), log long waits instead.
     */
    public function writeLockWaitLog(WSRequest $WSRequest, float $waited): void
    {
        if (
            !$this->paramsProvider->getLogParameterValue('store', '1')
            || $waited < (float)$this->paramsProvider->getLogParameterValue('lock_wait_threshold', '5')
        ) {
            return;
        }

        $this->logger->warning('CL', [
            'service' => $this->getServiceName($WSRequest),
            'action' => $WSRequest->getCustomAction() . ':lock_wait',
            'clientip' => $this->requestStack->getCurrentRequest()?->getClientIp(),
            'error' => sprintf('Waited %.1fs for cache lock', $waited),
            'duration' => $waited,
            'uri' => $WSRequest->action,
        ]);
    }

    private function getServiceName(WSRequest $WSRequest): string
    {
        $webservice = $WSRequest->webService;
        if ($WSRequest->subService) {
            $webservice .= '-' . $WSRequest->subService;
        }

        return $webservice;
    }
}
