<?php

namespace Openpay\Payment\Api;

interface TokenizationApiManagementInterface
{
    /**
     * get tokenization Api data.
     *
     * @api
     *
     * @param int $cartId
     * @param string $email
     *
     * @return string
     */
    public function getTokenizationData($cartId, $email);
}
