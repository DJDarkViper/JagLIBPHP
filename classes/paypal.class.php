<?php
/*
 * Usage:
 * Create a new PayPal class, designate the mode with either "buynow" or "cart"
 * Add an item to the cart with AddToCart, provide a text based entry for the product name, and a float for the price
 * Remove an item by providing the exact char-for-char item name in RemoveFromCart
 * When ready, GenerateForm, Automatic=true gets rid of any visual buttons and uatomatically submits the form
 *
 */
/**
 * @author Kyle Harrison &lt;silent.coyote1@gmail.com&gt;
 * @copyright Black Jaguar Studios 2011
 * @package JagLIBPHP
 * @license DBAD License 0.1: Commercial and Non-Commercial usage without explicit permission allowed, just say "thanks" if you get the opportunity :)
 */
class PayPal {
	// Public Stuff
	public $HTML = '';			// HTML Container
	public $FormID;				// Unique FormID
	public $Instructions;		// Payer Instructions to Vendor
	public $UserInfo;			// Address, Phone Number, etc;
	public $Tax;				// Overall Tax
	public $Shipping;			// Overall Shipping Calculation
	public $URL;				// Redirect URL upon completion of checkout
	public $DiscountRate;		// Discount Rate
	public $DiscountAmount;		// Discount Amount
	public $Custom;				// Custom Variable returned with $URL
	public $SandboxID;			// When using Sandbox mode, a merchant ID must be provided
	public $Message;			// Button Message on Completed Transaction page
	public $Sandbox;			// Sandbox Mode (Boolean)
	public $AjaxMessage;		// "Processing Now" message
	public $AjaxImage;			// Processing Image
	public $Cart = array();		// The Cart
	// Private Stuff
	private $Method;			// Method of paypal
	private $MerchantID;		// The Unique Merchant ID
	
	

	function  __construct($MerchantID, $Method = "buynow") {
		$this->MerchantID = $MerchantID;
		$this->Method = $Method;
		$this->FormID = 'paypal'.md5(strtotime("NOW"));
		$this->Sandbox = false;
		$this->Message = "Click here to Complete Transaction";
		
		$this->AjaxImage = "http://".$_SERVER['HTTP_HOST']."/images/ajax.gif";
		$this->AjaxMessage = "<center>Processing Transaction... <img src=\"".$this->AjaxImage."\" /></center>";
		$this->UserInfo = array();
	}
	
	/**
	 * Adds an item to the virtual cart!
	 * @param string $Item The name of the item being added
	 * @param float $Price The individual price of the object
	 * @param integer $Quantity The amount being ordered, default is "1"
	 */
	function AddToCart($Item, $Price, $Quantity = 1) {
		$this->Cart[] = array(
			"Item"=>$Item,
			"Price"=>$Price,
			"Quantity"=>$Quantity
		);
	}

	function RemoveFromCart($Item) {
		// doesnt work yet
	}
	
	/**
	* Sets the user information. All information is optional, if at least one is provided, address_override will be set to 1
	* @param array $i Info array: Street, City, Postal, Country, Phone
	*/
	function SetUser($i) {
		
		if(isset($i['FirstName'])) $this->UserInfo['FirstName'] = $i['FirstName'];
		if(isset($i['LastName'])) $this->UserInfo['LastName'] = $i['LastName'];
		/*
		if(isset($i['FirstName']) && isset($i['LastName'])) $this->UserInfo['Name'] = $i['FirstName'].' '.$i['LastName'];
		elseif(isset($i['FirstName'])) $this->UserInfo['Name'] = $i['FirstName'];
		elseif(isset($i['Name'])) $this->UserInfo['Name'] = $i['Name'];
		*/
		if(isset($i['Street'])) $this->UserInfo['Street'] = $i['Street'];
		if(isset($i['City'])) $this->UserInfo['City'] = $i['City'];
		if(isset($i['Postal'])) $this->UserInfo['Zip'] = $i['Postal'];
		if(isset($i['Zip'])) $this->UserInfo['Zip'] = $i['Zip'];
		if(isset($i['Country'])) $this->UserInfo['Country'] = $i['Country'];
		if(isset($i['Phone'])) $this->UserInfo['Phone'] = $i['Phone'];
		
		return true;
	}
	
