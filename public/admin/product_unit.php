<?php

    include "../../config/start.php";

    Session::isUser();

    switch( $_GET["module"] )
    {
        case "getList" :
            $product_unit = new ProductUnit( $_POST );
            $smarty->assign( "l_unit", $product_unit->getList() );

            $smarty->assign("page_name", "Produtos");
            $smarty->assign("page_description", "Cadastro de Unidades");

            // chamando o template
            if (!@$_POST['ajax']){
                $smarty->display( PATH_PLUGIN . "product/templates/product_unit_getList.html" );
            } else{
                $smarty->display( PATH_PLUGIN . "product/templates/product_unit_template.html" );
            }
        break;
        case "insert" :
            if (@$_POST) {
                $_POST["product_unit_code"] = strtoupper($_POST["product_unit_code"]);
                $client = new ProductUnit( $_POST );
                $client->insert();
                Json::get(200);
            }
        break;
        case "edit" :
            if ( @$_GET["product_unit_id"] ){
                if ( @$_POST["product_unit_id"] ){
                    $_POST["product_unit_code"] = strtoupper($_POST["product_unit_code"]);
                    $product_unit = new ProductUnit( $_POST );
                    $product_unit->update();
                    Json::get(200);
                }

                $product_unit = new ProductUnit( $_GET );
                $smarty->assign( "product_unit", $product_unit->get());
            } else{
                die($errorMessage["no_parameters_get"]);
            }
        break;
        case "del" :
            $product_unit = new ProductUnit( $_POST );
            $product_unit->delete();
            die();
        break;
    }
