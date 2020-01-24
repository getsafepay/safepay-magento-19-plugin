<?php
class Safepay_Safepay_Helper_Data extends Mage_Core_Helper_Abstract
{
    /** @var string Safepay sandbox API url. */
    public static $sandbox_api_url = 'https://sandbox.api.getsafepay.com/';

    /** @var string Safepay production API url. */
    public static $production_api_url = 'https://api.getsafepay.com/';

    /** @var string Safepay init transaction endpoint. */
    public static $init_transaction_endpoint = "order/v1/init";

    /** @var string Safepay sandbox API key. */
    

    const SANDBOX = "sandbox";

    const PRODUCTION = "production";

    const PRODUCTION_CHECKOUT_URL     = "https://www.getsafepay.com/components";
    const SANDBOX_CHECKOUT_URL        = "https://sandbox.api.getsafepay.com/components";

    /**
     * Get the response from an API request.
     * @param  string $endpoint
     * @param  array  $params
     * @param  string $method
     * @return array
     */
    public static function send_request($environment = "sandbox", $endpoint = "", $params = array(), $method = 'GET')
    {
        
        $baseURL = $environment === self::SANDBOX ? self::$sandbox_api_url : self::$production_api_url;
        $url = $baseURL . $endpoint;

        $data_string = json_encode($params);                                                                                   
                                                                                                                            
        $ch = curl_init($url);                                                                      
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($data_string))                                                                       
        );                                                                                                                   
                                                                                                                            
        $resp = curl_exec($ch);
       // Close request to clear up some resources
        curl_close($curl);
        
        $response	 = json_decode($resp);
        
        
        
        if ($response->status->message == 'success') {
           return $response;
        } else {
           return 'error';
        }
    }

    /**
     * Create a new charge request.
     * @param  int    $amount
     * @param  string $currency
     * @param  array  $metadata
     * @param  string $redirect
     * @param  string $name
     * @param  string $desc
     * @param  string $cancel
     * @return array
     */
    public static function create_charge($amount = null, $currency = null, $environment = "sandbox")
    {
        $args = array(
            "environment" => $environment
        );

        if (is_null($amount)) {
            return array(false, "Missing amount");
        }
        $args["amount"] = floatval($amount);

        if (is_null($currency)) {
            return array(false, "Missing currency");
        }
        $args["currency"] = $currency;

        $client = "";
        if ($environment === self::SANDBOX) {
            $client = Mage::getStoreConfig('payment/safepay/sandbox_key');
        } else if ($environment === self::PRODUCTION) {
            $client = Mage::getStoreConfig('payment/safepay/production_key');
        } else {
            return array(false, "Invalid environment");
        }

        if ($client === "") {
            return array(false, "Missing client");
        }
        $args["client"] = $client;

        $result = self::send_request($environment, self::$init_transaction_endpoint, $args, 'POST');
        
        return $result;
    }

    public function get_environment()
    {
        return Mage::getStoreConfig('payment/safepay/sandbox') ? self::SANDBOX : self::PRODUCTION;
    }

    public function get_shared_secret()
    {
        $key = Mage::getStoreConfig('payment/safepay/sandbox') ? Mage::getStoreConfig('payment/safepay/sandbox_webhook_secret') : Mage::getStoreConfig('payment/safepay/production_webhook_secret');
        return $key;
    }


    public function construct_url($order, $tracker="")
        {
            $baseURL = Mage::getStoreConfig('payment/safepay/sandbox') ? self::SANDBOX_CHECKOUT_URL : self::PRODUCTION_CHECKOUT_URL;
            $order_id = $order->getId();
            $params = array(
                "env"            => Mage::getStoreConfig('payment/safepay/sandbox') ? self::SANDBOX : self::PRODUCTION,
                "beacon"         => $tracker,
                "source"         => 'magento',
                "order_id"       => $order_id,
                "nonce"          => 'magento_order_id'. $order_id,
                "redirect_url"   => Mage::getUrl('safepay/payment/response'),
                "cancel_url"     => Mage::getUrl('safepay/payment/cancel')
            );

           $baseURL = $baseURL.'/?'.http_build_query($params);

            return $baseURL;
        }

        public function validate_signature($tracker, $signature)
        {
            $secret = $this->get_shared_secret();
            $signature_2 = hash_hmac('sha256', $tracker, $secret);

            if ($signature_2 === $signature) {
                return true;
            }

            return false;
        }
        
}