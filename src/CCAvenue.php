<?php

namespace Imlolman\CCAvenue;

use Imlolman\CCAvenue\Methods\Transaction;

class CCAvenue
{
    private static $instance;
    private $config;

    private function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Get the instance of the CCAvenue class, which is initialized using the init() method and stored in the static variable
     * 
     * @return CCAvenue
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            throw new \Exception("CCAvenue not initialized");
        }

        return static::$instance;
    }

    /**
     * Initialize the CCAvenue class
     * 
     * @param string $merchantId
     * @param string $accessCode
     * @param string $workingKey
     * @param string $mode // DEV or PROD
     * @param string $redirectUrl // Can be defined on per transaction basis
     * @param string $callbackUrl // Can be defined on per transaction basis
     * @param string $redirectMode // redirect or post
     */
    public static function init($merchantId, $accessCode, $workingKey, $mode = "PROD", $redirectUrl = "", $callbackUrl = "", $redirectMode = "redirect")
    {
        if (null === static::$instance) {
            if ($mode == "PROD") {
                $host = "secure.ccavenue.com";
            } else {
                $host = "test.ccavenue.com";
            }

            static::$instance = new static([
                "MERCHANT_ID" => $merchantId,
                "ACCESS_CODE" => $accessCode,
                "WORKING_KEY" => $workingKey,
                "HOST" => $host,
                "REDIRECT_URL" => $redirectUrl,
                "CALLBACK_URL" => $callbackUrl,
                "REDIRECT_MODE" => $redirectMode
            ]);
        }

        return static::$instance;
    }

    /**
     * Get the config of the CCAvenue class
     * 
     * @param string $key
     * 
     * @return array
     */
    public function getConfig($key = NULL)
    {
        if ($key) {
            return $this->config[$key];
        }

        return $this->config;
    }

    /**
     * Get the recurring checkout method
     * 
     * @return Transaction
     */
    public function getTransaction()
    {
        return new Transaction();
    }
}
