<?php

    // iniciar as configuraçõees do site
    include "../../config/start.php";

    // verificar admin na variavel de sessão
    Session::isUser();

    // controle das ações do plugin partner
    switch( $_GET["module"] )
    {
        case "getList" :
            $_POST["get_User"] = 1;
            $_POST["get_OrderStatus"] = 1;
            $_POST["get_Person"] = 1;
            $_POST["get_OrderItem"] = 1;
            $_POST["person_id"] = $user->UserProfile->UserProfileAccess->order->person->value == "Y" ? NULL : $user->person_id;
            $order = new Order( $_POST );
            $l_order = $order->getList();

            if( sizeof($l_order) ){
                foreach( $l_order as $order ){
                    $order->order_value_items = 0;
                    if( @$order->OrderItem ){
                        foreach( $order->OrderItem as $item ){
                            $order->order_value_items += $item->order_item_amount * $item->order_item_value;
                        }
                    }
                    $order->order_value_total = number_format($order->order_value_items + $order->order_addition - $order->order_discount,2,'.','');
                }
            }

            $status = new OrderStatus([]);
            $smarty->assign( "l_order_status", $status->getList() );

            $person = new Person([]);
            $smarty->assign( "l_person", $person->getList() );

            $smarty->assign( "l_order", $l_order );

            $params["get_KitItem"] = 1;
            $kit = new Kit( $params );
            $l_kit = $kit->getList();

            foreach( $l_kit as $kit ){
                $kit->kit_value_items = 0;
                foreach( $kit->KitItem as $item ){
                    $kit->kit_value_items += $item->kit_item_value_total;
                }
                $kit->kit_value_items = $kit->kit_value_items + $kit->kit_addition - $kit->kit_discount;
            }

            $smarty->assign( "l_kit", $l_kit );

            $smarty->assign("page_name", "Pedidos");
            $smarty->assign("page_description", "Cadastro de Pedidos");

            // chamando o template
            if (!@$_POST['ajax']){
                $smarty->display( PATH_PLUGIN . "order/templates/order_getList.html" );
            } else{
                $smarty->display( PATH_PLUGIN . "order/templates/order_template.html" );
            }
        break;
        case "insert" :
            if( @$_POST ){
                GLOBAL $user;
                $order = $_POST["data"];

                $order["person_id"] = @$order["person_id"] ? $order["person_id"] : $user->person_id;

                $status = new OrderStatus(Array(
                    "order_status_start" => "Y"
                ));
                $status = $status->get();
                $order["order_status_id"] = $status->order_status_id;
                $order["user_id"] = $user->user_id;

                $params["code_name"] = "order_code";
                $code = new Code($params);
                $code = $code->get();

                $order["order_code"] = substr( "00000{$code->code_value}", -6 );
                $order["order_addition"] = str_replace(".",",",$order["order_addition"]);
                $order["order_discount"] = str_replace(".",",",$order["order_discount"]);

                $p = new Order( $order );
                $order_id = $p->insert();

                $params["code_id"] = $code->code_id;
                $params["code_value"] = $code->code_value + 1;
                $code = new Code($params);
                $code->update();

                if( @$order["OrderItem"] ){
                    foreach ($order["OrderItem"] as $item){
                        $item["order_id"] = $order_id;
                        $item["order_item_value"] = str_replace(".",",",$item["order_item_value"]);
                        $item["order_item_value_total"] = str_replace(".",",",$item["order_item_value_total"]);
                        $order_item = new OrderItem($item);
                        $order_item->insert();
                    }
                }

                $receivable = new Receivable(Array(
                    "order_id" => $order_id,
                    "person_id" => $order["person_id"],
                    "modality_id" => 1001,
                    "receivable_code" => "{$order["order_code"]}-1",
                    "receivable_value" => str_replace(".",",",$order["order_value_total"]),
                    "receivable_deadline" =>  date("Y-m-d", strtotime("+7 days",strtotime(date("Y-m-d")))),
                    "receivable_payment_date" => NULL
                ));
                $receivable->insert();

                $u = new User([]);
                $to = $u->getMailList();

                $order = new Order(Array(
                    "order_id" => $order_id,
                    "get_Person" => 1,
                    "get_OrderItem" => 1,
                    "get_OrderStatus" => 1,
                    "get_Kit" => 1
                ));
                $message = $order->getMessage($order->get(), Array(
                    "title" => "Novo Pedido"
                ));

                $mail = new Mail(Array(
                    "subject" => "Novo Pedido",
                    "recipients" => $to,
                    "message" => $message
                ));

                $mail->sendMail();

                Json::get(200);
            }
        break;
        case "edit":
            if( @$_POST ) {
                $order = $_POST["data"];
                $order_id = $order["order_id"];
                $order_value = $order["order_value"];
                $order["order_addition"] = str_replace(".",",",$order["order_addition"]);
                $order["order_discount"] = str_replace(".",",",$order["order_discount"]);
                $k = new Order( $order );
                $k->update();

                if( @$order["OrderItem"] ) {
                    foreach ($order["OrderItem"] as $item){
                        $item["order_id"] = $order_id;
                        $item["order_item_value"] = str_replace(".",",",$item["order_item_value"]);
                        $item["order_item_value_total"] = str_replace(".",",",$item["order_item_value_total"]);
                        $order_item = new OrderItem($item);
                        if( @$item["order_item_id"] ) {
                            $order_item->update();
                        } else{
                            $order_item->insert();
                        }
                    }
                }

                if( @$order["OrderItemDel"] ) {
                    foreach ($order["OrderItemDel"] as $id){
                        $order_item = new OrderItem(Array(
                            "order_item_id" => $id
                        ));
                        $order_item->delete();
                    }
                }

                $receivable = new Receivable(Array(
                    "order_id" => $order_id
                ));
                $receivable = $receivable->get();
                $receivable_id = $receivable->receivable_id;

                $receivable = new Receivable(Array(
                    "receivable_id" => $receivable_id,
                    "receivable_value" => str_replace(".",",",$order_value)
                ));
                $receivable->update();

                Json::get(200);
            }
        break;
        case "get" :
            if ( !@$_GET["order_id"] ){
                die($errorMessage["no_parameters_get"]);
            }

            $order = new Order(Array(
                "order_id" => $_GET["order_id"],
                "get_OrderStatus" => 1,
                "get_OrderItem" => 1,
                "get_Kit" => 1
            ));
            $order = $order->get();
            $order->OrderItem = @$order->OrderItem ? $order->OrderItem : [];
            Json::get( 200, $order );
        break;
        case "del" :
            $order = new Order( $_POST );
            $order->delete();

            $receivable = new Receivable($_POST);
            $receivable = $receivable->getList();

            if( sizeof($receivable)) {
                foreach ($receivable as $r) {
                    $params["receivable_id"] = $r->receivable_id;
                    $receivableDelete = new Receivable($params);
                    $receivableDelete->delete();
                }
            }

            Json::get( 200 );
        break;
    }
