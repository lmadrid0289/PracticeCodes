<?php

namespace app\models\shopify;

/**
 * Product class creates a new Shopify product object useful for updates and creations of the products on a given store.
 */
class Product {

	/**
	 * The name of the product.
	 * NOTE: It will be used generate the product's handle (a unique human-friendly string for the product).
	 *
	 * @var string $title
	 */
	private $title;
	
	/**
	 * The description of the product.
	 * NOTE: It supports HTML formatting.
	 *
	 * @var string $bodyHTML
	 */
	private $bodyHTML;

	/**
	 * A string of comma-separated tags that are used for filtering and search. 
	 * NOTE: Each comma-separated tag can have up to 255 characters.
	 *
	 * @var string $tags
	 */
	private $tags;

	/**
	 * The suffix of the Liquid template for the product.
	 * NOTE: The default template 'product.liquid'.
	 *
	 * @var string $templateSuffix
	 */
	private $templateSuffix = null;

	/**
	 * The name of the product's vendor.
	 *
	 * @var string $vendor
	 */
	private $vendor;

	/**
	 * The name of the product's type.
	 *
	 * @var string $type
	 */
	private $type;

	/**
	 * A list of product image objects, each one representing an image associated with the product.
	 *
	 * @var string $images
	 */
	private $images;

	/**
	 * A list of product variants, each one representing a different version of the product.
	 *
	 * @var string $variants
	 */
	private $variants;

	/**
	 * A list of product metafields (additional informations about the product).
	 *
	 * @var string $metafields
	 */
	private $metafields;

	private $metafieldsInfo;

	/**
     * Constructor
	 * 
	 * @param array $rawProductData - An associative array containing the values of the product's properties.
     */
	public function __construct($rawProductData, $metafieldsInfo){
		$this->metafieldsInfo = $metafieldsInfo;
		$this->set($rawProductData);
	}

	/**
	 * Sets the product variables.
	 *
	 * @param array $productData - The values of the product's properties.
	 * 
	 * @return void
	 */
	private function set($productData){
		$this->title = $productData['title'];
		$this->bodyHTML = $productData['description'];
		$this->tags = $productData['keywords'];
		$this->vendor = $productData['vendor'];
		$this->type = $productData['type'];

		//	If the template suffix property is set
		if(array_key_exists('template_suffix', $productData)){
			$this->templateSuffix = $productData['template_suffix'];
		}
		
		$this->images = $this->getImages($productData['front_cover']);
		$this->variants = $this->getVariants($productData['variants']);
		$this->metafields = $this->getMetafields($productData['metafields']);
	}

	/**
	 * Lists all source URLs for images associated with the product that will be downloaded by Shopify.
	 *
	 * @param array $images - URLs of the potential images.
	 * 
	 * @return array $imagesToDownload - List of the images URLs that Shopify can download.
	 */
	private function getImages($images){
		$imagesToDownload = [];

		//	If there are any images to uplaod
        if(!empty($images)){

			//	Verify that each URL actually points to an accessible image
            foreach($images as $imageURL){
                // if(Helper::isAnImage($imageURL)){
					$imagesToDownload[] = ['src' => $imageURL];
                // }
            }
        }
        
        return $imagesToDownload;
	}

	/**
	 * Lists all variants associated with the product.
	 *
	 * @param array $variantsData - The variants to process.
	 * 
	 * @return array allVariants - The constructed array of variants.
	 */
	private function getVariants($variantsData){

		$allVariants = [];

		foreach($variantsData as $key => $variantData){
			$productVariantdObj = new ProductVariant($variantData);
			$allVariants[$key] = $productVariantdObj->get();
		}
		
		return $allVariants;
	}

	/**
	 * Lists all metafields associated with the product.
	 *
	 * @param array $metafieldsData - The metafields to process.
	 * 
	 * @return array $allMetafields - The constructed array of metafields.
	 */
	private function getMetafields($metafieldsData){
	
		$metafielsdObj = new Metafields();
		$allMetafields = $metafielsdObj->getFullList($this->metafieldsInfo, $metafieldsData);
	
		return $allMetafields;
	}

	/**
	 * Formats the product data (including images, variants and metafields) in a way that's understandable by Shopify.
	 *
	 * @return array $theProduct - The formated product data.
	 */
	public function get(){
		$theProduct['product'] = [
            'title' => $this->title,
            'body_html' => $this->bodyHTML,
            'vendor' => $this->vendor,
            'product_type' => $this->type,
			'tags' => $this->tags,
            'variants' => $this->variants,
            'metafields' => $this->metafields,
            'images' => $this->images
		];
		
		//  If the template suffix variable is not empty
        if(!empty($this->templateSuffix)){
			$theProduct['template_suffix'] = $this->templateSuffix;
		}

        return $theProduct;
	}
}
?>