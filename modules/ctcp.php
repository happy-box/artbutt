<?php

class Ctcp extends IRCine_Module {
	
	
	function install ( )
	{
		parent::install();
		$this->banned_channels = Mootyconf::get_values($this->main->server_config_prefix . '::banned_channels');
	}
	
	function uninstall ( )
	{
		parent::uninstall();
		$this->banned_channels = array ( );
	}
	
	function call ( $processed_data  )
	{
	
		$reply = false;
		$ctcp = explode ( ' ', trim ( $processed_data['message'] ) );
		$command = array_shift ( $ctcp );
		$rest = implode ( ' ', $ctcp );
		switch ( $command )
		{
			
			case 'VERSION':
				$reply = 'VERSION ' . Mootyconf::get_value('deerkins::ircname');
			break;
			
			case 'PING':
				$reply = 'PING ' . $rest;
			break;
			case 'TIME':
				$reply = 'TIME ' . date ( "D M j H:i:s Y" );
			break;
			case 'ACTION':
				return;
			
			default:
				$this->log ( 'unknown CTCP type: ' . $command );
				return NULL;
			
		}
		
		
		if ( $reply )
		{
			$this->log ( 'Plugin triggered, sending ' . $command . ' reply' );
			$this->message ( IRCINE_TYPE_CTCP_REPLY, $processed_data['nick'], $reply, IRCINE_PRIO_CRITICAL );
		}
	
	}

}
