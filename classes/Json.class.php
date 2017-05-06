<?php
	
	class Json
	{
		public $status;
		public $data;

		public function __construct( $status, $data )			
		{
			$this->status = $status;
			$this->data   = $data;
		}
		
		public static function get( $status, $data=NULL )
		{
			$ret = new Json( $status, $data );
			echo json_encode($ret);
			exit;
		}
	}
