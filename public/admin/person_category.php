<?php

    include "../../config/start.php";

    Session::isUser();

    switch( $_GET["module"] )
    {
        case "getList" :
            $category = new PersonCategory( $_POST );
            $smarty->assign( "l_category", $category->getList() );

            $smarty->assign("page_name", "Pessoas");
            $smarty->assign("page_description", "Cadastro de Categorias");

            // chamando o template
            if (!@$_POST['ajax']){
                $smarty->display( PATH_PLUGIN . "person/templates/person_category_getList.html" );
            } else{
                $smarty->display( PATH_PLUGIN . "person/templates/person_category_template.html" );
            }
        break;
        case "insert" :
            if (@$_POST) {
                $client = new PersonCategory( $_POST );
                $client->insert();
                Json::get(200);
            }
        break;
        case "edit" :
            if ( @$_GET["person_category_id"] ){
                if ( @$_POST["person_category_id"] ){
                    $category = new PersonCategory( $_POST );
                    $category->update();
                    Json::get(200);
                }

                $category = new PersonCategory( $_GET );
                $smarty->assign( "category", $category->get());
            } else{
                die($errorMessage["no_parameters_get"]);
            }
        break;
        case "del" :
            $category = new PersonCategory( $_POST );
            $category->delete();
            die();
        break;
    }
