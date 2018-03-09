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

		//main header (multipart mandatory)
		$Headers = "MIME-Version: 1.0" . $eol;
		$Headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
		$Headers .= "Content-Transfer-Encoding: 7bit" . $eol;
		$headers .= $this->Bod . $eol;

		//Body
		//message
		$Body = "--" . $separator . $eol;
		$Body .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
		$Body .= "Content-Transfer-Encoding: 8bit" . $eol;
		$Body .= $this->Body . $eol;

		//attachment
		//$content = chunk_split(base64_encode(file_get_contents($this->Attachment[0])));
		$Body .= "--" . $separator . $eol;
		$Body .= "Content-Type: application/octet-stream; name=\"" . $this->Attachment[0] . "\"" . $eol;
		$Body .= "Content-Transfer-Encoding: base64" . $eol;
		$Body .= "Content-Disposition: attachment" . $eol;
		$Body .= chunk_split(base64_encode(file_get_contents($this->Attachment[0]))) . $eol;
		$Body .= "--" . $separator . "--";



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

$mail->Subject("Your application is in review");

//AddAddress(FROM, array("display_name", "email"));
$mail->AddAddress(FROM, array("Evan", "evan@localhost"));
$mail->AddAddress(TO, array("Natasha", "natasha@localhost"));
$mail->AddAddress(TO, array("Steyn", "steyn@localhost"));
$mail->AddAddress(TO, array("Neil", "neil@localhost"));
$mail->AddAddress(CC, array("Lani", "lani@localhost"));

//add attachment
$mail->AddAttachment("250px-Uroplatus_fimbriatus_3.jpg");
//$mail->AddAttachment("dir/file_name");
//$mail->AddAttachment("dir/file_name");

