<?php
    // iniciar as configuraçõees do site
    include "../../config/start.php";

    // verificar admin na variavel de sessão
    Session::isUser();

    // controle das ações do plugin partner
    switch( $_GET["module"] )
    {
        case "getList" :
            if ( @$_POST['city_id'] ){
                $params = $_POST;
                $params["get_City"] = 1;

                $district = new District( $params );
                $smarty->assign( "l_district", $district->getList() );

                $_POST['city_id'] = null;
            } else {
                $smarty->assign( "l_district", [] );
            };

            $uf = new UF([]);
            $smarty->assign( "l_uf", $uf->getList() );

            $city = new City(Array("uf_id"=>1012));
            $smarty->assign( "l_city", $city->getList() );

            $district = new District(Array(
                "get_City" => 1,
                "city_id" => 3468
            ));
            $smarty->assign( "l_district", $district->getList() );

            $smarty->assign("page_name", "Bairro");
            $smarty->assign("page_description", "Cadastro de Bairros");

            // chamando o template
            if (!@$_POST['ajax']){
                $smarty->display( PATH_PLUGIN . "district/templates/district_getList.html" );
            } else{
                $smarty->display( PATH_PLUGIN . "district/templates/district_template.html" );
            }
        break;
        case "insert" :
            if (@$_POST) {
                $district = new District( $_POST );
                $district->insert();
            }
        break;
        case "edit" :
            if ( @$_GET["district_id"] ){
                if ( @$_POST["district_id"] ){
                    $district = new District( $_POST );
                    $district->update();
                }

                $district = new District( $_GET );
                $smarty->assign( "district", $district->get());
            } else{
                die($errorMessage["no_parameters_get"]);
            }
        break;
        case "del" :
            $district = new District( $_POST );
            $district->delete();
            die();
        break;
        case "getListDistrictCity" :
            if ( @$_GET['city_id'] ) {
                $district = new District($_GET);
                $smarty->assign("l_district", $district->getList());
            }
        break;
        case "getByIbgeCity" :
            $district = new District($_GET);

            $district->setWhereIbgeCity($_GET['city_ibge']);

            $smarty->assign("l_district", $district->get());
        break;

    }
