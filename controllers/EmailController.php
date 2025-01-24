<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class email{
    public  $from;
    public  $fromname;
    public  $to;
    public  $toname;
    public  $body;
    public  $altbody;
    public $Subject;
}
class EmailController
{
    
	public function __construct()
	{
        require '../vendor/autoload.php';
	}
    public function sendEmail(email $email){
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'mail.mocsa.pt';
        $mail->SMTPAuth   = true;
        $mail->Password = 'SqQrm02U+ab4';
        $mail->Username = 'redirect@mocsa.pt';
        $mail->Port = 465;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Encriptação SSL
         // Recetores
        $mail->setFrom($email->from, $email->fromname);
        $mail->addAddress($email->to, $email->toname);

        // Conteúdo do e-mail
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->isHTML(true);
        $mail->Subject = $email->Subject;
        $mail->Body    = $email->body;
        $mail->AltBody = $email->altbody;

        $mail->send();
        echo 'E-mail enviado com sucesso!';
    } 
}
