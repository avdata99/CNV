<?php
$subject = "[CNV] $subject";
$message = "$message - $contact";
$mail_from = "anon@cnv.hhba.info";
//$header = "From: $name <$mail_from>";
$header = "From: anónimo";
$to = 'DESTINO';

mail($to, $subject, $message, $header);
?>
