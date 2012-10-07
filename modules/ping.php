<?php

/**
 * Ping class
 * Responds to PING with PONG
 * @package IRCine
 * @author Jonas Skovmand <jonas@satf.se>
 **/

class Ping extends IRCine_Module {
	
	function call ( $processed_data )
	{

		$this->log ( 'Plugin triggered, sending PONG' );
		$this->raw ( 'PONG :' . $processed_data['message'], IRCINE_PRIO_CRITICAL ); // Responds with pong
	
	}

}
