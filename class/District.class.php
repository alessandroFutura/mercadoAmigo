<?php


	class District extends Model
	{
		public function __construct( $params )
		{
            //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'district', "district_name" );

            //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'district_id'    , 'i', '=', true, true);
            $this->table->addField( 'city_id'        , 'i', '=', true);
            $this->table->addField( 'district_name'  , 's', '=', true);
            $this->table->addField( 'district_update', 's', '=');
            $this->table->addField( 'district_date'  , 's', '=');

            // get_UserProfile
            $this->table->fields["city_id"]->link_class   = "City";
            $this->table->fields["city_id"]->link_id_name = "city_id";
            $this->table->fields["city_id"]->link_type    = "get";

            parent::__construct( $params );
		}

		public function setWhereIbgeCity( $ibge ){
            $sql = "city_id = (SELECT city.city_id FROM city WHERE city.city_ibge = {$ibge})";

            $this->customWhere[] = $sql;
        }

	}

