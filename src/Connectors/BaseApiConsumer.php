<?php
namespace Sync\Connectors;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

abstract class BaseApiConsumer implements ApiConsumerInterface
{
    protected string $token;
    protected string $baseUrl;

    public function __construct(string $token, string $baseUrl){
        $this->token = $token;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $method
     * @return ResponseInterface
     * @throws ApiConsumerException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function baseApiRequest(string $method):ResponseInterface
    {
        $client = new Client();

        if(substr($method,0,4) === 'http'){
            $url = $method;
        }else{
            $url = $this->baseUrl.$method;
        }

        $response = $client->request('GET',$url,[
            'headers'=>[
                'Accept'=>'application/json',
                'Authorization'=>'Bearer '.$this->token,
            ],
            'verify'=>false
        ]);

        $body = $response->getBody()->getContents();
        if($response->getStatusCode() !== 200){
            if($body !== null && isset(json_decode($body)->error_description)){
                throw new ApiConsumerException('['.$method.'] API Error: ' . $body);
            }else{
                throw new ApiConsumerException('['.$method.'] API Error: '. json_encode($response));
            }
        }
        return $response;
    }

    /**
     * @param string $method
     * @return \stdClass|\stdClass[]
     */
    abstract function makeApiRequest(string $method);

    /**
     * @return int
     */
    abstract function getIntegrationType():int;
}
