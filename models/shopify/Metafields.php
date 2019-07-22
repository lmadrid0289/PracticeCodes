<?php

namespace app\models\shopify;

/**
 * Metafields class creates a list of Shopify metafields.
 */
class Metafields {

    /**
     * Formats the metafield data in a way that's understandable by Shopify.
     *
     * @param string $key - The name of the metafield. Maximum length of 30 characters.
     * @param mixed $value - The information to be stored as metadata. Could be an integer or a string.
     * @param string $namespace - The container for a set of metafields. Maximum length of 20 characters.
     * @param string $valueType - The metafield's information type. The default value is 'string'.
     * 
     * @return array - The formatted metafield data. 
     */
    private function getSingleMetafield($namespace, $key, $value, $valueType = 'string'){

        $metafieldValue = empty($value) ? 'N/A' : $value;

		return [
            'namespace' => $namespace,
            'key' => $key, 
            'value' => $metafieldValue,
            'value_type' => $valueType
        ];
	}

    /**
     * Constructs an array containing all the metafields for a given product.
     * 
     * NOTE: This method still needs some (or a lot of) polishing but for now it gets the job done.
     *
     * @param array $metafieldsInfo - List of all the metafield namespaces and keys.
     * @param array $metafieldsValues - List of the values for the different keys.
     * 
     * @return array $fullList - The constructed array.
     */
    public function getFullList($metafieldsInfo, $metafieldsValues){
       
        $fullList = [];

        //  Go through the metafields information of each namespace
        foreach ($metafieldsInfo as $namespace => $metaInfo){

            //  If the metafield keys belonging to the given namespace needs to be numbered (Ex.: 'review_1', 'review_2', etc.)
            if($metaInfo['keys_numbered']){

                //  If there are some values to process for the given namespace
                if(!empty($metafieldsValues[$namespace])){

                    //  Go through each metafield key that needs to be numbered one by one for the given namespace
                    foreach($metaInfo['keys'] as $key) {

                        //  Go through the each metafield value for the given namespace
                        foreach($metafieldsValues[$namespace] as $index => $metaValue){
                            $num = ++$index;

                            if(is_array($metaValue)){
                                $fullList[] = $this->getSingleMetafield($namespace, "{$key}-{$num}", $metaValue[$key]);
                            }
                            else{
                                $fullList[] = $this->getSingleMetafield($namespace, "{$key}-{$num}", $metaValue);
                            }

                            //  If there are more than one key, the following key should have the same number
                            if(count($metaInfo['keys']) > 1){
                                $num = --$index;
                            }
                        }
                    }
                }
            }
            else{
                foreach($metaInfo['keys'] as $key) {

                    $fullList[] = $this->getSingleMetafield($namespace, $key, $metafieldsValues[$key]);
                }
            } 
        }

        return $fullList;
    }
}
?>