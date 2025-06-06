<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Logs;

use Hengebytes\WebserviceCoreAsyncBundle\Provider\ParamsProviderInterface;
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
        if (
            !$this->paramsProvider->getLogParameterValue('store', '1')
            || !$this->paramsProvider->getLogParameterValue(
                'store/' . $parsedResponse->mainAsyncResponse->WSRequest->getCustomAction(), '1'
            )
        ) {
            return;
        }

        $currentRequest = $this->requestStack->getCurrentRequest();
        $webservice = $parsedResponse->mainAsyncResponse->WSRequest->webService;
        if ($parsedResponse->mainAsyncResponse->WSRequest->subService) {
            $webservice .= '-' . $parsedResponse->mainAsyncResponse->WSRequest->subService;
        }

        $requestParams = $parsedResponse->mainAsyncResponse->WSRequest->getRequestParams();

        if ($this->maskSensitiveData === null) {
            $this->maskSensitiveData = (bool)$this->paramsProvider->getLogParameterValue('mask_sensitive_data');
        }
        if ($this->maskSensitiveMemberPII === null) {
            $this->maskSensitiveMemberPII = (bool)$this->paramsProvider->getLogParameterValue('mask_sensitive_member_pii');
        }
        $requestString = json_encode($requestParams, JSON_THROW_ON_ERROR);
        $responseBody = $parsedResponse->responseBody;
        if ($this->maskSensitiveData) {
            MaskLogHelper::maskSensitiveVar($requestString, $this->maskSensitiveMemberPII);
            MaskLogHelper::maskSensitiveVar($responseBody, true);
        }
        $responseMaxLength = $this->paramsProvider->getLogParameterValue('max_length', 900000);
        if ($responseBody && strlen($responseBody) > $responseMaxLength) {
            $response = substr($responseBody, 0, $responseMaxLength);
            $responseBody = $response;
        }

        $logContext = [
            'service' => $webservice,
            'action' => $parsedResponse->mainAsyncResponse->WSRequest->getCustomAction(),
            'clientip' => $currentRequest?->getClientIp(),
            'request' => $requestString,
            'response' => $responseBody,
            'error' => $parsedResponse->exception?->getMessage(),
            'duration' => $parsedResponse->mainAsyncResponse->WSResponse->getInfo('total_time'),
            'uri' => $parsedResponse->mainAsyncResponse->WSRequest->action,
        ];

        if ($logContext['error'] !== null) {
            $this->logger->error('CL', $logContext);
        } else {
            $this->logger->info('CL', $logContext);
        }
    }
}