<?php

/**
 * Mootyconf class
 *
 * (c) 2007 Lorenz Diener, lorenzd@gmail.com
 *
 * Ported to PHP by Jonas Skovmand <jonas@satf.se>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License or any later
 * version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package default
 * @author Lorenz Diener <lorenzd@gmail.com>, PHP port by Jonas Skovmand <jonas@satf.se>
 **/

class Mootyconf {
	
	public static $lc_config = array ( );
	public static $config = array ( );
	
	public static function read ( $filename, $main = false ) {
		
		$mainconfig = '';
		if ( $main && file_exists ( $main ) )
			$mainconfig = file_get_contents ( $main );
		
		if ( !file_exists ( $filename ) )
			return false;
		
		$secion = '';
		$file = file_get_contents ( $filename ) . PHP_EOL . $mainconfig;
		$lines = explode ( PHP_EOL, $file );
		
		foreach ( $lines as $l )
		{
			
			// "Whitespace sux"
			$l = trim ( $l );
			
			// comment
			if ( preg_match ( '/^#/', $l ) ) continue;
			
			// empty lines
			if ( preg_match ( '/^(\s*)$/', $l ) ) continue;
			
			if ( preg_match ( '/^(\s*)\[(.*)\]$/', $l, $sec ) )
			{
				
				// Section
				
				$section = '';
				$secparts = explode ( '::', $sec[2] );
				$seccount = count($secparts)-1;
				
				for ( $i=0; $i<$seccount; $i++ )
				{
					$part = $secparts[$i];
					
					if ( $section == '' )
						$section .= $part;
					else
						$section .= '::' . $part;
					
					if ( !isset ( $config[$section] ) )
						$config[$section] = array ( );
					
					$config[$section][] = $secparts[$i+1];
					
					
				}
				
				if ( $section == '' )
					$section .= $secparts[$seccount];
				else
					$section .= '::' . $secparts[$seccount];
					
			}
			else
			{
				
				// Value.. or not?
				
				if ( preg_match ( '/^(\s*)(.*?)(\s*)=(\s*)(.*)(\s*)$/', $l, $secparts ) )
				{
					
					// Value indeed
					$hashname = $secparts[2];
					
					if ( !isset ( $config[$section . '::' . $hashname] ) )
						$config[$section . '::' . $hashname] = array ( );
					
					$to_split = str_replace ( ',,', '\n', $secparts[5] );
					
					$nextval = explode ( ',', $to_split );
					$nextvalcount = count ( $nextval );
					
					for ( $i=0; $i<$nextvalcount; $i++ )
					{
						
						$nextval[$i] = str_replace ( array ( '\n', "\n" ), ',', preg_replace ( '/^(\s*)(.*)(\s*)$/', '$2', $nextval[$i] ) );
						
					}
					
					$config[$section . '::' . $hashname][] = $nextval;
					
				}
				else
				{
					
					die ( 'error reading mootyconf.' );
					
				}
				
			}
			
		}
		
		self::$config = $config;
		foreach ( array_keys ( $config ) as $key )
		{
			self::$lc_config[strtolower($key)] = $key;
		}
		
		return true;
		
	}
	
	public static function get_value ( $item )
	{
		
		$item = strtolower ( $item );
		
		if ( !isset ( self::$lc_config[$item] ) )
			return '';
		
		return implode ( ', ', self::$config[self::$lc_config[$item]][0] );
	}
	
	public static function get_values ( $item )
	{
		
		$item = strtolower ( $item );
		
		if ( !isset ( self::$lc_config[$item] ) )
			return array();
		
		return self::$config[self::$lc_config[$item]][0];
		
	}
	
}