<?php

namespace app\models\shopify;

/**
 * ProductVariant class creates a new Shopify product object useful for updates and creations of the products on a given store.
 */
class ProductVariant {

	/**
	 * The name of the product variant.
	 *
	 * @var string $title
	 */
	private $title;
	
	/**
	 * The barcode, UPC, or ISBN number for the product.
	 * NOTE: We use it for the 'ISBN-13'.
	 *
	 * @var string $barcode
	 */
	private $barcode;

	/**
	 * The price of the product variant.
	 *
	 * @var float $price
	 */
	private $price;

	/**
	 * The original price of the item before an adjustment or a sale.
	 * The default value is 'NULL'.
	 *
	 * @var float $compareAtPrice
	 */
	private $compareAtPrice = null;

	/**
	 * The fulfillment service associated with the product variant. 
	 * Valid values: 'manual' or the handle of a fulfillment service (a third party warehouse that prepares and ships orders on behalf of the store).
	 * The default value is 'manual', since the fulfilment is handled with Edgar's scripts via webhooks.
	 *
	 * @var string $fulfillmentService
	 */
	private $fulfillmentService = 'manual';

	/**
	 * The fulfillment service that tracks the number of items in stock for the product variant.
	 * The default value is 'shopify', since no fulfillment service is used.
	 *
	 * @var string $inventoryManagement
	 */
	private $inventoryManagement = 'shopify';

	/**
	 * Whether customers are allowed to place an order for the product variant when it's out of stock. 
	 * Valid values: 'deny' (not allowed) or 'continue' (allowed).
	 * The default value is 'deny', since not all products can backordered or preordered.
	 *
	 * @var string $inventoryPolicy
	 */
	private $inventoryPolicy = 'deny';

	/**
	 * Whether a customer needs to provide a shipping address when placing an order for the product variant.
	 *
	 * @var boolean $requiresShipping
	 */
	private $requiresShipping;

	/**
	 * A unique identifier for the product variant in the shop used mostly for fulfillment.
	 *
	 * @var string $sku
	 */
	private $sku;

	/**
	 * The default suffix of sku (required for fulfillment scripts).
	 *
	 * @var string $skuSuffix
	 */
	private $skuSuffix = 'n0nn1nWWW';

	/**
	 * Whether a tax is charged when the product variant is sold.
	 * The default value is 'TRUE', since this property is not handled.
	 *
	 * @var boolean $taxable
	 */
	private $taxable = true;

	/**
	 * The weight of the product variant in the unit system specified with '$weightUnit'.
	 * The default value is 1 to help calculating the shipping fares when it's required.
	 * 
	 * @var int $weight
	 */
	private $weight = 1;

	/**
	 * The unit of measurement that applies to the product variant's weight.
	 * The default value is 'lb' to help calculating the shipping fares when it's required.
	 * Valid values: 'g', 'kg', 'oz', or 'lb'.
	 *
	 * @var string $weightUnit
	 */
	private $weightUnit = 'lb';

	/**
     * Constructor
	 * 
	 * @param array $rawProductVariantData - An associative array containing the values of the product variant's properties.
     */
	public function __construct($rawProductVariantData){
		$this->set($rawProductVariantData);
	}

	/**
	 * Sets the product variant's variables.
	 *
	 * @param array $variantData - The values of the product variant's properties.
	 * 
	 * @return void
	 */
	private function set($variantData){
		$this->title = $variantData['title'];
		$this->barcode = $variantData['isbn_13'];
		$this->price = $variantData['price'];
		$this->requiresShipping = $variantData['requires_shipping'];
		$this->sku = $variantData['record_reference'];
	
		//  If the compare at price property is set
        if(array_key_exists('compare_at_price', $variantData)){
			$this->compareAtPrice = $variantData['compare_at_price'];
		}
		
		//  If the fulfillment service property is set
        if(array_key_exists('fulfillment_service', $variantData)){
			$this->fulfillmentService = $variantData['fulfillment_service'];
		}
		
		//  If the inventory management property is set
        if(array_key_exists('inventory_management', $variantData)){
			$this->inventoryManagement = $variantData['inventory_management'];
		}
		
		//  If the inventory policy property property is set
        if(array_key_exists('inventory_policy', $variantData)){
			$this->inventoryPolicy = $variantData['inventory_policy'];
		}
		
		//  If the taxable property is set
        if(array_key_exists('taxable', $variantData)){
			$this->taxable = $variantData['taxable'];
		}
		
		//  If the weight property is set
        if(array_key_exists('weight', $variantData)){
			$this->weight = $variantData['weight'];
		}
		
		//  If the weight unit property is set
        if(array_key_exists('weight_unit', $variantData)){
			$this->weightUnit = $variantData['weight_unit'];
        }
	}

	/**
     * Formats the variant data in a way that's understandable by Shopify.
     *
     * @return array $theVariant - The formatted variant data.
     */
    public function get(){
        $theVariant = [
            'title' => $this->title,
            'barcode' => $this->barcode,
            'price' => $this->price,
            'requiresShipping' => $this->requiresShipping,
            'sku' => $this->sku . $this->skuSuffix,
            'fulfillment_service' => $this->fulfillmentService,
            'inventory_management' => $this->inventoryManagement,
            'inventory_policy' => $this->inventoryPolicy,
            'taxable' => $this->taxable,
            'weight' => $this->weight,
            'weight_unit' => $this->weightUnit
        ];

        //  If the compare at price variable is not empty
        if(!empty($this->compareAtPrice)){
            $theVariant['compare_at_price'] = $this->compareAtPrice;
		}

        return $theVariant;
    }
}
?>