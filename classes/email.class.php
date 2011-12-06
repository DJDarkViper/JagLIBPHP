<?
class EMail {
	
	private $ContentType;
	private $Subject;
	private $Recipients;
	private $CC;
	private $BCC;
	private $Message;
	private $Regex;
	private $From;
	private $Linebreak;
	
	function __construct($FromName, $FromEmail, $Subject = "[No Subject]", $ContentType = "html") {
		$this->SetRegex('strict');
		
		$this->From = array(
			"Name"=>$FromName,
			"Email"=>$FromEmail
		);
		
		$this->SetSubject($Subject);
		
		$this->SetType($ContentType);
		
		$this->Recipients = array();
		$this->CC = array();
		$this->BCC = array();
		
	}
	
	/**
	* Sets the message template
	* @param string $regex The strength of the regular expression: "strict", "loose", "none"
	*/
	function SetRegex($strength) {
		switch($strength) {
			case "loose":
				$this->Regex = '/^([A-Za-z0-9]*)+[@]+([A-Za-z0-9]*)+[\.]+([A-Za-z0-9]*)$/';
				break;
			case "strict":
				$this->Regex = '/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/';
				break;
			case "none":
				$this->Regex = null;
				break;
			
		}
		
		return true;
	}
	
	/**
	* Changes the content type (support for Multipart coming soon)
	* @param string $type text based entry that interprets content type
	*/
	function SetType($type) {
		switch($type) {
			case "text":
			case "plain":
			case "txt":
			case "text/plain":
				$this->ContentType = "Content-type: text/plain\n";
				$this->Linebreak = "\n";
				return true;
			break;
			case "html":
			case "text/html":
			case "rich":
			default:
				$this->ContentType = "Content-type: text/html\n";
				$this->Linebreak = "<br />";
				return true;
			break;
		}
	}
	
	/**
	* Gets the Content Type of the Email
	*/
	function GetType() {
		return $this->ContentType;
	}
	
	/**
	* Sets the subject line of the email
	* @param string $string The text of the subject line
	*/
	function SetSubject($string) {
		$this->Subject = addslashes($string);
		return true;
	}
	
	/**
	* Gets the Subject Line in current storage
	*/
	function GetSubject() {
		return $this->Subject;
	}
	
	/**
	* Adds a new person to receive the email
	* @param string $email The email of the recipient
	* @param string $name (optional) the name of the recipient, will use email as name if no name is provided
	*/
	function AddRecipient($email, $name = "") {
		if($name == "") $name = $email;
		if(!preg_match($this->Regex, $email)) {
			return false;
		}
		
		$this->Recipients[] = array(
			"Name"=>addslashes($name),
			"Email"=>$email
		);
		
		return true;
	}
	
	/**
	* Sends email as a Carbon Copy to specified address
	* @param string $email The email of the recipient
	* @param string $name (optional) the name of the recipient, will use email as name if no name is provided
	*/
	function AddCC($email, $name = "") {
		if($name == "") $name = $email;
		if(!preg_match($this->Regex, $email)) {
			return false;
		}
		
		$this->CC[] = array(
			"Name"=>addslashes($name),
			"Email"=>$email
		);
		
		return true;
	}
	
	/**
	* Sends email as a Blind Carbon Copy to specified address
	* @param string $email The email of the recipient
	* @param string $name (optional) the name of the recipient, will use email as name if no name is provided
	*/
	function AddBCC($email, $name = "") {
		if($name == "") $name = $email;
		if(!preg_match($this->Regex, $email)) {
			return false;
		}
		
		$this->BCC[] = array(
			"Name"=>addslashes($name),
			"Email"=>$email
		);
		
		return true;
	}
	
	/**
	* Gets the list of people receiving the email in various formats
	* @param string $method Specify the way you want the list given to you using a single generic describing word
	* @param char $delim1 If returning a string type, this is the deliminator between name and email
	* @param char $delim2 If returning a string type, this is the deliminator between each recipient
	*/
	function GetRecipients($method = "array", $delim1=":", $delim2=", ") {
		switch($method) {
			case "array":
			case "list":
				return $this->Recipients;
			break;
			case "string":
			case "text":
				$list;
				foreach($this->Recipients as $r) $list[] = $r['Name'].$delim1.$r['Email'];
				return implode($delim2, $list);
			break;
			case "json":
			case "js":
				return json_encode($this->Recipients);
			break;
		}
	}
	
	/**
	* Sets the message template
	* @param string $msg The message body/template of the email, can use mailcodes, see below
	* @param string $msg (optional) you may instead use a template THANKYOU_REGISTER, THANKYOU_ORDER
	*/
	function SetMessage($msg) {
		
		switch($msg) {
			default:
				$this->Message = $msg;
			break;
			case "THANKYOU_REGISTER":
			break;
			case "THANKYOU_ORDER":
			break;
		}
		
		
		return true;
	}
	
	/**
	* Gets the current message
	*/
	function GetMessage() {
		//return	wordwrap($this->Message, 70, $this->Linebreak);
		return	$this->Message;
	}
	
	/**
	* Gets the headers set in the mail template
	* @param array $recipient Internal function
	*/
	private function GetHeaders($recipient) {
		$headers .= "From: ".$this->From['Name']." <".$this->From['Email'].">\n";

		if(count($this->CC) > 0) {
			$cc = array();
			foreach($this->CC as $c) $cc[] = $c['Name']." <".$c['Email'].">";
			$headers .= "CC: ".implode(", ", $cc)."\n";
		}
		if(count($this->BCC) > 0) {
			$bcc = array();
			foreach($this->BCC as $bc) $bcc[] = $bc['Name']." <".$bc['Email'].">";
			$headers .= "BCC: ".implode(", ", $bcc)."\n";
		}

		$headers .= $this->GetType();
		
		return $headers;
	}
	
	/**
	* Starts the process of sending the emails to the recipients
	*/
	function SendMail() {
		foreach($this->Recipients as $r) {
			
			$find = array(
				"[NAME]",
				"[EMAIL]"
			);
			$replace = array(
				$r['Name'],
				$r['Email']
			);
			
			if(!mail($r['Name']." <".$r['Email'].">", str_replace($find, $replace, $this->GetSubject()), str_replace($find, $replace, $this->GetMessage()), str_replace($find, $replace, $this->GetHeaders($r)))) {
				return false;
			}
		}
		return true;
	}
	
	
	
	
	
}
?>