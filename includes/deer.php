<?php

/*
	Deer class
	to be used by both IRCine and the deeritor (and others?)
	
	Space = Black
	A = white
	B = blue
	C = dark green
	D = light red
	E = red
	F = magenta
	G = orange
	H = yellow
	I = light green
	J = cyan
	K = light cyan
	L = light blue
	M = light magenta
	N = gray
	O = light gray
	
        A  A 
        A  A 
         AA  
        AAA  
         AA  
  AAAAAAAAA  
 AAAAAAAAAA  
 AAAAAAAAAA  
 A A    A A  
 A A    A A  
 A A    A A
*/

class Deer
{
	
	const FILL = '@';
	const COLOR = "\003";
	
	static $colors = array (
		' '	=>	'01,01',
		'A'	=>	'00,00',
		'B'	=>	'02,02',
		'C'	=>	'03,03',
		'D'	=>	'04,04',
		'E'	=>	'05,05',
		'F'	=>	'06,06',
		'G'	=>	'07,07',
		'H'	=>	'08,08',
		'I'	=>	'09,09',
		'J'	=>	'10,10',
		'K'	=>	'11,11',
		'L'	=>	'12,12',
		'M'	=>	'13,13',
		'N'	=>	'14,14',
		'O'	=>	'15,15',
		'_'	=>	'00,00',
	);
	
	// db settings
	private static $db = array (
		'host'	=>	'host',
		'user'	=>	'user',
		'database'	=>	'database',
		'password'	=>	'password'
	);
	
	
	
	static $mysqli;
	
	public static $deer_modifiers = array (
		'i'		=>	'invert',
		'm'		=>	'mirror',
		'n'		=>	'unitinu',
		'd'		=>	'divide',
		'r'		=>	'reverse',
		'u'		=>	'upsidedown',
		's'		=>	'square',
		'f'		=>	'flip',
		't'		=>	'transpose',
		'x'		=>	'x'
	);
	
	static $x_modified = 0;
	static $x_last_mods = array ( );
	
	static $deer_rel = array (
		' '	=>	'A',
		'A'	=>	' ', // will be turned into black when inverted!
		'B'	=>	'H',
		'C'	=>	'M',
		'D'	=>	'J',
		'E'	=>	'K',
		'F'	=>	'I',
		'G'	=>	'L',
		'H'	=>	'B',
		'I'	=>	'M',
		'J'	=>	'D',
		'K'	=>	'E',
		'L'	=>	'H',
		'M'	=>	'I',
		'N'	=>	'O',
		'O'	=>	'N',
		'_'	=>	' ',
	);
	
	function __construct ( )
	{
	
		self::db_connect();
	
	}
	
	function __destruct ( )
	{
	
		self::db_close();
	
	}
	
	static function db_connect ( )
	{
		if ( !ini_get ( 'mysqli.reconnect') )
			ini_set ( 'mysqli.reconnect', true );
		
		echo 'Connecting to DB...' . PHP_EOL;
		self::$mysqli = mysqli_init();
		self::$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
		self::$mysqli->real_connect( self::$db['host'], self::$db['user'], self::$db['password'], self::$db['database'], 3306, '/tmp/mysql.sock' );

		if ( mysqli_connect_errno ( ) ) {
			printf ( "Connect failed: %s\n", mysqli_connect_error ( ) );
			exit ( );
		}
	
		
	}
	
	
	static function db_close ( )
	{
		self::$mysqli->close ( );
	}
	
	private static function _longest_string_in_array ( $array )
	{
		$mapping = array_combine($array, array_map('strlen', $array));
		return array_keys($mapping, max($mapping));
	}
	
	private static function _first ( $array )
	{
	
		if ( isset ( $array[0] ) )
			return $array[0];
		
		return $array;
	
	}
	
