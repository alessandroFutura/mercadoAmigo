<?php


	class PersonCategory extends Model
	{
        /**
         * User constructor.
         * @param $params
         */
        public function __construct($params )
		{
		    //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'person_category', 'person_category_name' );

		    //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'person_category_id'     , 'i', '=', true, true);
            $this->table->addField( 'person_category_name'   , 's', '=', true);
            $this->table->addField( 'person_category_update' , 's', '=');
            $this->table->addField( 'person_category_date'   , 's', '=');

            self::addCustomFieldSelect('(select count(*) from person_category_link where person_category.person_category_id = person_category_link.person_category_id) person_category_count_person');

            parent::__construct( $params );
		}
    }

