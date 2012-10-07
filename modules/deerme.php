<?php

class Deerme extends IRCine_Module {
	
	var $deer_used = array ( );
	var $privileged_match = array ( );
	var $deer_timeout;
	var $timeout_punish;
	
	var $banlist = array (
		'nick'		=>	array ( ),
		'host'		=>	array ( ),
		'channel'	=>	array ( )
	);
	var $deeritor;
	
	var $last = array (
		'deer'		=> false,
		'user'		=>	false,
		'channel'	=>	false,
		'modifiers'	=>	array ( ),
	);
	
	function install ( )
	{
		parent::install();
		$this->deer_timeout = Mootyconf::get_value( $this->pluginspace . '::timeout');
		$this->timeout_punish = Mootyconf::get_value( $this->pluginspace . '::timeout_punish');
		$this->deeritor = Mootyconf::get_value('plugin::deerme::deeritor');
		
		$this->banlist = array (
			'nick'		=>	Mootyconf::get_values('plugin::deerme::ignore_nick'),
			'host'		=>	Mootyconf::get_values('plugin::deerme::ignore_host'),
			'channel'	=>	Mootyconf::get_values('plugin::deerme::ignore_channel')
		);
		$this->privileged_match = Mootyconf::get_values('plugin::deerme::privileged_match');
		
		$privileged = Mootyconf::get_values ( $this->pluginspace . '::privileged' );
		foreach ( $privileged as $a => $user )
		{
			$this->deer_privileged[] = array (
				'nick'		=>	$user,
				'host'		=>	Mootyconf::get_value( 'plugin::' . $this->module . '::' . $user . '::host' ),
				'timeout'	=>	Mootyconf::get_value( 'plugin::' . $this->module . '::' . $user . '::timeout')
			);
		}
	}
	
	function uninstall ( )
	{
		parent::uninstall();
		$this->banlist = array ( );
		$this->privileged_match = array ( );
		$this->deer_privileged = array ( );
		$this->deer_timeout = 0;
		$this->timeout_punish = 0;
		$this->deeritor = '';
	}
	
	/**
	 * Throttle deer, prevent spam, prevent deer automation
	 * Status: NOT IMPLEMENTED
	 */
	
	function throttle_deer ( $processed_data )
	{
		
		return true;
		
	}
	
