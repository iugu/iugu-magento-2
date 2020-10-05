<?php

namespace Iugu\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class ResponseSuccessValidator extends AbstractValidator
{
    /**
     * Performs validation of result code
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];

        if ($this->isSuccessfulTransaction($response)) {
            return $this->createResult(
                true,
                []
            );
        } else {
            $errors = [];
            if (!empty($response['body']['errors']) && is_array($response['body']['errors'])) {
                foreach ($response['body']['errors'] as $key => $error) {
                    foreach ($error as $value) {
                        $errors[] = __($key) . ' ' . $value;
                    }
                }
            } elseif (!empty($response['body']['errors'])) {
                $errors[] = $response['body']['errors'];
            }
            return $this->createResult(
                false,
                $errors,
                !empty($response['body']['LR']) ? [$response['body']['LR']] : []
            );
        }
    }

    /**
     * @param array $response
     * @return bool
     */
    private function isSuccessfulTransaction(array $response)
    {
        return !empty($response['body']['success']);
    }
}
