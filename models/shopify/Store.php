<?php

namespace app\models\shopify;

use yii\log\Logger;

/**
 * Store class provides various general methods which allows to interact with a Shopify store.
 */
class Store extends ShopifyAPI {

	/**
	 * This file will contain detailed messages/errors for debugging purposes.
	 *
	 * @var string $logfile
	 */
	private $logfile = 'shopify_store.log';

	/**
	 * The maximum number of products fetchable with one API call.
	 *
	 * @var integer $apiCallLimit
	 */
	private $apiCallLimit = 250;

	/**
	 * The selected store with which to interact.
	 *
	 * @var string $theShop
	 */
	private $theShop;

	/**
	 * Filter levels constants:
	 */
	//	Not filtered, raw data as received from Shopify
	const RAW_DATA = 0;

	//	Relevant detailed data
	const DETAILED_DATA = 1;

	//	Just basic information
	const BASIC_DATA = 2;

	//	Index handles by barcodes
	const HANDLES_X_BARCODES = 3;

	//	Index handles by record references (sku)
	const HANDLES_X_RECORD_REFERENCES = 4;

	//	Index Shopify product IDs by record references (sku)
	const SHOPIFY_IDS_X_RECORD_REFERENCES = 5;

	/**
     * Constructor
	 * 
	 * @param string $theShop - The selected store with which to interact.
	 * @param string $accessToken - The API access token used to interact with the store's data. By default, it's empty.
     */
	public function __construct($theShop, $accessToken = ''){

		$this->theShop = $theShop;

		//  Run the parent constructor
		parent::__construct($theShop, $accessToken);
	}

	/**
	 * Exchanges the given authorization code for a permanent access token.
	 *
	 * @param array $postData - The data needed to get an access token.
	 * 
	 * @return array $accessTokenData - The access token data. By default, it's empty.
	 */
	public function getOAuthAccessToken($postData){

		$accessTokenData = [];

		//  Trying to get the access token for the given store
		$shopifyResult = $this->makeRequest('POST', 'oauth/access_token', $postData);

		//  If the request was successful
		if($shopifyResult['success']){
			$accessTokenData = $shopifyResult['response'];
		}
		else{
			Logger::trace('ERROR: Failed to get the access token -> '.print_r($shopifyResult, true), $this->theShop);
		}
		
		return $accessTokenData;
	}
	
	/**
	 * Retrieves the numeric identifier for the main theme used for the given store.
	 *
	 * @return integer $mainThemeID - The ID of the main theme.
	 */
	public function getMainThemeID(){

		$mainThemeID = 0;

		//  Trying to get a list of a shop's themes
		$shopifyResult = $this->makeRequest('GET', 'themes.json?'.http_build_query(['role' => 'main']));

		//	If the request was successful
        if($shopifyResult['success']){
            $mainThemeID = $shopifyResult['response']['themes'][0]['id'];
        }
        else{
			Logger::trace('ERROR: Failed to get the main theme\'s ID -> '.print_r($shopifyResult, true), $this->theShop);
		}

		return $mainThemeID;
	}
	
	/**
	 * Uploads (creates or updates) the given asset in the given store.
	 *
	 * @param integer $themeID - The identifier for the theme under which the asset should be created.
	 * @param array $assetData - The name and the content of the given asset.
	 * 
	 * @return boolean $assetUploaded - 'TRUE' if the asset was successfully created or updated, 'FALSE' otherwise.
	 */
    private function uploadAsset($themeID, $assetData){
        $assetUploaded = false;

		//	Trying to create new asset or update an existing one
        $shopifyResult = $this->makeRequest('PUT', "/admin/themes/{$themeID}/assets.json", $assetData);
		
		//	If the request was successful
        if($shopifyResult['success']){
            $assetUploaded = true;
        }
        else{
			Logger::trace('ERROR: Failed to create or update the asset -> '.print_r($shopifyResult, true), $this->theShop);
        }

        return $assetUploaded;
	}

	/**
	 * Retrieves the number of products matching the specified query present in the store.
	 *
	 * @param array $extraUrlParameters - An associative array containing any extra search parameters. By default, it's empty.
	 * 
	 * @return mixed $productCount - The product count or 'NULL' on failure.
	 */
	private function getProductsCount($extraUrlParameters = []){
		$productCount = null;
		$endpoint = 'products/count.json';

		if(!empty($extraUrlParameters)){
			$endpoint .= '?'.http_build_query($extraUrlParameters);
		}

		//	Try to get the count of the products
		$shopifyResult = $this->makeRequest('GET', $endpoint);

		//	If the request was successful
		if($shopifyResult['success']){
			$productCount = $shopifyResult['response']['count'];
		}
		else{
			Logger::trace('ERROR: Failed to get the products count -> '.print_r($shopifyResult, true), $this->theShop);
		}

		return $productCount;
	}

