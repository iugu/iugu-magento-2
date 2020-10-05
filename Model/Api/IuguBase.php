<?php
namespace Iugu\Payment\Model\Api;

use Magento\Framework\DataObject;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Gateway\ConfigInterface;
use Zend_Http_Client_Exception;
use Zend_Http_Response;

class IuguBase extends DataObject
{
    const METHOD_POST = \Zend_Http_Client::POST;

    const METHOD_GET = \Zend_Http_Client::GET;

    const BODY = 'body';
    /**
     * @var ZendClientFactory
     */
    private $clientFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     * @param ZendClientFactory $clientFactory,
     * @param array $data
     */
    public function __construct(
        ConfigInterface $config,
        ZendClientFactory $clientFactory,
        array $data = []
    ) {
        $this->clientFactory = $clientFactory;
        $this->config = $config;
        parent::__construct($data);
    }

    /**
     * @param $postfix
     * @param array $params
     * @param string $method
     * @return Zend_Http_Response
     * @throws Zend_Http_Client_Exception
     */
    protected function execute($postfix, $params = [], $method = self::METHOD_POST)
    {
        $client = $this->clientFactory->create();
        $client->setMethod($method);
        $client->setRawData($this->getData(self::BODY));
        $client->setHeaders($this->getHeaders());

        $uri = $this->getConfigBaseUri() . $this->getConfigPostfix($postfix);
        $uri = str_replace(array_keys($params), $params, $uri);
        $uri .= (parse_url($uri, PHP_URL_QUERY) ? '&' : '?') . 'rand=' . uniqid();

        $client->setUri($uri);

        return $client->request();
    }

    public function setBody($body)
    {
        $this->setData(self::BODY, $body);
    }

    private function getHeaders()
    {
        $ambient = $this->config->getValue('ambient');
        $api_key = $this->config->getValue($ambient . '_api_key');
        $encoded_key = base64_encode($api_key . ':');
        return [
            "Authorization" => "Basic " . $encoded_key,
            "Content-Type" => "application/json",
            "Cache-Control" => "no-cache"
        ];
    }

    private function getConfigBaseUri()
    {
        return $this->config->getValue('api_uri');
    }

    protected function getConfigPostfix($postfix)
    {
        return $this->config->getValue($postfix);
    }
}
