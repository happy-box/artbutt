<?php

class Help extends IRCine_Module {

	var $h_triggers = array ( );
	
	function call ( $processed_data )
	{
		if ( empty ( $this->h_triggers ) )
		{
			foreach ( $this->main->module->modules as $module )
			{
				foreach ( $module->help_triggers as $trigger )
				{
					$this->h_triggers[$trigger['trigger']] = $trigger['description'];
				}
			}
		}
		
		$help = trim ( $this->strip_command ( $processed_data['message'] ) );
		
		if ( $help == '' )
			$this->notice ( $processed_data['nick'], chr ( 02 ) . 'List of commands: ' . chr ( 02 ) . implode (', ', array_keys ( $this->h_triggers ) ), IRCINE_PRIO_LOW );
		elseif ( isset ( $this->h_triggers[$help] ) )
			$this->notice ( $processed_data['nick'], chr ( 02 ) . 'Help for ' . $help . ': ' . chr ( 02 ) . $this->h_triggers[$help], IRCINE_PRIO_LOW );
		//else
		//	$this->notice ( $processed_data['nick'], chr ( 02 ) . 'Command ' . $help . ' does not exist.' . chr ( 02 ), IRCINE_PRIO_LOW );
	}

}
