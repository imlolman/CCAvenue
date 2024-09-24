<?php

namespace Imlolman\CCAvenue\Methods;

use Imlolman\CCAvenue\BaseClass;

class Transaction extends BaseClass
{
    protected $responseArray = [];

    /**
     * Initiates a CCAvenue transaction.
     *
     * @param array $compulsoryInfo An array containing compulsory information like 'order_id', 'amount', 'currency', 'redirect_url', 'cancel_url', 'language', etc.
     * @param array|null $billingInfo An optional array containing billing details like 'billing_name', 'billing_address', 'billing_city', 'billing_state', 'billing_zip', 'billing_country', 'billing_tel', and 'billing_email'.
     * @param array|null $shippingInfo An optional array containing shipping details like 'delivery_name', 'delivery_address', 'delivery_city', 'delivery_state', 'delivery_zip', 'delivery_country', and 'delivery_tel'.
     * @param array|null $merchantParams An optional array for merchant parameters like 'merchant_param1', 'merchant_param2', 'merchant_param3', 'merchant_param4', and 'merchant_param5'.
     * @param array|null $otherParams Additional optional parameters like 'promo_code' and 'customer_identifier'.
     *
     * @return string The transaction URL to initiate the payment.
     */
    public function initiate(array $compulsoryInfo, array $billingInfo = null, array $shippingInfo = null, array $merchantParams = null, array $otherParams = null)
    {
        // Combine all the inputs into a single array
        $formData = array_merge($compulsoryInfo, $billingInfo ?? [], $shippingInfo ?? [], $merchantParams ?? [], $otherParams ?? [], [
            'merchant_id' => $this->config['MERCHANT_ID'],
            'integration_type' => 'iframe_normal',
        ]);

        // Convert the data into a URL encoded query string
        $merchantData = '';
        foreach ($formData as $key => $value) {
            $merchantData .= $key . '=' . $value . '&';
        }

        // Encrypt the data using the inherited encrypt method and working key
        $encryptedData = $this->encrypt($merchantData);

        // Build the payment URL
        $paymentUrl = 'https://' . $this->config['HOST'] . '/transaction/transaction.do?command=initiateTransaction&encRequest=' . $encryptedData . '&access_code=' . $this->config['ACCESS_CODE'];

        // Return the payment URL
        return $paymentUrl;
    }

    /**
     * Verifies the response received from CCAvenue.
     *
     * @return array|bool An associative array containing the response data if the response is verified successfully, false otherwise.
     *
     * The array will contain the following keys:
     *
     * - 'order_id': The unique ID of the order.
     * - 'tracking_id': The transaction's tracking ID provided by the payment gateway.
     * - 'bank_ref_no': The bank reference number for the transaction.
     * - 'order_status': The status of the transaction (e.g., 'Success', 'Failure').
     * - 'failure_message': A message explaining the reason for a failed transaction, if applicable.
     * - 'payment_mode': The mode of payment used (e.g., 'Credit Card', 'Net Banking').
     * - 'card_name': The card used in the transaction (if applicable).
     * - 'status_code': Status code of the transaction (may be null).
     * - 'status_message': Detailed status message, usually explaining success or failure.
     * - 'currency': The currency of the transaction (e.g., 'INR').
     * - 'amount': The amount for the transaction.
     * - 'billing_name': The name of the billing customer.
     * - 'billing_address': The billing address.
     * - 'billing_city': The billing city.
     * - 'billing_state': The billing state.
     * - 'billing_zip': The billing ZIP/postal code.
     * - 'billing_country': The billing country.
     * - 'billing_tel': The billing contact number.
     * - 'billing_email': The billing email address.
     * - 'delivery_name': The name of the recipient for shipping (if different from billing).
     * - 'delivery_address': The delivery address.
     * - 'delivery_city': The delivery city.
     * - 'delivery_state': The delivery state.
     * - 'delivery_zip': The delivery ZIP/postal code.
     * - 'delivery_country': The delivery country.
     * - 'delivery_tel': The delivery contact number.
     * - 'merchant_param1' to 'merchant_param5': Any additional parameters sent by the merchant during the transaction.
     * - 'vault': Indicates if vault service is used ('Y' or 'N').
     * - 'offer_type': The type of offer applied, if any.
     * - 'offer_code': The offer code applied, if any.
     * - 'discount_value': The value of the discount applied, if any.
     * - 'mer_amount': The amount payable to the merchant.
     * - 'eci_value': The ECI value for the transaction (may be null).
     * - 'retry': Indicates if the transaction was retried ('Y' or 'N').
     * - 'response_code': Response code for the transaction.
     * - 'billing_notes': Any additional billing notes.
     * - 'trans_date': The date and time of the transaction.
     * - 'bin_country': The country of the card issuing bank.
     * - 'auth_ref_num': The authorization reference number.
     */
    public function verifyAndGetResponse()
    {
        try {
            if (!isset($_REQUEST['encResp'])) {
                throw new \Exception('No encrypted response found');
            }

            // Decrypt the response
            $rcvdString = $this->decrypt($_REQUEST['encResp']);
            $decryptValues = explode('&', $rcvdString);
            $dataSize = sizeof($decryptValues);

            // Parse the decrypted response into an associative array
            $response = [];
            for ($i = 0; $i < $dataSize; $i++) {
                $information = explode('=', $decryptValues[$i]);
                $response[$information[0]] = $information[1];
            }

            // Validate required fields
            if (!isset($response['order_id']) || !isset($response['order_status']) || !isset($response['amount'])) {
                throw new \Exception('Signature verification failed: required fields missing');
            }

            $this->responseArray = $response;

            return $response;
        } catch (\Exception $e) {
            throw new \Exception('Signature verification failed for CCAvenue: ' . $e->getMessage());
        }
    }