	public static function parse_deer ( $deer )
	{
		
		$lines = explode ( PHP_EOL, $deer );
		$newline = array ( );
		$awesomeline = array ( );
		$last_char = false;
		
		foreach ( $lines as $i => $kinsline )
		{
			
			if ( !isset ( $newline[$i] ) )
				$newline[$i] = array ( );
			
			$stripped = str_split ( $kinsline );
			
			foreach ( $stripped as $j => $char )
			{
			
				if ( $char != '' )
				{
					$char = strtoupper ( $char );
					
					if ( !isset ( $newline[$i][$j] ) )
						$newline[$i][$j] = array ( );
						
					
					$newline[$i][$j] = self::FILL;
					
					if ( $char != $last_char )
					{
						$newline[$i][$j] = self::COLOR . self::$colors[$char] . $newline[$i][$j];
						$last_char = $char;
					}
					
				}
				
			}
			
			$awesomeline[] = implode ( '', $newline[$i] );
			$last_char = false;
		
		}
		
		return implode ( PHP_EOL, $awesomeline );
		
	}
	
	public static function apply_modifiers ( $kinskode, $modifiers = array ( ) )
	{
		
		self::$x_last_mods = array ( );
	
		if ( !is_array ( $modifiers ) )
			$modifiers = str_split ( $modifiers );
		
		if ( empty ( $modifiers ) )
			return $kinskode;
		
		foreach ( $modifiers as $mod => $func )
		{
			
			if ( isset ( self::$deer_modifiers[$func] ) )
			{
			
				$tfunc = 'modify_' . self::$deer_modifiers[$func];
				if ( method_exists ( __CLASS__, $tfunc ) )
				{
					$kinskode = self::$tfunc ( $kinskode );
				}
				
			}
		
		}
		
		return $kinskode;
	
	}
	
	public static function modify_reverse ( $kinskode )
	{
	
		$lines = explode ( PHP_EOL, $kinskode );
		$final = array ( );
		
		foreach ( $lines as $string )
		{
		
			$final[] = strrev ( $string );
		
		}
		
		return implode ( PHP_EOL, $final );
	
	}
	
	public static function modify_invert ( $kinskode )
	{
	
		$kinskode = strtr ( strtoupper ( $kinskode ), self::$deer_rel );
		return $kinskode;
	
	}
	
	public static function modify_upsidedown ( $kinskode )
	{
	
		return implode ( PHP_EOL, array_reverse ( explode ( PHP_EOL, $kinskode ) ) );
	
	}
	
	public static function modify_mirror ( $kinskode, $direction = 0 )
	{
	
		$newline = array ( );
		$lines = explode ( PHP_EOL, $kinskode );
		$longest = strlen ( self::_first ( self::_longest_string_in_array ( $lines ) ) );
		
		$half = floor ( $longest / 2 );
		
		if ( $half >= 1 )
		{
		
			
			foreach ( $lines as $num => $line )
			{
			
				if ( $direction == 0 )
					$newline[] = substr ( self::modify_reverse ( $line ), 0, $half ) . substr ( $line, $half );
				elseif ( $direction == 1 )
					$newline[] = substr ( $line, 0, $half ) . substr ( self::modify_reverse ( $line ), $half );
				else
					$newline[] =  substr ( self::modify_reverse ( $line ), $half ) . substr ( $line, 0, $half );
			}
			
			$kinskode = implode ( PHP_EOL, $newline );
		
		}
		
		return $kinskode;
	
	}
	
	public static function modify_unitinu ( $kinskode )
	{
	
		return self::modify_mirror ( $kinskode, 1 );
	
	}
	
	public static function modify_divide ( $kinskode )
	{
	
		return self::modify_mirror ( $kinskode, 2 );
	
	}
	
	public static function modify_square ( $kinskode )
	{
	
		$lol = array ( );
		$lines = explode ( PHP_EOL, $kinskode );
		$longest = strlen ( self::_first ( self::_longest_string_in_array ( $lines ) ) );
		
		$half = floor ( $longest / 2 );
		if ( $half >= 1 )
		{
		
			foreach ( $lines as $num => $line )
			{
				
				if ( $num < floor ( count( $lines ) / 2 ) )
				{
					$lol[] = substr ( $line, $half, strlen ( $line ) ) . substr ( $line, 0, $half );
				}
				else
				{
					array_unshift ( $lol, substr ( $line, $half, strlen ( $line ) ) . substr ( $line, 0, $half ) );
				}
				
			}
			
			$kinskode = implode ( PHP_EOL, $lol );
		
		}
		
		return $kinskode;
	
	}
	
