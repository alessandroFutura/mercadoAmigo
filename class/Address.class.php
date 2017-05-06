<?php


	class Address extends Model
	{
		public function __construct( $params )
		{
            //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'address', "address_id" );

            //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'address_id'          , 'i', '=', true, true);
            $this->table->addField( 'person_id'           , 'i', '=', true);
            $this->table->addField( 'district_id'         , 'i', '=', true);
            $this->table->addField( 'address_main'        , 's', '=', true, false, true);
            $this->table->addField( 'address_delivery'    , 's', '=', true, false, true);
            $this->table->addField( 'address_cep'         , 's', '=', true);
            $this->table->addField( 'address_public_place', 's', '=', true);
            $this->table->addField( 'address_number'      , 's', '=', true);
            $this->table->addField( 'address_complement'  , 's', '=');
            $this->table->addField( 'address_update'      , 's', '=');
            $this->table->addField( 'address_date'        , 's', '=');

            // get_District
            $this->table->fields["district_id"]->link_class   = "District";
            $this->table->fields["district_id"]->link_id_name = "district_id";
            $this->table->fields["district_id"]->link_type    = "get";

            parent::__construct( $params );
		}

	}

