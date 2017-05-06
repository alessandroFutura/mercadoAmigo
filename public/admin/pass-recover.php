<?php
    // iniciar as configuraçõees do site
    include "../../config/start.php";

    // esta logado?
    Session::isLogged();

    if( @$_POST["user_user"] ){
        $login = new User(Array(
            "user_user" => $_POST["user_user"]
        ));
        $login = $login->get();

        if( @$login )
        {
            $url = URI_PUBLIC . "admin/nova-senha/" . base64_encode( date('Y-m-d H:i:s') . "*{$login->user_user}" );
            $message = "Olá $login->user_name!<br/><br/>";
            $message .= "Foi solicitado através do site O Mercado Amigo a recuperação da senha de acesso do seu escritório virtual.<br>";
            $message .= "<b><a href='{$url}'>Clique aqui</a></b> para gerar uma nova senha.<br/><br/>";
            $message .= "Caso não consiga acessar o link acima, copie e cole a seguinte url no seu navegador:<br/><b>{$url}</b><br/>";
            $message .= "Sua url tem a validade de 24h.<br/><br/>";
            $message .= "Caso não reconheça a origem desse email, por favor, desconsidere a mensagem.";

            $mail = new Mail(Array(
                "subject" => "Recuperação de Senha - O Mercado Amigo",
                "recipients" => Array(
                    Array(
                        "mail_address" => $login->user_mail,
                        "mail_recipient" => $login->user_name
                    )
                ),
                "message" => $message
            ));

            if( $mail->sendMail() ){
                $smarty->assign("success", "Email enviado com sucesso!");
            } else {
                $smarty->assign("error", "Erro ao enviar o email!");
            }
        }
        else
        {
            $smarty->assign( "error", "Usuário não encontrado!" );
        }
    }

    // chamando o template da home
    $smarty->display( PATH_TEMPLATES_ADMIN . "pass-recover.html" );
?>