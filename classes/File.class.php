<?php

	include PATH_CLASSES . "Thumbnail.class.php";
	
	class File
	{
		public static function CreatDir( $dir )
		{
			// Create directory
			@mkdir( $dir, 0755, true );
		}
		
		public static function UpThumb( $dir_file_name, $width, $height, $thumb_options )
		{
			try
			{
				$thumb = new Thumbnail( $_FILES['file']['tmp_name'] );
				// passando apenas os 3 primeiros parametros >> salvando em jpg crop and strech
				$thumb->save( $dir_file_name, $width, $height, $thumb_options );
			}
			catch( Exception $e )
			{
				
			}
		}

		public static function UpFile( $dir_file_name )
		{
			//1 MB
			$max_size = 1000000;
			if( $_FILES['file']['size'] < $max_size )
				move_uploaded_file( $_FILES['file']['tmp_name'], $dir_file_name );						
		}
		
		public static function RmFile( $file_dir )
		{
			@unlink( $file_dir );
		}
		
		public static function ClearDir( $path, array $extensions=null )
		{
			if( is_dir( $path ) && !is_link( $path ) )
			{
				if( $dir = opendir( $path ) )
				{
					while( ( $f = readdir( $dir ) ) !== false )
					{
						$ext = strtolower( substr( $f, strrpos( $f, "." ) + 1 ) );
						if( $f == '.' || $f == '..' || is_dir( $path.$f ) || is_link( $path . $f ) ) continue;
						if( $extensions && ! in_array( $ext, $extensions ) ) continue;
						@unlink( $path . $f );
					}
				}
			}
		}
		
		public static function renameDir( $old_name, $new_name )
		{
			rename( $old_name, $new_name );
		}

		public static function StrFileName( $file_name )
		{
			$a = array (
				'?' => 'S', '?' => 's', '?' => 'Dj', '?' => 'Z', '?' => 'z', '?' => 'A', '?' => 'A', '?' => 'A', '?' => 'A', '?' => 'A',
				'?' => 'A', '?' => 'A', '?' => 'C', '?' => 'E', '?' => 'E', '?' => 'E', '?' => 'E', '?' => 'I', '?' => 'I', '?' => 'I',
				'?' => 'I', '?' => 'N', '?' => 'O', '?' => 'O', '?' => 'O', '?' => 'O', '?' => 'O', '?' => 'O', '?' => 'U', '?' => 'U',
				'?' => 'U', '?' => 'U', '?' => 'Y', '?' => 'B', '?' => 'Ss', '?' => 'a', '?' => 'a', '?' => 'a', '?' => 'a', '?' => 'a',
				'?' => 'a', '?' => 'a', '?' => 'c', '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'i', '?' => 'i', '?' => 'i',
				'?' => 'i', '?' => 'o', '?' => 'n', '?' => 'o', '?' => 'o', '?' => 'o', '?' => 'o', '?' => 'o', '?' => 'o', '?' => 'u',
				'?' => 'u', '?' => 'u', '?' => 'y', '?' => 'y', '?' => 'b', '?' => 'y', '?' => 'f'
			);
			
			$file_name = str_replace( '.jpg', '', $file_name );
			$file_name = str_replace( '.jpeg', '', $file_name );
			$file_name = str_replace( '.JPG', '', $file_name );
			$file_name = str_replace( '.JPEG', '', $file_name );
			$file_name = str_replace( '.png', '', $file_name );
			$file_name = str_replace( '.PNG', '', $file_name );
			$file_name = str_replace( '.gif', '', $file_name );
			$file_name = str_replace( '.GIF', '', $file_name );
			$file_name = str_replace( '&', '-and-', $file_name );
			$file_name = trim( preg_replace( '/[^\w\d_ -]/si', '', $file_name ) );
			$file_name = str_replace( ' ', '-', $file_name );
			$file_name = str_replace( '--', '-', $file_name );

			return strtr( $file_name, $a );

			return $file_name;
		}
	}

?>