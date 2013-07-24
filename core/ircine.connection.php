<?php

class Connection extends IRCine_Core {
	
	var $socket;
	
	var $online_start;
	
	var $handshake = 0;
	
	var $joined_channels = array ( );
	
	var $retry_count = 0;
	var $retry_max = 3;
	
	var $reconnect_timeout = 10;
	var $listen_timeout = 480;
	
	var $exit_reason;
	
	var $server_config_prefix = '';
	
	public $info = array ( );
	
	
	function __destruct ( )
	{
	
		if ( $this->state ( ) == true )
		{
			
			$this->exit_reason = 'Disconnecting!';
			$this->disconnect ( );

		}
		
	}
	
	public function load ( )
	{
		
		$server_name = Mootyconf::get_values('deerkins::servers');
		$this->main->server_config_prefix = 'deerkins::servers::' . $server_name;
		$this->server_config_prefix = 'deerkins::servers::' . $server_name;
		
		$this->info = array (
			'name'		=>	$server_name,
			'address'	=>	Mootyconf::get_value($this->server_config_prefix . '::address'),
			'ssl'		=>	Mootyconf::get_value($this->server_config_prefix . '::ssl'),
			'port'		=>	Mootyconf::get_value($this->server_config_prefix . '::port'),
			'pass'		=>	Mootyconf::get_value($this->server_config_prefix . '::pass'),
			'channels'	=>	Mootyconf::get_values($this->server_config_prefix . '::channels'),
			'reconnect'	=>	Mootyconf::get_value($this->server_config_prefix . '::reconnect'),
			'ident'		=>	Mootyconf::get_value('deerkins::ident'),
			'realname'	=>	Mootyconf::get_value('deerkins::realname')
		);
		
		
	}
	
	function connect( )
	{
		
		$this->online_start = microtime ( true );
		
		$this->main->log ( 'Connecting to ' . $this->info['address'] . '...', __CLASS__ );
		if ($this->info['ssl']) {
			$this->socket = fsockopen( 'sslv3://' . $this->info['address'].'/', $this->info['port'], $erno, $errstr, 30 );
		} else {
			$this->socket = @fsockopen( $this->info['address'], $this->info['port'], $erno, $errstr, 30 );
		}
		@stream_set_blocking( $this->socket, 0 );
		@stream_set_timeout( $this->socket, $this->listen_timeout );
		
		if ( $this->state ( ) == false )
		{
		
			$this->main->log ( 'Could not connect to ' . $this->info['address'] . ': (' . $erno . ') ' . $errstr, __CLASS__ );
			$this->reconnect ( );
			
		}
		else
		{
			
			$this->retry_count = 0;
			$this->main->log ( 'Connection to IRC-server established', __CLASS__ );
			
			//Connection stuff
			if ( !empty( $this->info['pass'] ) )
			{
			
				$this->main->log ( 'Sending stored password', __CLASS__ );
				$this->main->send_data( 'PASS ' . $this->info['pass'], IRCINE_PRIO_CRITICAL );
				
			}
			
			$this->main->log ( 'Authenticating to IRC-server', __CLASS__ );
			$this->nickname ( Mootyconf::get_value('deerkins::nick') );
						
			$this->main->log ( 'Sending ident info', __CLASS__ );
			$this->main->send_data( 'USER ' . $this->info['ident'] . ' ' . $this->info['address'] . ' * :' . $this->info['realname'], IRCINE_PRIO_CRITICAL ); // Idents with server
			
			
			$this->main->log ( 'Connection to ' . $this->info['address'] . ' succeeded!', __CLASS__ );
			
			$this->listen ( );
			
		}
		
	}
	
	function disconnect ( )
	{
		
		if ( $this->state ( ) == true )
		{
			
			$this->main->log ( 'Disconnecting from current server...', __CLASS__ );
			$this->main->send_data ( 'QUIT :' . $this->exit_reason, IRCINE_PRIO_CRITICAL );
			
			fclose ( $this->socket );
			
		}
		
	}
	
	function reconnect ( )
	{
		
		if ( ( $this->info['reconnect'] == true ) && ( $this->retry_count < $this->retry_max ) )
		{
			
			$this->retry_count++;
			$reconnect_msecs = $this->reconnect_timeout * 1000000;
			
			$this->main->log ( 'Reconnecting in ' . $this->reconnect_timeout . ' seconds (#' . $this->retry_count . ').', __CLASS__ );
			
			usleep ( $reconnect_msecs );
			
			if ( !$this->connect ( ) )
			{
				$this->reconnect ( );
			}
			else
			{
				return true;
			}
			
		}
		else
		{
			
			$this->exit_reason = 'Could not reconnect to server after ' . $this->retry_max . ' retries.';
			
			exit();
			
		}
		
		
		
	}
	
