<?php


	class Kit extends Model
	{
        /**
         * User constructor.
         * @param $params
         */
        public function __construct($params )
		{
		    //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'kit', 'kit_code' );

		    //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'kit_id'           , 'i', '=', true, true);
            $this->table->addField( 'kit_active'       , 's', '=', true, false, true);
            $this->table->addField( 'kit_code'         , 's', '=', true);
            $this->table->addField( 'kit_name'         , 's', '=');
            $this->table->addField( 'kit_addition'     , 'd', '=', true);
            $this->table->addField( 'kit_discount'     , 'd', '=', true);
            $this->table->addField( 'kit_value'        , 'd', '=', true);
            $this->table->addField( 'kit_update'       , 's', '=');
            $this->table->addField( 'kit_date'         , 's', '=');

            // get_ProductUnit
            $this->table->fields["kit_id"]->link_class   = "KitItem";
            $this->table->fields["kit_id"]->link_id_name = "kit_id";
            $this->table->fields["kit_id"]->link_type    = "getList";

            parent::__construct( $params );
		}

	}

