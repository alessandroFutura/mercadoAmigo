<?php

    // iniciar as configuraçõees do site
    include "../config/start.php";

    if( !@$_GET["action"] ){
        header('HTTP/1.0 417 Expectation failed');
        $jsonStatus[417]->description = $errorMessage["no_parameters_get"];
        Json::get( $jsonStatus[417] );
    }

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, x-session-token');

    switch( $_GET["action"] )
    {
        case "get_districts":
            if( !@$_POST["city_id"] ){
                header('HTTP/1.0 417 Expectation failed');
                $jsonStatus[417]->description = $errorMessage["no_parameters_post"];
                Json::get( $jsonStatus[417] );
            }
            $district = new District(Array(
                "city_id" => $_POST["city_id"],
                "json" => 1
            ));
            $district->getList();
        break;
        case "get_citys":
            if( !@$_POST["uf_id"] ){
                header('HTTP/1.0 417 Expectation failed');
                $jsonStatus[417]->description = $errorMessage["no_parameters_post"];
                Json::get( $jsonStatus[417] );
            }
            $city = new City(Array(
                "uf_id" => $_POST["uf_id"],
                "json" => 1
            ));
            $city->getList();
        break;
        case "get_uf":
            if( !@$_POST["uf_code"] ){
                header('HTTP/1.0 417 Expectation failed');
                $jsonStatus[417]->description = $errorMessage["no_parameters_post"];
                Json::get( $jsonStatus[417] );
            }
            $uf = new UF(Array(
                "uf_code" => $_POST["uf_code"],
                "json" => 1
            ));
            $uf->get();
            break;
        case "get_ufs":
            $uf = new UF(Array(
                "json" => 1
            ));
            $uf->getList();
        break;
        case "get_banks":
            GLOBAL $l_bank;
            Json::get( 200, $l_bank );
        break;
        case "get_kits":
            $kit = new Kit(Array(
                "get_KitItem" => 1,
                "get_Product" => 1
            ));
            Json::get( 200, $kit->getList() );
        break;
    }

?>