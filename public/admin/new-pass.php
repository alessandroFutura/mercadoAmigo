<?php
    // iniciar as configuraçõees do site
    include "../../config/start.php";

    // esta logado?
    Session::isLogged();

    if( @$_GET["data"] )
    {
        $data = explode("*", base64_decode($_GET["data"]));

        if( @$data[0] && @$data[1] ) {

            $login = new User(Array(
                "user_user" => $data[1]
            ));
            $login = $login->get();

            if (@$login) {

                if(strtotime( date( "Y-m-d H:i:s", strtotime( "{$data[0]} + 1 day"))) < strtotime(date("Y-m-d H:i:s")) )
                {
                    $smarty->assign("error", "Url Inválida!");
                    $smarty->assign("error_desc", "Sua url perdeu o prazo de validade. Retorne a tela de login e solicite uma nova url para alterar a sua senha.");
                }
                else
                {
                    if (strtotime($login->user_update) > strtotime($data[0])) {
                        $smarty->assign("error", "Url Inválida!");
                        $smarty->assign("error_desc", "Sua url já foi utilizada.");
                    }
                    else
                    {
                        if (@$_POST["user_pass"] && @$_POST["user_pass_confirm"]) {
                            if ($_POST["user_pass"] == $_POST["user_pass_confirm"]) {
                               $user = new User(Array(
                                    "user_id" => $login->user_id,
                                    "user_active" => ( $login->user_active == "Y" ? "on" : NULL ),
                                    "user_pass" => md5($_POST["user_pass"]),
                                    "user_accept_contract" => ( $login->user_accept_contract == "Y" ? "on" : NULL )
                                ));
                                $user->update();
                                $smarty->assign("success", "Senha alterada com sucesso!");
                            } else
                                $smarty->assign("error", "As senhas não conferem!");
                        }
                    }
                }

                $smarty->assign("login", $login);
            }
            else
            {
                $smarty->assign("error", "Url Inválida!");
                $smarty->assign("error_desc", "Seu usuário não foi encontrado. Procure o administrador para obter suporte.");
            }
        }
        else
        {
            $smarty->assign( "error", "Url Inválida!" );
            $smarty->assign("error_desc", "A sua url não é válida! Procure o administrador para obter suporte.");
        }
    }
    else
    {
        $smarty->assign( "error", "Url Inválida!" );
        $smarty->assign("error_desc", "A sua url não é válida! Procure o administrador para obter suporte.");
    }

    // chamando o template da home
    $smarty->display( PATH_TEMPLATES_ADMIN . "new-pass.html" );
?>