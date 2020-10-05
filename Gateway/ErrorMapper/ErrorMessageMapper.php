<?php
declare(strict_types=1);

namespace Iugu\Payment\Gateway\ErrorMapper;

use Magento\Framework\Config\DataInterface;
use Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface;

class ErrorMessageMapper implements ErrorMessageMapperInterface
{
    /**
     * @var DataInterface
     */
    private $messageMapping;

    /**
     * @param DataInterface $messageMapping
     */
    public function __construct(DataInterface $messageMapping)
    {
        $this->messageMapping = $messageMapping;
    }

    /**
     * @inheritdoc
     * @since 100.2.2
     */
    public function getMessage(string $code)
    {
        $message = $this->messageMapping->get($code);
        return $message ? __($message) : __($code);
    }
}
