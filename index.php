<?php

//create a mailer class

//EMAIL HEADER CONSTANTS
define("TO", "TO");
define("FROM", "FROM");
define("CC", "CC");
define("BCC", "BCC");
define("REPLY", "REPLY");
//Reply-To


class Mail{
	private $TO;
	private $FROM;
	private $REPLY;
	private $CC;
	private $BCC;
	private $Subject;
	private $Body;
	private $Attachment;

	public function __construct()
	{
			$this->TO = array();
			$this->FROM = array();
			$this->REPLY = array();
			$this->CC = array();
			$this->BCC = array();
			$this->Body = "";
			$this->Attachment = array();
	}
	public function __destruct()
	{
	}

	//THIS FUNCTION ADD THE EMAILS TO THE MAIL
	public function AddAddress($EmailHeader, /*$Email*/$arrAddress)
	{
		/*
		echo "<pre>";
		print_r($arrAddress);
		echo "</pre>";
		*/

		if ($EmailHeader == FROM || $EmailHeader == REPLY){	//GENERALLY WE WILL ONLY HAVE ONE FROM, OVERRIDE THE CURRENT FROM
			switch ($EmailHeader) {
				case FROM:
					unset($this->FROM);
					$this->FROM = $arrAddress;
					break;
				case REPLY:
					unset($this->REPLY);
					$this->REPLY = $arrAddress;
					break;
				}
		} 
		else {

			//TEMP
			$Header;

			//CHECK WHAT TYPE OF EMAIL HEADER THIS IS
			switch ($EmailHeader) {
				case TO:
					$Header = &$this->TO;
					break;
				case CC:
					$Header = &$this->CC;
					break;
				case BCC:
					$Header = &$this->BCC;
					break;
				
				default:
					break;
			}

			//CHECK IF THE EMAIL HAS ALREADY BEEN ADDED, IF SO, THEN DONT ADD IT AGAIN !
			$bAlreadyAdded = 0;
			foreach ($Header as $Index => $AddressInHeader){
				if ($AddressInHeader == $arrAddress[1]){
					$bAlreadyAdded = 1;
					break;	//NO NEED TO CONTINUE
				}
			}

			if (!$bAlreadyAdded){
				array_push($Header, $arrAddress);
			}

		}

		return $this;	//RETURN AN INSTANCE OF THE CLASS
	}

	public function Subject($Subject)
	{
		$this->Subject = $Subject;
	}

	public function AddAttachment($arrAttachment)
	{
		//CHECK IF THE EMAIL HAS ALREADY BEEN ADDED, IF SO, THEN DONT ADD IT AGAIN !
		$bAlreadyAdded = 0;
		foreach ($this->Attachment as $Index => $Attachment){
			if ($Attachment == $arrAttachment){
				$bAlreadyAdded = 1;
				break;	//NO NEED TO CONTINUE
			}
		}

		if (!$bAlreadyAdded){
			array_push($this->Attachment, $arrAttachment);
		}
	}

	public function Body($Body)
	{
		$this->Body = $Body;
	}
	public function Preview($bShowHTMLEntities = 0)	//$bShowHTMLEntities IF TRUE, SHOW THE HMTL TAGS, IF FALSE, DON'T SHOW
	{
		//TODO: preview mail
		if ($bShowHTMLEntities){
			echo "<pre>";
			echo htmlentities($this->Body);
			echo "</pre>";
		} else {
			echo $this->Body;
		}
	}
	public function Send()
	{
		//a random hash will be necessary to send mixed content
		$separator = md5(time());
		//carriage return type (RFC)
		$eol = "\r\n";
		$mime_boundary = "==Multipart_Boundary_x{$separator}x";

		//header
		$Headers = "MIME-Version: 1.0" .$eol;
		$Headers .= "Content-Type: multipart/mixed; boundary=\"{$mime_boundary}\"" .$eol;

		//body
		$Body = "--{$mime_boundary}".$eol;
		$Body .= "Content-Type:text/html; charset=\"iso-8859-1\"".$eol;
		$Body .= "Content-Transfer-Encoding: 7bit".$eol.$eol;
		$Body .= $this->Body .= "".$eol;

		//attachment
		$Body .= "--{$mime_boundary}".$eol;
		$Body .= "Content-Type: application/octet-stream; name=\"{$this->Attachment[0]}\"".$eol;
		$Body .= "Content-Transfer-Encoding: base64".$eol;
		$Body .= "Content-Disposition: attachment; filename={$this->Attachment[0]}".$eol.$eol;
		$Body .= chunk_split(base64_encode(file_get_contents($this->Attachment[0])));
		$Body .= $eol;



		//add headers
		//$Headers = "MIME-Version: 1.0". $eol;
		//$Headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

		//add cc's
		$Cc = "";
		foreach ($this->CC as $Index => $arrAddress){
			$Cc = $arrAddress[0]."<".$arrAddress[1].">,";
		}
		$Headers .= "Cc: ". $Cc. $eol;
	   
	   //add from
		$Headers .= "From: ". $this->FROM[0]."<".$this->FROM[1].">". $eol;

		//add reply to
		$Headers .= "Reply-To: ". (isset($this->REPLY[0])?$this->REPLY[0]."<".$this->REPLY[1].">,":$this->FROM[0]."<".$this->FROM[1].">,"). $eol;

		//mailer
		$Headers .= "X-Mailer: PHP/" . phpversion();


		//to
		$To = "";
		foreach ($this->TO as $Index => $arrAddress){
			$To .= $arrAddress[0]."<".$arrAddress[1].">,";
		}


		//send mail
		if (!mail($To, $this->Subject, /*$this->Body*/$Body, $Headers)){
			echo "mail failed";
		}

		return 0;	//false is failed, true if succeded
	}
};



//TEST

$mail = new Mail();

$mail->Subject("hi there");

//AddAddress(FROM, array("display_name", "email"));
$mail->AddAddress(FROM, array("display_name", "display_name"));
$mail->AddAddress(TO, array("display_name", "display_name"));
$mail->AddAddress(TO, array("display_name", "display_name"));
$mail->AddAddress(TO, array("display_name", "display_name"));
$mail->AddAddress(CC, array("display_name", "display_name"));

//add attachment
//$mail->AddAttachment("dir/file_name");
//$mail->AddAttachment("dir/file_name");

//html
$mail->Body("<h1>Hi</h1><br><body><p>hello there</p></body>");


//$mail->Body($Body);

//TODO: preview how the mail will look
$mail->Preview(1);

//send the mail
$mail->Send();

?>
