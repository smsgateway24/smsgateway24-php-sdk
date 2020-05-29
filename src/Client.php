<?php


namespace SmsGateway24;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use SmsGateway24\Exceptions\SDKException;

class Client
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var Token
     */
    protected $token;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * Client constructor.
     *
     * @param ClientInterface $httpClient
     * @param Token           $token
     * @param string          $baseUrl
     */
    public function __construct(ClientInterface $httpClient, Token $token, string $baseUrl)
    {
        $this->httpClient = $httpClient;
        $this->token = $token;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string     $uri
     * @param array|null $query
     *
     * @return array
     * @throws SDKException
     */
    public function get(string $uri, ?array $query = [])
    {
        $query['token'] = $this->token->value;

        try {
            $response = $this->httpClient->request('GET', $this->baseUrl . $uri, [
                'query' => $query
            ]);
        } catch (GuzzleException $exception) {
            throw new SDKException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $this->parseResponse($response);
    }

    /**
     * @param string     $uri
     * @param array|null $data
     * @param bool       $json
     *
     * @return array
     * @throws SDKException
     */
    public function post(string $uri, ?array $data = [], $json = false)
    {
        $data['token'] = $this->token->value;

        try {
            $response = $this->httpClient->request('GET', $this->baseUrl . $uri, [
                ($json ? 'json' : 'form_params') => $data
            ]);
        } catch (GuzzleException $exception) {
            throw new SDKException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $this->parseResponse($response);
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws SDKException
     *
     * @return array
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $decodedBody = json_decode($response->getBody()->getContents(), true);

        if (isset($decodedBody['error']) and $decodedBody['error'] == 1) {
            $errorMessage = $decodedBody['message'] ?? "Unknown smsgateway24 API error";
            throw new SDKException($errorMessage);
        }

        return $decodedBody;
    }
}