<?php
class PayPalNVP {
	
	// Settings
	private $API			= "https://api-3t.paypal.com/nvp";
	private $Method			= null;
	private $Version		= null;
	private $PaymentType	= null;
	private $Mode			= null;
	
	private $SandboxMode 	= false;
	private $APICredentials = null;
	
	// Order Info
	private $Amount			= null;
	private $CurrencyID		= null;
	private $CurrencyCode	= null;
	
	// Card Info
	private $CardType 		= null;
	private $CardNumber 	= null;
	private $CardSecurity 	= null;
	private $Expiry 		= null;
	
	// Personal Info
	private $FirstName 		= null;
	private $LastName 		= null;
	private $Address 		= null;
	private $City 			= null;
	private $Province 		= null;
	private $CountryCode	= null;
	private $PostalCode 	= null;
	
	private $QueryString	= "";
	
	private $SystemMessage	= array();
	
	private $Response		= null; // This is filled with data AFTER Execution
	
	
	/**
	 * PayPal 3T NVP API Interface Class. Will automatically urlencode and cleanup provided information, and build the according strings and will even perform the cURL operation needed
	 * @author Kyle Harrison &lt;silent.coyote1@gmail.com&gt;
	 * @copyright Black Jaguar Studios 2011
	 * @package JagLIBPHP
	 * @license DBAD License 0.1: Commercial and Non-Commercial usage without explicit permission allowed, just say "thanks" if you get the opportunity :)
	 */
	public function __construct($APIUsername = null, $APIPassword = null, $APISignature = null) {
		$this->APICredentials 		= new stdClass();
		$this->Expiry 				= new stdClass();
		// Non Negotiable Stuff (for the moment)
		$this->Method 				= "DoDirectPayment";
		$this->Version 				= urlencode("51.0");
		$this->PaymentType 			= urlencode("Sale");
		$this->Mode					= "strict";
		
		if($APIUsername != null) 	$this->APICredentials->Username = urlencode($APIUsername);
		if($APIPassword != null) 	$this->APICredentials->Password = urlencode($APIPassword);
		if($APISignature != null) 	$this->APICredentials->Signature = urlencode($APISignature);
		
	}
	
	/**
	 * Sets up Sandbox mode
	 * @param Boolean $bool If set to true, will utilize PayPals Sample API Signature
	 */
	public function SetSandbox($bool = false) {
		$this->SandboxMode 	= true;
		$this->API 			= "https://api-3t.sandbox.paypal.com/nvp";
		if($bool) {
			// This is a premade Website Payment Pro account for testing purposes, feel free.
			$this->APICredentials->Username 	= urlencode("_wpp__1322682862_biz_api1.gmail.com");
			$this->APICredentials->Password 	= urlencode("1322682893");
			$this->APICredentials->Signature 	= urlencode("Axsq3TOrLuNTXm-53Cvi2FWJuwyGATI.cRXkvBo7JGXgZZasZ7K7MmUG");
		}
		
		return $this;
	}
	
