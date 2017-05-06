<?php


	class OrderStatus extends Model
	{
        /**
         * User constructor.
         * @param $params
         */
        public function __construct($params )
		{
		    //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'order_status', 'order_status_id' );

		    //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'order_status_id'          , 'i', '=', true, true);
            $this->table->addField( 'order_status_active'      , 's', '=', true, false, true);
            $this->table->addField( 'order_status_code'        , 's', '=', true);
            $this->table->addField( 'order_status_name'        , 's', '=', true);
            $this->table->addField( 'order_status_color'       , 's', '=', true);
            $this->table->addField( 'order_status_start'       , 's', '=', true, false, true);
            $this->table->addField( 'order_status_editable'    , 's', '=', true, false, true);
            $this->table->addField( 'order_status_end'         , 's', '=', true, false, true);
            $this->table->addField( 'order_status_super'       , 's', '=', true, false, true);
            $this->table->addField( 'order_status_super'       , 's', '=', true, false, true);
            $this->table->addField( 'order_status_mail_admin'  , 's', '=', true, false, true);
            $this->table->addField( 'order_status_mail_client' , 's', '=', true, false, true);
            $this->table->addField( 'order_status_update'      , 's', '=');
            $this->table->addField( 'order_status_date'        , 's', '=');

            self::addCustomFieldSelect('(select count(*) from `order` where order.order_status_id = order_status.order_status_id) order_status_count_order');

            parent::__construct( $params );
		}
    }

