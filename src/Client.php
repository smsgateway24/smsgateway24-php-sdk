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
        $query = $query ?? [];
        $query['token'] = $this->token->value;

        return $this->request('GET', $uri, [
            'query' => $query
        ]);
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
        $data = $data ?? [];
        $data['token'] = $this->token->value;

        return $this->request('POST', $uri, [
            ($json ? 'json' : 'form_params') => $data
        ]);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $options
     *
     * @return array
     * @throws SDKException
     */
    protected function request(string $method, string $uri, array $options)
    {
        // API returns error details in the body even for 4xx/5xx, so read it ourselves.
        $options['http_errors'] = false;

        try {
            $response = $this->httpClient->request($method, $this->baseUrl . $uri, $options);
        } catch (GuzzleException $exception) {
            throw new SDKException($exception->getMessage(), (int)$exception->getCode(), $exception);
        }

        return $this->parseResponse($response);
    }

    /**
     * The API does not use a single error format. Known variants:
     *  - {"error": 1, "message": "..."}                       (addsms, getsmsstatus, savetag, savecontact)
     *  - {"status": "error", "message": "..."}                (getdevicestatus)
     *  - {"error": "wrong token"}                             (savepaket)
     *  - {"result": {"error": true, "msgTitle", "msgBody"}}   (savepaket)
     *
     * @param ResponseInterface $response
     *
     * @throws SDKException
     *
     * @return array
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        $decodedBody = json_decode((string)$response->getBody(), true);

        if (!is_array($decodedBody)) {
            $message = $statusCode >= 400
                ? "smsgateway24 API HTTP error " . $statusCode
                : "Invalid smsgateway24 API response";
            throw new SDKException($message, $statusCode);
        }

        $errorMessage = $this->extractErrorMessage($decodedBody);

        if ($errorMessage === null && $statusCode >= 400) {
            $errorMessage = "smsgateway24 API HTTP error " . $statusCode;
        }

        if ($errorMessage !== null) {
            throw new SDKException($errorMessage, $statusCode >= 400 ? $statusCode : 0);
        }

        return $decodedBody;
    }

    /**
     * @param array $body
     *
     * @return string|null error message, or null if the response is not an error
     */
    protected function extractErrorMessage(array $body): ?string
    {
        $default = "Unknown smsgateway24 API error";

        if (array_key_exists('error', $body)) {
            $error = $body['error'];
            if ($error === 1 || $error === '1' || $error === true) {
                return $this->stringOrDefault($body['message'] ?? null, $default);
            }
            if (is_string($error) && $error !== '' && $error !== '0') {
                return $this->stringOrDefault($body['message'] ?? null, $error);
            }
        }

        if (isset($body['status']) && $body['status'] === 'error') {
            return $this->stringOrDefault($body['message'] ?? null, $default);
        }

        if (isset($body['result']) && is_array($body['result']) && ($body['result']['error'] ?? false) === true) {
            $title = $body['result']['msgTitle'] ?? '';
            $text = $body['result']['msgBody'] ?? '';
            $message = trim($title . ($title !== '' && $text !== '' ? ': ' : '') . $text);

            return $this->stringOrDefault($message, $default);
        }

        return null;
    }

    /**
     * @param mixed  $value
     * @param string $default
     *
     * @return string
     */
    private function stringOrDefault($value, string $default): string
    {
        return is_string($value) && $value !== '' ? $value : $default;
    }
}
