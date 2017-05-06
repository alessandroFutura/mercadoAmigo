<?php


	class UserProfileAccess extends Model
	{
		public function __construct( $params )
		{
            //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'user_profile_access', 'user_profile_access_id');

            //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'user_profile_access_id', 		 'i', '=', true, true );
		    $this->table->addField( 'user_profile_id', 				 'i', '=', true, true );
		    $this->table->addField( 'user_profile_access_module' ,   's', '=', true );
            $this->table->addField( 'user_profile_access_name',		 's', '=');
            $this->table->addField( 'user_profile_access_value',	 's', '=');
            $this->table->addField( 'user_profile_access_data_type', 's', '=');
            $this->table->addField( 'user_profile_access_date', 	 's', '=');

            parent::__construct( $params );
		}

		public function editAccess( $profile_id=NULL )
		{
			GLOBAL $db, $jsonStatus, $errorMessage;
			
			$profile_access = json_decode(file_get_contents( PATH_PLUGIN . "user/profile_access.json" ));
			
			if( !@$profile_id ){
				self::delete();
			}
			
			foreach( $profile_access as $k => $access ) {
				foreach( $access as $j => $ac ) {
					if( $j != "name" ){
						if( @$_POST["{$k}_{$j}"] ){					
							$params = Array(
								"user_profile_id" => @$profile_id ? $profile_id : $_POST["user_profile_id"],
								"user_profile_access_module" => $k,
								"user_profile_access_name" => $j,
								"user_profile_access_value" => ( $access->$j->data_type == "bool" ? "Y" : $_POST["{$k}_{$j}"] ),
								"user_profile_access_data_type" => $access->$j->data_type
							);
							$user_profile_access = new UserProfileAccess($params);
							$user_profile_access->insert();
						}
					}
				}
			}
		}

	}

