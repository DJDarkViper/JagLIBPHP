<?
/**
 * 
 * This class may seem a bit daunting at first, but I invite you to study and check it out. This class is designed
 * to include ALL functionality a standard hand written form would have, wrapped in a class to remove most mundane
 * elements of form creation (like repetative tags, formatting, names, ids, labels, javascript libraries, etc)
 * 
 * This class also includes a "SpeedForm" mode, which allows you to create a quick form based on a single PHP Array
 * to quickly get form creation OUT of the way. 
 * 
 * Please refer to forms.example.php for some examples of how the Forms class could work for you!
 * 
 * 
 * ~Kyle Harrison
 *
 * @author Kyle Harrison
 */
class Form {
	
	/**
	* Form HTML
	*/
	public $Form;
	
	/**
	* Form Array
	*/
	public $FormStruct;
	
	/**
	* Javascript Elements
	*/
	public $JSStruct;
	
	/**
	* Form Name
	*/
	public $Name;
	
	private $FormElement;
	private $FormFooter;
	
	private $JSElement;
	private $JSFooter;
	private $JSLibs;
	
	// Auto Generated
	private $RequiredFields;
	
	// Groups
	private $Groups;
	
	/**
	* <b>Form Helper Class</b><br />
	* All methods and properties are public, can use them to output specific form elements or generate a whole form
	* @param strong $name The form name <em>detault: form</em>
	* @param string $action The form action. <em>default: self</em>
	* @param string $method The method of form sending. <em>default: POST</em>
	* @param array $struct 3D Array of Initial elements
	* @author Kyle Harrison
	* @copyright Black Jaguar Studios 2011
	* @package JagLIB
	* @license Commercial and Non-Commercial usage without explicit permission allowed, just say "thanks" if you get the opportunity :)
	*/
	function __construct($name = "form", $action = "", $method = "POST", $params = array(), $struct = array()) {
		$this->Name = $name;
		$params = $this->ParseParams($params);
		$this->FormElement = "
		<form name=\"".$this->Name."\" id=\"".$this->Name."\" method=\"$method\" action=\"$action\" $params>
		";
		$this->FormFooter = "
		</form>
		";
		$this->JSElement = "<script type=\"text/javascript\">
		";
		$this->JSFooter = "
		</script>
		";
		
		if(count($struct) > 0) SetStruct($struct);
	}
	
	/**
	 * Sets the structure using an array element to perform the majority of simple work
	 */
	function SetStruct($struct) {
		if(ParseStructure($struct)) return true;
		else return false;
	}
	