	function deer ( $processed_data )
	{
		
		if ( ( $type = $this->is_banned ( $processed_data ) ) !== false )
		{
			$this->log ( Bashful::c('bold', 'red') . 'Banned' . Bashful::reset() . ': ' . Bashful::bold() . $processed_data['nick'] . Bashful::unbold() . '@' . $processed_data['host'] . ' on ' . $processed_data['replyto'] . ' (' . $type . ': ' . $processed_data[$type] . ')' );
			return;
		}
		
		$replyto = $processed_data['recepient'];
		$privileged = $this->is_privileged ( $processed_data );
		
		if ( strpos ( $replyto, '#' ) !== 0 && $privileged == false )
		{
			$this->log ( 'Ignoring deerme in priv' );
			return;
		}
		
		$timer_to = 'cake';//$timer_to = $replyto; // TODO: make this channel specific!
		
		if ( !isset ( $this->deer_used[$timer_to] ) )
			$this->deer_used[$timer_to] = false;
		
		$requested_deer = $this->strip_command ( $processed_data['message'] );
		
		if ( $requested_deer == '' )
			$requested_deer = 'deer';
		
		$mods_deer = $this->get_mods ( $requested_deer );
		$mods_deer == !empty($mods_deer) ? $mods_deer : array ( );
		
		$requested_deer = $this->strip_mods ( $requested_deer );
		
		$timeout = $this->deer_timeout;
		
		
		// check for samedeer or samenick. Add timeout as punishment.
		if ( ( $replyto == $this->last['channel'] && $processed_data['nick'] == $this->last['nick'] ) || ( $requested_deer != 'random' && $replyto == $this->last['channel'] && $requested_deer == $this->last['deer'] && array_diff ( $mods_deer, $this->last['modifiers'] ) == array ( ) ) )
		{
			$timeout = $timeout*$this->timeout_punish;
		}
		
		// if person is in NICELIST, completely ignore the punishment! :D:D:D
		$timeout = $privileged != false && is_numeric ( $privileged['timeout'] ) ? $privileged['timeout'] : $timeout; // if privileged, undo punish!
		
		if ( $requested_deer == 'help' )
		{
			$deertime = ( ! isset ( $this->deer_used[$timer_to] ) || ( $this->deer_used[$timer_to] + ( $timeout ) ) < microtime ( true ) ) ? 'Ready to deer!' : floor ( $this->deer_used[$timer_to] - ( microtime ( true ) ) + $timeout ) . ' seconds until deer.';
			
			$this->notice ( $processed_data['nick'], chr ( 02 ) . 'How to deer:' . chr ( 02 ) . ' Type deerme <mods>|<deer> to deer or deerme help modifiers for available mods. (' . Deer::count_deer() . ' deer total) ' . chr ( 02) .  'Status: ' . chr ( 02 ) . $deertime . ' ' . chr ( 02 ) . 'Create your own: ' . chr ( 02 ). $this->deeritor, IRCINE_PRIO_MEDIUM );
			return;
		}
		
		
		if ( $requested_deer == 'help modifiers' )
		{
			$modifiers = Deer::$deer_modifiers;
			$mods = array ( );
			
			foreach ( $modifiers as $mod => $name )
			{
				$mods[] = $mod . '(=' . $name . ')';
			}
			
			$mods = implode ( ', ', $mods );
			$this->notice ( $processed_data['nick'], chr ( 02 ) . 'Available modifiers: ' . chr ( 02 ) . $mods . '.', IRCINE_PRIO_MEDIUM );
			return;
		}
		
		
		if ( ( $this->deer_used[$timer_to] + ( $timeout ) ) > microtime ( true ) )
		{
			$this->log ( 'Deer called, but deer not so fast :(' );
			if ( $timeout < $this->deer_timeout )
			{
				$this->notice ( $processed_data['nick'], 'You are privileged! You have LOWER timeout than others (-' . ($this->deer_timeout-$timeout) . ' seconds), which is like ' . floor ( $this->deer_used[$timer_to] - ( microtime ( true ) ) + $timeout ) . ' seconds from now, bro.', IRCINE_PRIO_MEDIUM );
			}
			elseif ( $timeout > $this->deer_timeout )
			{
				
				$this->notice ( $processed_data['nick'], 'You have somehow been punished! You have HIGHER timeout than others (+' . ($timeout-$this->deer_timeout) . ' seconds), which is like <CENSORED> seconds from now, bro.', IRCINE_PRIO_MEDIUM );
				
			}
			elseif ( $timeout == $this->deer_timeout )
			{
				$this->notice ( $processed_data['nick'], 'Deer called, but deer not so fast :( It only walks the earth every ' . $timeout . ' seconds, which is like ' . floor ( $this->deer_used[$timer_to] - ( microtime ( true ) ) + $timeout ) . ' seconds from now, bro.', IRCINE_PRIO_MEDIUM );
			}
			return;
		}
		
		// fetch deer from db
		$deer = Deer::get_deer ( $requested_deer );
		
		if ( empty ( $deer ) ) // 404?
		{
			$this->log ( 'No such deer.' );
			$this->say ( $replyto, '404: Deer Not Found. Go to ' . $this->deeritor . ' and create it.', IRCINE_PRIO_MEDIUM );
			return;
		}
		
		// apply modifier, turn into IRC
		$data = explode ( PHP_EOL, Deer::parse_deer ( Deer::apply_modifiers ( $deer['kinskode'], $mods_deer ) ) );
		
		foreach ( $data as $line )
		{
			$this->say ( $replyto, $line, IRCINE_PRIO_MEDIUM ); // TODO: THROTTLE THIS BITCH?
		}
		
		$used_mods = in_array ( 'x', $mods_deer ) ? Deer::$x_last_mods : $mods_deer;
		$used_mods_friendly = empty ( $used_mods ) ? '' : ' (' . implode ( ', ', $used_mods ) . ')';
		
		if ( strtolower ( $requested_deer ) == 'random' )
			$this->say ( $replyto, $deer['deer'] . ' by ' . $deer['creator'] . $used_mods_friendly , IRCINE_PRIO_MEDIUM );
		
		$this->log ( 'Deer "' . $deer['deer'] . '" is walking in ' . $replyto . '!' );
		
		$this->deer_used[$timer_to] = microtime ( true );
		$this->last = array (
			'deer'		=>	$deer['deer'],
			'nick'		=>	$processed_data['nick'],
			'channel'	=>	$replyto,
			'creator'	=>	$deer['creator'],
			'modifiers'	=>	$used_mods
		);
	
	}
	
