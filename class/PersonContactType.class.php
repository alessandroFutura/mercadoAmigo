<?php


	class PersonContactType extends Model
	{
        /**
         * User constructor.
         * @param $params
         */
        public function __construct($params )
		{
		    //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'person_contact_type', 'person_contact_type_name' );

		    //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'person_contact_type_id'     , 'i', '=', true, true);
            $this->table->addField( 'person_contact_type_name'   , 's', '=', true);
            $this->table->addField( 'person_contact_type_update' , 's', '=');
            $this->table->addField( 'person_contact_type_date'   , 's', '=');

            self::addCustomFieldSelect('(select count(*) from person_contact where person_contact.person_contact_type_id = person_contact_type.person_contact_type_id) person_contact_type_count_person');

            parent::__construct( $params );
		}
    }

