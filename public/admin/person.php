<?php

    // iniciar as configuraçõees do site
    include "../../config/start.php";

    // verificar admin na variavel de sessão
    Session::isUser();

    // controle das ações do plugin partner
    switch( $_GET["module"] )
    {
        case "getList" :
            $person = new Person( $_POST );
            $smarty->assign( "l_person", $person->getList() );

            $params = [];
            $params["city_uf"] = "RJ";

            $uf = new UF( $_POST );
            $smarty->assign( "l_uf", $uf->getList() );

            $person_category = new PersonCategory(Array());
            $smarty->assign( "l_person_category", $person_category->getList() );

            $person_contact_type = new PersonContactType(Array());
            $smarty->assign( "l_person_contact_type", $person_contact_type->getList() );

            $smarty->assign("page_name", "Pessoa");
            $smarty->assign("page_description", "Cadastro de Pessoas");

            // chamando o template
            if (!@$_POST['ajax']){
                $smarty->display( PATH_PLUGIN . "person/templates/person_getList.html" );
            } else{
                $smarty->display( PATH_PLUGIN . "person/templates/person_template.html" );
            }
        break;
        case "insert" :
            if( @$_POST ){
                $person = $_POST["data"];
                $person_address = $person["person_address"];
                $person_contact = $person["person_contact"];
                $person_bank = $person["person_bank"];

                unset($person["person_address"]);
                unset($person["person_contact"]);
                unset($person["person_bank"]);

                $params["code_name"] = "person_code";
                $code = new Code($params);
                $code = $code->get();

                $person["person_code"] = substr( "00000{$code->code_value}", -6 );
                $person["person_birth"] = @$person["person_birth"] ? toUsDateFormat($person["person_birth"]) : NULL;
                $person = new Person( $person );
                $person_id = $person->insert();

                $params["code_id"] = $code->code_id;
                $params["code_value"] = $code->code_value + 1;
                $code = new Code($params);
                $code->update();

                foreach( $person_address as $pa ){
                    $pa["person_id"] = $person_id;
                    $address = new Address($pa);
                    $address->insert();
                }

                foreach( $person_contact as $pc ){
                    $pc["person_id"] = $person_id;
                    $contact = new PersonContact($pc);
                    $contact->insert();
                }

                if( @$person_bank["bank_code"] ){
                    $person_bank["person_id"] = $person_id;
                    $bank = new PersonBank($person_bank);
                    $bank->insert();
                }

                Json::get(200);
            }
        break;
        case "edit":
            if( @$_POST ) {

                $person = $_POST["data"];
                $person_address = $person["person_address"];
                $person_contact = $person["person_contact"];
                $person_bank = $person["person_bank"];
                $person_id = $person["person_id"];

                unset($person["person_address"]);
                unset($person["person_contact"]);
                unset($person["person_bank"]);

                foreach( $person_address as $pa ){
                    $address = new Address($pa);
                    if( @$pa["address_id"] ) {
                        $address->update();
                    } else{
                        $address->insert();
                    }
                }
                if( @$person["delete_address"] ){
                    foreach( $person["delete_address"] as $address_id ){
                        $address = new Address(Array(
                            "address_id" => $address_id,
                            "person_id" => $person_id
                        ));
                        $address->delete();
                    }
                }

                foreach( $person_contact as $pc ){
                    $contact = new PersonContact($pc);
                    if( @$pc["person_contact_id"] ){
                        $contact->update();
                    } else {
                        $contact->insert();
                    }
                }
                if( @$person["delete_contact"] ){
                    foreach( $person["delete_contact"] as $person_contact_id ){
                        $person_contact = new PersonContact(Array(
                            "person_contact_id" => $person_contact_id,
                            "person_id" => $person_id
                        ));
                        $person_contact->delete();
                    }
                }

                if( @$person_bank["bank_code"] ){
                    $bank = new PersonBank($person_bank);
                    $bank->update();
                }

                $person["person_birth"] = @$person["person_birth"] ? toUsDateFormat($person["person_birth"]) : NULL;
                $person = new Person( $person );
                $person->update();

                Json::get(200);
            }
        break;
        case "get" :
            if ( !@$_GET["person_id"] ){
                die($errorMessage["no_parameters_get"]);
            }

            $person = new Person(Array(
                "person_id" => $_GET["person_id"]
            ));
            $person = $person->get();

            $category = new PersonCategoryLink(Array(
                "person_id" => $person->person_id,
            ));

            $address = new Address(Array(
                "person_id" => $person->person_id,
                "get_District" => 1,
                "get_City" => 1,
                "get_UF" => 1
            ));

            $contact = new PersonContact(Array(
                "person_id" => $person->person_id
            ));

            $bank = new PersonBank(Array(
                "person_id" => $person->person_id
            ));

            $person->category = $category->getList();
            $person->address = $address->getList();
            $person->contact = $contact->getList();
            $person->bank = $bank->get();

            if( @$person->person_birth ){
                $person->person_birth = toBrDateFormat($person->person_birth);
            }
            Json::get( 200, $person );
        break;
        case "del" :

            $person = new Person( $_POST );
            $person->delete();

            $link = new PersonCategoryLink( $_POST );
            $link->table->fields["person_id"]->field_is_id = true;
            $link->delete();

            $address = new Address( $_POST );
            $address->table->fields["person_id"]->field_is_id = true;
            $address->delete();

            $contact = new PersonContact( $_POST );
            $contact->table->fields["person_id"]->field_is_id = true;
            $contact->delete();

            $bank = new PersonBank( $_POST );
            $bank->table->fields["person_id"]->field_is_id = true;
            $bank->delete();

            die();
        break;
    }
