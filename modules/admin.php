<?php


class Admin extends IRCine_Module {

	var $admins = array ( );
	
	function install ( )
	{
		
		parent::install();
		
		
		$admins = Mootyconf::get_values ( $this->pluginspace . '::admins' );
		foreach ( $admins as $a => $admin )
		{
			if ( $admin == '' ) continue;
			$this->admins[] = array (
				'nick'		=>	$admin,
				'host'		=>	Mootyconf::get_value( 'plugin::' . $this->module . '::' . $admin . '::host' ),
				'password'	=>	Mootyconf::get_value( 'plugin::' . $this->module . '::' . $admin . '::password' )
			);
		}
		
	}
	
	function uninstall ( )
	{
		
		parent::uninstall ( );
		$this->admins = array ( );
		
	}
	
	function call ( $processed_data )
	{
		if ( ( $msg = $this->authenticate ( $processed_data ) ) == false ) return;
		
		$message = $this->strip_command ( $msg ); // $msg is stripped of password if matched by nick + password!
		$cmd = array_shift(explode(' ',$message));
		if ( $this->main->module->trigger ( array ( 'message'	=>	$message, 'IRC_TYPE'	=>	'CONSOLE', 'type'		=>	'CONSOLE' ) ) )
		{
			$this->log ( 'admin->' . $cmd . ' called successfully, somehow!' );
		}
	}
	
	function raw_command ( $processed_data )
	{
		$command = $this->strip_command ( $processed_data['message'] );
		
		$this->main->message ( IRCINE_TYPE_RAW, false, $command, IRCINE_PRIO_HIGH );
		$this->log ( 'RAW command issued', __CLASS__ );
		
	}
	
	function say_cmd ( $processed_data )
	{
		$arg = explode ( ' ', $this->strip_command ( $processed_data['message'] ) ) ;
		$to = array_shift ( $arg );
		$message = implode ( ' ', $arg );
		
		if ( $to && $message )
		{
			$this->message ( IRCINE_TYPE_PRIV, $to, $message, IRCINE_PRIO_HIGH );
		$this->log ( 'SAY command issued' );
		}
		else
		{
			$this->log ( 'SAY command ' . Bashful::red() . 'FAILED' . Bashful::unred() . ': ' . $to . ' -> '. $message );
		}
			
		
	}
	
	function rejoin_channels ( $processed_data )
	{
		$channels = explode ( ' ', $this->strip_command ( $processed_data['message'] ) ) ;
		if ( count ( $channels ) > 0 )
			$this->main->connection->part_join_channels ( $channels, true );
		else
			$this->main->connection->part_join_channels ( $this->main->connection->joined_channels, true );
		
		$this->log ( 'Channels ' . implode(', ', $channels ) . ' rejoined' );
	
	}
	
	function quit ( $processed_data )
	{
		$msg = $this->strip_command ( $processed_data['message'] );
		$this->main->quit ( $msg );
		
	}
	
	public function join ( $processed_data )
	{
		parent::join ( $this->strip_command ( $processed_data['message'] ) );
	}
	
	public function part ( $processed_data )
	{
		parent::part ( $this->strip_command ( $processed_data['message'] ) );
	}
	
	public function nickname ( $processed_data )
	{
		parent::nickname ( $this->strip_command ( $processed_data['message'] ) );
	}
	
	public function deer ( $processed_data )
	{
		
		$console = explode ( ' ', $this->strip_command ( $processed_data['message'] ));
		$reply = array_shift ( $console );
		$message = 'deerme ' . implode ( ' ', $console );
		$this->main->module->modules['deerme']->deer(array (
			'message'		=>	$message,
			'nick'			=>	'__CONSOLE__',
			'__BYPASS__'	=>	true,
			'type'			=>	'PRIVMSG',
			'host'			=>	'localhost',
			'IRC_TYPE'		=>	'msg',
			'replyto'		=>	$reply,
			'recepient'		=>	$reply
		));
	}
	
	public function rehash ( $processed_data )
	{
		$modules = $this->strip_command ( $processed_data['message'] );
		$modules = $modules==''?array():explode ( ' ', $this->strip_command ( $processed_data['message'] ) );
		$this->main->module->rehash ( $modules );
		
	}
	
	function authenticate ( $processed_data )
	{
		
		if ( $processed_data['type'] == 'CONSOLE' ) // called from console, definitely admin
		{
			return $processed_data['message'];
		}
		
		if ( $this->admin_match ( 'host', $processed_data['host'] ) != false ) // host matches, admin!
		{
			$this->log ( 'admin auth match on host: ' . $processed_data['host'] );
			return $processed_data['message'];
		}
			
		if ( ( $admin = $this->admin_match ( 'nick', $processed_data['nick'] ) ) != false ) // nick matches, require password
		{
			$message_parts = explode ( ' ', $processed_data['message'] ); array_shift ( $message_parts );
			$password = array_shift ( $message_parts ); // first argument should be password
			
			if ( $admin['password'] != '' && $admin['password'] == $password )
			{
				$this->log ( 'admin auth match on USER AND PASSWORD' );
				return '!admin ' . implode ( ' ', $message_parts ); // returns message stripped of password
			}
			
		}
		
		$this->log ( Bashful::red() . 'AUTHENTICATION ERROR' . Bashful::unred() . ' BY ' . $processed_data['nick'] . ' (' . $processed_data['host'] . ') IN ' . $processed_data['replyto'] . ': ' . $processed_data['message'] );
		
	}
	
	function admin_match ( $key, $value )
	{
		
		foreach ( $this->admins as $admin )
		{
			if ( $admin[$key]!=''&&$admin[$key]==$value )
				return $admin;
		}
		return false;
	}

}
