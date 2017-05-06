<?php


	class Rede extends Model
	{
		public function __construct( $params )
		{
            //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'rede', "rede_id" );

            //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'rede_id'           , 'i', '=', true, true);
            $this->table->addField( 'rede_active'       , 's', '=', true, false, true);
		    $this->table->addField( 'user_id'           , 'i', '=', true);
            $this->table->addField( 'parent_person_id'  , 'i', '=', true);
            $this->table->addField( 'child_person_id'   , 'i', '=', true);
            $this->table->addField( 'rede_type'         , 's', '=', true);
            $this->table->addField( 'rede_update'       , 's', '=');
            $this->table->addField( 'rede_date'         , 's', '=');

            parent::__construct( $params );
		}

	}

