<?php


	class Product extends Model
	{
        /**
         * User constructor.
         * @param $params
         */
        public function __construct($params )
		{
		    //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'product', 'product_name' );

		    //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'product_id'           , 'i', '=', true, true);
            $this->table->addField( 'product_unit_id'      , 'i', '=', true);
            $this->table->addField( 'product_active'       , 's', '=', true, false, true);
            $this->table->addField( 'product_code'         , 's', '=', true);
            $this->table->addField( 'product_ean'          , 's', '=', true);
            $this->table->addField( 'product_name'         , 's', '=', true);
            $this->table->addField( 'product_description'  , 's', '=', true);
            $this->table->addField( 'product_update'       , 's', '=');
            $this->table->addField( 'product_date'         , 's', '=');

            // get_ProductUnit
            $this->table->fields["product_unit_id"]->link_class   = "ProductUnit";
            $this->table->fields["product_unit_id"]->link_id_name = "product_unit_id";
            $this->table->fields["product_unit_id"]->link_type    = "get";

            parent::__construct( $params );
		}

	}