	function listen ( )
	{
		
		if ( $this->state ( ) == true )
			$this->main->loop ( );
		else
			return false;
		
	}
	
	function state ( )
	{
		
		if ( !$this->socket )
			return false;
		
		$info = @stream_get_meta_data ( $this->socket );
		if ( is_array ( $info ) && $this->socket !== false && is_resource ( $this->socket ) && !feof ( $this->socket ) && !$info['timed_out'] )
			return true;
		else
			return false;
		
	}
	
	function handshake ( )
	{
	
		$match = array ( );
		$pattern = "/^\:(.*)\ ([0-9]{3})\ (" . Mootyconf::get_value('deerkins::nick') . ")\ \:(.*)$/";
		
		preg_match ( $pattern, $this->main->data, $match );
		if ( empty ( $match ) )
			return;
		
		if( $match[2] == '001' ) // authentication successful
		{
		
			$this->handshake = 1;
			$this->main->log ( 'Successfully authenticated', __CLASS__ );
			
		}
		
		if ( $match[2] == '376' ) // end of motd
		{
			
			$this->handshake = 2;
			$this->main->log ( 'MOTD complete, joining channels.', __CLASS__ );
			$this->part_join_channels ( $this->info['channels'] ); // Joins the channels.
			
		}
	
	}
	
	function nickname ( $nickname )
	{
		
		$this->main->log ( 'Getting nick: ' . $nickname, __CLASS__ );
		$this->main->send_data ( 'NICK ' . $nickname, IRCINE_PRIO_CRITICAL );
		$this->main->nickname = $nickname;
		
	}
	
	function join_channel ( $channel )
	{
		$channel = str_replace ( ' ', '', $channel );
		$this->main->log ( 'Joining channel: ' . $channel, __CLASS__ );
		$this->main->send_data( 'JOIN :' . $channel, IRCINE_PRIO_CRITICAL ); // Joins channel
			
	}
	
	function part_channel ( $channel )
	{
		$channel = str_replace ( ' ', '', $channel );
		$this->main->log ( 'Parting channel: ' . $channel, __CLASS__ );
		$this->main->send_data( 'PART :' . $channel, IRCINE_PRIO_CRITICAL ); // Joins channel
	
	}
	
	
	function part_join_channels ( $channels = array ( ), $rejoin = false, $part = true )
	{
	
		$joined_channels = $this->joined_channels;
		
		if ( ! is_array ( $channels ) )
				$channels = explode ( ',', $channels );
		
		if ( $rejoin )
		{
			
			if ( empty ( $channels ) )
				$channels = $this->joined_channels;
			
			$join_array = $channels;
			$part_array = $channels;
			
		}
		else
		{
			
			$join_array = array_diff ( $channels, $joined_channels );
			$part_array = array_diff ( $joined_channels, $channels );
			
		}
		
		
		if ( !empty ( $part_array ) && $part )
		{
		
			$part = implode ( ',', $part_array );
			$this->part_channel ( $part );
			
		}
		
		if ( !empty ( $join_array ) )
		{
		
			$join = implode ( ',', $join_array );
			$this->join_channel ( $join );
			
		}
		
		$this->joined_channels = $channels + $this->joined_channels;
	
	}
	
	function rejoin_reconnect ( )
	{
		
		if ( $this->disconnect_connect_server ( $this->info['address'], $this->info['port'], $this->info['pass'] ) )
			$this->part_join_channels ( $this->info['channels'], false, false );
		else
			$this->part_join_channels ( $this->info['channels'], false, true );
		
	}
	
	function disconnect_connect_server ( $host, $port, $password )
	{
		
		if ( ( $host != $this->info['address'] ) || ( $port != $this->info['port'] ) || ( $password != $this->info['pass'] )  )
		{
			
			$this->main->log ( 'New server information differs from current. Reconnecting to new server...', __CLASS__ );
			$this->disconnect ( );
			$this->main->clear_message_buffer ( );
			$this->joined_channels = array ( );
			$this->connect ( );
			return true;
			
		}
		else
			return false;
		
	}
	
	/**
	 * can_talk function
	 * returns if can talk in the channel
	 * @return boolean
	 * @author Jonas Skovmand <jonas@satf.se>
	 **/
	
	public function can_talk ( $channel )
	{
		
		return true; // TODO actually code this.
		
	}
	
}
