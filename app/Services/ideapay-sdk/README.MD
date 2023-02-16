# IdeaPay SDK

This IdeaPay Software Development Kit (SDK) is distributed for PHP users who wish to take advantage of the services provided by IdeaPay.

Transactions supported are as follows

  - Simple Payment or Fund Transfer
  - E-Commerce application
  - Refunds

## Usage

IdeaPay SDK requires [PHP](https://php.net/) v7.4.0 or above.

To get started, include the following in your code:
```sh
require "ideapay-sdk/load.php";
```

This will load all necessary assets required by the SDK.

### Setting Client Credentials

IdeaPay requires all clients to have their own ID and Secret values to be passed for validation. As such, follow the code below to pass on the client id and secret.

```sh
$client = new Pay(array(
    "client_id"     =>  CLIENT_ID
    "client_secret" =>  CLIENT_SECRET
));
```

...the same code format is followed by both EC() for e-commerce & Refund() for refunds.

### Simple Payment

For simple payments with no orders attached, simply follow the code below. To start off, we need to fetch an instance of the IdeaPay's client handler passing our client id & secret as shown previously.

As in any transaction, we need to fill up the customer information.
```sh
$client->set_customer_info(array(
    "first_name"    =>  FIRST_NAME,
    "last_name"     =>  LAST_NAME
    "email_address" =>  EMAIL_ADDRESS
));
```

Optional customer information properties:
| Key | Description |
| ------ | ------ |
| middle_name | customer middle name |
| phone_number | customer telephone number |
| mobile_number | customer mobile number |
| date_of_birth | customer date of birth |

Now we need to pass how much we are sending and at what currency by filling up the billing information.

```sh
$client->set_billing_info(array(
    "currency"  =>  "PHP"
    "amount"    =>  "1.00"
));
```
### E-Commerce Transaction

Usage is derived from simple payments but with the addition of orders. As such, fetch the instance of our client handler as follows...
```sh
$client = new EC(array(
    "client_id"     =>  CLIENT_ID
    "client_secret" =>  CLIENT_SECRET
));
```

Fill the customer information as usual...
```sh
$client->set_customer_info(array(
    "first_name"    =>  FIRST_NAME,
    "last_name"     =>  LAST_NAME
    "email_address" =>  EMAIL_ADDRESS
));
```

This time, completely fill the billing information but without the amount indicated..
```sh
$client->set_billing_info(array(
    "address_line1" =>  ADDRESS_LINE_1,
    "city"          =>  CITY,
    "state"         =>  STATE,
    "country"       =>  COUNTRY,
    "currency"      =>  "PHP"
));
```
Optional billing information properties:
| Key | Description |
| ------ | ------ |
| address_line2 | customer address line 2 |
| postal_code | customer postal code |
| discount | order discount amount |

Now we need to fill up our order with items.
```sh
$client->add_item(array(
    "name"      =>  ITEMNAME,
    "value"     =>  ITEMVALUE,
    "quantity"  =>  ITEMQUANTITY
));
```

#### Sending our request

With all the required information filled up, we can now send our request to IdeaPay.

```sh
$redirect_url = $client->send();
```

After sending the request, our client handler will return a URL string pointing to the next step, as well as store the payment ID assigned to the request.

#### Payment ID

The payment ID is the identifier returned by IdeaPay referring to the recent transaction request. It is advisable to store this somewhere safe for future requests.

```sh
$payment_id = $client->get_payment_id();
``` 

The payment ID can be assigned to the current payment by adding the following code:

```sh
$client->set_payment_id();
```

This will attempt to resume the payment session assigned to this ID made previously. 

#### Origin URI

The Origin URI is the URI used by IdeaPay to redirect users after a request succeeds or is canceled. It is also the URI used to send the POST notification to inform of the payment's success.

By default, the Origin URI is set to the currently used URI. For special occasions, however, the Origin URI can be manually set by the following code:

```sh
$client->set_origin($new_origin_uri);
```
 
wherein $new_origin_uri is the custom Origin URI.

### Refunds

The refund is simple. It only requires the payment ID acknowledged by IdeaPay. But first, we need to fetch an instance of our client handler...

```sh
$client = new Refund(array(
    "client_id"     =>  CLIENT_ID
    "client_secret" =>  CLIENT_SECRET
));
```

..attach our payment ID...

```sh
$client->set_payment_id(PAYMENT_ID);
```

..then send it on its way.

```sh
$client->send();
```
Thanks for using IdeaPay! Have a nice day!