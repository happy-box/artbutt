<?php

/**
 * Talk
 * For simple triggers created in the config
 */

class Talk extends IRCine_Module {
	
	
	function call ( $processed_data )
	{
		$message = explode ( ' ', $processed_data['message'] );
		$trigger = array_shift ( $message );
		$takes_args = Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::args' ) == 'none' ? false : true;
		
		if ( count ( $message ) > 0 && !$takes_args )
			return;
		
		switch ( Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::reply_type' ) )
		{
			
			case 'action':
				$this->action ( $processed_data['recepient'], str_replace ( array ( '%bold', '%sender' ), array ( chr(2), $processed_data['nick'] ), Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::message' ) ), IRCINE_PRIO_MEDIUM );
			break;
			
			case 'notice':
				$this->notice ( $processed_data['recepient'], str_replace ( array ( '%bold', '%sender' ), array ( chr(2), $processed_data['nick'] ), Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::message' ) ), IRCINE_PRIO_MEDIUM );
			break;
			
			case 'say':
			default:
				$this->say ( $processed_data['recepient'], str_replace ( array ( '%bold', '%sender' ), array ( chr(2), $processed_data['nick'] ), Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::message' ) ), IRCINE_PRIO_MEDIUM );
			
		}
		
		
	}
	
	
	function status ( )
	{
		
		$status = $this->main->status ( );
		$return = '' . chr ( 2 ) . 'Status' . chr ( 2 ) . ': memory usage (peak) - : ' . $status['memory_usage'] . ' (' . $status['peak_memory_usage'] . ') [' . $stats['memory_history'] . '] , buffer size: ' . $status['buffer_size'];
		
		return $return;
		
	}

}
