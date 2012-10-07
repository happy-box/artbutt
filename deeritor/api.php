<?php


/* api.php
 * makes images out of deer
 * author: Jonas Skovmand <jonas@satf.se>
 */

include 'deer.class.php';

$col = array (
	'height'	=>	40,
	'width'		=>	25
);

$letters = array (
	' '	=>	'#000000',
	'A'	=>	'#FFFFFF',
	'B'	=>	'#00007F',
	'C'	=>	'#009300',
	'D'	=>	'#FF0000',
	'E'	=>	'#7F0000',
	'F'	=>	'#9C009C',
	'G'	=>	'#FC7E00',
	'H'	=>	'#FFFF00',
	'I'	=>	'#00FC00',
	'J'	=>	'#009393',
	'K'	=>	'#00FFFF',
	'L'	=>	'#0000FC',
	'M'	=>	'#FF00FF',
	'N'	=>	'#7F7F7F',
	'O'	=>	'#D2D2D2',
	'_'	=>	'none'
);

$image_dir = 'img/';

function html2rgb ( $color )
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}

function cache_write ( $contents, $file )
{

	$fp = fopen ( $file ,'w' );
	fwrite ( $fp, $contents );
	fclose ( $fp );
    
}
$output = $_GET['output'];

$deer = new Deer ( );
$deer_info = $deer->get_deer ( $deer->sanitize_deer ( isset ( $_GET['deer'] ) ? $_GET['deer'] : 'deer' ) );
$kins = $deer_info['kinskode'];

if ( $deer_info['status'] != 'found' )
{

	header ( 'HTTP/1.1 404 Deer Not Found' );
	die ( 'Deer not found!' );

}



$modifiers = isset ( $_GET['modifiers'] ) && $_GET['modifiers'] != '' ? str_split ( preg_replace ( "/[^a-z]/i", '', $_GET['modifiers'] ) ) : array ( );
sort ( $modifiers );
$modifiers = implode ( array_unique ( $modifiers ) );

$sizemultiplier = strpos( $modifiers, 'h' ) !== false ? 3 : 1;
$cellspacing = strpos( $modifiers, 'c' ) !== false ? 2 : 0;
$lonely_secluded = strpos( $modifiers, 'a' ) !== false ? 10 : 1;

if ( strlen ( $modifiers ) > 0 )
	$kins = $deer->apply_modifiers ( $kins, $modifiers );

$kins = explode ( PHP_EOL, $kins );
$deer_file = $image_dir . $deer_info['deer'] . '__' . $modifiers . '.' . $output;

$mem = array ( 'height' => $deer->get_height ( $kins ), 'width'	=> $deer->get_width ( $kins ) );

$height = ( ( $mem['height'] * $col['height'] ) + ( ( $mem['height'] * $cellspacing ) - $cellspacing ) ) * $lonely_secluded * $sizemultiplier;
$width = ( ( $mem['width'] * $col['width'] ) + ( ( $mem['width'] * $cellspacing ) - $cellspacing ) ) * $lonely_secluded * $sizemultiplier;

$rectangles = '';
$allocated = array ( );

$y = 0;

switch ( $output )
{

	case 'jpg':
	case 'jpeg':
	case 'png':
		$img = @imagecreate ( $width, $height );
		$bg = imagecolorallocate ( $img, 255, 255, 255 );
	break;
	
	
	default:

}

foreach ( $kins as $deer_line )
{
	$x = 0;
	$columns = str_split ( $deer_line );
	
	foreach ( $columns as $column )
	{
	
		$hex = $letters[strtoupper($column)];
		$rgb = html2rgb ( $hex );
		
		switch ( $output )
		{
			
			case 'jpg':
			case 'jpeg':
			case 'png':
				if ( !isset ( $allocated[$hex] ) )
					$allocated[$hex] = imagecolorallocate ( $img, $rgb[0], $rgb[1], $rgb[2] );
				
				imagefilledrectangle  ( $img, $x, $y, $x + ( $col['width'] * $sizemultiplier) , $y + ( $col['height'] * $sizemultiplier), $allocated[$hex] );
			break;
			
			
			case 'svg':
			default:
				
				$fill = $rgb ? 'rgb(' . $rgb[0] . ',' . $rgb[1] . ',' . $rgb[2] . ')' : 'none';
				$rectangles .= '<rect fill="' . $fill . '" height="' . $col['height'] * $sizemultiplier. '" width="' . $col['width'] * $sizemultiplier . '" x="' . $x . '" y="' . $y . '" />' . PHP_EOL;
			
			
		}
		
		$x += ( $col['width'] + $cellspacing ) * $lonely_secluded * $sizemultiplier;
		
	}
	
	$y += ( $col['height'] + $cellspacing ) * $lonely_secluded * $sizemultiplier;

}

switch ( $output )
{
	
	case 'png':
		header ( 'Content-type: image/png' );
		
		ob_start();
		imagepng ( $img );
		$contents = ob_get_contents();
		ob_end_clean();
		cache_write ( $contents, $deer_file );
		echo $contents;
		imagedestroy ( $img );
	break;
	
	case 'jpg':
	case 'jpeg':
		
		header ( 'Content-type: image/jpeg' );
		
		ob_start();
		
		imagejpeg ( $img );
		$contents = ob_get_contents();
		
		ob_end_clean();
		
		cache_write ( $contents, $deer_file );
		
		echo $contents;
		
		imagedestroy ( $img );
		
	break;

	case 'svg':
	default:
		header ( 'Content-type: image/svg+xml' );
		$contents = '<?xml version="1.0" encoding="UTF-8"?>
<svg height="' . $height . 'px" width="' . $width . 'px" version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
' . $rectangles . '
</svg>';
		
		cache_write ( $contents, $deer_file );
		echo $contents;

}

