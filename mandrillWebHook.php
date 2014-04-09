<?php

require_once(dirname(__FILE__) . '/../../../wp-load.php' );
require_once(dirname(__FILE__) . '/hf-accountability.php');

if(isset($_POST['mandrill_events'])) {
	$Mailer = new HfMailer();
	$data = json_decode(stripslashes($_POST['mandrill_events']));
	//$Mailer->sendEmail(1, 'Webhook Test Data', print_r($data, true) );
}