    /**
     * Verifies the response received from CCAvenue and returns response if successful.
     *
     * @return bool Returns true if the response is successfully verified and decrypted, false otherwise.
     */
    public function verifyAndGetSuccessResponse()
    {
        try{
            $response = $this->verifyAndGetResponse();

            if($response && $response['order_status'] === 'Success'){
                return $response;
            } else {
                throw new \Exception('Response verified but: order status is not Success');
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verifies the response received from CCAvenue and sets the responseArray.
     *
     * @return bool Returns true if the response is successfully verified and decrypted, false otherwise.
     */
    public function verifyResponse()
    {
        $this->verifyAndGetResponse()? true : false;
    }

    /**
     * Check if the order status is 'Success'.
     *
     * @return bool Returns true if the order status is 'Success', false otherwise.
     */
    public function checkIfOrderIsSuccess()
    {
        return isset($this->responseArray['order_status']) && $this->responseArray['order_status'] === 'Success';
    }

    /**
     * Get the order ID from the response.
     *
     * @return string|null Returns the order ID or null if not available.
     */
    public function getOrderId()
    {
        return $this->responseArray['order_id'] ?? null;
    }

    /**
     * Get the amount of the order from the response.
     *
     * @return float|null Returns the amount as a float, or null if not available.
     */
    public function getOrderAmount()
    {
        return isset($this->responseArray['amount']) ? (float) $this->responseArray['amount'] : null;
    }

    /**
     * Get the currency of the order from the response.
     *
     * @return string|null Returns the currency of the order, or null if not available.
     */
    public function getOrderCurrency()
    {
        return $this->responseArray['currency'] ?? null;
    }

    /**
     * Get the payment mode used in the transaction.
     *
     * @return string|null Returns the payment mode (e.g., 'Net Banking', 'Credit Card'), or null if not available.
     */
    public function getPaymentMode()
    {
        return $this->responseArray['payment_mode'] ?? null;
    }

    /**
     * Get the bank reference number from the response.
     *
     * @return string|null Returns the bank reference number, or null if not available.
     */
    public function getBankReferenceNumber()
    {
        return $this->responseArray['bank_ref_no'] ?? null;
    }

    /**
     * Get the tracking ID of the transaction.
     *
     * @return string|null Returns the tracking ID, or null if not available.
     */
    public function getTrackingId()
    {
        return $this->responseArray['tracking_id'] ?? null;
    }

    /**
     * Get the billing information from the response.
     *
     * @return array|null Returns an associative array of billing info (name, address, city, etc.) or null if not available.
     */
    public function getBillingInfo()
    {
        return [
            'billing_name' => $this->responseArray['billing_name'] ?? null,
            'billing_address' => $this->responseArray['billing_address'] ?? null,
            'billing_city' => $this->responseArray['billing_city'] ?? null,
            'billing_state' => $this->responseArray['billing_state'] ?? null,
            'billing_zip' => $this->responseArray['billing_zip'] ?? null,
            'billing_country' => $this->responseArray['billing_country'] ?? null,
            'billing_tel' => $this->responseArray['billing_tel'] ?? null,
            'billing_email' => $this->responseArray['billing_email'] ?? null,
        ];
    }

    /**
     * Get the delivery information from the response.
     *
     * @return array|null Returns an associative array of delivery info (name, address, city, etc.) or null if not available.
     */
    public function getDeliveryInfo()
    {
        return [
            'delivery_name' => $this->responseArray['delivery_name'] ?? null,
            'delivery_address' => $this->responseArray['delivery_address'] ?? null,
            'delivery_city' => $this->responseArray['delivery_city'] ?? null,
            'delivery_state' => $this->responseArray['delivery_state'] ?? null,
            'delivery_zip' => $this->responseArray['delivery_zip'] ?? null,
            'delivery_country' => $this->responseArray['delivery_country'] ?? null,
            'delivery_tel' => $this->responseArray['delivery_tel'] ?? null,
        ];
    }

    /**
     * Check if there was any failure message in the response.
     *
     * @return string|null Returns the failure message, or null if no failure.
     */
    public function getFailureMessage()
    {
        return $this->responseArray['failure_message'] ?? null;
    }

    /**
     * Get the merchant parameters that were passed during the transaction.
     *
     * @return array Returns an array of merchant parameters from 1 to 5.
     */
    public function getMerchantParams()
    {
        return [
            'merchant_param1' => $this->responseArray['merchant_param1'] ?? null,
            'merchant_param2' => $this->responseArray['merchant_param2'] ?? null,
            'merchant_param3' => $this->responseArray['merchant_param3'] ?? null,
            'merchant_param4' => $this->responseArray['merchant_param4'] ?? null,
            'merchant_param5' => $this->responseArray['merchant_param5'] ?? null,
        ];
    }
}