//html
//$mail->Body("<h1>Hi</h1><br><body><p>hello there</p></body>");
$Body = "<div id='flex_1-0' class='comp flex article-content'>
<p>A Bcc (blind carbon copy) is a copy of an email message sent to a recipient whose <a href='https://www.lifewire.com/what-is-an-email-address-1171114' data-component='link' data-source='inlineLink' data-type='internalLink' data-ordinal='1'>email address</a> does not appear (as a recipient) in the message.</p><p>In other words, if you get a blind carbon copy email where the sender put only <a href='https://www.lifewire.com/what-is-my-email-address-4143261' data-component='link' data-source='inlineLink' data-type='internalLink' data-ordinal='2'>your email address</a> in the Bcc field, and put their own email in the To field, you will get the email but it will not identify your address in the To field (or any other field) once it hits your email account.</p>
<div id='billboard1-sticky_1-0' class='comp billboard1-sticky billboard-sticky is-lockable' data-height='1050' style=''>
<div class='spacer' style=''>
<div id='billboard1_1-0' class='comp billboard1 mntl-gpt-adunit gpt billboard' data-ad-width='300' data-ad-height='600' style=''>
<div id='billboard' class='wrapper' data-google-query-id='CIOtw6qP3dkCFe6iUQodCcsHTA'><div id='google_ads_iframe_/479/lifewire/lw_learn-how-email-messaging/billboard_0__container__' style='border: 0pt none; display: inline-block; width: 300px; height: 600px;'><iframe frameborder='0' src='https://tpc.googlesyndication.com/safeframe/1-0-17/html/container.html' id='google_ads_iframe_/479/lifewire/lw_learn-how-email-messaging/billboard_0' title='3rd party ad content' name='' scrolling='no' marginwidth='0' marginheight='0' width='300' height='600' data-is-safeframe='true' sandbox='allow-forms allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts allow-top-navigation-by-user-activation' style='border: 0px; vertical-align: bottom;'></iframe></div></div>
</div><!-- end: comp billboard1 mntl-gpt-adunit gpt billboard -->
</div>
</div><!-- end: comp billboard1-sticky billboard-sticky -->
<p>The primary reason people send blind carbon copies is to mask the other recipients from the list of recipients. Using our example again, if the sender bcc'd multiple people (by putting their addresses in the Bcc field before sending), none of those recipients would see who else the email was sent to.</p><p><em>Note: Bcc is also sometimes spelled BCC (all uppercase), bcced, bcc'd, and bcc:ed.</em></p><h3>Bcc vs Cc</h3><p>Bcc recipients are hidden from the other recipients, which is fundamentally different than To and Cc recipients, whose addresses do appear in the respective <a href='https://www.lifewire.com/what-is-an-email-header-1171127' data-component='link' data-source='inlineLink' data-type='internalLink' data-ordinal='3'>header</a> lines.</p><p>Every recipient of the message can see all the To and Cc recipients, but only the sender knows about Bcc recipients. If there is more than one Bcc recipient, they do not know about each other either, and they will typically not even see their own address in the email header lines.</p><p>The effect of this, in addition to the recipients being hidden, is that unlike regular emails or Cc emails, a 'reply all' request from any of the Bcc recipients will not send the message to the other Bcc email addresses.</p>
<div id='native-placeholder_1-0' class='comp native-placeholder mntl-block'></div><!-- end: comp native-placeholder mntl-block -->
<p class='html-slice'> This is because the other blind carbon copied recipients are unknown to the Bcc recipient.</p><p><em>Note: The underlying internet standard that specifies email format, <a href='http://www.rfc-editor.org/rfc/rfc5322.txt' data-component='link' data-source='inlineLink' data-type='externalLink' data-ordinal='4' target='_blank' rel='noopener'>RFC 5322</a>, is unclear about how hidden Bcc recipients are from each other; it leaves open the possibility that all Bcc recipients get a copy of the message (a message distinct from the copy To and Cc recipients get) where the full Bcc list, including all addresses, is included. This is highly uncommon, though.</em></p>
<div id='billboard2-sticky_1-0' class='comp billboard2-sticky billboard-sticky is-lockable' data-height='600' data-parent='' style=''>
<div class='spacer' style=''>
<div id='billboard2-dynamic_1-0' class='comp billboard2-dynamic mntl-gpt-dynamic-adunit mntl-gpt-adunit gpt billboard dynamic is-requested' data-index='2' data-ad-width='300' data-ad-height='600' style=''>
<div id='billboard2' class='wrapper' data-type='billboard' data-pos='btf1' data-priority='3' data-sizes='[[300, 250], [300, 600], [300, 601], [160, 600], [300, 251], [2, 1], 'fluid']' data-rtb='true' data-targeting='null' data-google-query-id='CMX73q-P3dkCFeypUQodS4oL3g'><div id='google_ads_iframe_/479/lifewire/lw_learn-how-email-messaging/billboard2_0__container__' style='border: 0pt none;'><iframe id='google_ads_iframe_/479/lifewire/lw_learn-how-email-messaging/billboard2_0' title='3rd party ad content' name='google_ads_iframe_/479/lifewire/lw_learn-how-email-messaging/billboard2_0' width='300' height='600' scrolling='no' marginwidth='0' marginheight='0' frameborder='0' srcdoc='' style='border: 0px; vertical-align: bottom;' data-integralas-id-4e7f3769-d4c9-70d2-b9d4-b3e7f8e742b1=''></iframe></div></div>
</div><!-- end: comp billboard2-dynamic mntl-gpt-dynamic-adunit mntl-gpt-adunit gpt billboard dynamic -->
</div>
</div><!-- end: comp billboard2-sticky billboard-sticky -->
<p class='html-slice'></p><h3>How and When Should I Use Bcc?</h3><p>Limit your use of Bcc to essentially one case: to protect the privacy of recipients. This might be useful when you send to a group whose members do not know each other or should not be aware of the other recipients.</p><p>Other than that, it's best not to use Bcc and instead to add all recipients to the To or Cc fields. Use the To field for people who are direct recipients and the Cc field for people who get a copy for their notice (but who need not themselves take action in response to the email; they're more or less supposed to be a 'listener' of the message).</p><p><em>Tip:&nbsp;See <a href='https://www.lifewire.com/how-to-add-bcc-recipients-in-gmail-1171985' data-component='link' data-source='inlineLink' data-type='internalLink' data-ordinal='5'>How to Use Bcc in Gmail</a>&nbsp;if you're trying to send a blind carbon copy message through your Gmail account. It's supported by other email providers and clients too, like <a href='https://www.lifewire.com/add-bcc-recipients-outlook-1173691' data-component='link' data-source='inlineLink' data-type='internalLink' data-ordinal='6'>Outlook</a>&nbsp;and&nbsp;<a href='https://www.lifewire.com/how-to-send-mail-to-bcc-recipients-in-iphone-mail-1172563' data-component='link' data-source='inlineLink' data-type='internalLink' data-ordinal='7'>iPhone Mail</a>.</em></p><h3>How Does Bcc Work?</h3><p>When an email message is delivered, its recipients are specified independently from the email headers you see as part of the message (the To, Cc and Bcc lines).</p><p>If you add Bcc recipients, your email program may take all addresses from the Bcc field combined with the addresses from the To and Cc fields, and specify them as recipients to the mail server it uses to send the message. While the To and Cc fields are left in place as part of the message header, the email program then removes the Bcc line, however, and it will appear blank to all recipients.</p>
<div id='billboard3-sticky_1-0' class='comp billboard3-sticky billboard-sticky is-lockable' data-height='600' data-parent='' style=''>
<div class='spacer' style=''>
<div id='billboard3-dynamic_1-0' class='comp billboard3-dynamic mntl-gpt-dynamic-adunit mntl-gpt-adunit gpt billboard dynamic is-requested' data-index='3' data-ad-width='300' data-ad-height='250' style=''>
<div id='billboard3' class='wrapper' data-type='billboard' data-pos='btf2' data-priority='5' data-sizes='[[300, 250], [300, 252], [3, 1], 'fluid']' data-rtb='true' data-targeting='null' data-google-query-id='CP-ahrOP3dkCFeuG7QodxoYCOA'><div id='google_ads_iframe_/479/lifewire/lw_learn-how-email-messaging/billboard3_0__container__' style='border: 0pt none; display: inline-block; width: 300px; height: 250px;'><iframe frameborder='0' src='https://tpc.googlesyndication.com/safeframe/1-0-17/html/container.html' id='google_ads_iframe_/479/lifewire/lw_learn-how-email-messaging/billboard3_0' title='3rd party ad content' name='' scrolling='no' marginwidth='0' marginheight='0' width='300' height='250' data-is-safeframe='true' sandbox='allow-forms allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts allow-top-navigation-by-user-activation' style='border: 0px; vertical-align: bottom;'></iframe></div></div>
</div><!-- end: comp billboard3-dynamic mntl-gpt-dynamic-adunit mntl-gpt-adunit gpt billboard dynamic -->
</div>
</div><!-- end: comp billboard3-sticky billboard-sticky -->
<p>It's also possible for the email program to hand the email server the message headers as you entered them and expect it to deduce Bcc recipients from them. The mail server then will send each of the addresses a copy, but delete the Bcc line itself or at least blank it out.</p><h3>An Example of a Bcc Email</h3><p>If the idea behind blind <a href='https://www.lifewire.com/sending-an-email-to-multiple-recipients-cc-and-bcc-1171178' data-component='link' data-source='inlineLink' data-type='internalLink' data-ordinal='8'>carbon copies</a> is still confusing, consider an example where you're sending an email to your employees..</p><p>You want to send an email to Billy, Mary, Jessica, and Zach. The email is regarding where they can go online to find the new work you've assigned to each of them. However, to protect their privacy, none of these people know each other and shouldn't have access to the other people's email addresses or names.</p>
<p>You could send a separate email to each of them, putting Billy's email address in the regular To field, and then doing the same for Mary, Jessica, and Zach. However, that means you have to make four separate emails to send the same thing, which might not be awful for just four people but would be a waste of time for dozens or hundreds.</p><p>You can't use the Cc field because that will negate the whole purpose of the blind carbon copy feature.</p><p>Instead, you put your own email address in the To field followed by the recipients' email address into the Bcc field so that all four will get the same email.</p><p>When Jessica opens her message, she'll see that it came from you but also that it was <em>sent</em> to you (since you put your own email in the To field). She will not, however, see anyone else's email. When Zach opens his, he'll see the same To and From information (your address) but none of the other people's information. The same is true for the other two recipients.</p><p>This approach allows for a non-confusing, clean email that has your email address in both the sender and to field. However, you could also make the email appear to be sent to 'Undisclosed Recipients' so that each recipient will realize that they weren't the only one who got the email.</p><p>See&nbsp;<a href='https://www.lifewire.com/send-email-to-undisclosed-outlook-1173806' data-component='link' data-source='inlineLink' data-type='internalLink' data-ordinal='9'>How to Send an Email to Undisclosed Recipients in Outlook</a> for an overview of that, which you can transform to work with your own email client if you don't use Microsoft Outlook.</p><p>&nbsp;</p>
<div id='native_1-0' class='comp native mntl-gpt-dynamic-adunit mntl-gpt-adunit gpt native dynamic is-requested'>
<div id='native' class='wrapper' data-type='native' data-pos='native' data-priority='4' data-sizes='[[1,3]]' data-rtb='true' data-targeting='{}' data-google-query-id='CJ-J0rGP3dkCFSqlUQod8REBtA' style='display: none;'><div id='google_ads_iframe_/479/lifewire/lw_learn-how-email-messaging/native_0__container__' style='border: 0pt none;'><iframe id='google_ads_iframe_/479/lifewire/lw_learn-how-email-messaging/native_0' title='3rd party ad content' name='google_ads_iframe_/479/lifewire/lw_learn-how-email-messaging/native_0' width='1' height='3' scrolling='no' marginwidth='0' marginheight='0' frameborder='0' srcdoc='' style='border: 0px; vertical-align: bottom;'></iframe></div></div>
</div><!-- end: comp native mntl-gpt-dynamic-adunit mntl-gpt-adunit gpt native dynamic -->
<div id='billboard4-sticky-lazy_1-0' class='comp billboard4-sticky-lazy billboard-sticky' data-height='600' data-parent='' style=''>
<div class='spacer' style=''>
<div id='billboard4-dynamic-lazy_2-0' class='comp billboard4-dynamic-lazy billboard-lazy mntl-lazy-ad mntl-gpt-dynamic-adunit mntl-gpt-adunit gpt billboard dynamic js-lazy-ad is-requested' data-ad-width='300' data-ad-height='250' style=''>
<div id='billboard4' class='wrapper' data-type='billboard' data-pos='btf3' data-priority='6' data-sizes='[[300, 250], [300, 253], [250, 250], [200, 200], [180, 150], [4, 1], 'fluid']' data-rtb='true' data-targeting='{}' data-google-query-id='CI3or8653dkCFce1UQodZRwKeA'><div id='google_ads_iframe_/479/lifewire/lw_learn-how-email-messaging/billboard4_0__container__' style='border: 0pt none; display: inline-block; width: 300px; height: 250px;'><iframe frameborder='0' src='https://tpc.googlesyndication.com/safeframe/1-0-17/html/container.html' id='google_ads_iframe_/479/lifewire/lw_learn-how-email-messaging/billboard4_0' title='3rd party ad content' name='' scrolling='no' marginwidth='0' marginheight='0' width='300' height='250' data-is-safeframe='true' sandbox='allow-forms allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts allow-top-navigation-by-user-activation' style='border: 0px; vertical-align: bottom;'></iframe></div></div>
</div><!-- end: comp billboard4-dynamic-lazy billboard-lazy mntl-lazy-ad mntl-gpt-dynamic-adunit mntl-gpt-adunit js-billboard-lazy gpt billboard dynamic -->
</div>
</div><!-- end: comp billboard4-sticky-lazy billboard-sticky -->
<div id='billboard5-sticky-lazy_1-0' class='comp billboard5-sticky-lazy billboard-sticky' data-height='600' data-parent='' style=''>
<div class='spacer' style=''>
<div id='billboard5-dynamic-lazy_2-0' class='comp billboard5-dynamic-lazy billboard-lazy mntl-lazy-ad mntl-gpt-dynamic-adunit mntl-gpt-adunit js-billboard-lazy gpt billboard dynamic' style=''>
<div id='billboard5' class='wrapper' data-type='billboard' data-pos='btf4' data-priority='7' data-sizes='[[300, 250], [300, 254], [250, 250], [200, 200], [180, 150], [6, 1], 'fluid']' data-rtb='true' data-targeting='{}'></div>
</div><!-- end: comp billboard5-dynamic-lazy billboard-lazy mntl-lazy-ad mntl-gpt-dynamic-adunit mntl-gpt-adunit js-billboard-lazy gpt billboard dynamic -->
</div>
</div><!-- end: comp billboard5-sticky-lazy billboard-sticky -->
<div id='billboard6-sticky-lazy_1-0' class='comp billboard6-sticky-lazy billboard-sticky' data-height='600' data-parent='' style=''>
<div class='spacer' style=''>
<div id='billboard6-dynamic-lazy_1-0' class='comp billboard6-dynamic-lazy billboard-lazy mntl-lazy-ad mntl-gpt-dynamic-adunit mntl-gpt-adunit js-billboard-lazy gpt billboard dynamic' style=''>
<div id='billboard6' class='wrapper' data-type='billboard' data-pos='btf5' data-priority='8' data-sizes='[[300, 250], [300, 255], [250, 250], [200, 200], [180, 150], [7, 1], 'fluid']' data-rtb='true' data-targeting='{}'></div>
</div><!-- end: comp billboard6-dynamic-lazy billboard-lazy mntl-lazy-ad mntl-gpt-dynamic-adunit mntl-gpt-adunit js-billboard-lazy gpt billboard dynamic -->
</div>
</div><!-- end: comp billboard6-sticky-lazy billboard-sticky -->
<div id='billboard7-sticky-lazy_1-0' class='comp billboard7-sticky-lazy billboard-sticky' data-height='600' data-parent='' style=''>
<div class='spacer' style=''>
<div id='billboard7-dynamic-lazy_1-0' class='comp billboard7-dynamic-lazy billboard-lazy mntl-lazy-ad mntl-gpt-dynamic-adunit mntl-gpt-adunit js-billboard-lazy gpt billboard dynamic' style=''>
<div id='billboard7' class='wrapper' data-type='billboard' data-pos='btf6' data-priority='9' data-sizes='[[300, 250], [300, 256], [250, 250], [200, 200], [180, 150], [8, 1], 'fluid']' data-rtb='true' data-targeting='{}'></div>
</div><!-- end: comp billboard7-dynamic-lazy billboard-lazy mntl-lazy-ad mntl-gpt-dynamic-adunit mntl-gpt-adunit js-billboard-lazy gpt billboard dynamic -->
</div>
</div><!-- end: comp billboard7-sticky-lazy billboard-sticky -->
<div id='article-feedback_2-0' class='comp article-feedback mntl-article-feedback'>
<div class='article-feedback__rating-section js-rating-section'>
<div class='article-feedback__heading'>Was this page helpful?</div>
<button id='article-feedback__thumbs-up-button_2-0' class='comp article-feedback__thumbs-up-button mntl-button article-feedback__rating-button js-rating-button' data-thumbs-signal='THUMBS_UP'>
<svg class='icon icon-thumbs-up mntl-button__icon article-feedback__rating-icon'>
<use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='#icon-thumbs-up'></use>
</svg>
</button><!-- end: comp article-feedback__thumbs-up-button mntl-button article-feedback__rating-button js-rating-button -->
<button id='article-feedback__thumbs-down-button_2-0' class='comp article-feedback__thumbs-down-button mntl-button article-feedback__rating-button js-rating-button'>
<svg class='icon icon-thumbs-down mntl-button__icon article-feedback__rating-icon'>
<use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='#icon-thumbs-down'></use>
</svg>
</button><!-- end: comp article-feedback__thumbs-down-button mntl-button article-feedback__rating-button js-rating-button -->
</div>
<div class='article-feedback__success-section js-success-section is-hidden'>
<div class='article-feedback__heading'>Thanks for letting us know!</div>
<div class='loc success-section'>
<ul id='social-share_3-0' class='comp social-share mntl-social-share share' data-title='What Does Blind Carbon Copy (Bcc) Mean?' data-description='A blind carbon copy (BCC) sends an email message without revealing the recipient's address. Here's more information, and an example of a Bcc email.' data-tracking-container='true'>
<li class='share-item share-item-facebook'>
<a data-href='https://www.facebook.com/dialog/share?app_id=1833670990199396&amp;display=popup&amp;href=https%3A%2F%2Fwww.lifewire.com%2Fwhat-is-bcc-blind-carbon-copy-1171131%3Futm_source%3Dfacebook%26utm_medium%3Dsocial%26utm_campaign%3Dshareurlbuttons&amp;redirect_uri=https%3A%2F%2Fwww.lifewire.com%2FfacebookShareRedirect.htm' data-network='facebook' class='share-link share-link-facebook' title='Share on Facebook'>
<svg class='icon icon-facebook '>
<use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='#icon-facebook'></use>
</svg>
Share
</a>
</li>
<li class='share-item share-item-pinterest'>
<a data-href='http://pinterest.com/pin/create/button/?url=https%3A%2F%2Fwww.lifewire.com%2Fwhat-is-bcc-blind-carbon-copy-1171131%3Futm_source%3Dpinterest%26utm_medium%3Dsocial%26utm_campaign%3Dshareurlbuttons&amp;description=What+Does+Blind+Carbon+Copy+%28Bcc%29+Mean%3F&amp;media=https%3A%2F%2Ffthmb.tqn.com%2F3QbYJCS6kacqF-DibANTpykcpwI%3D%2F750x0%2FBCC_example-56a2899e3df78cf772774ab5.jpg' data-network='pinterest' class='share-link share-link-pinterest' title='Share on Pinterest'>
<svg class='icon icon-pinterest '>
<use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='#icon-pinterest'></use>
</svg>
Pin
</a>
</li>
<li class='share-item share-item-email'>
<a data-href='https://www.lifewire.com/what-is-bcc-blind-carbon-copy-1171131?utm_source=emailshare&amp;utm_medium=social&amp;utm_campaign=shareurlbuttons' data-network='emailshare' class='share-link share-link-email' title='Email this article'>
<svg class='icon icon-envelope '>
<use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='#icon-envelope'></use>
</svg>
Email
</a>
</li>
</ul><!-- end: comp social-share mntl-social-share share -->
</div>
</div>
<div class='article-feedback__feedback-section js-feedback-section is-hidden'>
<div class='article-feedback__heading'>Tell us why!</div>
<button id='article-feedback__open-form-button_2-0' class='comp article-feedback__open-form-button mntl-button article-feedback__feedback-button js-open-form-button'>
Other
</button><!-- end: comp article-feedback__open-form-button mntl-button article-feedback__feedback-button js-open-form-button -->
<button id='article-feedback__not-enough-details-button_2-0' class='comp article-feedback__not-enough-details-button mntl-button article-feedback__feedback-button js-submit-feedback-button'>
Not enough details
</button><!-- end: comp article-feedback__not-enough-details-button mntl-button article-feedback__feedback-button js-submit-feedback-button -->
<button id='article-feedback__hard-to-understand-button_2-0' class='comp article-feedback__hard-to-understand-button mntl-button article-feedback__feedback-button js-submit-feedback-button'>
Hard to understand
</button><!-- end: comp article-feedback__hard-to-understand-button mntl-button article-feedback__feedback-button js-submit-feedback-button -->
<form action='/ugc-feedback' method='post' class='article-feedback__feedback-form js-feedback-form is-hidden'>
<textarea class='article-feedback__feedback-text js-feedback-text' placeholder='Tell us more...' required='required' maxlength='1500'></textarea>
<button id='article-feedback__submit-button_2-0' class='comp article-feedback__submit-button mntl-button'>
Submit
</button><!-- end: comp article-feedback__submit-button mntl-button -->
<input type='hidden' name='doc-id' value='1171131' class='js-doc-id'>
</form>
</div>
</div><!-- end: comp article-feedback mntl-article-feedback -->
</div>";

