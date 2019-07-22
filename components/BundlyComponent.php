<?php
namespace app\components;

use app\models\Product;
use app\models\Shopify;
use app\models\Bundles;
use app\models\Shops;


class BundlyComponent {

    public static function createProduct($productData){
		$productCreated = false;
        
        //  Try to create the new product
        $shopifyResult = $this->makeRequest('POST', 'products.json', $productData);

		//	If the request was successful
        if($shopifyResult['success']){
			$productCreated = true;
        }
        else{
        	Helper::dataLogger($this->logfile, 'ERROR: Failed to create the following product -> '.print_r($shopifyResult, true), $this->chosenStore);
		}

		return $productCreated;
    }
    
    public static function  updateProduct($productID, $productData){
		$productUpdated = false;
        
        //  Try to update the existing product
        $shopifyResult = $this->makeRequest('PUT', "products/{$productID}.json", ['product' =>$productData]);

		//	If the request was successful
        if($shopifyResult['success']){
			$productUpdated = true;
        }
        else{
        	Helper::dataLogger($this->logfile, 'ERROR: Failed to update the following product -> '.print_r($shopifyResult, true), $this->chosenStore);
		}

		return $productUpdated;
	}

 


}


?>