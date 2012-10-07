<?php

/**
 * IRCine - IRC engine (totally original name)
 *
 * @package IRCine
 * @author Jonas Skovmand
 * @created 2008-07-21
 * 
 * Yes, I do have a thing for coloured crayons.
 * TODO is_mode, kick, mode, invite, topic
 **/


class IRCine {

 /**
  * Holds the singleton instance
  *
  * @var instance
  **/
  
	public static $instance;
 
 /**
  * Holds the RAW data transferred from the IRC server
  *
  * @var data
  **/
  
	var $data;
	
 /**
  * Holds the processed data from the IRC server
  *
  * @var processed_data
  **/
  
	var $processed_data;
	
 /**
  * Holds the message buffer in priorities
  *
  * @var message_buffer
  **/
  
	var $message_buffer = array ( );
	
 /**
  * Count of the message_buffer
  *
  * @var instance
  **/
  
	var $buffer_size = 0;
	
 /**
  * INT used to distribute priorities
  *
  * @var give_to_the_poor
  **/
	
	var $give_to_the_poor = 0;

 /**
  * Timestamp of the last time the message_buffer was touched
  *
  * @var last_timestamp
  **/
  
	var $last_timestamp = 0;
	
	
 /**
  * Debug level of output log
  *
  * @var debuglevel
  **/
  
	var $debuglevel = IRCINE_DEBUG_HIGH;
	
 /**
  * msecs receive delay
  *
  * @var receive_delay
  **/
  
	var $receive_delay = 100;
	
 /**
  * msecs send delay
  *
  * @var send_delay
  **/
  
	var $send_delay = 250;
	
	
	var $command_timeout = 1000;
	
 /**
  * To timestamp or not to timestamp
  *
  * @var timestamp
  **/
	
	var $timestamp = true; // Timestamps messages in console. False = no timestamp.
	
	var $log_handle;
	
	var $memory_usage = 0;
	
	var $server_config_prefix = '';
	
	var $config_prefix = 'deerkins'; 
	
	var $nickname;
	
	var $config_file;
	
	/**
	 * method __construct
	 *
	 * @return void
	 * @author Jonas Skovmand
	 **/

	public function __construct ( $config )
	{
		
		$this->config_file = $config;
		$this->log_handle = fopen ( LOG_PATH . 'IRCine-' . date ( "Ymd" ) . '.log' , "a+" );
		
		$this->log ( 'STARTUP: IRCine version ' . IRCINE_VERSION . ' started.' );
		
		$this->connection = new Connection ( &$this );
		$this->module = new Module ( &$this );
		$this->input = new Input ( &$this );
		//$this->deer = new Deer ( $this );
		
		$this->clear_message_buffer ( );
		
		$this->log ( 'STARTUP: IRCine base loaded.' );
		
		$this->connection->load ( );
		$this->debuglevel = (int) Mootyconf::get_value('deerkins::debug_level');
		
		if( $this->module->load ( ) )
		{
		
			$this->log ( 'STARTUP: Modules loaded.' );
			$this->connection->connect ( );
			
		}
		else
			$this->quit ( 'Failed to load one or more modules.' );
		
	}
	
	function __destruct ( )
	{
	
		$this->log ( 'SHUTDOWN INITIATED' );
		$this->log ( 'Bot was shut down: ' . $this->connection->exit_reason );
		
		@fclose ( $this->log_handle );
	
	}
	
	function quit ( $reason = false )
	{
		if ( $reason != false && is_array ( $reason ) )
			$this->connection->exit_reason = $reason[0];
		elseif ( $reason != false )
			$this->connection->exit_reason = $reason;
		else
			$this->connection->exit_reason = Mootyconf::get_value('deerkins::quit_message');
		
		$this->connection->disconnect ( );
		
		exit ( );
	
	}
	
	function status ( )
	{
		
		$status['memory_usage'] = memory_get_usage ( true );
		$status['peak_memory_usage'] = memory_get_peak_usage ( true );
		$status['buffer_size'] = $this->buffer_size;
		
		$change_mem = ( $this->memory_usage == 0 ) ? $status['memory_usage'] : ( $status['memory_usage'] - $this->memory_usage );
		
		if ( $change_mem > 0 )
			$status['memory_history'] = '+' . $change_mem;
		elseif ( $change_mem == 0 )
			$status['memory_history'] = '±' . $change_mem;
		else
			$status['memory_history'] = '-' . $change_mem;
		
		$this->memory_usage = $status['memory_usage'];
		
		return $status;
		
	}
	
