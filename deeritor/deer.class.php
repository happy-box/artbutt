<?php

class Deer
{

	private $db = array (
		'host'	=>	'localhost',
		'user'	=>	'db_user',
		'database'	=>	'db_database',
		'password'	=>	'db_password'
	);
	
	var $mysqli;
	
	var $deer_modifiers = array (
		'i'		=>	'invert',
		'm'		=>	'mirror',
		'n'		=>	'unitinu',
		'd'		=>	'divide',
		'r'		=>	'reverse',
		'u'		=>	'upsidedown',
		's'		=>	'square',
		'f'		=>	'flip',
		'x'		=>	'x'
	);
	
	var $x_modified = 0;
	
	var $deer_rel = array (
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
	
		$this->mysqli = new mysqli( $this->db['host'], $this->db['user'], $this->db['password'], $this->db['database'] );
	
		if ( mysqli_connect_errno ( ) ) {
			printf ( "Connect failed: %s\n", mysqli_connect_error ( ) );
			exit ( );
		}
	
	}
	
	
	function __destruct ( )
	{
	
		$this->mysqli->close ( );
	
	}
	
	function _longest_string_in_array ( $array )
	{
		$mapping = array_combine($array, array_map('strlen', $array));
		return array_keys($mapping, max($mapping));
	}
	
