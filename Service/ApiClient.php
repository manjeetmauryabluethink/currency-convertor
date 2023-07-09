<?php

declare(strict_types=1);

namespace Bluethinkinc\CurrencyConvertor\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request as GuzzleHttpRequest;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ApiClient
 */
class ApiClient
{
    /**
     * API request URL
     */
    const API_REQUEST_URI = 'https://api.apilayer.com/';

    /**
     * API request endpoint
     */
    const API_REQUEST_ENDPOINT = 'exchangerates_data/convert?';

     /**
     * API Access Key
     */
    const API_ACCESS_KEY = 'currency/freecurrencyapi/api_key';

     /**
     * from change like USD
     */
    const FROM = 'currency/freecurrencyapi/from';

     /**
     * to means change currenct like INR
     */
    const TO = 'currency/freecurrencyapi/to';

     /**
     * enter amount 
     */
    const AMOUNT = 'currency/freecurrencyapi/amount';

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

     /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * GitApiService constructor
     *
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param SerializerInterface $serializer
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        SerializerInterface $serializer,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Fetch some data from API
     */
    public function execute()
    {
        $response = $this->doRequest(static::API_REQUEST_ENDPOINT);
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();
        $responseArrayContent = $this->serializer->unserialize($responseContent);
        return $responseArrayContent;
        // here you will have the API response in JSON format
        // Add your logic using $responseContent
    }

    /**
     * Do API request with provided params
     *
     * @param string $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     *
     * @return Response
     */
    private function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ): Response {
        /** @var Client $client */
        $client = $this->clientFactory->create();
        $headers = [
            'content-type' => 'application/json'
        ];

        $url = self::API_REQUEST_URI;
        $endPoint = self::API_REQUEST_ENDPOINT;
        $accessKey = $this->scopeConfig->getValue(
            self::API_ACCESS_KEY,
            ScopeInterface::SCOPE_STORE
        );
        $from = $this->scopeConfig->getValue(
            self::FROM,
            ScopeInterface::SCOPE_STORE
        );
        $to = $this->scopeConfig->getValue(
            self::TO,
            ScopeInterface::SCOPE_STORE
        );
        $amount = $this->scopeConfig->getValue(
            self::AMOUNT,
            ScopeInterface::SCOPE_STORE
        );
        $parameters = [
            'apikey' => $accessKey,
            'from' => $from,
            'to' => $to,
            'amount'=>$amount
        ];
        $data = http_build_query($parameters);
        $urlCmpt = $url.$endPoint.$data;
        try {
            $response = $client->send(
                new GuzzleHttpRequest(
                    $requestMethod,
                    $urlCmpt,
                    $headers
                )
            );
        } catch (GuzzleException $exception) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }
}