	function loop ( )
	{
	
		$this->log ( 'Starting main loop' );
		$raw_array = array ( );
		$last_part = '';
		
		while ( $this->connection->state ( ) == true )
		{
			
			$this->check_buffer ( );
			
			$stdin = $this->input->read();
			
			if ( $stdin != '' )
			{
				
				$this->module->trigger ( array ( // fake a processed_data array
					'message'	=>	$stdin,
					'IRC_TYPE'	=>	'CONSOLE',
					'type'		=>	'CONSOLE'
				) );
				
			}
			
			usleep ( $this->receive_delay * 1000 );
			
			$raw_data = @ fread ( $this->connection->socket, 10240 );
			
			if ( ! empty ( $raw_data ) )
			{
								
				$raw_data = str_replace("\r", '', $raw_data);
				$raw_data = $last_part.$raw_data;

				$last_part = substr($raw_data, strrpos($raw_data ,"\n")+1);
				$raw_data = substr($raw_data, 0, strrpos($raw_data ,"\n"));
				$raw_array = explode("\n", $raw_data);
				
				
			}
			
			while( count ( $raw_array ) > 0 )
			{
				
				$this->data = array_shift ( $raw_array );
									
				if( $this->debuglevel == IRCINE_DEBUG_HIGH )
					$this->log ( '<-- ' . $this->data );
				
				if ( $this->connection->handshake == 0 || $this->connection->handshake == 1 )
					$this->connection->handshake ( );
				
				$this->processed_data = $this->process_data ( $this->data );
				$this->module->trigger ( $this->processed_data );
			
			}
			
		}
		
		$this->log ( 'WARNING: Lost connection to server' );
		if ( $this->connection->info['reconnect'] == true )
		{
			
			$this->connection->reconnect ( );
			
		}
		
	
	}
	
	function process_data ( $data )
	{
			
		$cmdpattern = "/^([A-Z]+)\ \:(.*)$/";
		preg_match ( $cmdpattern, $data, $cmdmatch );
		
		$msgpattern = "/^\:(.*)\!(.*)\@([a-zA-Z0-9\.\-]+)\ ([A-Z]+)\ ([^\:\ ]+)\ \:(.*)$/";
		preg_match ( $msgpattern, $data, $match );
		
		//$serverpattern = "/^\:(.*)\ ([0-9]{3})\ (" . $this->nickname . ")\ \:(.*)$/";
		
		if( !empty( $cmdmatch ) )
		{
			
			return array (
				'IRC_TYPE'	=>	'cmd',
				'type'	=>	$cmdmatch[1],
				'message'	=>	$cmdmatch[2]
			);
			
		}
		elseif ( ! empty ($match) )
		{
			
			// check for CTCP, and set type accordingly. Get rid of anything CTCP stupid.
			// also, FUCK CTCP!!
			if ( preg_match ( '/^\001/', $match[6] ) )
			{
				$match[4] = 'CTCP';
				$match[6] = preg_replace ( '/\001/', '', $match[6] );
			}
			
			return array (
				'IRC_TYPE'	=>	'msg',
				'nick'	=>	$match[1],
				'ident'	=>	$match[2],
				'host'	=>	$match[3],
				'type'	=>	$match[4],
				'replyto'	=>	$match[5],
				'message'	=>	$match[6],
				'recepient'	=>	$match[5] == $this->nickname ? $match[1] : $match[5]
			);
			
		}
		
		return array ( 'IRC_TYPE' => 'none' );
		
	}
	
	function clear_message_buffer ( )
	{
		
		$this->message_buffer[ IRCINE_PRIO_HIGH ] = array ( );
		$this->message_buffer[ IRCINE_PRIO_MEDIUM ] = array ( );
		$this->message_buffer[ IRCINE_PRIO_LOW ] = array ( );
		
		$this->buffer_size = 0;
		$this->last_timestamp = 0;
		
	}
	
	function check_buffer ( )
	{
	
		$high_count = count ( $this->message_buffer[ IRCINE_PRIO_HIGH ] );
		$medium_count = count ( $this->message_buffer[ IRCINE_PRIO_MEDIUM ] );
		$low_count = count ( $this->message_buffer[ IRCINE_PRIO_LOW ] );
		$this->buffer_size = $high_count + $medium_count + $low_count;
		
		$now = microtime ( true );
		
		if ( $this->last_timestamp == 0 )
			$this->last_timestamp = $now;
		
		
		if ( $now >= ( $this->last_timestamp + ( $this->send_delay / 1000 ) ) )
		{
			//2 + strlen($line) / 120 // some hybrid ircd shizzle.
			if ( $high_count > 0 && $this->give_to_the_poor <= 2 )
			{
				$message = array_shift ( $this->message_buffer [ IRCINE_PRIO_HIGH ] );
				$this->raw_send ( $message );
				$this->give_to_the_poor++;
				$this->last_timestamp = $now; // + ( 2 + strlen($message) / 120 );
				
			}
			elseif ( $medium_count > 0 )
			{
				
				$message = array_shift ( $this->message_buffer [ IRCINE_PRIO_MEDIUM ] );
				$this->raw_send ( $message );
				$this->last_timestamp = $now; // + ( 2 + strlen($message) / 120 );
				$this->give_to_the_poor = 0;
				
			}
			elseif ( $low_count > 0 )
			{
				
				$message = array_shift ( $this->message_buffer [ IRCINE_PRIO_LOW ] );
				$this->raw_send ( $message );
				$this->last_timestamp = $now; // + ( 2 + strlen($message) / 120 );
				
			}
			
		}
	
	}
	
