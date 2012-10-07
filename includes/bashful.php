<?php

/**
 * Bashful
 * Colourful bash output
 * @package default
 * @author Jonas Skovmand <jonas@satf.se>
 **/

class Bashful {
	
	private static $end = 'm';
	private static $esc = "\033";
	
	private static $g = array (
		'reset'		=>	0,
		'bold'		=>	1,
		'faint'		=>	2,
		'italic'	=>	3,
		'underline'	=>	4,
		'blink'		=>	5
	);
	
	private static $c = array (
		'black'		=>	30,
		'red'		=>	31,
		'green'		=>	32,
		'yellow'	=>	33,
		'blue'		=>	34,
		'magenta'	=>	35,
		'cyan'		=>	36,
		'white'		=>	37
	);
	
	private static $b = array (
		'black'		=>	40,
		'red'		=>	41,
		'green'		=>	42,
		'yellow'	=>	43,
		'blue'		=>	44,
		'magenta'	=>	45,
		'cyan'		=>	46,
		'white'		=>	47
	);
	
	private static function attr ( $attr ) { return self::$esc . '[' . $attr . self::$end; }
	
	public static function c ( $attr = false, $text = false, $bg = false )
	{
		$r = '';
		if ( $attr )					$r .= self::$g[$attr];
		if ( $text && !$attr )			$r .= self::$c[$text];
		if ( $text && $attr )			$r .= ';' . self::$c[$text];
		if ( $bg && !$attr && !$text )	$r .= self::$b[$bg];
		if ( $bg && ($attr || $text ) )	$r .= ';' . self::$b[$bg];
		
		return self::attr($r);
	}
	
	// c(olor) aliases
	public static function color ($a,$t,$b)		{ return self::c($a,$t,$b); }
	public static function colour ($a,$t,$b)	{ return self::c($a,$t,$b); }
	
	// HELPERS BELOW
	
	// colours
	public static function black ( )		{ return self::c(false,'black'); }
	public static function red ( )			{ return self::c(false,'red'); }
	public static function green ( )		{ return self::c(false,'green'); }
	public static function yellow ( )		{ return self::c(false,'yellow'); }
	public static function blue ( )			{ return self::c(false,'blue'); }
	public static function magenta ( )		{ return self::c(false,'magenta'); }
	public static function cyan ( )			{ return self::c(false,'cyan'); }
	public static function white ( )		{ return self::c(false,'white'); }
	
	
	public static function _black ( )		{ return self::r(); }
	public static function _red ( )			{ return self::r(); }
	public static function _green ( )		{ return self::r(); }
	public static function _yellow ( )		{ return self::r(); }
	public static function _blue ( )		{ return self::r(); }
	public static function _magenta ( )		{ return self::r(); }
	public static function _cyan ( )		{ return self::r(); }
	public static function _white ( )		{ return self::r(); }
	                                          
	public static function unblack ( )		{ return self::_black(); }
	public static function unred ( )		{ return self::_red(); }
	public static function ungreen ( )		{ return self::_green(); }
	public static function unyellow ( )		{ return self::_yellow(); }
	public static function unblue ( )		{ return self::_blue(); }
	public static function unmagenta ( )	{ return self::_magenta(); }
	public static function uncyan ( )		{ return self::_cyan(); }
	public static function unwhite ( )		{ return self::_white(); } 
	
	
	// bold
	public static function bold ( )			{ return self::c('bold'); }
	public static function b ( )			{ return self::bold(); }
	public static function strong ( )		{ return self::bold(); }
	
	// unbold
	public static function _bold ( )		{ return self::r(); }
	public static function _b ( )			{ return self::_bold(); }
	public static function unbold ( )		{ return self::_bold(); }
	public static function _strong ( )		{ return self::_bold(); }
	
	// underline
	public static function u ( )			{ return self::c('underline'); }
	
	// ununderline
	public static function _u ( )			{ return self::r(); }
	
	// blink
	public static function blink ( )		{ return self::c('blink'); }
	
	// unblink
	public static function _blink ( )		{ return self::r(); }
	
	// reset
	public static function reset ( )		{ return self::c('reset'); }
	public static function r ( )			{ return self::reset(); }
	
}