	/**
	 * Creates the PayPal Form
	 * @param bool $Automatic If set to true, the form will automatically be submitted ASAP, else a paypal button will be presented
	 */
	function GenerateForm($Automatic = false) {
		if($Automatic == true) {
			// Makes the form go. Embedded function, no dependencies needed.
			$this->HTML .= '
			<script>
				function init() {
				  // quit if this function has already been called
				  if (arguments.callee.done) return;

				  // flag this function so we dont do the same thing twice
				  arguments.callee.done = true;

				  // kill the timer
				  if (_timer) clearInterval(_timer);

				  // do stuff

				  document.forms["[FORMID]"].submit();

				};

				/* for Mozilla/Opera9 */
				if (document.addEventListener) {
				  document.addEventListener("DOMContentLoaded", init, false);
				}

				/* for Internet Explorer */
				/*@cc_on @*/
				/*@if (@_win32)
				  document.write("<script id=__ie_onload defer src=javascript:void(0)><\/script>");
				  var script = document.getElementById("__ie_onload");
				  script.onreadystatechange = function() {
					if (this.readyState == "complete") {
					  init(); // call the onload handler
					}
				  };
				/*@end @*/

				/* for Safari */
				if (/WebKit/i.test(navigator.userAgent)) { // sniff
				  var _timer = setInterval(function() {
					if (/loaded|complete/.test(document.readyState)) {
					  init(); // call the onload handler
					}
				  }, 10);
				}

				/* for other browsers */
				window.onload = init;

			</script>
			';
		}
		// Template
		$this->HTML .= '
		<form action="[ACTION]" name="[FORMID]" id="[FORMID]" method="post">
			<input type="hidden" name="cmd" value="[METHOD]">
			<input type="hidden" name="business" value="[MERCHID]">
			<input type="hidden" name="lc" value="CA">
			[PRODUCTS]
			[TOTAL]
			[QUANTITY]
			<input type="hidden" name="currency_code" value="CAD">
			<input type="hidden" name="button_subtype" value="services">
			<input type="hidden" name="no_note" value="0">
			<input type="hidden" name="cn" value="[INSTRUCTIONS]">
			[TAX]
			[SHIPPING]
			<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHosted">
			<input type="hidden" name="return" value="[URL]">
			<input type="hidden" name="rm" value="2">
			<input type="hidden" name="cbt" value="[MESSAGE]">
			<input type="hidden" name="custom" value="[CUSTOM]"> ';
		if(count($this->UserInfo)>0) {
			
			$this->HTML .= '<input type="hidden" name="no_shipping" value="1">
			<input type="hidden" name="address_override" value="1">';
			
			$this->HTML .= ((isset($this->UserInfo['Street'])) ? '<input type="hidden" name="address1" value="'.$this->UserInfo['Street'].'">'."\n" : "");
			$this->HTML .= ((isset($this->UserInfo['City'])) ? '<input type="hidden" name="city" value="'.$this->UserInfo['City'].'">'."\n" : "");
			$this->HTML .= ((isset($this->UserInfo['Zip'])) ? '<input type="hidden" name="zip" value="'.$this->UserInfo['Zip'].'">'."\n" : "");
			$this->HTML .= ((isset($this->UserInfo['Country'])) ? '<input type="hidden" name="country" value="'.$this->UserInfo['Country'].'">'."\n" : "");
			$this->HTML .= ((isset($this->UserInfo['Phone'])) ? '<input type="hidden" name="night_phone_a" value="'.$this->UserInfo['Phone'].'">'."\n" : "");
			$this->HTML .= ((isset($this->UserInfo['FirstName'])) ? '<input type="hidden" name="first_name" value="'.$this->UserInfo['FirstName'].'">'."\n" : "");
			$this->HTML .= ((isset($this->UserInfo['LastName'])) ? '<input type="hidden" name="last_name" value="'.$this->UserInfo['LastName'].'">'."\n" : "");
			
			
		} else {
			$this->HTML .= '<input type="hidden" name="no_shipping" value="2">';
		}
		if($Automatic == false) {
			$this->HTML .= '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">';
		}
		$this->HTML .= '</form>';
		$this->HTML .= (($Automatic) ? $this->AjaxMessage : "" );
		$Action = (($this->Sandbox) ? "https://www.sandbox.paypal.com/cgi-bin/webscr" : "https://www.paypal.com/cgi-bin/webscr" );
		$this->MerchantID = (($this->Sandbox) ? $this->SandboxID : $this->MerchantID );


		// Product Processing
		
		switch($this->Method) {
			case "buynow":
				$cmd = "_xclick";
				$Products;
				$TotalPrice;
				$Quantity;
				if(count($this->Cart) > 1) {
					$cmd = "_cart";
					foreach($this->Cart as $k=>$c) {
						$Products[] = $c["Item"];
						$TotalPrice[] = $c["Price"];
						$Quantity[] = $c["Quantity"];
					}

					$ptmp = $Products;
					$ttmp = $TotalPrice;
					$qtmp = $Quantity;
					foreach($ptmp as $k=>$p) {
						$key = $k+1;
						$phtml = '<input type="hidden" name="item_name_'.$key.'" value="'.$p.'" >'."\n";
						$thtml = '<input type="hidden" name="amount_'.$key.'" value="'.$ttmp[$k].'" >'."\n";
						$qhtml = '<input type="hidden" name="quantity_'.$key.'" value="'.$qtmp[$k].'" >'."\n";
						
						if(is_array($Products)) $Products = '<input type="hidden" name="upload" value="1" >'."\n";
						if(is_array($TotalPrice)) $TotalPrice = '';
						if(is_array($Quantity)) $Quantity = '';

						$Products .= $phtml;
						$TotalPrice .= $thtml;
						$Quantity .= $qhtml;
					}
					$Shipping = '<input type="hidden" name="shipping_1" value="'.$this->Shipping.'">';
					$Tax = '<input type="hidden" name="tax_cart" value="'.(($this->Tax != "") ? $this->Tax : "0" ).'">';
				} else {
					$Products = '<input type="hidden" name="item_name" value="'.$this->Cart[0]["Item"].'">'."\n";
					$TotalPrice = '<input type="hidden" name="amount" value="'.$this->Cart[0]["Price"].'">'."\n";
					$Quantity = '<input type="hidden" name="quantity" value="'.$this->Cart[0]["Quantity"].'">'."\n";
					$Shipping = '<input type="hidden" name="shipping" value="'.$this->Shipping.'">'."\n";
					$Tax = '<input type="hidden" name="tax" value="'.$this->Tax.'">'."\n";
				}
				
				break;
			case "cart":
				
				break;
		}

		$this->HTML = str_replace(
			array(
				"[FORMID]",
				"[MERCHID]",
				"[PRODUCTS]",
				"[TOTAL]",
				"[QUANTITY]",
				"[INSTRUCTIONS]",
				"[TAX]",
				"[SHIPPING]",
				"[URL]",
				"[METHOD]",
				"[CUSTOM]",
				"[ACTION]",
				"[MESSAGE]"
			),
			array(
				$this->FormID,
				$this->MerchantID,
				$Products,
				$TotalPrice,
				$Quantity,
				$this->Instructions,
				$Tax,
				$Shipping,
				$this->URL,
				$cmd,
				$this->Custom,
				$Action,
				$this->Message
			),
			$this->HTML
		);




		
		return $this->HTML;
	}
	
	
	/**
	 * Generates an HTML based Receipt for usage with Email or Database
	 * @see PayPal->Cart Property for raw cart data
	 */
	function GenerateReceipt() {
		
	}
}
?>
