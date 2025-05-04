<?php
require_once('config.php');
require_once('phpMailer/PHPMailerAutoload.php');
require_once("classes/CommonFunction.php");


$subject ="Test Subject";

$body = " my text";

$to = "merrineee@gmail.com";








		$mail = new PHPMailer(true);
		$mail->IsSMTP();
		$mail->SMTPDebug  = false;
		$mail->SMTPKeepAlive = true;
		$mail->SMTPAuth   = true;
		$mail->Host       = SITE_NAME ;
		$mail->Username   = SITE_USER;
		$mail->Password   = SITE_PASS;
		$mail->Hostname       = SITE_NAME;
		$mail->SetFrom(SITE_USER, 'Dev Engineers');
		$mail->Sender = SITE_USER;
		$mail->AddReplyTo(SITE_USER, 'Dev Engineers');
		$mail->AddAddress($to,'Richard');

		$mail->SMTPAutoTLS = false;
		$mail->SMTPSecure = 'none';

		$mail->Subject = $subject;

		$mail->IsHTML(true);


		$mail->AltBody = '';
		$mail->MsgHTML($body);

            if (!$mail->send()) {

				//var_dump($mail);
				 echo "Email not sent successfully";


            } else {

				 echo "Email sent successfully";

            }


?>