	public static function modify_flip ( $kinskode )
	{
	
		$deer = array ( );
		$lines = explode ( PHP_EOL, $kinskode );
		
		foreach ( $lines as $num => $line )
		{
		
			$sline = str_split ( $line );
			
			foreach ( $sline as $num => $val )
			{
			
				if ( !isset ( $deer[$num] ) )
					$deer[$num] = '';
				
				$deer[$num] .= str_repeat ( $val, 1 );
			
			}
		
		}
		
		if ( !empty ( $deer ) )
			return implode ( PHP_EOL, $deer );
		
		return $kinskode;
	
	}
	
	public static function modify_transpose ( $kinskode )
	{
	
		$deer = array ( );
		$lines = explode ( PHP_EOL, $kinskode );
		$i = 0;
		
		foreach ( $lines as $line )
		{
			$linelen = strlen($line);
			if ( $i == $linelen )
				$i = 0;
			
			if ( $i == 0 )
				$deer[] = $line;
			else
				$deer[] = substr ( $line, -$i ) . substr ( $line, 0, $linelen-$i );
			
			$i++;	
		}
		
		if ( !empty ( $deer ) )
			return implode ( PHP_EOL, $deer );
		
		return $kinskode;
	
	}
	
	public static function modify_x ( $kinskode, $iteration = 0 )
	{
	
		if ( $iteration < 3 )
		{
			
			$iteration++;
			$mods = self::$deer_modifiers;
			unset ( $mods['x'] );
			$mods = array_keys ( $mods );
			$deer_modifiers = self::$deer_modifiers;
			$rand = rand(4,10);
			
			for ( $i=0; $i<$rand; $i++ )
			{
				shuffle ( $mods );
				$modifier = 'modify_' . $deer_modifiers[$mods[0]];
				
				if ( method_exists ( __CLASS__, $modifier ) )
				{
					$kinskode = self::$modifier ( $kinskode );
					self::$x_last_mods[] = $deer_modifiers[$mods[0]];
					self::$x_modified++;
				}
				
			
			}
		
		}
		
		if ( rand ( 0, 1 ) == 1 )
			return self::modify_x ( $kinskode, $iteration );
		
		return $kinskode;
		
	}

	public static function sanitize_deer ( $deer )
	{
		
		return preg_replace ( "/[^a-z0-9\040\-\_]/i", '', $deer ); // safe for filenames
		
	}
		
	public static function deername ( $name )
	{

		return $name . preg_replace('/([ ])/e', 'chr(rand(97,122))', '     '); // adds a 5 random character suffix
		
	}

	public static function get_deer ( $deer )
	{
		
		self::db_connect();
		
		if ( $deer == 'random' )
		{
			
			$deer_query = self::$mysqli->query ( "SELECT * FROM `deer` ORDER BY RAND() LIMIT 1" ) or die ( "Could not fetch deer, developer screwed up." . self::$mysqli->error );
			
		}
		elseif ( $deer == 'latest' )
		{
			
			$deer_query = self::$mysqli->query ( "SELECT * FROM `deer` ORDER BY `date` DESC LIMIT 1" ) or die ( "Could not fetch deer, developer screwed up." );
			
		}
		else
		{
			
			$deer_query = self::$mysqli->query ( sprintf ( "SELECT * FROM `deer` WHERE `deer`='%s' LIMIT 1", self::$mysqli->real_escape_string ( $deer ) ) ) or die ( "Could not fetch deer, developer screwed up." );
			
		}
		
		if ( $deer_query->num_rows > 0 )
		{
			
			$deer_stuff = $deer_query->fetch_array ( MYSQLI_ASSOC );
			
			return array (
				'status'	=> 'found',
				'deer'		=> $deer_stuff['deer'],
				'creator'	=> $deer_stuff['creator'],
				'date'		=> $deer_stuff['date'],
				'kinskode'	=> $deer_stuff['kinskode'],
				'irccode'	=> self::parse_deer ( $deer_stuff['kinskode'] )
			);		
			
		}
		else
		{
			
			return array ();
			
		}
	
	}
	
