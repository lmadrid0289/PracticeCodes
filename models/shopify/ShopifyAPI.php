<?php

namespace app\models\shopify;



/**
 * Shopify class allows to interact with Shopify's RESTful API.
 * Read more: https://help.shopify.com/api
 */
class ShopifyAPI {

	/**
	 * The contents of the 'User-Agent: ' header to be used in a HTTP request.
	 *
	 * @var string $userAgent
	 */
    private $userAgent = 'PHP-ShopifyAPI/1.0';

	/**
	 * The maximum number of seconds to allow cURL functions to execute.
	 *
	 * @var integer $timeout
	 */
	private $timeout = 10;

	/**
	 * 'TRUE' to return the transfer as a string.
	 *
	 * @var boolean $returnTransfer
	 */
	private $returnTransfer = true;	

	/**
	 * 'FALSE' to stop cURL from verifying the peer's certificate.
	 *
	 * @var boolean $sslVerification
	 */
	private $sslVerification = false;

	/**
	 * List of the HTTP request methods that are accepted by Shopify's API.
	 *
	 * @var array $acceptedMethods
	 */
    private $acceptedMethods = ['GET','POST','PUT','DELETE'];
	
	/**
	 * It allows to average 2 calls per second to avoid the Shopify bucket overflow.
	 * 500000 microseconds = HALF SECOND.
	 *
	 * @var integer $haltTime
	 */
	private $haltTime = 500000;

	/**
	 * Possible response status codes returned by Shopify
	 * Read more: https://help.shopify.com/api/guides/response-status-codes
	 *
	 * @var array $statusCodesResponses
	 */
    private $statusCodesResponses = [
        100 => '100 Continue -> The server has received the request headers and the client should proceed to send the request body.',
        200 => '200 OK -> The request was successfully processed by Shopify.',
        201 => '201 Created -> The request has been fulfilled and a new resource has been created.',
        202 => '202 Accepted -> The request has been accepted, but not yet processed.',
        400 => '400 Bad Request -> The request was not understood by the server, generally due to bad syntax.',
        401 => '401 Unauthorized -> The necessary authentication credentials are not present in the request or are incorrect. https://help.shopify.com/api/guides/api-credentials',
        402 => '402 Payment Required -> The requested shop is frozen.',
        403 => '403 Forbidden -> The server is refusing to respond to the request. This is generally because the appropriate scope was not requested for the action. https://help.shopify.com/api/guides/authentication/oauth#scopes',
        404 => '404 Not Found -> The requested resource was not found but could be available again in the future.',
        406 => '406 Not Acceptable -> The requested resource is only capable of generating content not acceptable according to the Accept headers sent in the request.',
        422 => '422 Unprocessable Entity -> The request body was well-formed but contains semantical errors.',
        429 => '429 Too Many Requests -> The request was not accepted because the application has exceeded the rate limit. https://help.shopify.com/api/guides/api-call-limit',
        500 => '500 Internal Server Error -> An internal error occurred in Shopify.',
        501 => '501 Not Implemented -> The requested endpoint is not available on that particular shop.',
        503 => '503 Service Unavailable -> The server is unavailable. Check the status page for reported service outages. https://status.shopify.com/'
	];

	/**
	 * Undocumented variable
	 *
	 * @var string $shopURL
	 */
	private $shopURL;

	/**
	 * Undocumented variable
	 *
	 * @var string $accessToken
	 */
	private $accessToken;

	/**
	 * Constructor
	 *
	 * @param string $shop - The domain of the shop.
	 * @param string $token - The API access token used to interact with the shop.
	 */
	public function __construct($shop, $token){
		$this->shopURL = "https://{$shop}/admin/";
		$this->accessToken = $token;
	}

	/**
	 * Allows to make a request to Shopify's API by using cURL.
	 *
	 * @param string $method - One of the following 4 HTTP methods: ('GET','POST','PUT','DELETE').
	 * @param string $endpoint - It determines which resource is solicited for the given request.
	 * @param array $args - An associative array of additional data that need to be passed for the given request. As it's only used for 'POST' & 'PUT', the default value is an empty array.
	 * 
	 * @return array $apiResult - An associative array containing the complete result of the given request.
	 */
	protected function makeRequest($method, $endpoint, $args = []){
		
		$apiResult = ['success' => false, 'request' => null, 'http_code' => null,  'response' => null];
		
		//	If the provided method is valid
		if (in_array($method, $this->acceptedMethods)) {

			//	Delay the execution by a half second to not overflow the Shopify API bucket
			usleep($this->haltTime);
			
			//	Build the full URL for the given request
			$requestURL = $this->shopURL . $endpoint; //	E.g. https://API_KEY:PASSWORD@HOST/admin/products/count.json
		
			//	Encode the request arguments
			$jsonData = json_encode($args);
			$apiResult['request'] = ['method' => $method, 'url' => $requestURL, 'data' => $jsonData];

			//	Set the Media type of the body of the request (used with POST and PUT requests).	
			$httpHeader = ['Content-Type: application/json; charset=utf-8'];

			//	Set the API token (if there is one) 
			if(!empty($this->accessToken)){
				$httpHeader[] = "X-Shopify-Access-Token: {$this->accessToken}";
			}
			
			//	Initializing a new cURL session and performing the specified request
			$ch = curl_init();

			//	Set various options on the given cURL session handle
			curl_setopt($ch, CURLOPT_URL, $requestURL);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, $this->returnTransfer); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerification); 
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

			//	Set the data to post if it's a 'POST' or 'PUT' request
			//	And if there IS some data to set in first place
			if(in_array($method, ['POST', 'PUT']) && !empty($args)){
				curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
			}
			
			//	Perform the given cURL session
			$curlResponse = curl_exec($ch);

			//	Get and set the last received HTTP code
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$apiResult['http_code'] = $httpCode;

			$decodedResponse = null;
			//	If the request was successful
			if($curlResponse !== false){
				//	Try to convert the result (JSON encoded string) into an associative array
				$decodedResponse = json_decode($curlResponse, true);

				//	If the result cannot be decoded
				if(is_null($decodedResponse)){
					//	It's either because the encoded data was badly formatted or was deeper than the recursion limit
					$apiResult['error'] = ['type' => 'JSON', 'code' => json_last_error(), 'message' => 'Unexpected response format: '.json_last_error_msg()];
				}
			}
			else{
				//	It failed because of a cURL error
				$apiResult['error'] = ['type' => 'cURL', 'code' => curl_errno($ch), 'message' => curl_error($ch)];
			}

			//	Closes the given cURL session and frees all resources
			curl_close($ch);
			
			//	If some data has been received and has been decoded successfully
			if(!is_null($decodedResponse)){
				//	If the requested was received, understood and accepted (if status/http code is equals to 2XX)
				if ($httpCode >= 200 && $httpCode < 300) {
					$apiResult['success'] = true;
					$apiResult['response'] = $decodedResponse;
				}
				else{
					$msg = array_key_exists($httpCode, $this->statusCodesResponses) ? $this->statusCodesResponses[$httpCode] : 'Unknown';
					$apiResult['error'] = ['type' => 'SHOPIFY', 'code' => $httpCode, 'message' => $msg, 'details' => $decodedResponse['errors']];
				}
			}
		}
		return $apiResult;
	}

}