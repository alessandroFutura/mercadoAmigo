<?php

require( PATH_PHPMAILER . "class.phpmailer.php" );

class Mail
{
    public function __construct( $params )
    {
        $this->mail = new PHPMailer();
        $this->mail->IsSMTP();
        $this->mail->SMTPDebug = 0;
        $this->mail->Debugoutput = 'html';
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Host = 'mail.omercadoamigo.com.br';
        $this->mail->Port = 587;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'contato@omercadoamigo.com.br';
        $this->mail->Password = '*****';
        $this->mail->SetFrom( 'contato@omercadoamigo.com.br' , 'O Mercado Amigo' );
        $this->mail->IsHTML(true);

        $this->mail->Subject = $params["subject"];
        foreach( $params["recipients"] as $recipient ){
            $this->mail->AddAddress( $recipient["mail_address"], $recipient["mail_recipient"] );
        }
        $this->mail->Body = $params["message"];
        $this->mail->AltBody = $params["message"];
    }

    public function sendMail()
    {
        $ret = $this->mail->Send();
        $this->mail->ClearAllRecipients();
        $this->mail->ClearAttachments();

        return $ret;
    }

}

?>
