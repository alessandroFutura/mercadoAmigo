<?php


	class UserProfile extends Model
	{
		public function __construct( $params )
		{
            //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'user_profile', "user_profile_name" );

            //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'user_profile_id'    , 'i', '=', true, true);
            $this->table->addField( 'user_profile_name'  , 's', '=', true);
            $this->table->addField( 'user_profile_update', 's', '=');
            $this->table->addField( 'user_profile_date'  , 's', '=');

            self::addCustomFieldSelect('(select count(*) from user where user.user_profile_id = user_profile.user_profile_id) count_users');

            // get_UserProfileAccess
            $this->table->fields["user_profile_id"]->link_class   = "UserProfileAccess";
            $this->table->fields["user_profile_id"]->link_id_name = "user_profile_id";
            $this->table->fields["user_profile_id"]->link_type    = "getList";

            parent::__construct( $params );
		}

	}

