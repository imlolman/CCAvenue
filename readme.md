# CCAvenue Payment Gateway PHP Integration Guide 🎉

Welcome to the **CCAvenue Payment Gateway Integration** guide! If you're here, you're likely navigating the maze that is CCAvenue integration.

Read the entire guide to have a good and comedic experience. Star the repository if you like it.
Fear not! I've been through the trenches, and I'm here to make your journey smoother (and maybe even enjoyable!).

## Table of Contents

- [The Struggle is Real 😫](#the-struggle-is-real-)
- [Getting Started 🚀](#getting-started-)
- [Integration Steps 🛠️](#integration-steps-️)
  - [1. Initiating a Transaction](#1-initiating-a-transaction)
  - [2. Handling the Response](#2-handling-the-response)
  - [3. Setting Up the Webhook](#3-setting-up-the-webhook)
- [Available Methods 🧰](#available-methods-)
- [Understanding the Flow 🧭](#understanding-the-flow-)
- [Final Thoughts 🤔](#final-thoughts-)

---

## The Struggle is Real 😫

Let's be honest: integrating CCAvenue can be a **nightmare**. Here's why:

1. **Terrible Portal UI**: It's clunky, slow, and feels like it's from the early 2000s.

2. **No Test Credentials Online**: Unlike modern gateways, you can't just generate test credentials. You have to **email them**. Yes, in 2023.

3. **Merchant Authentication Failed (Error 10002)**: This error will haunt you until you realize you need to **whitelist your testing domain**, including `localhost`. Again, via email.

4. **Chat Support? Meh**: It's there, but don't expect miracles. You'll often need to pick up the phone.

5. **Communication is Key**: Your best bet is to **call them directly** at **(+91 8801033323)** or email **service@ccavenue.com**.

6. **Same Credentials for Test & Production**: Proceed with caution! You don't want to mix up test and live transactions.

---

## Getting Started 🚀

Before diving in, make sure you have:

- A **verified CCAvenue account** (patience is a virtue here).
- **Test Credentials** (remember, email them to get these).
- A development environment with **PHP (latest version)** or **Laravel**.

---

## Integration Steps 🛠️

### 1. Installing this Library

First, install the `imlolman/ccavenue-php-sdk` package via Composer:

```bash
composer require imlolman/ccavenue-php-sdk
```

### 2. Initiating a Transaction

Here's how you start a transaction with CCAvenue:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
use Imlolman\CCAvenue\CCAvenue;

$merchantId = "your_merchant_id";
$accessCode = "your_access_code";
$workingKey = "your_working_key";
$mode = "DEV"; // Use "PROD" for production

$ccavenue = CCAvenue::init($merchantId, $accessCode, $workingKey, $mode);

$transaction = $ccavenue->getTransaction();

$compulsoryInfo = [
    'order_id' => 'ORDER12345',
    'amount' => '100.00',
    'currency' => 'INR',
    'redirect_url' => 'http://yourdomain.com/response.php',
    'cancel_url' => 'http://yourdomain.com/cancel.php',
    'language' => 'EN'
];

$billingInfo = [
    'billing_name' => 'Jane Doe',
    'billing_address' => '123 Main Street',
    'billing_city' => 'Mumbai',
    'billing_state' => 'Maharashtra',
    'billing_zip' => '400001',
    'billing_country' => 'India',
    'billing_tel' => '9876543210',
    'billing_email' => 'jane@example.com'
];

$shippingInfo = [
    'delivery_name' => 'Jane Doe',
    'delivery_address' => '456 Side Street',
    'delivery_city' => 'Delhi',
    'delivery_state' => 'Delhi',
    'delivery_zip' => '110001',
    'delivery_country' => 'India',
    'delivery_tel' => '9876543210'
];

$paymentUrl = $transaction->initiate($compulsoryInfo, $billingInfo, $shippingInfo);

// Redirect to the payment page
header('Location: ' . $paymentUrl);

// If you get 10002 error, you need to whitelist your domain bye contacting CCAvenue via call. Why don't you read entire guide? You will have fun, really. Also don't forget to star the repository.
```

### 3. Handling the Response

After the transaction, CCAvenue will redirect the user to your `response.php` page:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
use Imlolman\CCAvenue\CCAvenue;

$merchantId = "your_merchant_id";
$accessCode = "your_access_code";
$workingKey = "your_working_key";
$mode = "DEV"; // Use "PROD" for production

$ccavenue = CCAvenue::init($merchantId, $accessCode, $workingKey, $mode);

$transaction = $ccavenue->getTransaction();

try {
    $response = $transaction->verifyAndGetSuccessResponse();
    // Process successful response
    // For example, save order details to database
} catch (\Exception $e) {
    // Handle errors here
    echo $e->getMessage();
}
```

### 4. Setting Up the Webhook

To receive automatic payment updates, set up a `webhook.php` file:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
use Imlolman\CCAvenue\CCAvenue;

$merchantId = "your_merchant_id";
$accessCode = "your_access_code";
$workingKey = "your_working_key";
$mode = "PROD"; // Webhooks are only available in production mode

$ccavenue = CCAvenue::init($merchantId, $accessCode, $workingKey, $mode);

$transaction = $ccavenue->getTransaction();

try {
    $response = $transaction->verifyAndGetSuccessResponse();
    // Process webhook response
    // For example, update order status in database
} catch (\Exception $e) {
    // Handle errors here
    echo $e->getMessage();
}
```

**Important**: Email CCAvenue your webhook URL so they can enable webhook support for your account.

---

## Available Methods 🧰

Here's a handy list of methods provided by the `Imlolman\CCAvenue` library:

### `initiate($compulsoryInfo, $billingInfo, $shippingInfo)`

Starts a transaction. Provide the order details, billing info, and shipping info. Returns a payment URL to redirect the customer.

```php
$paymentUrl = $transaction->initiate($compulsoryInfo, $billingInfo, $shippingInfo);
```

### `verifyAndGetResponse()`

Verifies the response from CCAvenue and returns the response array.

```php
$response = $transaction->verifyAndGetResponse();
```

### `verifyAndGetSuccessResponse()`

Verifies the response and checks if the order status is `Success`.

```php
try {
    $response = $transaction->verifyAndGetSuccessResponse();
    // Transaction is successful
} catch (\Exception $e) {
    // Transaction failed
}
```

### `checkIfOrderIsSuccess()`

Checks if the transaction's order status is successful.

```php
$isSuccess = $transaction->checkIfOrderIsSuccess();
```

### `getOrderId()`

Retrieves the order ID from the response.

```php
$orderId = $transaction->getOrderId();
```

### `getOrderAmount()`

Gets the amount of the order.

```php
$amount = $transaction->getOrderAmount();
```

### `getOrderCurrency()`

Retrieves the currency used for the order.

```php
$currency = $transaction->getOrderCurrency();
```

### `getPaymentMode()`

Gets the payment method used (e.g., Net Banking, Credit Card).

```php
$paymentMode = $transaction->getPaymentMode();
```

### `getBankReferenceNumber()`

Retrieves the bank reference number for the transaction.

```php
$bankRefNo = $transaction->getBankReferenceNumber();
```

### `getTrackingId()`

Gets the tracking ID assigned to the transaction by the gateway.

```php
$trackingId = $transaction->getTrackingId();
```

### `getBillingInfo()`

Retrieves the billing details.

```php
$billingInfo = $transaction->getBillingInfo();
// $billingInfo is an associative array
```

### `getDeliveryInfo()`

Retrieves the shipping details.

```php
$deliveryInfo = $transaction->getDeliveryInfo();
// $deliveryInfo is an associative array
```

### `getFailureMessage()`

Gets the failure message if the transaction failed.

```php
$failureMessage = $transaction->getFailureMessage();
```

### `getMerchantParams()`

Retrieves any merchant-specific parameters passed during the transaction.

```php
$merchantParams = $transaction->getMerchantParams();
// $merchantParams is an associative array
```

---

## Understanding the Flow 🧭

Here's how the entire process works:
[![](https://mermaid.ink/img/pako:eNp9Us1qwzAMfhXhyy5tHyCHwmh3XlnoYZCLFiuNIZEzW94Ipe8-NT8NXdl8sS19fxY-m9JbMpmJ9JmIS9o7PAVsCwZdHQZxpeuQBY6RwmP13acAuRN6bO12z1_ESTtj7yqw3m5vjAwODZYU4TXYWfrWVODMz-CNrAtUSoTKBzhg3xLLSJhBayVcDTLYu9g12McZp_uJ7hIswi8sFBbkngRdE38p30VeooiHQLHzHGnT1d0KrOcngdJz5UKrkxg1awoEPcnm8YFj3rz239MM4EBsHZ_-C5ArZEmcC0oasvQKiYoYRjSFQHGe_zQ-dhZFxy81CvjBv8YIH0Q8C5Cd2GZlWlJBZ_WrnK-1wkhNLRUm06OlClMjhSn4olBM4vOeS5NJSLQywadTbbIKm6i3NPhO_2yqXn4AjPjdtw?type=png)](https://mermaid.live/edit#pako:eNp9Us1qwzAMfhXhyy5tHyCHwmh3XlnoYZCLFiuNIZEzW94Ipe8-NT8NXdl8sS19fxY-m9JbMpmJ9JmIS9o7PAVsCwZdHQZxpeuQBY6RwmP13acAuRN6bO12z1_ESTtj7yqw3m5vjAwODZYU4TXYWfrWVODMz-CNrAtUSoTKBzhg3xLLSJhBayVcDTLYu9g12McZp_uJ7hIswi8sFBbkngRdE38p30VeooiHQLHzHGnT1d0KrOcngdJz5UKrkxg1awoEPcnm8YFj3rz239MM4EBsHZ_-C5ArZEmcC0oasvQKiYoYRjSFQHGe_zQ-dhZFxy81CvjBv8YIH0Q8C5Cd2GZlWlJBZ_WrnK-1wkhNLRUm06OlClMjhSn4olBM4vOeS5NJSLQywadTbbIKm6i3NPhO_2yqXn4AjPjdtw)

---

## Final Thoughts 🤔

Integrating CCAvenue isn't for the faint of heart, but with this guide, you're well on your way to mastering it. Remember:

- **Whitelisting**: Email them to whitelist your testing domain and `localhost`.
- **Webhooks**: Send them your webhook URL to receive automatic updates.
- **Patience**: It's a virtue, especially when dealing with CCAvenue.

---

Happy coding! 🎉