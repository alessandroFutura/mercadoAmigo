<?php

    include "../../config/start.php";

    Session::isUser();

    switch( $_GET["module"] )
    {
        case "getList" :
            $status = new OrderStatus( $_POST );
            $smarty->assign( "l_status", $status->getList() );

            $smarty->assign("page_name", "Status de Pedido");
            $smarty->assign("page_description", "Cadastro de Status");

            // chamando o template
            if (!@$_POST['ajax']){
                $smarty->display( PATH_PLUGIN . "order/templates/order_status_getList.html" );
            } else{
                $smarty->display( PATH_PLUGIN . "order/templates/order_status_template.html" );
            }
        break;
        case "insert" :
            if (@$_POST) {
                $client = new OrderStatus( $_POST );
                $client->insert();
                Json::get(200);
            }
        break;
        case "edit" :
            if ( @$_GET["order_status_id"] ){
                if ( @$_POST["order_status_id"] ){

                    if( @$_POST["order_status_start"] ){
                        $status = new OrderStatus([]);
                        $status = $status->getList();
                        foreach( $status as $s ){
                            if( $s->order_status_start == "Y" ){
                                $status_up = new OrderStatus(Array(
                                    "order_status_id" => $s->order_status_id,
                                    "order_status_active" => $s->order_status_active == "Y" ? "on" : NULL,
                                    "order_status_editable" => $s->order_status_editable == "Y" ? "on" : NULL,
                                    "order_status_start" => "N"
                                ));
                                $status_up->update();
                            }
                        }
                    }
                    $status = new OrderStatus( $_POST );
                    $status->update();
                    Json::get(200);
                }

                $status = new OrderStatus( $_GET );
                $smarty->assign( "status", $status->get());
            } else{
                die($errorMessage["no_parameters_get"]);
            }
        break;
        case "del" :
            $status = new OrderStatus( $_POST );
            $status->delete();
            die();
        break;
    }
