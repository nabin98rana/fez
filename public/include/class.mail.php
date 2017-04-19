<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Eventum - Issue Tracking System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003, 2004 MySQL AB                                    |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// | Authors: Jo�o Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: s.class.mail.php 1.26 04/01/23 00:25:55-00:00 jpradomaia $
//


/**
 * Class to handle the business logic related to sending email to
 * outside recipients. This class utilizes the PEAR::Mail
 * infrastructure to deliver email in a compatible way across
 * different platforms.
 *
 * @version 1.0
 * @author Jo�o Prado Maia <jpm@mysql.com>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "class.mail_queue.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.mime_helper.php");

class Mail_API
{
	// variable to keep the Mail_mime object
	var $mime;
	// variable to keep the headers to be used in the email
	var $headers = array();
	// text version of this message
	var $text_body = '';


	/**
	 * Class constructor. It includes and initializes the required
	 * PEAR::Mail related objects
	 *
	 * @access  public
	 */
	function Mail_API()
	{
		@include_once(APP_PEAR_PATH . 'Mail.php');
		@include_once(APP_PEAR_PATH . 'Mail/mime.php');
		$this->mime = new Mail_mime("\r\n");
	}


	/**
	 * Believe it or not, this is a method that will remove excess occurrences
	 * of 'Re:' that commonly are found in email subject lines.
	 *
	 * @access  public
	 * @param   string $subject The subject line
	 * @return  string The subject line with the extra occurrences removed from it
	 */
	function removeExcessRe($subject)
	{
		$re_pattern = "/^(([Rr][Ee][Ss]?|�����|Antwort|SV|[Aa][Ww])(\[[0-9]+\])?[ \t]*: ){2}(.*)/";
		if (preg_match($re_pattern, $subject)) {
			$subject = preg_replace($re_pattern, 'Re: $4', $subject);
			return Mail_API::removeExcessRe($subject);
		} else {
			return $subject;
		}
	}


	/**
	 * Returns the canned explanation about why an email message was blocked
	 * and saved into an internal note.
	 *
	 * @access  public
	 * @return  string The canned explanation
	 */
	function getCannedBlockedMsgExplanation()
	{
		$msg = "WARNING: This message was blocked because the sender was not allowed to send emails to the associated issue. ";
		$msg .= "Only staff members listed in the assignment or authorized replier fields can send emails.\n";
		$msg .= str_repeat('-', 70) . "\n\n";
		return $msg;
	}


	/**
	 * Checks whether the given string contains the magic cookie or not.
	 *
	 * @access  public
	 * @param   string $message The email message
	 * @return  boolean
	 */
	function hasMagicCookie($message)
	{
		if (strstr($message, 'really-send-this-mail')) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Returns the given string without the magic cookie, if any.
	 *
	 * @access  public
	 * @param   string $message The email message
	 * @return  string The message without the magic cookie
	 */
	function stripMagicCookie($message)
	{
		return str_replace('really-send-this-mail', '', $message);
	}


	/**
	 * Method used to parse a string and return all email addresses contained
	 * within it.
	 *
	 * @access  public
	 * @param   string $str The string containing email addresses
	 * @return  array The list of email addresses
	 */
	function getEmailAddresses($str)
	{
		$str = Mail_API::fixAddressQuoting($str);
		$str = Mime_Helper::encodeValue($str);
		$structs = Mail_RFC822::parseAddressList($str);
		$addresses = array();
		foreach ($structs as $structure) {
			if ((!empty($structure->mailbox)) && (!empty($structure->host))) {
				$addresses[] = $structure->mailbox . '@' . $structure->host;
			}
		}
		return $addresses;
	}


	/**
	 * Method used to build a properly quoted email address, in the form of
	 * "Sender Name" <sender@example.com>.
	 *
	 * @access  public
	 * @param   string $address The email address value
	 * @return  array The address information
	 */
	function fixAddressQuoting($address)
	{
		// check if we have a <
		if (strstr($address, '<')) {
			$address = stripslashes($address);
			if (strstr($address, "'")) {
				$first_part = substr($address, 0, strpos($address, '<') - 1);
				$first_part = '"' . $first_part . '"';
				$second_part = substr($address, strpos($address, '<'));
				$address = $first_part . ' ' . $second_part;
				// if the address was already in the format "'name'" <address>, then this code
				// will end up adding even more double quotes, so let's remove any excess
				return str_replace('""', '"', $address);
				//                return $first_part . ' ' . $second_part;
			} else {
				return $address;
			}
		} else {
			return $address;
		}
	}


	/**
	 * Method used to break down the email address information and
	 * return it for easy manipulation.
	 *
	 * @access  public
	 * @param   string $address The email address value
	 * @return  array The address information
	 */
	function getAddressInfo($address)
	{
		$address = Mail_API::fixAddressQuoting($address);
		$address = Mime_Helper::encodeValue($address);
		include_once(APP_PEAR_PATH . "Mail/RFC822.php");
		$t = Mail_RFC822::parseAddressList($address);
		if (get_class($t) != 'PEAR_Error') {
			return array(
            'sender_name' => $t[0]->personal,
            'email'       => $t[0]->mailbox . '@' . $t[0]->host,
            'username'    => $t[0]->mailbox,
            'host'        => $t[0]->host
			);
		} else {
			return array(
            'sender_name' => 'spamerror',
            'email'       => 'spam@spam.com',
            'username'    => 'spam',
            'host'        => 'spam.com'
            );
		}
	}


	/**
	 * Method used to get the email address portion of a given
	 * recipient information.
	 *
	 * @access  public
	 * @param   string $address The email address value
	 * @return  string The email address
	 */
	function getEmailAddress($address)
	{
		$info = Mail_API::getAddressInfo($address);
		return $info['email'];
	}


	/**
	 * Method used to get the name portion of a given recipient information.
	 *
	 * @access  public
	 * @param   string $address The email address value
	 * @return  string The name
	 */
	function getName($address)
	{
		$info = Mail_API::getAddressInfo($address);
		if (!empty($info['sender_name'])) {
			return $info['sender_name'];
		} else {
			return $info['email'];
		}
	}


	/**
	 * Method used to get the formatted name of the passed address
	 * information.
	 *
	 * @access  public
	 * @param   string $name The name of the recipient
	 * @param   string $email The email of the recipient
	 * @return  string
	 */
	function getFormattedName($name, $email)
	{
		return $name . " <" . $email . ">";
	}


	/**
	 * Method used to get the application specific settings regarding
	 * which SMTP server to use, such as login and server information.
	 *
	 * @access  public
	 * @return  array
	 */
	function getSMTPSettings()
	{
	  $port = 25;
    if (defined('APP_EMAIL_SMTP_PORT')) {
      $port = APP_EMAIL_SMTP_PORT;
    }
		return array(
		  'host' => APP_EMAIL_SMTP,
      'port' => $port
    );
	}

	/**
	 * Method used to set the text version of the body of the MIME
	 * multipart message that you wish to send.
	 *
	 * @access  public
	 * @param   string $text The text-based message
	 * @return  void
	 */
	function setTextBody($text)
	{
		$this->text_body = $text;
		$this->mime->setTXTBody($text);
	}


	/**
	 * Method used to set the HTML version of the body of the MIME
	 * multipart message that you wish to send.
	 *
	 * @access  public
	 * @param   string $html The HTML-based message
	 * @return  void
	 */
	function setHTMLBody($html)
	{
		$this->mime->setHTMLBody($html);
	}


	/**
	 * Method used to add an embedded image to a MIME message.
	 *
	 * @access  public
	 * @param   string $filename The full path to the image
	 * @return  void
	 */
	function addHTMLImage($filename)
	{
		$this->mime->addHTMLImage($filename);
	}


	/**
	 * Method used to set extra headers that you may wish to use when
	 * sending the email.
	 *
	 * @access  public
	 * @param   mixed $header The header(s) to set
	 * @param   mixed $value The value of the header to be set
	 * @return  void
	 */
	function setHeaders($header, $value = FALSE)
	{
		if (is_array($header)) {
			foreach ($header as $key => $value) {
				$this->headers[$key] = Mime_Helper::encodeValue($value);
			}
		} else {
			$this->headers[$header] = Mime_Helper::encodeValue($value);
		}
	}


	/**
	 * Method used to add an email address in the Cc list.
	 *
	 * @access  public
	 * @param   string $email The email address to be added
	 * @return  void
	 */
	function addCc($email)
	{
		$this->mime->addCc($email);
	}


	/**
	 * Removes the warning message contained in a message, so that certain users
	 * don't receive that extra information as it may not be relevant to them.
	 *
	 * @access  public
	 * @param   string $str The body of the email
	 * @return  string The body of the email, without the warning message
	 */
	function stripWarningMessage($str)
	{
		$str = str_replace(Mail_API::getWarningMessage('allowed'), '', $str);
		$str = str_replace(Mail_API::getWarningMessage('blocked'), '', $str);
		return $str;
	}


	/**
	 * Returns the warning message that needs to be added to the top of routed
	 * issue emails to alert the recipient that he can (or not) send emails to
	 * the issue notification list.
	 *
	 * @access  public
	 * @param   string $type Whether the warning message is of an allowed recipient or not
	 * @return  string The warning message
	 */
	function getWarningMessage($type)
	{
		if ($type == 'allowed') {
			$str = 'ADVISORY: Your reply will be sent to the notification list.';
		} else {
			$str = 'WARNING: If replying, add yourself to Authorized Repliers list first.';
		}
		return $str;
	}





	/**
	 * Strips out email headers that should not be sent over to the recipient
	 * of the routed email. The 'Received:' header was sometimes being used to
	 * validate the sender of the message, and because of that some emails were
	 * not being delivered correctly.
	 *
	 * @access  public
	 * @param   string $headers The full headers of the email
	 * @return  string The headers of the email, without the stripped ones
	 */
	function stripHeaders($headers)
	{
		$headers = preg_replace('/\r?\n([ \t])/', '$1', $headers);
		$headers = preg_replace('/^(Received: .*\r?\n)/m', '', $headers);
		// also remove the read-receipt header
		$headers = preg_replace('/^(Disposition-Notification-To: .*\r?\n)/m', '', $headers);
		return $headers;
	}


	/**
	 * Method used to send the SMTP based email message.
	 *
	 * @access  public
	 * @param   string $from The originator of the message
	 * @param   string $to The recipient of the message
	 * @param   string $subject The subject of the message
	 * @return  string The full body of the message that was sent
	 */
	function send($from, $to, $subject, $save_email_copy = 0)
	{
		// encode the addresses
		$from = MIME_Helper::encodeAddress($from);
		$to = MIME_Helper::encodeAddress($to);
		$subject = MIME_Helper::encode($subject);

		$this->setTextBody( $this->text_body);

    // Build with UTF-8 charsets
    $params = array(
      'html_charset' => 'utf-8',
      'text_charset' => 'utf-8'
    );
		$body = $this->mime->get($params);

		$this->setHeaders(array(
            'From'    => $from,
            'To'      => Mail_API::fixAddressQuoting($to),
            'Subject' => $subject
		));
		$hdrs = $this->mime->headers($this->headers);
		$res = Mail_Queue::add($to, $hdrs, $body, $save_email_copy);
		if (PEAR::isError($res)) {
			return $res;
		} else {
			// RFC 822 formatted date
			$header = 'Date: ' . date('D, j M Y H:i:s O') . "\r\n";
			// return the full dump of the email
			foreach ($hdrs as $name => $value) {
				$header .= "$name: $value\r\n";
			}
			$header .= "\r\n";
			return $header . $body;
		}
	}


	/**
	 * Method used to get the list of issues to be displayed in the grid layout.
	 *
	 * @access  public
	 * @param   string $from The sender of the email
	 * @param   string $to The recipient of the email
	 * @param   string $subject The subject of this email
	 * @return  string The full header version of the email
	 */
	function getFullHeaders($from, $to, $subject)
	{
		// encode the addresses
		$from = MIME_Helper::encodeAddress($from);
		$to = MIME_Helper::encodeAddress($to);
		$subject = MIME_Helper::encode($subject);

		$body = $this->mime->get();
		$this->setHeaders(array(
            'From'    => $from,
            'To'      => $to,
            'Subject' => $subject
		));
		$hdrs = $this->mime->headers($this->headers);
		// RFC 822 formatted date

		// @@@ CK - 5/4/2005 - Changed the gmdate to date.
		$header = 'Date: ' . date('D, j M Y H:i:s O') . "\r\n";
		//        $header = 'Date: ' . gmdate('D, j M Y H:i:s O') . "\r\n";
		// return the full dump of the email
		foreach ($hdrs as $name => $value) {
			$header .= "$name: $value\r\n";
		}
		$header .= "\r\n";
		return $header . $body;
	}


	/**
	 * Method used to save a copy of the given email to a configurable address.
	 *
	 * @access  public
	 * @param   string $hdrs The headers to be used in this email
	 * @param   string $body The body of the email
	 */
	function saveEmailInformation($hdrs, $body)
	{
		$log = FezLog::get();
		static $subjects;

		// do we really want to save every outgoing email?
		$setup = Setup::load();
		if ((@$setup['smtp']['save_outgoing_email'] != 'yes') || (empty($setup['smtp']['save_address']))) {
			return false;
		}

		// ok, now parse the headers text and build the assoc array
		$structure = Mime_Helper::decode($hdrs . "\n\n" . $body, FALSE, FALSE);
		$_headers =& $structure->headers;
		$header_names = Mime_Helper::getHeaderNames($hdrs);
		$headers = array();
		foreach ($_headers as $lowercase_name => $value) {
			$headers[$header_names[$lowercase_name]] = $value;
		}

		// prevent duplicate emails from being sent out...
		$subject = @$headers['Subject'];
		if (@in_array($subject, $subjects)) {
			return false;
		}

		// replace the To: header with the requested address
		$address = $setup['smtp']['save_address'];
		$headers['To'] = $address;

		$params = Mail_API::getSMTPSettings();
		$mail =& Mail::factory('smtp', $params);
		$res = $mail->send($address, $headers, $body);
		if (PEAR::isError($res)) {
			$log->err(array($res->getMessage(), $res->getDebugInfo(), __FILE__, __LINE__));
		}

		$subjects[] = $subject;
	}
	//}

	/**
	 * Since Mail::prepareHeaders() is not supposed to be called statically, this method
	 * instantiates an instance of the mail class and calls prepareHeaders on it.
	 *
	 * @param array $headers The array of headers to prepare, in an associative
	 *              array, where the array key is the header name (ie,
	 *              'Subject'), and the array value is the header
	 *              value (ie, 'test'). The header produced from those
	 *              values would be 'Subject: test'.
	 * @return mixed Returns false if it encounters a bad address,
	 *               otherwise returns an array containing two
	 *               elements: Any From: address found in the headers,
	 *               and the plain text version of the headers.
	 */
	public static function prepareHeaders($headers)
	{
		$params = Mail_API::getSMTPSettings();
		$mail =& Mail::factory('smtp', $params);
		return $mail->prepareHeaders($headers);
	}
}
