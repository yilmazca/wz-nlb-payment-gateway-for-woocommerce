<?php

namespace PaymentGatewayJson\Client\Transaction;

use PaymentGatewayJson\Client\Transaction\Base\AbstractTransactionWithReference;
use PaymentGatewayJson\Client\Transaction\Base\AmountableInterface;
use PaymentGatewayJson\Client\Transaction\Base\AmountableTrait;
use PaymentGatewayJson\Client\Transaction\Base\ItemsInterface;
use PaymentGatewayJson\Client\Transaction\Base\ItemsTrait;

/**
 * Refund: Refund money from a previous Debit (or Capture) transaction to the customer.
 *
 * @note Preauthorized transactions can be reverted with a Void transaction, not a Refund!
 *
 * @package PaymentGatewayJson\Client\Transaction
 */
class Refund extends AbstractTransactionWithReference implements AmountableInterface, ItemsInterface {
    use AmountableTrait;
    use ItemsTrait;

    /** @var string */
    protected $callbackUrl;
    
    /** @var string */
    protected $transactionToken;

    /** @var string */
    protected $description;

    /**
     * @return string
     */
    public function getCallbackUrl() {
        return $this->callbackUrl;
    }

    /**
     * @param string $callbackUrl
     */
    public function setCallbackUrl($callbackUrl) {
        $this->callbackUrl = $callbackUrl;
    }

    /**
     * @return string
     */
    public function getTransactionToken()
    {
        return $this->transactionToken;
    }

    /**
     * @param string $transactionToken
     */
    public function setTransactionToken($transactionToken)
    {
        $this->transactionToken = $transactionToken;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

}