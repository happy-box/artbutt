<?php


class Invite extends IRCine_Module {
	
	var $banned_channels = array ( );
	
	function install ( )
	{
		parent::install();
		$this->banned_channels = Mootyconf::get_values($this->main->server_config_prefix . '::banned_channels');
	}
	
	function call ( $processed_data )
	{
		
		$channel = $processed_data['message'];
		
		if ( in_array ( $channel, $this->banned_channels ) )
		{
			$this->log ( 'invited to banlisted channel (' . $channel . '), ignoring.' );
			return;
		}
		
		$this->log ( 'invited to ' . $channel . ', joining.' );
		$this->join($channel);
		
	}
	
}