<?php


class IRCine_Module
{
	
	public $main;
	public $module;
	public $pluginspace;
	public $triggers = array ( );
	public $help_triggers = array ( );
	private $hide_types = array ( 'CONSOLE', 'cmd', '' );
	
	function __construct ( &$main )
	{
		$this->main = $main;
		$this->module = get_class ( $this );
		$this->pluginspace = $this->main->server_config_prefix . '::plugin::' . strtolower ( $this->module );
		
		$this->log ( 'Module loaded.' );
		
		$this->install();
	}
	
	function __destruct ( )
	{
		$this->log ( 'Module unloaded.' );
	}
	
	public function install ( )
	{
		
		$triggers = Mootyconf::get_values ( $this->pluginspace . '::active' );
		
		foreach ( $triggers as $a => $trigger )
		{
			
			$this->register_trigger (
				$trigger,
				Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::type' ),
				Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::description' ),
				Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::message' ),
				Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::args' ),
				Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::exec' )
			);
			
			if ( Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::hide' ) == '' && ( Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::type' ) == 'PRIVMSG' || Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::type' ) == 'action' ) )
			{
				$this->help_triggers[] = array (
					'trigger' => $trigger,
					'description' => Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::description' )
				);
			}
			
			$this->triggers[] = array ( 'trigger' => $trigger, 'type' => Mootyconf::get_value( 'plugin::' . $this->module . '::' . $trigger . '::type' ) );
		}
		
		$this->log ( 'installed' );
		
	}
	
	public function uninstall ( )
	{
		
		$this->log ( 'uninstalling' );
		
		foreach ( $this->triggers as $trigger )
		{
			$this->unregister_trigger ( $trigger['trigger'], $trigger['type'] );
		}
		
		$this->help_triggers = array ( );
		$this->triggers = array ( );
		
		$this->log ( 'uninstalled.' );
		
	}
	
	public function call ( ) { }
	
	public function log ( $msg )
	{
		$this->main->log ( $msg, $this->module );
	}
	
	public function strip_command ( $msg )
	{
		$msg = explode ( ' ', $msg );
		array_shift ( $msg );
		return implode ( ' ', $msg );
		
	}
	
	public function join ( $channel )
	{
		$this->main->connection->join_channel ( $channel );
	}
	
	public function part ( $channel )
	{
		$this->main->connection->part_channel ( $channel );
	}
	
	public function nickname ( $nickname )
	{
		$this->main->connection->nickname ( $nickname );
	}
	
	public function message ( $type, $to, $message, $priority )
	{
		$this->main->message ( $type, $to, $message, $priority );
	}
	
	public function say ( $to = false, $msg, $prio = IRCINE_PRIO_LOW )
	{
		$this->message ( IRCINE_TYPE_PRIV, $to, $msg, $prio );
	}
	
	public function notice ( $to = false, $msg, $prio = IRCINE_PRIO_LOW )
	{
		$this->message ( IRCINE_TYPE_NOTICE, $to, $msg, $prio );
	}
	
	public function action ( $to = false, $msg, $prio = IRCINE_PRIO_LOW )
	{
		$this->message ( IRCINE_TYPE_ACTION, $to, $msg, $prio );
	}
	
	public function raw ( $msg, $priority )
	{
		$this->message ( IRCINE_TYPE_RAW, false, $msg, $priority );
	}
	
	public function rehash_modules ( $modules )
	{
		if ( empty ( $modules ) )
			$modules = array ( $this->module );
		
		$this->main->module->rehash ( $modules );
		
	}
	
	public function register_trigger ( $trigger, $type, $description, $msg = false, $takes_args = 'loose', $exec = false )
	{
		$this->main->module->register_trigger (  get_class( $this ), $trigger, $type, $description, $msg, $takes_args, $exec );
	}
	
	public function unregister_trigger ( $trigger, $type )
	{
		$this->main->module->unregister_trigger ( $trigger, $type );
	}
	
	public function cfg ( )
	{
		return $this->main->config_prefix . '::' . strtolower ( $this->module );
	}
	
	function get_args ( $message )
	{
		$message = explode ( ' ', $message );
		array_shift ( $message );
		return $message;
	}
	
}