	/**
	 * Generic Multipurpose Card Data setter, pass in almost any information to set the card up<br />This method auto url encodes the data, but does nto validate, so <b>BE CAREFUL</b>.
	 * @param Array $data An array or object (stdclass) of settable data. See the Examples below
	 * @example $PayPal->card( array( "type"=>"visa", "number"=>"1111111111111111", "security"=>"111", "expiry"=>array("year"=>"2012", "month"=>"02") ) );
	 * @example $exp->expiry = (object) array("year"=>"2012", "month"=>"02"); <br />$exp->type = "visa"; <br />$PayPal->card($exp); // This just sets expiry data and a card type
	 */
	public function card($data = array()) {
		// Check
		// is $data an array or an object? if not, kick out
		if(!is_array($data) && !is_object($data)) return $this;
		// If so, if an array, typecast to object
		if(is_array($data)) $data = (object) $data;
		
		// Grab data as requested
		if(isset($data->type)) 		$this->CardType 	= urlencode($data->type);
		if(isset($data->number)) 	$this->CardNumber 	= urlencode($data->number);
		if(isset($data->security)) 	$this->CardSecurity = urlencode($data->security);
		if(isset($data->expiry)) {
			// Is this an array or object of data?
			if(!is_array($data->expiry) && !is_object($data->expiry)) {
				// try to parse as date object because tis not an array or object, return false if cannot
			} else {
				// Typecast to object if an array
				if(is_array($data->expiry)) 	$data->expiry 		 = (object) $data->expiry;
				// Set data as requested
				if(isset($data->expiry->month)) $this->Expiry->Month = $data->expiry->month;
				if(isset($data->expiry->year))  $this->Expiry->Year  = $data->expiry->year;
			}
			
			
		}
		
		$this->BuildQuery();
		return $this;
	}
	
	
	/**
	 * Generic Multipurpose Personal Data setter, pass in almost any information to set the person up (billing)<br />This method auto url encodes the data, but does nto validate, so <b>BE CAREFUL</b>.
	 * @param Array $data An array or object (stdclass) of settable data. See the Examples below
	 * @example $PayPal->person( array( "firstname"=>"John", "lastname"=>"Smith", "address"=>"1234 West Dr", "city"=>"Riverdale", "province"=>"BC", "postal"=>"V0H1Z0" ) );
	 * @example $person->firstname = "John";<br />$person->lastname = "Smith";<br />$PayPal->person($person); // This just sets the first and last name, all options are available however to set
	 */
	public function person($data = array()) {
		// Check
		// is $data an array or an object? if not, kick out!!
		if(!is_array($data) && !is_object($data)) return $this;
		// If so, if an array, typecast to object
		if(is_array($data)) $data = (object) $data;
		
		if(isset($data->firstname)) 	$this->FirstName 	= urlencode($data->firstname);
		if(isset($data->lastname)) 		$this->LastName 	= urlencode($data->lastname);
		if(isset($data->address)) 		$this->Address 		= urlencode($data->address);
		if(isset($data->city)) 			$this->City 		= urlencode($data->city);
		if(isset($data->province)) 		$this->Province		= urlencode($data->province);
		if(isset($data->postal))		$this->PostalCode 	= urlencode($data->postal);
		if(isset($data->countrycode))	$this->CountryCode  = urlencode($data->countrycode);
		
		$this->BuildQuery();
		return $this;
	}
	
	private function ParseExpiryDate() {
		
		return $this->Expiry->Month.$this->Expiry->Year;
	}
	
	private function BuildQuery() {
		$data = array();
		
		if($this->Method != null) 					 $data[] = "METHOD=".$this->Method;
		if($this->Version != null) 					 $data[] = "VERSION=".$this->Version;
		if($this->APICredentials->Password != null)  $data[] = "PWD=".$this->APICredentials->Password;
		if($this->APICredentials->Username != null)  $data[] = "USER=".$this->APICredentials->Username;
		if($this->APICredentials->Signature != null) $data[] = "SIGNATURE=".$this->APICredentials->Signature;
		if($this->PaymentType != null) 				 $data[] = "PAYMENTACTION=".$this->PaymentType;
		if($this->Amount != null) 					 $data[] = "AMT=".$this->Amount;
		if($this->CardType != null) 				 $data[] = "CREDITCARDTYPE=".$this->CardType;
		if($this->CardNumber != null) 				 $data[] = "ACCT=".$this->CardNumber;
		if($this->CardSecurity != null) 			 $data[] = "CVV2=".$this->CardSecurity;
		if($this->Expiry != null) 					 $data[] = "EXPDATE=".$this->ParseExpiryDate();
		if($this->FirstName != null)				 $data[] = "FIRSTNAME=".$this->FirstName;
		if($this->LastName != null)					 $data[] = "LASTNAME=".$this->LastName;
		if($this->Address != null)					 $data[] = "STREET=".$this->Address;
		if($this->City != null)						 $data[] = "CITY=".$this->City;
		if($this->Province != null)					 $data[] = "STATE=".$this->Province;
		if($this->PostalCode != null)				 $data[] = "ZIP=".$this->PostalCode;
		if($this->CountryCode != null)				 $data[] = "COUNTRYCODE=".$this->CountryCode;
		if($this->CurrencyCode != null)				 $data[] = "CURRENCYCODE=".$this->CurrencyCode;
		
		
		$this->QueryString = implode("&", $data);
		
		return this;
	}
	
