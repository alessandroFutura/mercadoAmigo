<?php


	class PersonAddress extends Model
	{
        /**
         * User constructor.
         * @param $params
         */
        public function __construct($params )
		{
		    //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'person_address', 'person_address_cep' );

		    //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'person_address_id'          , 'i', '=', true, true);
            $this->table->addField( 'person_id'                  , 'i', '=', true);
            $this->table->addField( 'person_address_cep'         , 's', '=', true);
            $this->table->addField( 'person_address_uf'          , 's', '=', true);
            $this->table->addField( 'city_id'                    , 'i', '=', true);
            $this->table->addField( 'person_address_district'    , 's', '=');
            $this->table->addField( 'person_address_public_place', 's', '=', true);
            $this->table->addField( 'person_address_number'      , 's', '=', true);
            $this->table->addField( 'person_address_complement'  , 's', '=');
            $this->table->addField( 'person_address_update'      , 's', '=');
            $this->table->addField( 'person_address_date'        , 's', '=');

            parent::__construct( $params );
		}
    }

