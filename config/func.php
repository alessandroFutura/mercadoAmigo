<?php

    function toUsDateFormat( $date )
    {
        $date = str_replace( "/", "-", $date );
        $date = explode( "-", $date );
        return $date[2] . "-" . $date[1] . "-" . $date[0];
    }

	function toBrDateFormat( $date )
	{
		$date = explode( "-", $date );
		return $date[2] . "/" . $date[1] . "/" . $date[0];
	}

    function mysqlToBrDateFormat( $date )
    {
        $date = str_replace( "/", "-", $date );
        $date = explode( "-", $date );
        return $date[2] . "/" . $date[1] . "/" . $date[0];
    }

    function numberToMysql( $number ){
	    $number = str_replace( ".", "", $number );
        $number = str_replace( ",", ".", $number );
        return $number;
    }

    function getBrowser()
	{
		if( !@$_SERVER["HTTP_USER_AGENT"] ) return NULL;

		$lista_navegadores = array( "MSIE", "Firefox", "Chrome", "Safari", "OPR" );
		$navegador_usado = $_SERVER["HTTP_USER_AGENT"];

		foreach($lista_navegadores as $valor_verificar)
		{
			if( strrpos($navegador_usado, $valor_verificar ))
			{
				return $valor_verificar;
				$navegador = $valor_verificar;
				$posicao_inicial = strpos($navegador_usado, $navegador) + strlen($navegador);
				$versao = substr($navegador_usado, $posicao_inicial, 5);
			}
		}

		return $navegador;
	}

	function normalizeParams()
	{
		foreach ($_POST as $key => $value) {
			if( is_array($value) ){
				foreach ($value as $k => $v1) {
					if( is_array($v1) ){
						foreach ($v1 as $j => $v2) {
							if( is_array($v2)){
                                foreach ($v2 as $l => $v3){
                                    $_POST[$key][$k][$j][$l] = utf8_decode(str_replace("'", "", $v3));
                                }
                            } else{
                                $_POST[$key][$k][$j] = utf8_decode(str_replace("'", "", $v2));
                            }
						}
					} else{
						$_POST[$key][$k] = utf8_decode(str_replace("'", "", $v1));
					}
				}
			} else{
				$_POST[$key] = utf8_decode(str_replace("'", "", $value));
			}
		}
		foreach ($_GET as $key => $value) {
			$_GET[$key] = str_replace("'", "", $value);
		}
	}

    function formatedName( $name )
    {
        return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"),$name);
    }
    
    function formatedNameForLink( $name ){
        $link = trim(strtolower(formatedName(utf8_encode($name))));
        $link = preg_replace('/\s\s+/', ' ', $link);
        $link = str_replace(" ", "-", $link);

        return $link;
    }

	function searchRedirect()
	{
		if (@$_POST['codeStand']){
			header('location:' . URI_PUBLIC . 'stands2/pesquisa/'. $_POST['codeStand'] . '/');
		}
	}

	function upImage( $file, $pathSave, $width, $heigth, $crop = false, $quality = 75 ){

		$imnfo = getimagesize( $file );

		// Definindo o tamanho da image
		$new_width  = $width;
		$new_height = $heigth;
		$crop_x = 0;
		$crop_y = 0;

		if ($imnfo[0] > $imnfo[1] ){
			if ($crop){
				$crop_x = $imnfo[1] * ($width / $heigth);
				if ($imnfo[0] > $crop_x) {
					$crop_x   = floor(($imnfo[0] - $crop_x) / 2);
					$crop_y   = 0;
					$imnfo[0] = $imnfo[0] - ($crop_x * 2);
				} else {
					$crop_y   = floor( ($crop_x - $imnfo[0]) / 2 );
					$crop_x   = 0;
					$imnfo[1] = $imnfo[1] - ($crop_y * 2);
				}
			} else {
				$scale      = $imnfo[0] / $imnfo[1];
				$new_width  = round( $heigth * $scale );
				$new_height = $heigth;
			}
		} else {
			if ($crop){
				$crop_y = $imnfo[0] * ($width / $heigth);
				if ($imnfo[1] > $crop_x) {
					$crop_y   = floor(($imnfo[1] - $crop_y) / 2);
					$crop_x   = 0;
					$imnfo[1] = $imnfo[1] - ($crop_y * 2);
				} else {
					$crop_x   = floor( ($crop_y - $imnfo[0]) / 2 );
					$crop_y   = 0;
					$imnfo[0] = $imnfo[0] - ($crop_x * 2);
				}
			} else {
				$scale      = $imnfo[1] / $imnfo[0];
				$new_width  = $width;
				$new_height = round( $width * $scale );
			}
		}

		// Carrega a imagem
		$source = imagecreatefromjpeg( $file );
		// Cria uma imagem no tamanho desejado
		$thumb  = imagecreatetruecolor($new_width, $new_height);
		// Copia a imagem
		imagecopyresampled($thumb, $source, 0, 0, $crop_x, $crop_y, $new_width, $new_height, $imnfo[0], $imnfo[1]);
		// Salva a imagem
		imagejpeg($thumb, $pathSave, $quality);
	}

    function treeAccess( $l_access )
    {
        $user_access = json_decode(file_get_contents( PATH_PLUGIN . "user/profile_access.json" ));

        if( @$l_access ){
            foreach( $l_access as $access ){
                $module = "{$access->user_profile_access_module}";
                $object = "{$access->user_profile_access_name}";
                if( !@$user_access->$module->$object ){
                    $user_access->$module->$object = new StdClass();
                }
                $user_access->$module->$object->value = $access->user_profile_access_value;
                $user_access->$module->$object->data_type = $access->user_profile_access_data_type;
            }
        }
        return $user_access;
    }

    function treeConfig( $l_config )
    {

        $ret = new StdClass();
        $target = Array();

        foreach( $l_config as $config )
        {
            $section = $config->config_section;
            $name = $config->config_name;
            if( !@$ret->$section ) $ret->$section = new StdClass();
            if( !@$ret->$section->$name ) $ret->$section->$name = new StdClass();

            if( $section == "target" )
            {
                $ret->$section->$name = Array();
                $target[] = explode( ":", $config->config_value );
                $ret->$section->$name = $target;
            }
            else
            {
                $ret->$section->$name = $config->config_value;
            }
        }

        return $ret;

    }

    function sanitizeString( $str ) {
        $str = preg_replace('/[áàãâä]/ui', 'a', $str);
        $str = preg_replace('/[éèêë]/ui', 'e', $str);
        $str = preg_replace('/[íìîï]/ui', 'i', $str);
        $str = preg_replace('/[óòõôö]/ui', 'o', $str);
        $str = preg_replace('/[úùûü]/ui', 'u', $str);
        $str = preg_replace('/[ç]/ui', 'c', $str);
        $str = preg_replace('/ /ui', '_', $str);
        // $str = preg_replace('/[,(),;:|!"#$%&/=?~^><ªº-]/', '_', $str);
        //$str = preg_replace('/[^a-z0-9]/i', '_', $str);
        //$str = preg_replace('/_+/', '_', $str); // ideia do Bacco :)
        return $str;
    }

    function getNodeEmpty( $branch )
    {
        $nodeId = 0;

        while( !$nodeId ){

            $newBranch = Array();
            foreach( $branch as $b ){

                if( !$nodeId ) {

                    $rede = new Rede(Array(
                        "parent_person_id" => $b
                    ));
                    $rede = $rede->getList();

                    if (sizeof($rede) < 3) {
                        $nodeId = $b;
                    }

                    $newBranch[] = $b;
                }
            }

            unset($branch);
            $branch = $newBranch;
            unset($newBranch);

        }

        return $nodeId;
    }

?>