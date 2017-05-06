<?php

    // iniciar as configuraçõees do site
    include "../../config/start.php";

    // verificar admin na variavel de sessão
    Session::isUser();

    // controle das ações do plugin partner
    switch( $_GET["module"] )
    {
        case "getList" :
            $_POST["get_ProductUnit"] = 1;
            $_POST["get_ProductCost"] = 1;
            $_POST["get_ProductPrice"] = 1;
            $product = new Product( $_POST );
            $l_product = $product->getList();

            if( @$l_product ){
                foreach( $l_product as $product ){
                    $params["product_id"] = $product->product_id;
                    $cost = new ProductCost($params);
                    $price = new ProductPrice($params);
                    $product->product_cost = $cost->get();
                    $product->product_price = $price->get();
                }
            }
            $smarty->assign( "l_product", $l_product );

            $person = new Person([]);
            $person->setWhereCategory('1003');
            $smarty->assign( "l_person", $person->getList() );

            $product_unit = new ProductUnit(Array());
            $smarty->assign( "l_product_unit", $product_unit->getList() );

            $smarty->assign("page_name", "Produtos");
            $smarty->assign("page_description", "Cadastro de Produtos");

            // chamando o template
            if (!@$_POST['ajax']){
                $smarty->display( PATH_PLUGIN . "product/templates/product_getList.html" );
            } else{
                $smarty->display( PATH_PLUGIN . "product/templates/product_template.html" );
            }
        break;
        case "insert" :
            if( @$_POST ){
                GLOBAL $user;
                $product = $_POST["data"];

                $params["code_name"] = "product_code";
                $code = new Code($params);
                $code = $code->get();

                $product["product_code"] = substr( "00000{$code->code_value}", -6 );
                $p = new Product( $product );
                $product_id = $p->insert();

                $params["code_id"] = $code->code_id;
                $params["code_value"] = $code->code_value + 1;
                $code = new Code($params);
                $code->update();

                if( @$product["product_cost_value"] ){
                    $cost = new ProductCost(Array(
                        "user_id" => $user->user_id,
                        "provider_id" => @$product["product_provider_id"] ? $product["product_provider_id"] : NULL,
                        "product_id" => $product_id,
                        "product_cost_value" => str_replace(".",",",$product["product_cost_value"])
                    ));
                    $cost->insert();
                }

                if( @$product["product_price_value"] ){
                    $price = new ProductPrice(Array(
                        "user_id" => $user->user_id,
                        "product_id" => $product_id,
                        "product_price_value" => str_replace(".",",",$product["product_price_value"])
                    ));
                    $price->insert();
                }

                Json::get(200);
            }
        break;
        case "insertCost":
            $cost = new ProductCost(Array(
                "user_id" => $user->user_id,
                "provider_id" => $_POST["cost"]["provider_id"],
                "product_id" => $_POST["cost"]["product_id"],
                "product_cost_value" => str_replace(".",",",$_POST["cost"]["product_cost_value"])
            ));
            $product_cost_id = $cost->insert();
            $cost = new ProductCost(Array(
                "product_cost_id" => $product_cost_id,
                "get_User" => 1,
                "get_Person" => 1
            ));
            $cost = $cost->get();
            Json::get(200,$cost);
        break;
        case "insertPrice":
            $price = new ProductPrice(Array(
                "user_id" => $user->user_id,
                "product_id" => $_POST["price"]["product_id"],
                "product_price_value" => str_replace(".",",",$_POST["price"]["product_price_value"])
            ));
            $product_price_id = $price->insert();
            $price = new ProductPrice(Array(
                "product_price_id" => $product_price_id,
                "get_User" => 1
            ));
            $price = $price->get();
            Json::get(200,$price);
        break;
        case "edit":
            if( @$_POST ) {

                $product = $_POST["data"];
                $product_id = $product["product_id"];

                $product = new Product( $product );
                $product->update();

                Json::get(200);
            }
        break;
        case "get" :
            if ( !@$_GET["product_id"] ){
                die($errorMessage["no_parameters_get"]);
            }

            $product = new Product(Array(
                "product_id" => $_GET["product_id"],
                "get_ProductUnit" => 1
            ));
            $product = $product->get();

            $cost = new ProductCost(Array(
                "product_id" => $product->product_id,
                "get_User" => 1,
                "get_Person" => 1
            ));
            $product->cost = $cost->getList();

            $price = new ProductPrice(Array(
                "product_id" => $product->product_id,
                "get_User" => 1
            ));
            $product->price = $price->getList();

            Json::get( 200, $product );
        break;
        case "del" :
            $product = new Product( $_POST );
            $product->delete();

            $cost = new ProductCost( $_POST );
            $cost->table->fields["product_id"]->field_is_id = true;
            $cost->delete();

            $price = new ProductPrice( $_POST );
            $price->table->fields["product_id"]->field_is_id = true;
            $price->delete();

            die();
        break;
    }
