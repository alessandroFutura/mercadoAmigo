<?php

    include "../../config/start.php";

    Session::isUser();

    switch( $_GET["module"] )
    {
        case "getList" :
            $contact_type = new PersonContactType( $_POST );
            $smarty->assign( "l_contact_type", $contact_type->getList() );

            $smarty->assign("page_name", "Pessoas");
            $smarty->assign("page_description", "Cadastro de Categorias");

            // chamando o template
            if (!@$_POST['ajax']){
                $smarty->display( PATH_PLUGIN . "person/templates/person_contact_type_getList.html" );
            } else{
                $smarty->display( PATH_PLUGIN . "person/templates/person_contact_type_template.html" );
            }
        break;
        case "insert" :
            if (@$_POST) {
                $client = new PersonContactType( $_POST );
                $client->insert();
                Json::get(200);
            }
        break;
        case "edit" :
            if ( @$_GET["person_contact_type_id"] ){
                if ( @$_POST["person_contact_type_id"] ){
                    $contact_type = new PersonContactType( $_POST );
                    $contact_type->update();
                    Json::get(200);
                }

                $contact_type = new PersonContactType( $_GET );
                $smarty->assign( "contact_type", $contact_type->get());
            } else{
                die($errorMessage["no_parameters_get"]);
            }
        break;
        case "del" :
            $contact_type = new PersonContactType( $_POST );
            $contact_type->delete();
            die();
        break;
    }
