<?php
namespace Sync\OAuth;

use GuzzleHttp\Client;

class OAuthHandler
{
    protected string $id;
    protected string $secret;
    protected string $redirect;
    protected string $tokenUrl;

    protected ?string $token = null;


    /**
     * Provider constructor.
     * @param string $id
     * @param string $secret
     * @param string $redirect
     */
    public function __construct(string $id, string $secret, string $redirect, string $tokenUrl)
    {
        $this->id = $id;
        $this->secret = $secret;
        $this->redirect = $redirect;
        $this->tokenUrl = $tokenUrl;
    }

    /**
     * @throws OAuthException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handleRequest()
    {
        if (!isset($_GET['code'])) {
            throw new OAuthException('Missing "code" parameter');
        }
        $this->getToken($_GET['code']);
    }

    /**
     * @param string $token
     */
    public function setToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * @param string|null $code
     * @return string|null
     * @throws OAuthException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken(string $code = null): ?string
    {
        if ($code === null) {
            return $this->token;
        }
        return $this->doTokenRequest($code);
    }

    protected function doTokenRequest(string $code):string
    {
        $data = [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirect
        ];

        $client = new Client();
        $response = $client->request('POST', $this->tokenUrl, [
            'auth' => [$this->id, $this->secret],
            'form_params' => $data,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'verify' => false,
        ]);

        $body = $response->getBody()->getContents();

        if ($response->getStatusCode() !== 200) {
            if ($body !== null && isset(json_decode($body)->error_description)) {
                throw new OAuthException('Cannot retrieve bearer token: ' . $body);
            } else {
                throw new OAuthException('Cannot retrieve bearer token');
            }
        }

        $response = json_decode($body);
        if (!isset($response->access_token) || !isset($response->token_type) || $response->token_type !== 'bearer') {
            throw new OAuthException('Missing access_token or token_type does not equal "bearer"');
        }

        $this->token = $response->access_token;
        return $this->token;
    }
}