	public static function search_deer ( $deer )
	{
	
		self::db_connect();
		$deer_query = self::$mysqli->query ( "SELECT deer FROM `deer` WHERE `deer` LIKE '" . self::$mysqli->real_escape_string ( $deer ) . "%' LIMIT 10" ) or die ( "Something went wrong!" );

		if ( $deer_query->num_rows > 0 )
		{
			$listdeer = array ( );
			while ( $try_deer = $deer_query->fetch_array ( MYSQLI_ASSOC ) )
			{
				$listdeer[] = $try_deer['deer'];
			}

			return implode ( PHP_EOL, $listdeer );
		}
	
	}
	
	public static function count_deer ( )
	{
		
		self::db_connect();
		$deer_query = self::$mysqli->query ( "SELECT COUNT(*) as count FROM `deer`" ) or die ( "Something went wrong!" );

		if ( $deer_query->num_rows > 0 )
		{
			$num_deer = $deer_query->fetch_array ( MYSQLI_ASSOC );

			return $num_deer['count'];
		}
		return 0;
	}
	
	public static function write_deer ( $deername, $deercreator, $kins_deer, $irc_deer, $retry = false )
	{

		self::db_connect();
		$deer = ( $retry ) ? deername ( $deername ) : $deername;
		$deercreator = ( empty ( $deercreator ) ) ? 'n/a' : $deercreator;
		
		$finddeer = self::$mysqli->query ( sprintf ( "SELECT id FROM `deer` WHERE `deer`='%s'", self::$mysqli->real_escape_string ( $deer ) ) ) or die ( self::$mysqli->error );
		
		if ( $finddeer->num_rows == 0 )
		{
			
			$ins = self::$mysqli->query ( sprintf ( "INSERT INTO `deer` (`deer`, `creator`, `date`, `kinskode`, `irccode`) VALUES ('%s', '%s', NOW(), '%s', '%s')", self::$mysqli->real_escape_string ( $deer ), self::$mysqli->real_escape_string ( $deercreator ), self::$mysqli->real_escape_string ( $kins_deer ), self::$mysqli->real_escape_string ( $irc_deer ) ) ) or die ( self::$mysqli->error );
			
			return $deer;
			
		}
		else
			return write_deer ( $deername, $deercreator, $kins_deer, $irc_deer, true );
		
	}
	
	public static function paged_deer ( $start = 0, $stop = 10, $extended = false )
	{
		
		self::db_connect();
	
		$start = ( is_numeric ( $start ) ) ? $start : 0;
	
		if ( $extended == false )
			$query = "SELECT deer,creator,date FROM `deer` ORDER BY `date` DESC LIMIT " . $start . ", " . $stop;
		else
			$query = "SELECT deer,creator,date,kinskode FROM `deer` ORDER BY `date` DESC LIMIT " . $start . ", " . $stop;
		
		$deer_query = self::$mysqli->query ( $query ) or die ( "Unable to do stuff" );
		
		if ( $deer_query->num_rows > 0 )
		{
			$return['status'] = 'list';
			while ( $r = $deer_query->fetch_array ( MYSQLI_ASSOC ) )
			{
			
				$return['deer'][] = $r;
			
			}
			
			return $return;
			
		}
		else
			return array ( 'status' => 'error', 'error' => 'no deer' );
	
	}

	public static function fix_deer ( $max_rows )
	{
		$deer = array ( );
		$line = 0;
		$complete_deer = '';
		
		foreach ( $_POST['data'] as $column )
		{
			
			$find = array ( 'empty', 'w', ',' );
			$replace = array ( '_', ' ', '' );
			
			if ( $line < $max_rows )
			{
				
				$column = str_replace ( $find, $replace, $column );
				$deer[$line] = preg_replace ( "/[^a-o\_\-\ w]/i", '', $column );
				
			}
			
			$line++;
			
		}
		
		return implode ( PHP_EOL, $deer );
		
	}
	
	public static function get_height ( $deer )
	{
		return count ( $deer );
	}
	
	public static function get_width ( $deer )
	{
		$widest = 0;
		
		foreach ( $deer as $line )
		{
		
			if ( strlen ( $line ) > $widest )
				$widest = strlen ( $line );
		
		}
		
		
		return $widest;
	
	}

}
