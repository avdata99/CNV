<?php
require_once('recaptchalib.php');

//print_r($_POST);

$privatekey = "6LfC4eUSAAAAAAdgRBE5348CHAcRolzty36a3qMS";
$resp = recaptcha_check_answer ($privatekey,
                              $_SERVER["REMOTE_ADDR"],
                              $_POST["recaptcha_challenge_field"],
                              $_POST["recaptcha_response_field"]);

if (!$resp->is_valid) {
  // What happens when the CAPTCHA was entered incorrectly
  echo "El reCAPTCHA no fue ingresado correctamente. Por favor, inténtelo de nuevo.";
  http_response_code(500);

} else {
  // Your code here to handle a successful verification
  $subject = "[CNV] " . $_POST['name'];

  $message = <<<MSG
-- Mensaje --
{$_POST['message']}

-- Contacto --
{$_POST['contact']}

REMOTE_ADDR: {$_SERVER['REMOTE_ADDR']}
HTTP_X_FORWARDED_FOR: {$_SERVER['HTTP_X_FORWARDED_FOR']}
MSG;

  $mail_from = 'anon@cnv.hhba.info';
  $header = 'From: anónimo';

  // FIXME definir dirección destino
  $to = '{{DEFINIR}}';

  $mail_res = mail($to, $subject, $message, $header);
  if ($mail_res) {
    echo "ok";
  } else {
    echo "Ocurrió un error al intentar enviar el formulario. Por favor, inténtelo de nuevo.";
    http_response_code(500);
  }
}
?>
