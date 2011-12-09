<?

/**** NOTES::EXPLANATION::CONCEPT


a typical textbox would be:
$id = md5(count($Structure)+1);
$TextBox[$id] = array(
	"name"=>"Test",
	"id"=>"Test",
	"class"=>null
)

this would then be in the $Structure
$Structure[] = array("TextBox"=>$id);

This will then, when foreaching through the structure array will point to the correct ID hash in the correct category
The render() function will take all the data found within specific category renderer, and save it to the $html array
show() will spit out the rendered HTML onto the page 
 */

class Form {
	
	// Class Settings
	private $Settings 	= null;
	
	// Form Settings
	private $Form		= null;
	
	// HTML 4 Fields
	private $TextBox 	= array();
	private $Password	= array();
	private $Hidden 	= array();
	private $TextArea 	= array();
	private $Checkbox	= array();
	private $Radio 		= array();
	private $Select		= array();
	
	// HTML 5 Fields
	private $Email 		= array();
	private $Audio 		= array();
	private $Video 		= array();
	
	// Javascript Stuff
	private $Scripts	= array();
	
	// Storage
	private $Structure 	= array();
	private $HTML 		= '';
	
	/**
	 * @author Kyle Harrison &lt;silent.coyote1@gmail.com&gt;
	 * @copyright Black Jaguar Studios 2011
	 * @package JagLIBPHP
	 * @version 2.0
	 * @license DBAD License 0.1: Commercial and Non-Commercial usage without explicit permission allowed, just say "thanks" if you get the opportunity :)
	*/
	function __construct($settings = array()) {
		$this->Settings = (object) array(
			"mode"=>"build",
		);
		
		$this->Form = (object) array(
			"action"=>null,
			"method"=>"post",
			"enctype"=>null,
			"onsubmit"=>null
		);
		
		
		if(count($settings)>0) {
			// were setting some settings
			foreach($settings as $setting=>$value) {
				
				// We are looking for specific settings, ignore everything else
				switch($setting) {
					
					// Class Settings
					case "mode":
						$this->Settings->mode = $value;
						break;
					
					// Form Settings
					case "action":
						$this->Form->action = $value;
						break;
					case "method":
						$this->Form->method = $value;
						break;
					case "enctype":
						$this->Form->enctype = $value;
						break;
					case "onsubmit":
						$this->Form->onsubmit = value;
						break;
					
				}
				
			}
		}
		
		
		return $this;
		
	}
	
	
	public function text() {
		
	}
	
	
	
	private function ParseParams($params) {
		$parameters = array();
		foreach($params as $k=>$p) {
			switch($k) {
				case "size":
					$parameters[] = "size=\"$p\"";
				break;
				case "style":
					$parameters[] = "style=\"$p\"";
				break;
				case "length":
				case "width":
					$parameters[] = "style=\"width: $p\"";
				break;
				case "click":
				case "onclick":
					$parameters[] = "onclick=\"$p\"";
					break;
				case "blur":
				case "onblur":
					$parameters[] = "onblur=\"$p\"";
				break;
				case "change":
				case "onchange":
					$parameters[] = "onchange=\"$p\"";
				break;
				case "focus":
				case "onfocus":
					$parameters[] = "onfocus=\"$p\"";
				break;
				default:
					$parameters[] = "$k=\"$p\"";
				break;
			}
		}
		
		return implode(" ", $parameters);
	}
	
	
}
?>