	/**
	 * Retrieves the record reference from a sku depending on the vendor.
	 *
	 * @param string $productSKU - The SKU of the product.
	 * @param string $productVendor - The vendor of the product.
	 * 
	 * @return mixed $recordReference - Record reference on success, 'NULL' on failure.
	 */
	private function getProductRecordReference($productSKU, $productVendor){
		$recordReference = null;
		//	Split the given sku string by the Shopify's sku separator (regular expression).
		$skuArr = preg_split(SHOPIFY_SKU_SEPARATOR, $productSKU);

		//	If everything went well
		if(!empty($skuArr)){
			//	If the record reference is placed on a special position for the given vendor
			if(array_key_exists($productVendor, RECORD_REFERENCE_X_VENDOR)){
				//	Fetch it from that position
				$recordReferencePosition = RECORD_REFERENCE_X_VENDOR[$productVendor];

				//	TODO: remove this condition later, it's only a temporary safeguard against badly labeled products
				if(array_key_exists($recordReferencePosition, $skuArr)){
					$recordReference = $skuArr[$recordReferencePosition];
					
				}
			}
			else{
				$recordReference = $skuArr[0];
			}
		}

		$recordReference = strlen($recordReference) == 6 ? $recordReference : null;

		return $recordReference;
	}

	/**
	 * Filters the variants of the product to only keep necessary information.
	 *
	 * @param array $rawVariants - The raw variants data to filter.
	 * @param string $productVendor - The vendor of the product.
	 * 
	 * @return array $filteredVariants - List of the filtered variants.
	 */
	private function getFilteredVariants($rawVariants, $productVendor){
		$filteredVariants = [];

		foreach($rawVariants as $rawVaraint){
			$filteredVariants[] = [
				'id' => $rawVaraint['id'],
				'price' => $rawVaraint['price'],
				'title' => $rawVaraint['title'],
				'created_at' => explode('T', $rawVaraint['created_at'])[0],
				'record_reference' => $this->getProductRecordReference($rawVaraint['sku'], $productVendor),
				'barcode' => $rawVaraint['barcode'],
				'requires_shipping' => $rawVaraint['requires_shipping'],
				'inventory_management' => $rawVaraint['inventory_management'],
				'inventory_quantity' => $rawVaraint['inventory_quantity']	
			];
		}

		return $filteredVariants;
	}

	/**
	 * Filters the products to only keep some detailed information and discard everything else.
	 *
	 * @param array $rawProducts - The raw products data to filter.
	 * 
	 * @return array $detailedProductsData - List of the filtered products.
	 */
	private function getDetailedProductsData($rawProducts){
		$detailedProductsData = [];

		foreach($rawProducts as $rawProduct){
			$detailedProductsData[] = [
				'id' => $rawProduct['id'],
				'title' => $rawProduct['title'],
				'template_suffix' => $rawProduct['template_suffix'],
				'body_html' => str_replace(['\r','\n'],'',$rawProduct['body_html']),
				'vendor' => $rawProduct['vendor'],
				'product_type' => $rawProduct['product_type'],
				'handle' => $rawProduct['handle'],
				'tags' => $rawProduct['tags'],
				'image' => $rawProduct['image']['src'],
				'created_at' => explode('T', $rawProduct['created_at'])[0],	// Keep only the date (day) without time (hours)
				'updated_at' => explode('T', $rawProduct['updated_at'])[0],
				'published_at' => (isset($rawProduct['published_at']) ? explode('T', $rawProduct['published_at'])[0] : ''),	//	Hidden products don't have a publication date
				'variants' => $this->getFilteredVariants($rawProduct['variants'], $rawProduct['vendor'])
			];
		}

		return $detailedProductsData;
	}

