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
        $webservice = $WSRequest->webService;
        if ($WSRequest->subService) {
            $webservice .= '-' . $WSRequest->subService;
        }

        if ($this->maskSensitiveData === null) {
            $this->maskSensitiveData = (bool)$this->paramsProvider->getLogParameterValue('mask_sensitive_data');
        }
        if ($this->maskSensitiveMemberPII === null) {
            $this->maskSensitiveMemberPII = (bool)$this->paramsProvider->getLogParameterValue('mask_sensitive_member_pii');
        }

        $requestStringParts = [];
        $requestOptions = $WSRequest->getOptions();
        foreach (['query', 'json', 'body'] as $field) {
            if (!empty($requestOptions[$field])) {
                $value = $requestOptions[$field];
                if ($field === 'body' && !empty($value['body'])) {
                    $value = $value['body'];
                }
                try {
                    $requestStringParts[] = is_string($value)
                        ? $value
                        : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    $requestStringParts[] = $e->getMessage();
                }
            }
        }
        $requestString = implode("\n\n", $requestStringParts);

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
            'action' => $WSRequest->getCustomAction(),
            'clientip' => $currentRequest?->getClientIp(),
            'request' => $requestString,
            'response' => $responseBody,
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
}