	function prev_deer ( $processed_data )
	{
		
		$recp = $processed_data['recepient'];
		
		if ( $this->last['deer'] != false )
		{
			$app = !empty ( $this->last['modifiers'] ) ? ' (with the following mods: ' . implode(', ', $this->last['modifiers'] ) . ')': '';
			$this->say ( $recp, 'The previous deer to walk the earth was ' . $this->last['deer'] . ' by ' . $this->last['creator'] . $app, IRCINE_PRIO_MEDIUM );
		}
		else
		{
			$this->say ( $recp, 'No deer has been sighted yet!', IRCINE_PRIO_MEDIUM );
		}
	
	}
	
	function is_privileged ( $processed_data )
	{
		if ( isset ( $processed_data['__BYPASS__'] ) )
		{
			$this->log ( 'CONSOLE BYPASS' );
			return array ( 'nick' => '__BYPASS__', 'host' => 'none', 'timeout' => 0 );
		}
		
		if ( in_array ( 'host', $this->privileged_match ) && ( $privileged = $this->get_deer_privileged ( 'host', $processed_data['host'] ) ) != false )
		{
			$this->log ( 'privileged auth match on host: ' . $processed_data['host'] );
			return $privileged;
		}
			
		if ( in_array ( 'nick', $this->privileged_match ) && ( $privileged = $this->get_deer_privileged ( 'nick', $processed_data['nick'] ) ) != false )
		{
			
			$this->log ( 'privileged auth match on nick: ' . $processed_data['nick'] );
			return $privileged;
			
		}
		
		return false;
		
	}
	
	function is_banned ( $processed_data )
	{
		
		if ( isset ( $processed_data['__BYPASS__'] ) )
			return false;
		
		foreach ( $this->banlist['host'] as $banned )
		{
			if ( strtolower($banned) == strtolower($processed_data['host']) )
				return 'host';
		}
		
		foreach ( $this->banlist['nick'] as $banned )
		{
			if ( strtolower($banned) == strtolower($processed_data['nick']) )
				return 'nick';
		}
		
		if ( strpos ( $processed_data['replyto'], '#' ) == 0 )
		{
			foreach ( $this->banlist['channel'] as $banned )
			{
				if ( strtolower($banned) == strtolower($processed_data['replyto']) )
					return 'replyto';
			}
		}
		
		return false;
		
	}
	
	function get_deer_privileged ( $key, $value )
	{
		foreach ( $this->deer_privileged as $privileged )
		{
			if ( $privileged[$key]!=''&&strtolower($privileged[$key])==strtolower($value) )
				return $privileged;
		}
		return false;
	}
	
	function strip_mods ( $requested_deer )
	{

		$mod_pos = strpos ( $requested_deer, '|' );

		if ( $mod_pos !== false )
		{
			$requested_deer = substr ( $requested_deer, $mod_pos == 0 ? 1 : $mod_pos+1 );
			return $requested_deer == false ? '' : $requested_deer ;
		}

		return $requested_deer;
	}
	
	function get_mods ( $requested_deer )
	{
		$mods = substr ( $requested_deer, 0, strpos ( $requested_deer, '|' ) );
		
		if ( $mods == '' )
			return array ( );
		
		$mods = str_split ( $mods );
		if ( in_array ( 'x', $mods ) )
			return array ( 'x' );
		
		$mods = array_unique ( $mods );
		sort ( $mods );
		
		return $mods;
	}
}