	/**
	 * Filters the products to only keep some basic information and discard everything else.
	 * NOTE: it only extends to the first (main/default) variant of the product.
	 *
	 * @param array $rawProducts - The raw products data to filter.
	 * 
	 * @return array $basicProductsData - List of the filtered products.
	 */
	private function getBasicProductsData($rawProducts){
		$basicProductsData = [];

		foreach($rawProducts as $rawProduct){
			$basicProductsData[] = [
				'id' => $rawProduct['id'],
				'title' => $rawProduct['title'],
				'handle' => $rawProduct['handle'],
				'vendor' => $rawProduct['vendor'],
				'product_type' => $rawProduct['product_type'],
				'tags' => $rawProduct['tags'],
				'variant_id' =>  $rawProduct['variants'][0]['id'],
				'price' =>  $rawProduct['variants'][0]['price'],
				'barcode' => $rawProduct['variants'][0]['barcode'],
				'record_reference' =>  $this->getProductRecordReference($rawProduct['variants'][0]['sku'], $rawProduct['vendor']),
				'inventory_item_id' => $rawProduct['variants'][0]['inventory_item_id'],
			];
		}

		return $basicProductsData;
	}

	/**
	 * Indexes the handles by the barcodes for the given products.
	 *
	 * @param array $rawProducts - The raw products data to index.
	 * 
	 * @return array $indexedHandles - List of the indexed products.
	 */
	private function getHandlesIndexedByBarcodes($rawProducts){
		$indexedHandles = [];

		foreach($rawProducts as $rawProduct){	
			if(!empty($rawProduct['variants'][0]['barcode'])){
				$indexedHandles[$rawProduct['variants'][0]['barcode']] = $rawProduct['handle'];
			}
		}

		return $indexedHandles;
	}

	/**
	 * Indexes the handles by the record references for the given products.
	 *
	 * @param array $rawProducts - The raw products data to index.
	 * 
	 * @return array $indexedHandles - List of the indexed products.
	 */
	private function getHandlesIndexedByRecordReferences($rawProducts){
		$indexedHandles = [];

		foreach($rawProducts as $rawProduct){	
			if(!empty($rawProduct['variants'][0]['sku'])){
				$recordReference = $this->getProductRecordReference($rawProduct['variants'][0]['sku'], $rawProduct['vendor']);
				$indexedHandles[$recordReference] = $rawProduct['handle'];
			}
		}

		return $indexedHandles;
	}

	/**
	 * Indexes the Shopify IDs by the record references for the given products.
	 *
	 * @param array $rawProducts - The raw products data to index.
	 * 
	 * @return array $indexedIDs - List of the indexed products.
	 */
	private function getShopifyIDsIndexedByRecordReferences($rawProducts){
		$indexedIDs = [];

		foreach($rawProducts as $rawProduct){	
			if(!empty($rawProduct['variants'][0]['sku'])){
				$recordReference = $this->getProductRecordReference($rawProduct['variants'][0]['sku'], $rawProduct['vendor']);
				$indexedIDs[$recordReference] = $rawProduct['id'];
			}
		}

		return $indexedIDs;
	}

	/**
	 * Filters the products to only keep the necessary data and discard everything else according to the given filter level.
	 *
	 * @param array $rawProducts - The raw products data to filter.
	 * @param integer $filterLevel - Determines how the products should be filtered.
	 * 
	 * @return array $filteredProducts - List of the filtered products.
	 */
	private function filterProductsByLevel($rawProducts, $filterLevel){

		switch ($filterLevel) {
			case self::RAW_DATA:
				$filteredProducts = $rawProducts;
				break;
			case self::DETAILED_DATA:
				$filteredProducts = $this->getDetailedProductsData($rawProducts);
				break;
			case self::BASIC_DATA:
				$filteredProducts = $this->getBasicProductsData($rawProducts);
				break;
			case self::HANDLES_X_BARCODES:
				$filteredProducts = $this->getHandlesIndexedByBarcodes($rawProducts);
				break;
			case self::HANDLES_X_RECORD_REFERENCES:
				$filteredProducts = $this->getHandlesIndexedByRecordReferences($rawProducts);
				break;
			case self::SHOPIFY_IDS_X_RECORD_REFERENCES:
				$filteredProducts = $this->getShopifyIDsIndexedByRecordReferences($rawProducts);
				break;
			default:
				//	No filter
				$filteredProducts = $rawProducts;
		}

		return $filteredProducts;
	}

