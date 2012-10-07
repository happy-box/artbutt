<?php

/**
 * "Dumb" nickserv bot. Can be easily tricked and is probably server dependent
 **/
class Nickserv extends IRCine_Module {
	
	var $nickserv = 'NickServ';
	
	function check_nickserv ( $processed_data )
	{
		
		if ( $processed_data['message'] == 'This nickname is registered and protected. If it is your' && $processed_data['nick'] === $this->nickserv )
			$this->identify ( );
		
	}
	
	function identify ( )
	{
	
		if ( Mootyconf::get_value( $this->pluginspace . '::password') != '' )
		{
		
			$this->main->log ( 'Identifying with NickServ...', __CLASS__ );
			$this->main->message ( IRCINE_TYPE_PRIV, $this->nickserv, 'IDENTIFY ' . Mootyconf::get_value( $this->pluginspace . '::password'), IRCINE_PRIO_CRITICAL );
		
		}
		
	}

}
