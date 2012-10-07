<?php

class Input extends IRCine_Core {
	
	function read ( $length = 255 )
	{
		
		$string =  fread( STDIN, $length );
		return rtrim ( $string );
		
	}
	
}
