<?php
	// iniciar as configuraçõees do site
	include "../../config/start.php";

    // verificar admin na variavel de sessão
    Session::isUser();

	// controle das ações do plugin partner
	switch( $_GET["module"] )
	{
		case "getList" :
            $profile = new UserProfile( $_POST );
            $smarty->assign( "l_profile", $profile->getList() );
            $smarty->assign("user_profile_access", treeAccess(0) );

			$smarty->assign("page_name", "Usuário");
			$smarty->assign("page_description", "Cadastro de Perfil do Usuário");

			// chamando o template
			if (!@$_POST['ajax']){
				$smarty->display( PATH_PLUGIN . "user/templates/user_profile_getList.html" );
			} else{
				$smarty->display( PATH_PLUGIN . "user/templates/user_profile_template.html" );
			}
		break;
        case "insert" :
            if (@$_POST) {
                $params = $_POST;
                $profile = new UserProfile( $params );
                $profile_id = $profile->insert();
                $profile_access = new UserProfileAccess( $params );
                $profile_access->editAccess($profile_id);
            }
            break;
        case "edit" :
            if ( @$_GET["user_profile_id"] ){
                if ( @$_POST["user_profile_id"] ){
                    $params = $_POST;
                    $profile = new UserProfile( $params );
                    $profile->update();
                    $profile_access = new UserProfileAccess( $params );
                    $profile_access->editAccess();
                }

                $params = $_GET;
                $params["json"] = NULL;
                $params["get_UserProfileAccess"] = 1;
                $profile = new UserProfile( $params );
                $user_profile = $profile->get();
                $user_profile->UserProfileAccess = treeAccess($user_profile->UserProfileAccess);

                if( @$_GET["json"] ){
                    Json::get( $jsonStatus[200], $user_profile );
                }

                $smarty->assign( "profile", $user_profile);
            } else{
                die($errorMessage["no_parameters_get"]);
            }
            break;
		case "del" :
            $profile = new UserProfile( $_POST );
            $profile->delete();
            $access = new UserProfileAccess($_POST);
            $access->delete();
			die();
		break;
	}

?>