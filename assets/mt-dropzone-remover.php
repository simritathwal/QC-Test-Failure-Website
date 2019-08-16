<?php

// Check if its an ajax request, exit if not
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    $output = json_encode(array(
        'type' => 'error',
        'text' => 'Sorry Request must be Ajax POST'
    ));
    die($output);
}

// Notify via Email once the file is removed completely
if ( $_POST && (filter_var($_POST["event"], FILTER_SANITIZE_STRING) == "onRemovedFile") ) {
    // Remove the file
    if ($_POST) {
        $uploadDir   = './uploads/';
        $fileName	 = filter_var($_POST["fileName"], FILTER_SANITIZE_STRING);

        if(isset($fileName)){
            unlink($uploadDir.$fileName);
        }
    }

    // Get the email address info and sanitize
    include('mt-dropzone-email.php');
    $emailAddress       = filter_var($receiverEmail, FILTER_VALIDATE_EMAIL);

    // Sanitize input data using PHP filter_var().
	$fileName		    = filter_var($_POST["fileName"], FILTER_SANITIZE_STRING);
    $emailSender		= filter_var($_POST["emailSender"], FILTER_SANITIZE_STRING);
    $emailSubject		= filter_var($_POST["fileRemovedEmailSubject"], FILTER_SANITIZE_STRING);
	$emailMessage		= filter_var($_POST["fileRemovedEmailMessage"], FILTER_SANITIZE_STRING);

    // File Url
    $uploadLoc          = "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/uploads';

	// Email Body
	$message_body  = "<p>" . $emailMessage . "</p>" . "\r\n";
    $message_body .= "<a href='" . $uploadLoc .'/'. $fileName . "'>" . $fileName . "</a>" . "\r\n";

	// Proceed with PHP email - DO NOT change anything here
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . $emailSender . '<' . $emailAddress . '>' . "\r\n";
    $headers .= "X-Mailer:  PHP/" . phpversion() . "\r\n";

	$send_mail = mail($emailAddress, $emailSubject, $message_body, $headers);

	if(!$send_mail) {
		// If mail couldn't be sent, output the error. Please heck your PHP email configuration with your hosting.
		$output = json_encode(array('type'=>'error', 'text' => 'Could not send mail! Please check your PHP mail configuration.'));
		die($output);
	} else {
		$output = json_encode(array('type'=>'success', 'text' => 'Email Successfully Sent'));
		die($output);
	}
}
