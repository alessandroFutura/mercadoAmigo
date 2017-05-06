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
            //$params["get_ReceivableProfile"] = 1;
            $params["get_Person"]      = 1;

            $receivable = new Receivable( $params );
            $smarty->assign( "l_receivable", $receivable->getList() );

            $person = new Person([]);
            $smarty->assign( "l_person", $person->getList() );

            $smarty->assign("page_name", "A Receber");
			$smarty->assign("page_description", "Cadastro de Títulos a Receber");

			// chamando o template
			if (!@$_POST['ajax']){
				$smarty->display( PATH_PLUGIN . "receivable/templates/receivable_getList.html" );
			} else{
				$smarty->display( PATH_PLUGIN . "receivable/templates/receivable_template.html" );
			}
		break;
		case "insert" :
			if( @$_POST ){
                if( @$_FILES["pdf"] ){
                    try
                    {
                        $path = PATH_FILES . "receivable" . date('/Y/M/');
                        if( !file_exists( $path ) ){
                            mkdir( $path, 0777, true );
                        }
                        $file = date("YmdHis") . "_" . sanitizeString($_FILES["pdf"]["name"]);
                        $path .= $file;
                        if( move_uploaded_file( $_FILES["pdf"]["tmp_name"], $path ) ) {
                            $_POST["receivable_file"] = date('Y/M/') . $file;
                        }
                    }
                    Catch( Exception $e )
                    {

                    }
                }

			    $params = $_POST;
                $params["receivable_deadline"] = toUsDateFormat($_POST["receivable_deadline"]);
                $params["receivable_payment_date"] = @$_POST["receivable_payment_date"] ? toUsDateFormat($_POST["receivable_payment_date"]) : "";
                $receivable = new Receivable( $params );
                $receivable->insert();
			}
		break;
        case "get":
            $_GET["get_Person"] = 1;
            $_GET["get_Order"] = 1;
            $_GET["get_OrderItem"] = 1;
            $_GET["get_Kit"] = 1;
            $receivable = new Receivable( $_GET );
            $receivable = $receivable->get();

            $category = new PersonCategoryLink(Array(
                "person_id" => $receivable->Person->person_id,
            ));

            $address = new Address(Array(
                "person_id" => $receivable->Person->person_id,
                "get_District" => 1,
                "get_City" => 1,
                "get_UF" => 1
            ));

            $contact = new PersonContact(Array(
                "person_id" => $receivable->Person->person_id
            ));

            $receivable->Person->category = $category->getList();
            $receivable->Person->address = $address->getList();
            $receivable->Person->contact = $contact->getList();

            if( @$receivable->receivable_deadline ){
                $receivable->receivable_deadline = toBrDateFormat($receivable->receivable_deadline);
            }
            if( @$receivable->receivable_payment_date ){
                $receivable->receivable_payment_date = toBrDateFormat($receivable->receivable_payment_date);
            }
            if( @$receivable->receivable_drop_date ){
                $receivable->receivable_drop_date = toBrDateFormat($receivable->receivable_drop_date);
            }
            if( @$receivable->Person->person_birth ){
                $receivable->Person->person_birth = toBrDateFormat($receivable->Person->person_birth);
            }

            Json::get(200,$receivable);
        break;
		case "edit" :

            if( @$_FILES["pdf"] ){
                try
                {
                    $path = PATH_FILES . "receivable" . date('/Y/M/');
                    if( !file_exists( $path ) ){
                        mkdir( $path, 0777, true );
                    }
                    $file = date("YmdHis") . "_" . sanitizeString($_FILES["pdf"]["name"]);
                    $path .= $file;
                    if( move_uploaded_file( $_FILES["pdf"]["tmp_name"], $path ) ) {
                        $_POST["receivable_file"] = date('Y/M/') . $file;
                    }
                }
                Catch( Exception $e )
                {

                }
            }

		    $_POST["receivable_deadline"] = toUsDateFormat($_POST["receivable_deadline"]);
            $_POST["receivable_payment_date"] = @$_POST["receivable_payment_date"] ? toUsDateFormat($_POST["receivable_payment_date"]) : NULL;
            $_POST["receivable_drop_date"] = @$_POST["receivable_drop"] ? date("Y-m-d") : NULL;

		    $receivable = new Receivable( $_POST );
            $receivable->update();

            if( @$_POST["receivable_drop"] ){

                $receivable_value = $_POST["receivable_value"];
                if( @$_POST["order_id"] ) {

                    $l_commission = Array(7.00, 21.00, 3.00, 4.00, 15.00, 6.00, 1.00, 1.00, 1.00, 4.50);
                    $params = Array(
                        "person_id" => $_POST["person_id"],
                        "receivable_id" => $_POST["receivable_id"],
                        "order_id" => $_POST["order_id"],
                        "modality_id" => $_POST["modality_id"],
                        "checking_account_status" => "A"
                    );

                    foreach ($l_commission as $key => $commission) {
                        $rede = new Rede(Array(
                            "child_person_id" => $params["person_id"]
                        ));
                        $rede = $rede->get();
                        if (@$rede->parent_person_id) {
                            $receivable_value -= $commission;
                            $params["person_id"] = $rede->parent_person_id;
                            $params["person_origin_id"] = $_POST["person_id"];
                            $params["checking_account_value"] = $commission;
                            $checkingAccount = new CheckingAccount($params);
                            $checkingAccount->insert();
                        }
                    }
                }

                $params["person_id"] = 1002;
                $params["person_origin_id"] = $_POST["person_id"];
                $params["checking_account_value"] = $receivable_value;
                $params["order_id"] = @$_POST["order_id"] ? $_POST["order_id"] : NULL;
                $params["checking_account_status"] = "L";
                $params["receivable_id"] = $_POST["receivable_id"];
                $params["modality_id"] = $_POST["modality_id"];

                $checkingAccount = new CheckingAccount($params);
                $checkingAccount->insert();
            }

            Json::get(200);

		break;
        case "del" :
            $receivable = new Receivable( $_POST );
            $receivable->delete();
			die();
		break;
	}
