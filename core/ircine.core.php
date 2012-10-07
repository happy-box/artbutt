<?php


class IRCine_Core {
	
	public $main;
	public $class;
	
	public function __construct ( &$main )
	{
		$this->main = $main;
		$this->class = get_class ( $this );
		$this->main->log ( 'loaded.', $this->class );
	}
	
	public function __destruct ( )
	{
		$this->main->log ( 'unloaded.', $this->class );
	}
	
}
