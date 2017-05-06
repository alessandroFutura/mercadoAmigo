<?php


	class PersonContact extends Model
	{
        /**
         * User constructor.
         * @param $params
         */
        public function __construct($params )
		{
		    //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'person_contact', 'person_contact_main DESC' );

		    //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'person_contact_id'      , 'i', '=', true, true);
            $this->table->addField( 'person_id'              , 'i', '=', true);
            $this->table->addField( 'person_contact_type_id' , 'i', '=', true);
            $this->table->addField( 'person_contact_main'    , 's', '=', true, false, true);
            $this->table->addField( 'person_contact_value'   , 's', '=', true);
            $this->table->addField( 'person_contact_name'    , 's', '=');
            $this->table->addField( 'person_contact_update'  , 's', '=');
            $this->table->addField( 'person_contact_date'    , 's', '=');

            parent::__construct( $params );
		}
    }