	/**
	* Takes a multi-dimensional array of elements and overwrites the existing saved form (if any)
	*/
	private function ParseStructure($array) {
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
	
	/**
	 * Creates a label object and adds it to the overall structure
	 * @param string $text The text used for the label
	 * @param string $for the referenced object name to associate with
	 * @param bool $br Sets whether to attach a linebreak at the end
	 */
	function CreateLabel($text, $for, $br = false) {
		//return "<label for=\"$for\">$text</label>".(($br == true)?"<br />" : "");
		return "<label for=\"$for\">$text</label>";
	}
	
	/**
	* Creates a TextField
	* @param string $name Name of the field to be referenced (will set the ID to be similar)
	* @param string $label (Optional) A label to associate with the field
	* @param string $value (Optional) The initial set value for the item
	* @param array $params (Optional) An array of form element parameters (like onChange, size, etc)
	*/
	function AddText($name, $label = "", $value = "", $params = array()) {
		$params = $this->ParseParams($params);
		if($label != "") $label = $this->CreateLabel($label, $name, true);
		$html = "<input type=\"text\" value=\"$value\" name=\"$name\" id=\"$name\" $params />";
		$this->FormStruct[] = array("Type"=>"TextField", "HTML"=>$html, "Label"=>$label);
	}
	
	/**
	 * Creates a Password Field, Reacts much the same way as AddText 
	 * @param string $name Name of the field to be referenced (will set the ID to be similar)
	 * @param string $label (Optional) A label to associate with the field
	 * @param string $value (Optional) The initial set value for the item
	 * @param array $params (Optional) An array of form element parameters (like onChange, size, etc)
	*/
	function AddPassword($name, $label = "", $value = "", $params = array()) {
		$params = $this->ParseParams($params);
		if($label != "") $this->FormStruct[] = $this->CreateLabel($label, $name, true);
		$html .= "<input type=\"password\" value=\"$value\" name=\"$name\" id=\"$name\" $params />";
		$this->FormStruct[] = $html;
	}
	
	/**
	 * Creates a Hidden Element
	 * @param string $name Name of the field to be referenced (will set the ID to be similar)
	 * @param mixed $value (Optional) The set value for the hidden field
	 */
	function AddHidden($name, $value = "") {
		$this->FormStruct[] = "<input type=\"hidden\" name=\"$name\" id=\"$name\" value=\"$value\" />";
		
		return true;
	}
	
	/**
	 * Creates a File Upload Element
	 */
	function AddFile($name, $label = "", $params = array()) {
		if($label != "") $this->FormStruct[] = $this->CreateLabel($label, $name, true);
		$this->FormStruct[] = "<input type=\"file\" name=\"$name\" id=\"$name\" />";
		
		return true;
	}
	
	/**
	 * Creates a TextArea element
	 * @param string $name Name of the field to be referenced (will set the ID to be similar)
	 * @param string $label (Optional) A label to associate with the field
	 * @param string $value (Optional) The initial set value for the item
	 * @param array $params (Optional) An array of form element parameters (like onChange, size, etc)
	 */
	function AddTextArea($name, $label = "", $value = "", $params = array()) {
		$params = $this->ParseParams($params);
		if($label != "") $this->FormStruct[] = $this->CreateLabel($label, $name, true);
		$html .= "<textarea name=\"$name\" id=\"$name\" $params>$value</textarea>";
		$this->FormStruct[] = $html;
	}
	
	/**
	* Utilizes a 2D array to spill out a simple select menu
	* @param string $name Name of the field to be referenced (will set the ID to be similar)
	* @param array $fields A list of field elements (ex: Value=>Label, Value=>Label)
	* @param string/int $value (Optional) A number or integer set to select the inital element in the box
	* @param array $params (Optional) An array of form element parameters (like onChange, size, etc)
	*/
	function AddSelect($name, $fields = array(), $value = "", $params = array()) {
		$html = "<select name=\"$name\" id=\"$name\" $params>";
		
		foreach($fields as $k=>$v) $html .= "<option value=\"$v\">$k</option>";
		
		$html = "</select>";
		$this->FormStruct[] = $html;
	}
	
	/**
	* Accepts a multi-dimensional array to account for option groups
	* @param string $name Name of the field to be referenced (will set the ID to be similar)
	* @param array $fields A list of field elements (ex: array(GroupName=>array(Value=>Label, Value=>Label), GroupName=>array(Value=>Label, Value=>Label)))
	* @param string/int $value (Optional) A number or integer set to select the inital element in the box
	* @param array $params (Optional) An array of form element parameters (like onChange, size, etc)
	*/
	function AddSelectGroup($name, $fields = array(), $params = array()) {
		$html = "<select name=\"$name\" id=\"$name\" $params>";
		
		foreach($fields as $k=>$v) {
			$html .= "<optgroup label=\"$v\">";
			
			foreach($v as $key=>$f) $html .= "<option value=\"$f\">$key</option>";
			
			$html .= "</optgroup>";
		}
		
		$html = "</select>";
		$this->FormStruct[] = $html;
	}
	
	/**
	 * Creates a single Radio Button
	 * @param string $name Name of the field to be referenced (will set the ID to be similar)
	 * @param string $label (Optional) A label to associate with the field
	 * @param string $value (Optional) The initial set value for the item
	 * @param bool $checked (Optiona) Checked or not (false by default)
	 * @param array $params (Optional) An array of form element parameters (like onChange, size, etc)
	 */
	function AddRadioButton($name, $label = "", $value = "", $checked = false, $params = array()) {
		$params = $this->ParseParams($params);
		$checked = (($checked == true) ? "checked" : "" );
		$this->FormStruct[] = "<input type=\"radio\" name=\"$name\" id=\"$name\" value=\"$value\" $checked $params /> $label";
		
		return true;
	}
	
	/**
	 * Creates a single Radio Button
	 * @param string $name Name of the field to be referenced (will set the ID to be similar)
	 * @param string $label (Optional) A label to associate with the field
	 * @param array $struct (Optional) An array dictating the structre of the list (ex: Value=>Label, Value=>Label
	 * @param array $params (Optional) An array of form element parameters (like onChange, size, etc) 
	 */
	function AddRadioList($name, $label = "", $struct = array(), $params = array()) {
		
		$html = array();
		$count = 1;
		
		$params = $this->ParseParams($params);
		
		foreach($struct as $k=>$v) {
			$id = ($count++).preg_replace("/[^a-zA-Z0-9]/i", "", strtolower($name));
			$html[] = "<input type=\"radio\" name=\"$name\" id=\"$id\" value=\"$v\" $params /> $k ";
		}
		
		
		$this->Group[] = $html;
		if($label != "") $this->FormStruct[] = $this->CreateLabel($label, $name);
		$this->FormStruct[] = "Group->Radio|".(count($this->Group)-1);
		
		return true;
	}
	
	/**
	 * Creates a single Radio Button
	 * @param string $name Name of the field to be referenced (will set the ID to be similar)
	 * @param string $label (Optional) A label to associate with the field
	 * @param array $struct (Optional) An array dictating the structre of the list (ex: Value=>Label, Value=>Label
	 * @param array $params (Optional) An array of form element parameters (like onChange, size, etc) 
	 */
	function AddCheckList($name, $label = "", $struct = array(), $params = array()) {
		$html = array();
		$count = 1;
		
		$params = $this->ParseParams($params);
		
		foreach($struct as $k=>$v) {
			$id = ($count++).preg_replace("/[^a-zA-Z0-9]/i", "", strtolower($name));
			$html[] = "<input type=\"checkbox\" name=\"{$name}[]\" id=\"$id\" value=\"$v\" $params /> $k ";
		}
		
		
		$this->Group[] = $html;
		if($label != "") $this->FormStruct[] = $this->CreateLabel($label, $name);
		$this->FormStruct[] = "Group->Checkbox|".(count($this->Group)-1);
		
		return true;
	}
	
	
	/**
	 * Creates a submit button that sends the form to the action with the method specified
	 * @param string $name (Optional) Name of the field to be referenced (will set the ID to be similar)
	 * @param string $label (Optional) A label to associate with the field
	 * @param array $params (Optional) An array of form element parameters (like onChange, size, etc)  
	 */
	function AddSubmit($name = "submit", $label = "Submit", $params = array()) {
		$params = $this->ParseParams($params);
		$this->FormStruct[] = "<input type=\"submit\" name=\"$name\" id=\"$name\" value=\"$label\" $params />";
		
		return true;
	}
	
	/**
	 * Creates a blank button that can trigger raw script entities
	 * @param string $name Name of the field to be referenced (will set the ID to be similar)
	 * @param string $label (Optional) A label to associate with the field
	 * @param array $params (Optional) An array of form element parameters (like onChange, size, etc)  
	 */
	function AddButton($name, $label = "Click Here", $params = array()) {
		$params = $this->ParseParams($params);
		$this->FormStruct[] = "<input type=\"button\" name=\"$name\" id=\"$name\" value=\"$label\" $params />";
		
		return true;
	}
	
	/**
	 * Creates a new Javascript element, when used multiple times will add all javascript elements to a single <script> element, and organize these elements based on order added.
	 * @param string $script the raw Javascript to be added to the script block
	 * @param string $function If provided, the block is transferred into a function under the same name as the string provided in this attribute
	 * @param array $attributes If provided will create a list of usable attributes for the created function (only works if $function is not null) (ex: arg1, arg2, arg3)
	 */
	function AddJS($script, $function = null, $attributes = array()) {
		$js = "";
		if($function != null) $js .= "
	function $function(".implode(", ", $attributes).") {
		";
	$js .= $script;
		if($function != null) $js .= "
	}
		";
		
		$this->JSStruct[] = $js;
		
		return true;
	}
	
	/**
	 * Generates a set script which utilizes the data provided to oversee mundane tasks (such as automated form validation)
	 * @param string $js The name of the generator to perform
	 */
	function GenerateScript($js) {
		switch($js) {
			default:
			break;
		}
		
		return true;
	}
	
	/**
	 * Provides easy access to include a javascript library of your choice, these elements will be added to the very top, above your script tags no matter where this method is called. Please be aware of conflicts between packages though
	 * @param string $lib the basic name of the preferred library (ex: jq or jquery to include the jQuery library)
	 */
	function AddJSLib($lib) {
		
		switch($lib) {
			case "jq":
			case "jquery":
				$lib = "http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js";
				break;
			case "jqui":
			case "jqueryui":
			case "jquery-ui":
				$lib = "http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js";
				break;
			case "moo":
			case "mootools":
				$lib = "http://ajax.googleapis.com/ajax/libs/mootools/1.3.2/mootools-yui-compressed.js";
				break;
			case "proto":
			case "prototype":
				$lib = "http://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js";
				break;
			case "scriptaculous":
			case "script.aculo.us":
				$lib = "http://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js";
				break;
			case "dojo":
				$lib = "http://ajax.googleapis.com/ajax/libs/dojo/1.6.1/dojo/dojo.xd.js";
				break;
			case "yahoo":
			case "yahooui":
			case "yui":
				$lib = "http://ajax.googleapis.com/ajax/libs/yui/3.3.0/build/yui/yui-min.js";
			case "extjs":
			case "ext":
				$lib = "http://ajax.googleapis.com/ajax/libs/ext-core/3.1.0/ext-core.js";
			case "chrome":
			case "chromeframe":
				$lib = "http://ajax.googleapis.com/ajax/libs/chrome-frame/1.0.2/CFInstall.min.js";
				break;
			case "swf":
			case "swfobj":
			case "swfobject":
				$lib = "http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js";
			default:
				$lib = $lib;
		}
		
		if(count($this->JSLibs) > 0) {
			// Check to see if lib has already been called, do nothing if so
			$found = false;
			foreach($this->JSLibs as $v=>$l) {
				if($v == $lib) $found = true;
			}
		} else $found = false;
		
		if($found == false)	$this->JSLibs[$lib] = "<script type=\"text/javascript\" src=\"$lib\"></script>
		";
		
		return true;
	}
	
	/**
	 * Uses all available information and generates the raw HTML to display
	 * @param $Type a switch that dictates how the form will be presented, using basic static css classes for later manipulation. Defaults to "basic" which is a BR broken vertical list
	 * @param $TemplatePath the relative or absolute path to a template form that incorperates the usage of tokens
	 */
	function GenerateForm($Type = "basic", $TemplatePath = "form.php") {
		
		// Initialize
		$this->Form = "";
		
		
		if(count($this->JSLibs) > 0) $this->Form .= implode("", $this->JSLibs);
		
		if(count($this->JSStruct) > 0) { 
			$this->Form .= $this->JSElement.implode("
		", $this->JSStruct).$this->JSFooter.$this->FormElement; 
		}
		
		// Modes of output
		switch($Type) {
			default:
			case "basic":
				
				
				
				/*
				 * Step through each of the FormStruct elements and display them, BUT also find special 
				 * pointer elements and process those (like Group pointers)
				 */
				foreach($this->FormStruct as $f) {
					
					$html = "";
					
					if(strpos($f, "Group->") !== FALSE) {
						
						$kv = explode("|", str_replace("Group->", "", $f));
						$type = $kv[0];
						$index = $kv[1];
						
						switch($type) {
							case "Radio":
								foreach($this->Group[$index] as $k=>$v) {
									$html .= $v;
								}
								break;
							case "Checkbox":
								foreach($this->Group[$index] as $k=>$v) {
									$html .= $v;
								}
								break;
						}
						
					} else {
						$html = $f . "<br />";
					}
					
					$this->Form .= $html;
				}
				
				
				
				
				break;
			case "table":
				
				var_dump($this->FormStruct);
				
				break;
			case "div":
				break;
			case "li":
				break;
			case "template":
			case "tpl":
				break;
		}
		
		
		
		
		
		
		$this->Form .= $this->FormFooter;
		
		return $this->Form;
	}
	
	/**
	 * Displays the generated form HTML
	 */ 
	function DisplayForm($Type = "basic", $TemplatePath = "form.php") {
		echo $this->GenerateForm($Type, $TemplatePath);
	}
}
?>