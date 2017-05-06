<?php


	class UF extends Model
	{
		public function __construct( $params )
		{
            //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'uf', "uf_name" );

            //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'uf_id'    , 'i', '=', true, true);
            $this->table->addField( 'uf_code'  , 's', '=', true);
		    $this->table->addField( 'uf_name'  , 's', '=', true);
            $this->table->addField( 'uf_ibge'  , 's', '=');
            $this->table->addField( 'uf_update', 's', '=');
            $this->table->addField( 'uf_date'  , 's', '=');

            parent::__construct( $params );
		}

	}

