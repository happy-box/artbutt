<?php

class Halp extends IRCine_Module {

	var $halp_list_file;
	var $random_things = array ( );

	function install ( )
	{
		
		parent::install();
		$this->halp_list_file = MODULE_PATH . Mootyconf::get_value('plugin::halp::halplist');
		$this->load_halp_list ( );
		
	}
	
	function rand_halp ( $processed_data )
	{
		
		$args = $this->get_args ( $processed_data['message'] );
		$admin = false;
		
		$prefix = '';
		if ( @ strtolower ( $args[0] ) == 'to' && !empty ( $args[1] ) )
			$prefix = ', ' . $args[1];
		
		if ( @ strtolower ( $args[0] ) == 'me' )
		{
			
			if ( $this->main->module->is_loaded ( 'admin' ) )
				$admin = $this->main->module->modules['admin']->admin_match('host', $processed_data['host']);
				
			if ( $admin != false && $admin != '' )
			{
				$prefix = ', GOD OF ALL THAT IS SANE';
			}
			else
			{
				$prefix = ', ASSHOLE';
			}
				
		}
		
		$rand = rand ( 0, count ( $this->random_things ) - 1 );
		
		
		$this->say ( $processed_data['replyto'], 'HERE, TAKE THIS' . $prefix . ': <' . trim ( str_replace ( "\n", "", $this->random_things[$rand] ) ) . chr ( 15 ) . '>', IRCINE_PRIO_MEDIUM );
	
	}
	
	function add_halp ( $processed_data )
	{
		
		$args = $this->get_args ( $processed_data['message'] );
		
		if ( !empty ( $args ) )
		{
			
			$args = implode ( ' ', $args );
			$args = preg_replace ( "/[\x{00a0}]+/u", "", $args );
			$args = preg_replace ( "/\s+/", " ", $args );
			$args = trim ( $args );
			
			if ( array_search ( $args, $this->random_things ) === false && !empty ( $args ) )
			{
				
				$handle = fopen ( $this->halp_list_file, "a" );
				
				if ( !$handle )
				{
					
					$this->log ( 'Could not open halp list for writing!' );
				
				}
				else
				{
					if ( ! fwrite ( $handle, "\r\n" . $args ) )
						$this->log ( 'Failed to write ' . $args . ' to halp list!' );
					else
						$this->log ( 'Wrote ' . $args . ' to halp list!' );
					
				}
				fclose ( $handle );
				
				$this->random_things[] = $args;
				$this->say ( $processed_data['replyto'], 'THANKS FOR YOUR ADDITIONAL HALP: <' . $args . '>!', IRCINE_PRIO_MEDIUM );
			}
			else
				$this->say ( $processed_data['replyto'], "THIS IS NOT HALPFUL!", IRCINE_PRIO_MEDIUM );
			
		}
		else
			$this->say ( $processed_data['replyto'],  "ADDHALP FAIL :(", IRCINE_PRIO_MEDIUM );
		
	}
	
	function load_halp_list ( )
	{
		
		$this->log ( 'Loading halp list...' );
		clearstatcache ( );
		$handle = fopen ( $this->halp_list_file, "rb" );
			
		if ( $handle !== false )
		{
			
			$halps = fread( $handle, filesize ( $this->halp_list_file ) );
			
			$this->random_things = explode ( PHP_EOL, $halps );
			
			fclose ( $handle );
			
		}
		
		
	}
	
	function list_halp ( $processed_data )
	{
		
		$args = $this->get_args ( $processed_data['message'] );
		$halps = array ( );
		
		if ( empty ( $this->random_things ) )
			$this->random_things = $this->load_halp_list ( );
		
		$keys = array_rand ( $this->random_things, 10 );
		$keys = array_unique ( $keys );
		
		foreach ( $keys as $key ) 
		{
			$halps[] = str_replace ( "\n", "", trim ( $this->random_things[$key] ) );
		}
		
		$halps = implode ( ', ', $halps );
		
		$this->say ( $processed_data['replyto'],  'HERE, TAKE SOME: ' . $halps, IRCINE_PRIO_MEDIUM );
		
	
	}
	
	function has_halp ( $processed_data )
	{
		
		$args = $this->get_args ( $processed_data['message'] );
		
		$args = implode ( ' ', $args );
		
		if ( in_array ( $args, $this->random_things ) )
			$this->say ( $processed_data['replyto'], $args . " - I HAS THEM!", IRCINE_PRIO_MEDIUM );
		else
			$this->say ( $processed_data['replyto'],  "I AM DISABLED :(", IRCINE_PRIO_MEDIUM );
		
	}
	
	function num_halp ( $processed_data )
	{
		
		if ( count ( $this->random_things ) < 9000 )
			$append = 'It is a scientific fact that this value is below the magic number of ninethousand.';
		else
			$append = 'This number is EQUAL TO or ABOVE the number 9000. What did you expect here? Some reference to Dragon Ball Z? Buzz off.';
		
		$this->say ( $processed_data['replyto'], "I HAS MANY HALP. PROBABLY AROUND, MORE THAN OR EXACTLY EQUAL TO " . chr ( 2 ) . count ( $this->random_things ) . chr ( 2 ) . ". " . $append, IRCINE_PRIO_MEDIUM );
		
	}
	
	function grep_halp ( $processed_data )
	{
		$search = trim ( $this->strip_command ( $processed_data['message'] ) );
		$found = 0;
		
		if ( $search == '' )
			return $this->say ( $processed_data['replyto'], 'I GREPPED HARD, LIKE I UNDERSTOOD. NO GREP THOUGH.', IRCINE_PRIO_MEDIUM );
		
		foreach ( $this->random_things as $halp )	
			if ( stripos ( $halp, $search ) !== false )
				$found++;
		
		if ( $found > 0 )
			$this->say ( $processed_data['replyto'], 'VERY HALPFUL! ' . $found . ' HALPS!!!', IRCINE_PRIO_MEDIUM );
		else
			$this->say ( $processed_data['replyto'], 'I GREPPED HARD, LIKE I UNDERSTOOD. NOT FOUND ANYTHING SORRY.', IRCINE_PRIO_MEDIUM );
	}

}
