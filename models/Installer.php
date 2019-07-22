<?php 
namespace app\models;

use Yii;
use app\models\shopify\Store as ShopifyStore;
use app\models\Shops;

class Installer extends model {

    public static function installationURL($shop){
        $InstallationURL = '';
        
        //if the shop if authorized to install the app one of the list
        if(in_array($shop,Yii::$app->params['authorized_shops'])){
            $nonce= bin2hex(openssl_random_pseudo_bytes(10));
            //generate random string nonce and save in session 
            $_SESSION['nonce'] = $nonce; 
            $apiKey = Yii::$app->params['api_key'];
            $scopes = Yii::$app->params['access_scopes'];
            $appURL = Yii::$app->params['app_URL'];
            $InstallationURL = "https://{$shop}/admin/oauth/authorize?client_id={$apiKey}&scope={$scopes}&redirect_uri={$appURL}auth&state={$nonce}";
        }

        return $InstallationURL;
    }
//validate instalation
    public static function validateShopifyRequest($urlParams){

        //  If the HMAC varaiable is set then a validation can be attempted
		if(isset($urlParams['hmac'])){
			//	Retrieve and remove the 'HMAC' (hash-based message authentication code) from the URL parameters
            $receivedHMAC = $urlParams['hmac'];

            unset($urlParams['hmac']);

			//	Generate URL-encoded query string with the remaining parameters 
            $stringToHash = http_build_query($urlParams);

			//	Generate a keyed hash value using the HMAC method
            $genratedHMAC = hash_hmac('sha256', $stringToHash, Yii::$app->params['api_secret']);

			//	Compare the received 'HMAC' with the genrated one
            $isValid = hash_equals($receivedHMAC, $genratedHMAC);

        }
        
        return $isValid;

    }

    public static function exchangeAuthCodeForToken($code, $shop){

        $exchangeResult = ['success' => true,'shop' => $shop];

        $tokenRequestData = [
            'client_id' => Yii::$app->params['api_key'],
            'client_secret' => Yii::$app->params['api_secret'],
            'code' => $code
        ];

        $shopifyStoreObj = new ShopifyStore($shop);

        $accessTokenData = $shopifyStoreObj->getOAuthAccessToken($tokenRequestData);

        //  If the request was successful
        if(!empty($accessTokenData)){
        
            $accessTokenData['shop'] =  $shop;
            $savingResult = self::saveToken($accessTokenData);

            //  If the token was successfully saved in the database
            if($savingResult['success']){

                $exchangeResult['success'] = true;
            }
            else{
                $exchangeResult['error_details'] = $savingResult['error_details'];
            }
        }
        else{
            $exchangeResult['error_details'] = ['code' => 500, 'details' => null, 'details_code' => 'exchange_failed']; 
        }

        return $exchangeResult;
    }


    

    


    public static function saveToken($shopData){
        $shopObj = new Shops();       

        $shopObj->permanent_domain = $shopData['shop'];
        $shopObj->access_token = $shopData['access_token'];
        $shopObj->scopes = $shopData['scope'];
        
        
        return $shopObj->save();;     

    }
}

