<?php

    // iniciar as configuraçõees do site
    include "../../config/start.php";

    // verificar admin na variavel de sessão
    Session::isUser();

    // controle das ações do plugin partner
    switch( $_GET["module"] )
    {
        case "dashboard":
            $smarty->assign("page_name", "Escritório Virtual");
            $smarty->assign("page_description", "DashBoard");
            $smarty->display( PATH_PLUGIN . "office/templates/office_dashboard.html" );
        break;
        case "newClient":

            $params = [];
            $params["city_uf"] = "RJ";
            $uf = new UF( $_POST );
            $smarty->assign( "l_uf", $uf->getList() );
            $person_contact_type = new PersonContactType(Array());
            $smarty->assign( "l_person_contact_type", $person_contact_type->getList() );

            $smarty->assign("page_name", "Escritório Virtual");
            $smarty->assign("page_description", "Novo Cliente");
            $smarty->display( PATH_PLUGIN . "office/templates/office_newClient.html" );
        break;
        case "payments":
            $params["person_id"] = $user->person_id;
            $params["get_Person"] = 1;
            $receivable = new Receivable( $params );
            $smarty->assign( "l_receivable", $receivable->getList() );
            $smarty->assign("page_name", "Escritório Virtual");
            $smarty->assign("page_description", "Meus Pagamentos");
            $smarty->display( PATH_PLUGIN . "office/templates/office_payments.html" );
            break;
        case "editInfo":
            $smarty->assign("page_name", "Escritório Virtual");
            $smarty->assign("page_description", "Meu Cadastro");
            $smarty->display( PATH_PLUGIN . "office/templates/office_editInfo.html" );
        break;
        case "rede":

            $nodecolor = Array( "#ff4c4c", "#0099e5", "#34bf49", "#c68143", "#016773", "#689550", "#f7afff", "#c9510c", "#394956", "#7d3f98", "#663300" );
            $textcolor = Array( "#ff4c4c", "#0099e5", "#34bf49", "#c68143", "#016773", "#689550", "#f7afff", "#c9510c", "#394956", "#7d3f98", "#663300" );
            $strokeStyle = Array( "#ff4c4c", "#0099e5", "#34bf49", "#c68143", "#016773", "#689550", "#f7afff", "#c9510c", "#394956", "#7d3f98", "#663300" );
            $nodecolorEmpty = "#f96305";
            $linkcolor = "gray";
            $nodecolorUseful = "gray";

            $rede = new Rede(Array(
                "child_person_id" => $user->person_id
            ));
            $rede = $rede->get();

            $level = 1;
            $emptyNode = 0;
            $width = 1000;
            $branch = Array( $user->person_id );
            $pos = Array(
                1  => Array( -300, 0, 300, 300, 300, 300, 300, 300 ),
                2  => Array( -100, 0, 100 ),
                3  => Array(  -40, 0, 40 ),
                4  => Array(  -40, 0, 40 ),
                5  => Array(  -10, 0, 10 ),
                6  => Array(  -10, 0, 10 ),
                7  => Array(  -10, 0, 10 ),
                8  => Array(  -10, 0, 10 ),
                9  => Array(  -10, 0, 10 ),
                10 => Array(  -10, 0, 10 )
            );
            $pId = 9999;
            $columnRede = Array();
            for( $l=1; $l<=10; $l++ ){
                $columnRede[$l] = (Object)Array(
                    "level" => $l,
                    "people" => 0,
                    "total_people" => pow(3,$l)
                );
            }

            $nodes[$user->person_id] = (Object)Array(
                "name" => $user->Person->person_name,
                "nodecolor" => $nodecolor[0],
                "textcolor" => $textcolor[0],
                "position" => (Object)Array(
                    "x" => ($width/2),
                    "y" => 40
                ),
                "textPosition" => -20
            );
            $edges = new StdClass();

            $n=2;
            while( $level > 0 ){

                $newBranch = Array();
                $redes = 0;

                $subLevel = 0;
                foreach( $branch as $b ){

                    $rede = new Rede(Array(
                        "parent_person_id" => $b
                    ));
                    $rede = $rede->getList();

                    $node[$b] = Array();
                    $edges->$b = new StdClass();

                    if( $b < 9000 ){
                        for( $i=0; $i<3; $i++ ){
                            $newBranch[] = @$rede[$i] ? $rede[$i]->child_person_id : $pId;
                            if (@$rede[$i]) {
                                $columnRede[$level]->people++;
                                $p = new Person(Array(
                                    "person_id" => @$rede[$i] ? $rede[$i]->child_person_id : $pId
                                ));
                                $p = $p->get();
                                $redes++;
                            } else {
                                if( !@$emptyNode ){
                                    $emptyNode = $pId;
                                }
                                $p = (Object)Array(
                                    "person_id" => $pId,
                                    "person_name" => "Aberto"
                                );
                                $pId--;
                            }

                            $large = $width / ( pow(3,$level) * 2 );
                            $addition = $i * ( $width / pow(3,$level) );
                            $levelAddition = $subLevel * ( $level > 1 ? ( $width / pow(3,$level-1) ) : 0 );

                            $nodes[$p->person_id] = (Object)Array(
                                "level" => $level,
                                "name" => $p->person_name,
                                "nodecolor" => @$rede[$i] ? $nodecolor[$level] : $nodecolorUseful,
                                "textcolor" => @$rede[$i] ? $textcolor[$level] : $nodecolorUseful,
                                "position" => (Object)Array(
                                    "x" => $nodes[$b]->position->x + $pos[$level][$i],//$large + $addition + $levelAddition,
                                    "y" => $level * 80 + 40
                                ),
                                "textPosition" => $n % 2 == 0 ? 30 : -20
                            );
                            $n++;

                            $person_id = $p->person_id;
                            $edges->$b->$person_id = new StdClass();
                            $edges->$b->$person_id->linkcolor = $linkcolor;
                            $edges->$b->$person_id->pt1 = $nodes[$b]->position;
                            $edges->$b->$person_id->pt2 = $nodes[$p->person_id]->position;

                        }
                        $subLevel++;
                    }
                }

                if( $redes )
                {
                    $level++;
                    unset($branch);
                    $branch = $newBranch;
                    unset($newBranch);
                }
                else
                {
                    $level = 0;
                }

            }

            $nodes[$emptyNode]->name = "Derramamento da Rede";
            $nodes[$emptyNode]->nodecolor = $nodecolorEmpty;
            $nodes[$emptyNode]->textcolor = $nodecolorEmpty;

            $smarty->assign( "nodes", (Object)$nodes );
            $smarty->assign( "edges", $edges );
            $smarty->assign( "emptyNode", $emptyNode );
            $smarty->assign( "nodecolorUseful", $nodecolorUseful );
            $smarty->assign( "columnRede", $columnRede );

            $smarty->assign( "nodecolor", $nodecolor );
            $smarty->assign( "textcolor", $textcolor );
            $smarty->assign( "nodecolorEmpty", $nodecolorEmpty );

            $smarty->assign("page_name", "Escritório Virtual");
            $smarty->assign("page_description", "Minha Rede");
            $smarty->display( PATH_PLUGIN . "office/templates/office_rede.html" );
        break;

        case "addNewClient":
            if( @$_POST ){
                $person = $_POST["data"];
                $person["person_category"][0] = 1002;
                $person_address = $person["person_address"];
                $person_contact = $person["person_contact"];
                $person_bank = $person["person_bank"];

                if( @$person->rede_type && $user->user_id < 1003 ){
                    $person_rede["rede_type"] = $person->rede_type;
                } else{
                    $person_rede["rede_type"] = "N1";
                }

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
                    $pa["address_delivery"] = "on";
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

                /// USUÁRIO //
                $pass = rand(100000,999999);
                $newUser = new User(Array(
                    "user_profile_id" => 1002,
                    "person_id" => $person_id,
                    "user_active" => "on",
                    "user_user" => str_replace( Array(".","-"), Array("",""), $_POST["data"]["person_cpf"] ),
                    "user_pass" => md5($pass),
                    "user_name" => $_POST["data"]["person_name"],
                    "user_mail" => $person_contact[0]["person_contact_value"]
                ));
                $newUserId = $newUser->insert();

                $newUser = new User(Array(
                    "user_id" => $newUserId
                ));
                $message = $newUser->getMessage( $newUser->get(), $pass );

                $mail = new Mail(Array(
                    "subject" => "Bem-Vindo ao Mercado Amigo",
                    "recipients" => Array(
                        Array(
                            "mail_address" => $person_contact[0]["person_contact_value"],
                            "mail_recipient" => $_POST["data"]["person_name"]
                        )
                    ),
                    "message" => $message
                ));

                $mail->sendMail();

                /// USUÁRIO //

                $child_person_id = $person_id;

                if( $person_rede["rede_type"] == "" && $user->user_id == 1002 ){
                    $parent_person_id = $user->person_id;
                } else{
                    $rede = new Rede(Array(
                        "parent_person_id" => $user->person_id
                    ));
                    $rede = $rede->getList();

                    if( sizeof($rede) < 3 ){
                        $parent_person_id = $user->person_id;
                        $person_rede["rede_type"] = "N1";
                    } else{
                        $branch = Array();
                        foreach( $rede as $r ){
                            $branch[] = $r->child_person_id;
                        }
                        $parent_person_id = getNodeEmpty($branch);
                    }
                }

                $rede = new Rede(Array(
                    "rede_active" => "Y",
                    "user_id" => $user->user_id,
                    "parent_person_id" => $parent_person_id,
                    "child_person_id" => $child_person_id,
                    "rede_type" => $person_rede["rede_type"]
                ));
                $rede->insert();

                $u = new User([]);
                $to = $u->getMailList();

                $parent_person = new Person(Array(
                    "person_id" => $user->person_id
                ));
                $child_person = new Person(Array(
                    "person_id" => $child_person_id
                ));

                $person = new Person([]);
                $message = $person->getMessage( $parent_person->get(), $child_person->get(), Array(
                    "title" => "Novo Cadastro",
                    "rede" => $person_rede["rede_type"] == "N1" ? "Nível 1" : "Derramamento na Rede"
                ));

                $mail = new Mail(Array(
                    "subject" => "Novo Cadastro na Rede",
                    "recipients" => $to,
                    "message" => $message
                ));

                $mail->sendMail();

                Json::get(200);
            }
        break;
    }
