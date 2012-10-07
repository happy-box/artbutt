<?php

class Module extends IRCine_Core {
	
	var $main;
	
	var $modules = array ( );
	var $loaded_modules = array ( );
	var $registered_triggers = array ( );
	var $trigger_users = array ( );
	
	function load ( )
	{
		
		$this->main->log ( 'Loading modules.', __CLASS__ );
		require_once CORE_PATH . 'ircine.module.template.php';
		
		$modules = Mootyconf::get_values ( $this->main->server_config_prefix . '::plugins');
		
		foreach ( $modules as $k => $module )
		{
			
			if ( $this->import ( $module ) )
			{
				
				if ( $this->is_loaded($module)  )
					unset (  $this->modules[$module] );
				
				$ucmodule = ucfirst ( $module );
				
				$this->modules[$module] =& new $ucmodule ( $this->main );
				//$this->modules[$module]->install ( );
				
				$this->main->log ( 'installed module: ' . $ucmodule, __CLASS__ );
				
				if ( ! in_array ( $module, $this->loaded_modules )  )
					$this->loaded_modules[] = $module;
				
				
			}
			
		}
		
		return true;
		
	}
	
	function path ( $module )
	{
	
		return MODULE_PATH . $module . '.php';
	
	}
	
	function import ( $module )
	{
		
		$module_file = $this->path ( $module );
		
		if ( ! class_exists ( ucfirst ( $module ) ) )
		{
			if ( require_once ( $module_file ) )
				return true;
		}
		
		return false;
		
	}
	
	function trigger ( $processed_data )
	{
		
		if ( $processed_data['IRC_TYPE'] == 'none' )
			return false;
		
		$trigger = explode ( ' ', trim ( $processed_data['message'] ) );
		
		if ( ( $k = $this->find_trigger ( $trigger[0], $processed_data['type'] ) ) !== false )
		{
			
			// throttle spam and such.
			if ( $this->throttle ( $processed_data ) ) return;
			
			if ( $processed_data['type'] == 'msg' )
				$this->main->log ( Bashful::bold() . $trigger[0] . Bashful::unbold() .  ' on ' . $processed_data['type'] . ' triggered by ' . Bashful::blue() . $processed_data['nick'] . Bashful::unblue(), __CLASS__ );
			
			$k = $this->registered_triggers[$k];
			$k['module'] = strtolower ( $k['module'] );
			
			if ( $k['exec'] !== false && method_exists ( $this->modules[$k['module']], $k['exec'] ) )
			{
				$this->modules[$k['module']]->{$k['exec']} ( $processed_data );
				return true;
			}
			elseif ( method_exists ( $this->modules[$k['module']], 'call' ) )
			{
				$this->modules[$k['module']]->call ( $processed_data );
				return true;
			}
			
			$this->main->log ( 'Error: Trigger specified, but no route found for \'' . $k['module'] . '->' . $trigger . '\'', __CLASS__ );
		}
		
		return false;
		
	}
	
	function find_trigger ( $trigger, $type )
	{
		
		$trigger = strtolower ( $trigger );
		
		foreach ( $this->registered_triggers as $k => $v )
		{
			
			if ( ( $v['trigger'] == $trigger || $v['trigger'] == '*' ) && ( $v['type'] == $type || $v['type'] == '*' ) )
			{
				
				return $k;
				
			}
			
		}
		
		return false;
		
	}
	
	public function register_trigger ( $module, $trigger, $type, $description, $msg, $takes_args, $exec )
	{
		
		// if trigger already exists, remove it
		if ( ( $trigger_key = $this->find_trigger ( $trigger, $type ) ) !== false )
		{
			
			if ( $this->registered_triggers[$trigger_key]['module'] == $module )
			{
				unset ( $this->registered_triggers[$trigger_key] );
			}
			
		}
		
		// Trigger must match trigger and type! Only one type may be supplied/trigger.
		$this->registered_triggers[] = array (
			'module'		=>	$module,
			'trigger'		=>	strtolower ( $trigger ),
			'type'			=>	$type,
			'description'	=>	$msg,
			'msg'			=>	$msg,
			'takes_args'	=>	$takes_args,
			'exec'			=>	$exec
		);
		
		$this->main->log ( 'trigger registered: ' . $module . ' -> ' . $trigger . ' on type ' . strtoupper($type), __CLASS__ );
		
	}
	
	public function unregister_trigger ( $trigger, $type )
	{
		
		$trigger_key = $this->find_trigger ( $trigger, $type );
		
		if ( $trigger_key == false )
		{
			$this->main->log ( 'failed to unregister ' . $trigger . ' on type ' . $type . '; trigger does not exist!', __CLASS__ );
			return;
		}
		
		unset ( $this->registered_triggers[$trigger_key] );
		$this->main->log ( 'unregistered trigger ' . $trigger . ' on type ' . $type . '.', __CLASS__ );
		
	}
	
	public function rehash ( $modules = array ( ) )
	{
		
		if ( empty ( $modules ) )
			$modules = $this->loaded_modules;
		
		load_mooty ( $this->main->config_file );
		
		foreach ( $modules as $module )
		{
			
			if ( $this->is_loaded ( $module ) )
			{
				
				$this->modules[$module]->uninstall();
				$this->modules[$module]->install();
				
			}
		}
		
	}
	
	function is_loaded ( $module )
	{
		
		return isset ( $this->modules[strtolower($module)] );
		
	}
	
	function is_service ( $pd )
	{
		$nick = strtolower ( $pd['nick'] );
		if ( $nick == 'nickserv' )
			return true;
		
		if ( $nick == 'chanserv' )
			return true;
		
	}
	
	function throttle ( $processed_data )
	{
		if ( $processed_data['IRC_TYPE'] != 'msg' )
			return false;
		
		
		
		// check if admin too, mebe.
		$admin = false;
		if ( $this->is_loaded('admin') )
			$admin = $this->modules['admin']->admin_match('host', $processed_data['host']);
		
		if ( $admin || $this->is_service ( $processed_data ) )
			return false;
		
		
		$now = microtime(true);
		if ( isset ( $this->trigger_users[$processed_data['host']] ) && $this->trigger_users[$processed_data['host']] + ($this->main->command_timeout/1000) > $now )
		{
			$this->trigger_users[$processed_data['host']] += 2;
			$this->main->log ( 'THROTTLE: ignoring message ' . $processed_data['nick'] . '.', __CLASS__ );
			$this->trigger_users[$processed_data['host']] = $now;
			return true;
			
		}
		
		return false;
	}
	
	function reload ( $modules = array ( ), $rehash = true )
	{
		
		if ( $this->load ( $modules, $rehash ) )
		{
			
			return true;
			
		}
		else
			return false;
		
	}
}