	function raw_send ( $msg )
	{
	
		$result = @fwrite( $this->connection->socket, $msg . "\r\n" );
		
		if( $result == false )
		{
		
			$this->log ( 'ERROR: Failed to send data: ' . $msg . "\r\n" );
			
		}
		else
		{
		
			if ( $this->debuglevel > IRCINE_DEBUG_LOW )
				$this->log ( '--> ' . $msg . "\r\n" );
		
		}
	
	
	}
	
	function send_data ( $msg, $prio = IRCINE_PRIO_LOW )
	{
	
		switch ( $prio )
		{
		
			case IRCINE_PRIO_CRITICAL:
			
				$this->raw_send ( $msg, $prio );
				
			break;
			
			case ( IRCINE_PRIO_LOW || IRCINE_PRIO_MEDIUM || IRCINE_PRIO_HIGH ):
			
				$this->message_buffer [ $prio ][] = $msg;
			
			break;
			
			default:
				
				$this->log ( 'No priority: ' . $msg );
		
		}
		
		
	}
	
	function message ( $type, $to = false, $msg, $prio = IRCINE_PRIO_LOW )
	{
			
		switch ( $type )
		{
			
			case IRCINE_TYPE_RAW:
			
				$this->send_data ( $msg, $prio ); // Sends ctcp request to target
				
			break;
			
			case IRCINE_TYPE_PRIV:
			
				$msg = str_replace ( PHP_EOL, '', $msg );
				if ( !empty ( $msg ) || $msg != '' )
					$this->send_data ( 'PRIVMSG ' . $to . ' :' . $msg, $prio ); // Sends message to channel
				
			break;
			
			case IRCINE_TYPE_NOTICE:
			
				$this->send_data ( 'NOTICE ' . $to . ' :' . $msg, $prio ); // Sends notice to target
				
			break;
			
			case IRCINE_TYPE_CTCP_REPLY:
			
				$this->send_data ( 'NOTICE ' . $to . ' :' . chr( 1 ) . $msg . chr( 1 ), $prio ); // Sends ctcp reply to target
				
			break;
			
			case IRCINE_TYPE_CTCP_REQUEST:
			
				$this->send_data ( 'PRIVMSG ' . $to . ' :' . chr( 1 ) . $msg . chr( 1 ), $prio ); // Sends ctcp request to target
				
			break;
			
			case IRCINE_TYPE_ACTION:
			
				$this->send_data ( 'PRIVMSG ' . $to . ' :' . chr( 1 ) . 'ACTION ' . $msg . chr( 1 ), $prio ); // Sends ctcp reply to target
				
			break;
		
			default:
				return;
			
		
		}
		
	
	}	
	
	function log ( $msg, $handler = __CLASS__ )
	{
		
		$prefix = strpos ( $msg, '<--' ) === false && strpos ( $msg, '-->' ) === false ? ' -!- ' : ' ';
		$msg = str_replace( array ( "\n", "\r" ), '', $msg );
		
		if ( is_object ( $handler ) )
			$handler = get_class ( $handler );
		elseif( !$handler )
			$handler = get_class ( $this );
		
		$log = date("Y-m-d H:i:s") . ' ' . Mootyconf::get_value('deerkins::nick') . ' ( ' . $handler  . ' ) !? ' . $prefix .  $msg;
		$output = date("Y-m-d H:i:s") . ' ' . Mootyconf::get_value('deerkins::nick') . ' ' . Bashful::bold() . '(' . Bashful::unbold() . ' ' . $handler  . ' ' . Bashful::bold() . ')' . Bashful::unbold() . $prefix .  $msg;

		if ( $this->debuglevel == IRCINE_DEBUG_HIGH || $this->debuglevel == IRCINE_DEBUG_MEDIUM )
		{
			if ( !@fwrite ( $this->log_handle, $log . PHP_EOL ) )
				$output .= " [NO LOG]";
		}
		echo $output . PHP_EOL;
	
	}
	
	function out ( $msg, $handler = __CLASS__ )
	{
		
		echo '(' . get_class ( $handler ) . ') ' . $msg . PHP_EOL . '> ';
		
	}
}
