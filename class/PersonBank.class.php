<?php


	class PersonBank extends Model
	{
		public function __construct( $params )
		{
            //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'person_bank', "bank_code" );

            //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'person_bank_id'        , 'i', '=', true, true);
            $this->table->addField( 'person_id'             , 'i', '=', true);
            $this->table->addField( 'bank_code'             , 's', '=');
		    $this->table->addField( 'person_bank_agency'    , 's', '=');
            $this->table->addField( 'person_bank_account'   , 's', '=');
            $this->table->addField( 'person_bank_type'      , 's', '=');
            $this->table->addField( 'person_bank_update'    , 's', '=');
            $this->table->addField( 'person_bank_date'      , 's', '=');

            parent::__construct( $params );
		}

	}

