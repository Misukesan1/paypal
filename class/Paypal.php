<?php

class Paypal {

    private $clientId;
    private $clientSecret;
    private $authentification;
    
    public function __construct (string $clientId, string $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }
    
    /**
     * First step before creating one order
     *
     * @return object
     */
    public function generateTokenAuth () :object {
        $response = $this->apiCall('v1/oauth2/token', 'grant_type=client_credentials', 'application/x-www-form-urlencoded', 'POST');
        $this->authentification = $response->access_token;
       
        return $response;
    }
    
    /**
     * Revoke the token after creating one order
     *
     * @return object
     */
    public function revokeTokenAuth () :object {
        if ($this->authentification != null) {
            $postField = "token={$this->authentification}&token_type_hint=ACCESS_TOKEN";
            $response = $this->apiCall('v1/oauth2/token/terminate', $postField, 'application/json', 'POST');
            $this->authentification = null;

            return $response;
        }
    }
    
    /**
     * Create a order and get the url (approve) to proceed the payment
     *
     * @param  int $amount the amount to pay
     * @return object
     */
    public function createOrder (int $amount) :object {
        $postField = '{
            "intent": "CAPTURE",
            "purchase_units": [
                {
                    "amount": {
                        "currency_code": "EUR",
                        "value": "'.$amount.'"
                    }
                }
            ],
            "application_context": {
                "return_url": "http://quentin.local/paypal_tuto/success.php",
                "cancel_url": "http://quentin.local/paypal_tuto/error.php"
            }
        }';
        $response = $this->apiCall("v2/checkout/orders", $postField, 'application/json', 'POST');
        return $response;
    }
        
    /**
     * Informations of order
     *
     * @param  string $orderId property of createOrder object
     * @return object
     */
    public function showOrderDetail (string $orderId) :object {
        $endPoint = "v2/checkout/orders/{$orderId}";
        $response = $this->apiCall($endPoint, null, 'application/json', 'GET');
        return $response;
    }
    
    /**
     * Proceed to payment 
     *
     * @param  string $orderId property of createOrder object
     * @return object
     */
    public function capturePayment (string $orderId) :object {
        $endPoint = "v2/checkout/orders/{$orderId}/capture";
        $response = $this->apiCall($endPoint, null, "application/json", "POST");

        return $response;
    }
        
    /**
     * The Paypal API call 
     * See the paypal restAPI documentation
     *
     * @param  string $endPointUrl the url request of api 
     * @param  string|null $postField the post var to send
     * @param  string $applicationType the content-type
     * @param  string $customRequest the method request
     * @return object
     */
    public function apiCall (string $endPointUrl, string|null $postField, string $applicationType, string $customRequest) :object {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api-m.sandbox.paypal.com/{$endPointUrl}",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => $customRequest,
          CURLOPT_POSTFIELDS => $postField,
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            "Content-Type: {$applicationType}"
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);

        // Display the curl error
        if ($response === false) {
            echo 'Erreur cURL : ' . curl_error($curl);
            return (object) array();
        }

        // The token has been deleted
        if ($response == null) {
            return (object) ["error" => "Le token d'authentification à été révoqué."];
        } else {
            return json_decode($response);
        }
    }
}