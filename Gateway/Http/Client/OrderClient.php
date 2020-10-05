<?php
namespace Iugu\Payment\Gateway\Http\Client;

use Iugu\Payment\Model\Api\IuguCharge;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * Class Client
 */
class OrderClient implements ClientInterface
{
    const URI_SUFFIX = '/charge';

    /**
     * @var IuguCharge
     */
    private $apiCharge;

    /**
     * @var ZendClientFactory
     */
    private $clientFactory;

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Client constructor.
     * @param ZendClientFactory $clientFactory
     * @param Logger $logger
     * @param IuguCharge $apiCharge
     * @param ConverterInterface|null $converter
     */
    public function __construct(
        ZendClientFactory $clientFactory,
        Logger $logger,
        IuguCharge $apiCharge,
        ConverterInterface $converter = null
    ) {
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
        $this->converter = $converter;
        $this->apiCharge = $apiCharge;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     * @throws ClientException
     * @throws ConverterException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $result = [];
        $log = [];
        try {
            $response = $this->apiCharge->charge($transferObject->getBody());

            $body = $this->converter
                ? $this->converter->convert($response->getBody())
                : json_decode($response->getBody());

            $result = [
                'code' => $response->getStatus(),
                'body' => $this->objectToArray($body)
            ];

            $log['response'] = $result;
        } catch (\Zend_Http_Client_Exception $exception) {
            throw new ClientException(__($exception->getMessage()));
        } catch (ConverterException $exception) {
            throw $exception;
        } finally {
            $this->logger->debug($log, null, true);
        }

        return $result;
    }

    private function objectToArray($obj)
    {
        $array = (array) $obj;
        foreach ($array as &$attribute) {
            if (is_object($attribute) || is_array($attribute)) {
                $attribute = $this->objectToArray($attribute);
            }
        }
        return $array;
    }
}
