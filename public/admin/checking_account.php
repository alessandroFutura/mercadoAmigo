<?php
	// iniciar as configuraçõees do site
	include "../../config/start.php";

	// verificar admin na variavel de sessão
	Session::isUser();

	// controle das ações do plugin partner
	switch( $_GET["module"] )
	{
		case "getList" :

		    $account = new CheckingAccount(Array(
                "person_id" => $user->user_profile_id > 1001 ? $user->person_id : NULL,
		        "get_Person" => 1,
                "get_Receivable" => 1,
            ));
		    $l_account = $account->getList();

            $status = Array(
                "A" => (Object)Array(
                    "color" => "#333", "title" => "Aberto"
                ),
                "L" => (Object)Array(
                    "color" => "green", "title" => "Liberado"
                ),
                "C" => (Object)Array(
                    "color" => "red", "title" => "Crédito"
                )
            );

		    if( sizeof($l_account) ){
		        foreach( $l_account as $account ){
                    $person = new Person(Array(
                        "person_id" => $account->person_origin_id
                    ));
                    $account->PersonOrigin = $person->get();
                    $account->checking_account_status = $status[$account->checking_account_status];
                }
            }

            $smarty->assign("page_name", "Conta Corrente");
			$smarty->assign("page_description", "Comissões");

			$smarty->assign( "l_checking_account", $l_account );

			// chamando o template
			if (!@$_POST['ajax']){
				$smarty->display( PATH_PLUGIN . "account/templates/account_getList.html" );
			} else{
				$smarty->display( PATH_PLUGIN . "account/templates/account_template.html" );
			}
		break;
	}
