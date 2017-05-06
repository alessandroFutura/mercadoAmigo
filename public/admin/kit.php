<?php

    // iniciar as configuraçõees do site
    include "../../config/start.php";

    // verificar admin na variavel de sessão
    Session::isUser();

    // controle das ações do plugin partner
    switch( $_GET["module"] )
    {
        case "getList" :
            $_POST["get_KitItem"] = 1;
            $kit = new Kit( $_POST );
            $l_kit = $kit->getList();

            if( sizeof($l_kit)){
                foreach( $l_kit as $kit ){
                    $kit->kit_value_items = 0;
                    if( @$kit->KitItem ){
                        foreach( $kit->KitItem as $item ){
                            $kit->kit_value_items += $item->kit_item_amount * $item->kit_item_value;
                        }
                    }
                    $kit->kit_value_items = number_format($kit->kit_value_items + $kit->kit_addition - $kit->kit_discount,2,'.','');
                }
            }

            $smarty->assign( "l_kit", $l_kit );

            $params["get_ProductUnit"] = 1;
            $params["get_ProductPrice"] = 1;
            $product = new Product( $params );
            $l_product = $product->getList();

            foreach( $l_product as $product ){
                $params["product_id"] = $product->product_id;
                $cost = new ProductCost($params);
                $price = new ProductPrice($params);
                $product->product_cost = $cost->get();
                $product->product_price = $price->get();
            }

            $smarty->assign( "l_product", $l_product );

            $smarty->assign("page_name", "Kits");
            $smarty->assign("page_description", "Cadastro de Kits");

            // chamando o template
            if (!@$_POST['ajax']){
                $smarty->display( PATH_PLUGIN . "kit/templates/kit_getList.html" );
            } else{
                $smarty->display( PATH_PLUGIN . "kit/templates/kit_template.html" );
            }
        break;
        case "insert" :
            if( @$_POST ){
                GLOBAL $user;
                $kit = $_POST["data"];

                $params["code_name"] = "kit_code";
                $code = new Code($params);
                $code = $code->get();

                $kit["kit_code"] = substr( "00000{$code->code_value}", -6 );
                $kit["kit_addition"] = str_replace(".",",",$kit["kit_addition"]);
                $kit["kit_discount"] = str_replace(".",",",$kit["kit_discount"]);
                $kit["kit_value"] = str_replace(".",",",$kit["kit_value"]);
                $p = new Kit( $kit );
                $kit_id = $p->insert();

                $params["code_id"] = $code->code_id;
                $params["code_value"] = $code->code_value + 1;
                $code = new Code($params);
                $code->update();

                if( @$kit["KitItem"] ) {
                    foreach ($kit["KitItem"] as $item){
                        $item["kit_id"] = $kit_id;
                        $item["kit_item_value"] = str_replace(".",",",$item["kit_item_value"]);
                        $item["kit_item_value_total"] = str_replace(".",",",$item["kit_item_value_total"]);
                        $kit_item = new KitItem($item);
                        $kit_item->insert();
                    }
                }

                Json::get(200);
            }
        break;
        case "edit":
            if( @$_POST ) {
                $kit = $_POST["data"];
                $kit_id = $kit["kit_id"];
                $kit["kit_addition"] = str_replace(".",",",$kit["kit_addition"]);
                $kit["kit_discount"] = str_replace(".",",",$kit["kit_discount"]);
                $kit["kit_value"] = str_replace(".",",",$kit["kit_value"]);
                $k = new Kit( $kit );
                $k->update();

                if( @$kit["KitItem"] ) {
                    foreach ($kit["KitItem"] as $item){
                        $item["kit_id"] = $kit_id;
                        $item["kit_item_value"] = str_replace(".",",",$item["kit_item_value"]);
                        $item["kit_item_value_total"] = str_replace(".",",",$item["kit_item_value_total"]);
                        $kit_item = new KitItem($item);
                        if( @$item["kit_item_id"] ) {
                            $kit_item->update();
                        } else{
                            $kit_item->insert();
                        }
                    }
                }

                if( @$kit["KitItemDel"] ) {
                    foreach ($kit["KitItemDel"] as $id){
                        $kit_item = new KitItem(Array(
                            "kit_item_id" => $id
                        ));
                        $kit_item->delete();
                    }
                }

                Json::get(200);
            }
        break;
        case "get" :
            if ( !@$_GET["kit_id"] ){
                die($errorMessage["no_parameters_get"]);
            }

            $kit = new Kit(Array(
                "kit_id" => $_GET["kit_id"],
                "get_KitItem" => 1,
                "get_Product" => 1
            ));
            $kit = $kit->get();
            $kit->KitItem = @$kit->KitItem ? $kit->KitItem : [];
            Json::get( 200, $kit );
        break;
        case "del" :
            $kit = new Kit( $_POST );
            $kit->delete();

            $kitItem = new KitItem($_POST);
            $l_kitItem = $kitItem->getList();

            foreach( $l_kitItem as $item ){
                $item = new KitItem(Array(
                    "kit_item_id" => $item->kit_item_id
                ));
                $item->delete();
            }

            die();
        break;
    }