	/**
	 * Performs the current API call as it is.
	 */
	public function exec() {
		
		
		if($this->Mode == "strict") {
			
			if(
				$this->Method == null 					 ||
				$this->Version == null 					 ||
				$this->APICredentials == null 			 ||
				$this->APICredentials->Password == null  ||
				$this->APICredentials->Username == null  ||
				$this->APICredentials->Signature == null ||
				$this->PaymentType == null 				 ||
				$this->Amount == null 					 ||
				$this->CardType == null 				 ||
				$this->CardNumber == null 				 ||
				$this->CardSecurity == null 			 ||
				$this->Expiry == null 					 ||
				$this->Expiry->Year == null 			 ||
				$this->Expiry->Month == null 			 ||
				$this->FirstName == null 				 ||
				$this->LastName == null 				 ||
				$this->Address == null 					 ||
				$this->City == null 					 ||
				$this->Province == null 				 ||
				$this->PostalCode == null 				 ||
				$this->CountryCode == null 				 ||
				$this->CurrencyCode == null
			) {
				$this->SystemMessage[] = "Error: Strict Mode says No Go. Parameters are missing!";
				return false;
			}
			
		}
		
		
		$payment = curl_init();
		
		curl_setopt($payment, CURLOPT_URL, $this->API);
		curl_setopt($payment, CURLOPT_VERBOSE, 1);
		curl_setopt($payment, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($payment, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($payment, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($payment, CURLOPT_POST, 1);
		
		curl_setopt($payment, CURLOPT_POSTFIELDS, $this->QueryString);
		
		$response = curl_exec($payment);
		
		$this->Response = $response;
		
		if(!$response) return false;
		else {
			
			$response = explode("&", $response);
			
			$parsedResponse = array();
			foreach($response as $v) {
				$tmp = explode("=", $v);
				$parsedResponse[$tmp[0]] = $tmp[1];
			}
			
			if(count($parsedResponse) < 1 || !array_key_exists('ACK', $parsedResponse) || $parsedResponse['ACK'] != 'Success') {
				
				
				if(array_key_exists('L_LONGMESSAGE0', $parsedResponse))
					for($i = 0; $i <= 10; $i++) {
						if(array_key_exists('L_LONGMESSAGE'.$i, $parsedResponse))
							$this->SystemMessage[] = "Error Code <a href='https://merchant.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_errorcodes'>#" . $parsedResponse['L_ERRORCODE'.$i] . "</a>: " . urldecode($parsedResponse['L_LONGMESSAGE'.$i]);
						else break;
					}
				else 
					$this->SystemMessage[] = 'We could not process your payment at this time. Please verify your credit card information and try again.';
				
				
			} else {
				
				// if were here, everything was a success
				
				$this->SystemMessage[] = "success";
				
				
			}
		}
	}
	
	public function valid() {
		if(
			$this->Method == null 					 ||
			$this->Version == null 					 ||
			$this->APICredentials == null 			 ||
			$this->APICredentials->Password == null  ||
			$this->APICredentials->Username == null  ||
			$this->APICredentials->Signature == null ||
			$this->PaymentType == null 				 ||
			$this->Amount == null 					 ||
			$this->CardType == null 				 ||
			$this->CardNumber == null 				 ||
			$this->CardSecurity == null 			 ||
			$this->Expiry == null 					 ||
			$this->Expiry->Year == null 			 ||
			$this->Expiry->Month == null 			 ||
			$this->FirstName == null 				 ||
			$this->LastName == null 				 ||
			$this->Address == null 					 ||
			$this->City == null 					 ||
			$this->Province == null 				 ||
			$this->PostalCode == null 				 ||
			$this->CountryCode == null 				 ||
			$this->CurrencyCode == null
		) {
			$msg = "Error: Validator says that parameters may be missing for the following parameters: ";
			$missing = array();
			foreach($this as $k=>$item) if(($k != "SystemMessage" && $k != "Response") && $item == null) $missing[] = "<u>".$k."</u>"; 
			$this->SystemMessage[] = $msg.implode(", ", $missing);
			
			return false;
		}
		else return true;
			
	}
	
	// Single Set Methods
	
	
	public function amount($double) {
		if(!is_double($double)) return $this;
		
		$this->Amount = urlencode($double);
		
		return $this;
	}
	
	public function currency_code($code) {
		if(!is_string($code)) return $this;
		$this->CurrencyCode = $code;
		return $this;
	}
	
	public function currency_id($id) {
		if(!is_string($id)) return $this;
		$this->CurrencyID = $id;
		return $this;
	}
	
	
	public function card_type($type) {
		$acceptable = array("MasterCard", "Visa", "Discover", "AMEX", "Maestro");
		
		if(!is_string($type)) return $this;
		if(!in_array($type, $acceptable)) return $this;
		
		$this->card(array("type"=>$type));

		return $this;
	}
	
	public function card_number($number) {
		$number = (string) $number;
		$this->card(array("number"=>$number));
		return $this;
	}
	
	public function card_security($number) {
		$number = (string) $number;
		$this->card(array("security"=>$number));
		return $this;
	}
	
	public function card_expiry($type, $number) {
		$types = array("year", "month");
		
		if(!in_array($type, $types)) return $this;
		
		$tmp = new stdClass();
		$tmp->expiry = new stdClass();
		$tmp->expiry->{$type} = (string) $number;
		
		$this->card($tmp);
		
		return $this;
	}
	
	public function firstname($name) {
		if(!is_string($name)) return $this;
		$this->person(array("firstname"=>$name));
		return $this;
	}
	
	public function lastname($name) {
		if(!is_string($name)) return $this;
		$this->person(array("lastname"=>$name));
		return $this;
	}
	
	public function address($address) {
		if(!is_string($address)) return $this;
		$this->person(array("address"=>$address));
		return $this;
	}
	
	public function city($city) {
		if(!is_string($city)) return $this;
		$this->person(array("city"=>$city));
		return $this;
	}
	
	public function province($province) {
		if(!is_string($province)) return $this;
		$this->person(array("province"=>$province));
		return $this;
	}
	
	public function state($state) {
		return $this->province($state);
	}
	
	public function postal($postal) {
		if(!is_string($postal)) return $this;
		$this->person(array("postal"=>$postal));
		return $this;
	}
	
	public function zip($zip) {
		return $this->postal($zip);
	}
	
	public function country($country) {
		$codes = array("US", "CA");
		if(!in_array($country, $codes)) return $this;
		$this->CountryCode = urlencode($country);
		return $this;
	}
	
	public function username($api_username) {
		if(!is_string($api_username)) return $this;
		$this->APICredentials->Username = urlencode($api_username);
		return $this;
	}
	
	public function password($api_password) {
		if(!is_string($api_password)) return $this;
		$this->APICredentials->Password = urlencode($api_password);
		return $this;
	}
	
	public function signature($api_signature) {
		if(!is_string($api_signature)) return $this;
		$this->APICredentials->Signature = urlencode($api_signature);
		return $this;
	}
	
	/**
	 * Returns all recorded SystemMessages (Errors and stuff)
	 * @param Boolean $latest Setting this to true will return only the latest message. Default: false
	 * @return if $latest is set to false (default) will return an array of messages
	 * @return if $latest is set to true, will return the string value of the latest message
	 */
	public function GetMessages($latest = false) {
		if($latest == true && count($this->SystemMessage) > 0) return $this->SystemMessage[ count($this->SystemMessage)-1 ]; // get latest
		else return $this->SystemMessage; // get all
	}
}


/***** EXAMPLE ******/

/* // Uncomment to see working example
// Create an array of values
$card = array(
	"type"=>"Visa",
	"number"=>"4485506696547530",
	"security"=>213,
	"expiry"=>array(
		"year"=>"2015",
		"month"=>"05"	
	)
);

// Or an object of values, the person and card methods will read both and ONLY what you give it
$me = (object) array(
	"firstname"=>"Matthias",
	"lastname"=>"Rothstein",
	"address"=>"2248 Wildwood Street",
	"city"=>"Youngstown",
	"province"=>"OH",
	"postal"=>"44503",
	"countrycode"=>"US"
);

$pp = new PayPal();

// Utilizing method stringing, we can build our PayPal 3T API System, and execute it, all in ONE line!
$pp->SetSandbox(true)
	->currency_code("USD")
	->currency_id("USD")
	->amount(20.0)
	->card($card)
	->person($me)
	->exec();

// Note: Data is overwritten chronologically, nothing is set in true stone
// The person and card methods only process information it is given, and also automatically encode the data

$msg = $pp->GetMessages(true);



echo "<pre>".print_r($pp, true)."</pre>";
echo "<pre>".print_r($msg, true)."</pre>";
*/