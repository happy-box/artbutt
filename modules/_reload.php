<?php

class Reload extends IRCine_Module {

	function call (  )
	{
		
		$ck = '!reload ' . $this->main->config->config['bot']['modules']['admin']['password'];
		if ( $this->main->processed_data['IRC_TYPE'] == 'msg' && strlen ( $this->main->processed_data['message'] ) >= strlen ( $ck ) )
		{
			
			$test = substr_compare ( $this->main->processed_data['message'], $ck, 0, strlen (  $ck ), true );
			
			if ( $test == 0 )
			{
				
				if ( strlen ( $this->main->processed_data['message'] ) > strlen ( $ck ) )
				{
					$args = explode ( ' ', trim( substr( $this->main->processed_data['message'], strlen ( $ck ) ) ) );
				}
				else
				{
					$args = array ( );
				}
				
				$reload_these = ( count ( $args ) > 0 ) ? implode ( ', ', $args ) : 'all plugins';
				
				$this->main->log ( 'Plugin triggered, reloading: ' . $reload_these . '.', __CLASS__ );
				
				$reloaded = $this->main->module->reload ( $args, true );
				
				$astring = ( count ( $reloaded ) > 0 ) ? implode ( ', ', $reloaded ) : 'NONE';
				
				if( $this->main->config->config['user']['nick'] == $this->main->processed_data['replyto'] )
				{
					$replyto = $this->main->processed_data['nick'];
				}
				else
				{
					$replyto = $this->main->processed_data['replyto'];
				}
				
				$this->main->message ( IRCINE_TYPE_PRIV, $replyto, 'Reloaded plugins: ' . $astring . '.', IRCINE_PRIO_MEDIUM );
			}
			
		}
		
	}
	
	
}