$mail->Body($Body);

//TODO: preview how the mail will look
$mail->Preview(1);

//send the mail
$mail->Send();

/*
 $filename = '250px-Uroplatus_fimbriatus_3.jpg';
    $path = 'your path goes here';
    $file = $filename;

    $mailto = 'steyn@localhost, neil@localhost';
    $subject = 'Subject';
    $message = 'My message';

    $content = file_get_contents($file);
    $content = chunk_split(base64_encode($content));

    // a random hash will be necessary to send mixed content
    $separator = md5(time());

    // carriage return type (RFC)
    $eol = "\r\n";

    // main header (multipart mandatory)
    $headers = "From: name <test@test.com>" . $eol;
    $headers .= "MIME-Version: 1.0" . $eol;
    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
    $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
    $headers .= "This is a MIME encoded message." . $eol;

    // message
    $body = "--" . $separator . $eol;
    $body .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
    $body .= "Content-Transfer-Encoding: 8bit" . $eol;
    $body .= $message . $eol;

    // attachment
    $body .= "--" . $separator . $eol;
    $body .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
    $body .= "Content-Transfer-Encoding: base64" . $eol;
    $body .= "Content-Disposition: attachment" . $eol;
    $body .= $content . $eol;
    $body .= "--" . $separator . "--";

    //SEND Mail
    if (mail($mailto, $subject, $body, $headers)) {
        echo "mail send ... OK"; // or use booleans here
    } else {
        echo "mail send ... ERROR!";
        print_r( error_get_last() );
    }
*/

?>
