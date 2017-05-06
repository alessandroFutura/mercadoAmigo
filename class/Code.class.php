<?php


	class Code extends Model
	{
		public function __construct( $params )
		{
            //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'code', "code_id" );

            //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'code_id'     , 'i', '=', true, true);
            $this->table->addField( 'code_name'   , 's', '=', true);
            $this->table->addField( 'code_value'  , 'i', '=', true);
            $this->table->addField( 'code_update' , 's', '=');

            parent::__construct( $params );
		}

	}