	function _first ( $array )
	{
	
		if ( isset ( $array[0] ) )
			return $array[0];
		
		return $array;
	
	}
	
	
	function parse_deer ( $deer )
	{
		
		$color = chr ( 3 );
		$fill = '@';
		
		$letters = array (
			' ',
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'_'
		);
		
		
		$colors = array (
			$color . '01,01' . $fill,
			$color . '00,00' . $fill,
			$color . '02,02' . $fill,
			$color . '03,03' . $fill,
			$color . '04,04' . $fill,
			$color . '05,05' . $fill,
			$color . '06,06' . $fill,
			$color . '07,07' . $fill,
			$color . '08,08' . $fill,
			$color . '09,09' . $fill,
			$color . '10,10' . $fill,
			$color . '11,11' . $fill,
			$color . '12,12' . $fill,
			$color . '13,13' . $fill,
			$color . '14,14' . $fill,
			$color . '15,15' . $fill,
			$color . '00,00' . $fill,
		);
		
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
					$color = $colors[array_search ( $char, $letters )];
					
					if ( !isset ( $newline[$i][$j] ) )
						$newline[$i][$j] = array ( );
					
					if ( $char == $last_char )
					{
						$newline[$i][$j] = $fill;
					}
					else
					{
						$newline[$i][$j] = $colors[array_search ( $char, $letters )];
						$last_char = $char;
					}
					
				}
				
			}
			
			$last_char = false;
			
			$awesomeline[] = implode ( '', $newline[$i] );
		
		}
		
		return implode ( PHP_EOL, $awesomeline );
		
	}
	
	function apply_modifiers ( $kinskode, $modifiers = array ( ) )
	{
	
		if ( !is_array ( $modifiers ) )
			$modifiers = str_split ( $modifiers );
		
		$modifiers = array_unique ( $modifiers );
		
		if ( in_array ( 'x', $modifiers ) )
			$modifiers = array ( 'x' );
		
		foreach ( $modifiers as $mod => $func )
		{
			
			if ( isset ( $this->deer_modifiers[$func] ) )
			{
			
				$tfunc = 'modify_' . $this->deer_modifiers[$func];
				if ( method_exists ( $this, $tfunc ) )
				{
					$kinskode = $this->{$tfunc} ( $kinskode );
				}
				
			}
		
		}
		
		return $kinskode;
	
	}
	
	function modify_reverse ( $kinskode )
	{
	
		$lines = explode ( PHP_EOL, $kinskode );
		$final = array ( );
		
		foreach ( $lines as $string )
		{
		
			$final[] = strrev ( $string );
		
		}
		
		return implode ( PHP_EOL, $final );
	
	}
	
	function modify_invert ( $kinskode )
	{
	
		$kinskode = strtr ( strtoupper ( $kinskode ), $this->deer_rel );
		return $kinskode;
	
	}
	
	function modify_upsidedown ( $kinskode )
	{
	
		return implode ( PHP_EOL, array_reverse ( explode ( PHP_EOL, $kinskode ) ) );
	
	}
	
	function modify_mirror ( $kinskode, $direction = 0 )
	{
	
		$newline = array ( );
		$lines = explode ( PHP_EOL, $kinskode );
		$longest = strlen ( $this->_first ( $this->_longest_string_in_array ( $lines ) ) );
		
		$half = floor ( $longest / 2 );
		
		if ( $half >= 1 )
		{
		
			
			foreach ( $lines as $num => $line )
			{
			
				if ( $direction == 0 )
					$newline[] = substr ( $this->modify_reverse ( $line ), 0, $half ) . substr ( $line, $half );
				elseif ( $direction == 1 )
					$newline[] = substr ( $line, 0, $half ) . substr ( $this->modify_reverse ( $line ), $half );
				else
					$newline[] =  substr ( $this->modify_reverse ( $line ), $half ) . substr ( $line, 0, $half );
			}
			
			$kinskode = implode ( PHP_EOL, $newline );
		
		}
		
		return $kinskode;
	
	}
	
	function modify_unitinu ( $kinskode )
	{
	
		return $this->modify_mirror ( $kinskode, 1 );
	
	}
	
	function modify_divide ( $kinskode )
	{
	
		return $this->modify_mirror ( $kinskode, 2 );
	
	}
	
	function modify_square ( $kinskode )
	{
	
		$lol = array ( );
		$lines = explode ( PHP_EOL, $kinskode );
		$longest = strlen ( $this->_first ( $this->_longest_string_in_array ( $lines ) ) );
		
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
	
	
	function modify_flip ( $kinskode )
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
	
	
	function modify_x ( $kinskode, $iteration = 0 )
	{
	
		if ( $iteration < 3 )
		{
			
			$iteration++;
			$rand = rand ( 4, 10 );
			$deer_modifiers = $this->deer_modifiers;
			unset ( $deer_modifiers['x'] );
			$deer_modifiers = array_values ( $deer_modifiers );
			shuffle ( $deer_modifiers );
			
			for ( $i=0; $i<$rand; $i++ )
			{
			
				$mod_rand = rand ( 0, count($deer_modifiers )-1 );
				$modifier = 'modify_' . $deer_modifiers[$mod_rand];
				if ( method_exists ( $this, $modifier ) )
				{
					$kinskode = $this->{$modifier} ( $kinskode );
					$this->x_modified++;
				}
				
			
			}
		
		}
		
		if ( rand ( 0, 1 ) == 1 )
			return $this->modify_x ( $kinskode, $iteration );
		
		return $kinskode;
		
	}

	function sanitize_deer ( $deer )
	{
		
		return preg_replace ( "/[^a-z0-9\040\-\_]/i", '', $deer ); // safe for filenames
		
	}
	
	function deername ( $name )
	{
		
		return $name . preg_replace('/([ ])/e', 'chr(rand(97,122))', '     '); // adds a 5 random character suffix
		
	}

	function get_deer ( $deer )
	{
	
		$deer_query = $this->mysqli->query ( sprintf ( "SELECT * FROM `deer` WHERE `deer`='%s' LIMIT 1", $this->mysqli->real_escape_string ( $deer ) ) ) or die ( "Could not fetch deer, host screwed up." );
		
		if ( $deer_query->num_rows > 0 )
		{
			
			$deer_stuff = $deer_query->fetch_array ( MYSQLI_ASSOC );
			
			return array (
				'status'	=> 'found',
				'deer'		=> $deer_stuff['deer'],
				'creator'	=> $deer_stuff['creator'],
				'date'		=> $deer_stuff['date'],
				'kinskode'	=> $deer_stuff['kinskode'],
				'irccode'	=> $this->parse_deer ( $deer_stuff['kinskode'] )
			);		
			
		}
		else
		{
			
			return array (
				'status'	=> 'error',
				'error'		=> 'not found'
			);
			
		}
	
	}
	
	function search_deer ( $deer )
	{
	
		$deer_query = $this->mysqli->query ( "SELECT deer FROM `deer` WHERE `deer` LIKE '" . $this->mysqli->real_escape_string ( $deer ) . "%' LIMIT 10" ) or die ( "Something went wrong. Honestly!" );

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
	
	function write_deer ( $deername, $deercreator, $kins_deer, $irc_deer, $retry = false )
	{

		$deer = ( $retry ) ? deername ( $deername ) : $deername;
		$deercreator = ( empty ( $deercreator ) ) ? 'n/a' : $deercreator;
		
		$finddeer = $this->mysqli->query ( sprintf ( "SELECT id FROM `deer` WHERE `deer`='%s'", $this->mysqli->real_escape_string ( $deer ) ) ) or die ( $this->mysqli->error );
		
		if ( $finddeer->num_rows == 0 )
		{
			
			$ins = $this->mysqli->query ( sprintf ( "INSERT INTO `deer` (`deer`, `creator`, `date`, `kinskode`, `irccode`) VALUES ('%s', '%s', NOW(), '%s', '%s')", $this->mysqli->real_escape_string ( $deer ), $this->mysqli->real_escape_string ( $deercreator ), $this->mysqli->real_escape_string ( $kins_deer ), $this->mysqli->real_escape_string ( $irc_deer ) ) ) or die ( $this->mysqli->error );
			
			return $deer;
			
		}
		else
			return $this->write_deer ( $deername, $deercreator, $kins_deer, $irc_deer, true );
		
	}
	
	function paged_deer ( $start = 0, $stop = 10, $extended = false )
	{
	
		$start = ( is_numeric ( $start ) ) ? $start : 0;
	
		if ( $extended == false )
			$query = "SELECT deer,creator,date FROM `deer` ORDER BY `date` DESC LIMIT " . $start . ", " . $stop;
		else
			$query = "SELECT deer,creator,date,kinskode FROM `deer` ORDER BY `date` DESC LIMIT " . $start . ", " . $stop;
		
		$deer_query = $this->mysqli->query ( $query ) or die ( "Unable to do stuff" );
		
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

	function fix_deer ( $max_rows )
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
	
	function get_height ( $deer )
	{
	
		return count ( $deer );
	
	}
	
	function get_width ( $deer )
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