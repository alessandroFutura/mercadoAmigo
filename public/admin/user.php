<?php
	// iniciar as configuraçõees do site
	include "../../config/start.php";

	// verificar admin na variavel de sessão
	Session::isUser();

	// controle das ações do plugin partner
	switch( $_GET["module"] )
	{
		case "getList" :
            $params = $_POST;
            $params["get_UserProfile"] = 1;
            $params["get_Person"]      = 1;

            $user = new User( $params );
            $smarty->assign( "l_user", $user->getList() );

            $profile = new UserProfile( [] );
            $smarty->assign( "l_profile", $profile->getList() );

            $person = new Person([]);
            $smarty->assign( "l_person", $person->getList() );

            $smarty->assign("page_name", "Usuário");
			$smarty->assign("page_description", "Cadastro de Usuário");

			// chamando o template
			if (!@$_POST['ajax']){
				$smarty->display( PATH_PLUGIN . "user/templates/user_getList.html" );
			} else{
				$smarty->display( PATH_PLUGIN . "user/templates/user_template.html" );
			}
		break;
		case "insert" :
			if (@$_POST) {
                $params = $_POST;
                $pass = $params["user_pass"];
                $params["user_pass"] = md5($params["user_pass"]);
                $user = new User( $params );
                $user_id = $user->insert();

                $user = new User(Array(
                    "user_id" => $user_id
                ));
                $message = $user->getMessage( $user->get(), $pass );

                $mail = new Mail(Array(
                    "subject" => "Bem-Vindo ao Mercado Amigo",
                    "recipients" => Array(
                        Array(
                            "mail_address" => $params["user_mail"],
                            "mail_recipient" => $params["user_name"]
                        )
                    ),
                    "message" => $message
                ));

                $mail->sendMail();

                Json::get( $jsonStatus[200] );
			}
		break;
		case "edit" :
			if( @$_GET["user_id"] ){
				if( @$_POST["user_id"] ){
                    $params = $_POST;
                    if( @$params["user_pass"]){
                        $params["user_pass"] = md5($params["user_pass"]);
                    }
                    $user = new User( $params );
                    $user->update();
				}
                $user = new User( $_GET );
                $smarty->assign( "user", $user->get() );
			} else{
				die($errorMessage["no_parameters_get"]);
			}
		break;
        case "editPass" :
            if ( @$_POST["user_id"] && @$_POST["user_pass"] && @$_POST["user_pass_old"]){
                $user = new User(Array(
                    "user_id" => $_POST["user_id"],
                    "user_pass" => md5($_POST["user_pass_old"])
                ));
                $user = $user->get();

                if( @$user ) {
                    $user = new User(Array(
                        "user_id" => $_POST["user_id"],
                        "user_active" => "Y",
                        "user_pass" => md5($_POST["user_pass"])
                    ));
                    $user->update();
                    Json::get( $jsonStatus[200], (Object)Array( "status" => 1 ) );
                }
                Json::get( $jsonStatus[200], (Object)Array( "status" => 0 ) );
            }
            header('HTTP/1.0 420 Method Failured');
            Json::get( $jsonStatus[420] );
        break;
		case "del" :
            $user = new User( $_POST );
            $user->delete();
			die();
		break;
        case "contractAccept":
            $user = new User(Array(
                "user_id" => $user->user_id,
                "user_active" => "on",
                "user_accept_contract" => "on"
            ));
            $user->update();
            Json::get( $jsonStatus[200] );
        break;
	}