	/**
	 * Fetches all products corresponding to the given search criteria.
	 *
	 * @param integer $filterLevel - Determines how the products should be filtered.
	 * @param array $searchCriteria - The specifications about which products should be fetched. The default values is an empty array which translates to all products.
	 * 
	 * @return array $filteredProducts - List of the filtered products.
	 */
	public function getAllProducts($filterLevel = self::BASIC_DATA, $searchCriteria = []){
		//	Determine the number of pages to be fetched, the fractions are rounded UP
		$productsCount = $this->getProductsCount();
		
		$nbPages = !is_null($productsCount) ? ceil($productsCount/$this->apiCallLimit) : 1;

		$rawProducts = [];
		//	This loop allow to do as many requests as needed to obtain all products
		for ($x = 1; $x <= $nbPages; $x++) {
			if(!array_key_exists('limit', $searchCriteria)){
				$searchCriteria['limit'] = $this->apiCallLimit;
			}
			$searchCriteria['page'] = $x;
			$urlParameters = http_build_query($searchCriteria);

			//	Try to get the products
			$shopifyResult = $this->makeRequest('GET', "products.json?{$urlParameters}");

			
			//	If the request was successful
			if($shopifyResult['success']){
				$rawProducts = array_merge($rawProducts, $shopifyResult['response']['products']);
			}
			else{
				Logger::trace('ERROR: Failed to fetch the products -> '.print_r($shopifyResult, true), $this->theShop);
			}
		}

		$filteredProducts = $this->filterProductsByLevel($rawProducts, $filterLevel);
		return $filteredProducts;
	}

	/**
	 * Fetches all products belonging to the given vendors.
	 *
	 * @param array $productVendors - The list of the product vendors.
	 * @param integer $filterLevel - Determines how the products should be filtered.
	 * 
	 * @return array $productsByVendors - List of the products.
	 */
	public function getProductsByVendors($productVendors, $filterLevel = self::BASIC_DATA){
		$productsByVendors = [];

		foreach($productVendors as $productVendor){
			$productsByVendors = array_merge($productsByVendors, $this->getAllProducts($filterLevel, ['vendor' => $productVendor]));
		}

		return $productsByVendors;
	}

	/**
	 * Fetches all products belonging to the given product types.
	 *
	 * @param array $productTypes - The list of the product types.
	 * @param integer $filterLevel - Determines how the products should be filtered.
	 * 
	 * @return array $productsByTypes - List of the products.
	 */
	public function getProductsByTypes($productTypes, $filterLevel = self::BASIC_DATA){
		$productsByTypes = [];

		foreach($productTypes as $productType){
			$productsByTypes = array_merge($productsByTypes, $this->getAllProducts($filterLevel, ['product_type' => $productType]));
		}

		return $productsByTypes;
	}

	/**
	 * Fetches all products belonging to the given collections.
	 *
	 * @param array $collectionIDs - The list of the collection IDs.
	 * @param integer $filterLevel - Determines how the products should be filtered.
	 * 
	 * @return array $productsByCollections - List of the products.
	 */
	public function getProductsByCollections($collectionIDs, $filterLevel = self::BASIC_DATA){
		$productsByCollections = [];

		foreach($collectionIDs as $collectionID){
			$productsByCollections = array_merge($productsByCollections, $this->getAllProducts($filterLevel, ['collection_id' => $collectionID]));
		}

		return $productsByCollections;
	}

	/**
	 * Creates a new product with the given data.
	 *
	 * @param array $productData - The information about the product that needs to be created.
	 * 
	 * @return boolean $productCreated - 'TRUE' on success, 'FALSE' on failure.
	 */
	public function createProduct($productData){
		$productCreated = false;
        
        //  Try to create the new product
        $shopifyResult = $this->makeRequest('POST', 'products.json', $productData);

		//	If the request was successful
        if($shopifyResult['success']){
			$productCreated = true;
        }
        else{
        	Logger::trace('ERROR: Failed to create the following product -> '.print_r($shopifyResult, true), $this->theShop);
		}

		return $productCreated;
	}

	/**
	 * Updates the given product with the given data.
	 *
	 * @param integer $productID - The unique numeric identifier of the product.
	 * @param array $productData - The information about the product that needs to be updated.
	 * 
	 * @return boolean $productUpdated - 'TRUE' on success, 'FALSE' on failure.
	 */
	public function updateProduct($productID, $productData){
		$productUpdated = false;
        
        //  Try to update the existing product
        $shopifyResult = $this->makeRequest('PUT', "products/{$productID}.json", ['product' =>$productData]);

		//	If the request was successful
        if($shopifyResult['success']){
			$productUpdated = true;
        }
        else{
        	Logger::trace('ERROR: Failed to update the following product -> '.print_r($shopifyResult, true), $this->theShop);
		}

		return $productUpdated;
	}
}
?>