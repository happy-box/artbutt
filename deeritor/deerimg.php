<?php

include 'deer.class.php';

function findexts ($filename) 
{ 
	$filename = strtolower($filename) ; 
	$exts = split("[/\\.]", $filename) ; 
	$n = count($exts)-1; 
	$exts = $exts[$n]; 
	return $exts; 
}
function rgb2hex2rgb($c){
   if(!$c) return false;
   $c = trim($c);
   $out = false;
  if(preg_match("/^[0-9ABCDEFabcdef\#]+$/i", $c)){
      $c = str_replace('#','', $c);
      $l = strlen($c) == 3 ? 1 : (strlen($c) == 6 ? 2 : false);

      if($l){
         unset($out);
         $out[0] = $out['r'] = $out['red'] = hexdec(substr($c, 0,1*$l));
         $out[1] = $out['g'] = $out['green'] = hexdec(substr($c, 1*$l,1*$l));
         $out[2] = $out['b'] = $out['blue'] = hexdec(substr($c, 2*$l,1*$l));
      }else $out = false;
             
   }elseif (preg_match("/^[0-9]+(,| |.)+[0-9]+(,| |.)+[0-9]+$/i", $c)){
      $spr = str_replace(array(',',' ','.'), ':', $c);
      $e = explode(":", $spr);
      if(count($e) != 3) return false;
         $out = '#';
         for($i = 0; $i<3; $i++)
            $e[$i] = dechex(($e[$i] <= 0)?0:(($e[$i] >= 255)?255:$e[$i]));
             
         for($i = 0; $i<3; $i++)
            $out .= ((strlen($e[$i]) < 2)?'0':'').$e[$i];
                 
         $out = strtoupper($out);
   }else $out = false;
         
   return $out;
}

$url = $_GET['_img'];
//$url = "http://i.imgur.com/DBPKpY7.jpg";
$file = filter_var ( $url,  FILTER_VALIDATE_URL ); 

if ( !$file )
	exit;

$ext = findexts ( $file );

if ( $ext == 'jpg' || $ext == 'jpeg' )
{
	$img = imagecreatefromjpeg($file);
}
elseif ( $ext == 'gif' )
{
	$img = imagecreatefromgif($file);
}
elseif ( $ext == 'png' )
{
	$img = imagecreatefrompng($file);
}
else
{
	exit;
}

$max_width = 30;
$max_height = 33;

$colsize = 1.6667;
$columns = 30;
$rows = 20;

$low_width = $max_width / $columns;
$low_height = $max_height / $rows;


$width = imagesx($img);
$height = imagesy($img);

$img_ratio = $width / $height;

if ( $img_ratio >= 1 )
{
	// width is more than height
	$use_width = ($width>$max_width)?$max_width:$width;
	$use_height = $use_width / $img_ratio;
	$resize_width = $use_width / $columns;
	$resize_height = $resize_width / $img_ratio;
}
else
{
	// height is more than width
	$use_height = ($height>$max_height)?$max_height:$height;
	$use_width = $use_height * $img_ratio;
	$resize_height = $use_height / $rows;
	$resize_width = $resize_height * $img_ratio;
}


$resize_width = $columns;
$resize_height = round($rows*$colsize);

// print("RESIZE DIMS: ".$resize_width."x".$resize_height."!!!");

# Create small version of the original image:
$newImg = imagecreatetruecolor($width,$height);
imagecopyresized($newImg,$img,0,0,0,0, $resize_width,$resize_height,$width,$height);

// imagejpeg($newImg, "test_smallversion.jpg");

# Create 100% version ... blow it back up to it's initial size:
$newImg2 = imagecreatetruecolor($use_width,$use_height);
imagecopyresized($newImg2,$newImg,0,0,0,0,$use_width,$use_height,$resize_width, $resize_height);


// imagejpeg($newImg2, "test_small_blownupversion.jpg");

$colors = array (
	'w'	=>	'#000000',
	'a'	=>	'#FFFFFF',
	'b'	=>	'#00007F',
	'c'	=>	'#009300',
	'd'	=>	'#FF0000',
	'e'	=>	'#7F0000',
	'f'	=>	'#9C009C',
	'g'	=>	'#FC7E00',
	'h'	=>	'#FFFF00',
	'i'	=>	'#00FC00',
	'j'	=>	'#009393',
	'k'	=>	'#00FFFF',
	'l'	=>	'#0000FC',
	'm'	=>	'#FF00FF',
	'n'	=>	'#7F7F7F',
	'o'	=>	'#D2D2D2'
);


/*

Create image with kinskode palette
get pixel from real image, match closest with kinskode palette

win.

*/

$hexcolors = array_values ( $colors );
$palette_length = count ( $hexcolors );
$fh = 10;
$fw = $palette_length*$fh;
$fw--;

$palette = imagecreate( $fw, $fh );
$icol = array();
for ( $i=0; $i<$palette_length; $i++ )
{
	$c = rgb2hex2rgb($hexcolors[$i]);
	$thisx = (($i*$fh)+$fh)-1;
	$icol[$i] = imagecolorallocate ( $palette, $c[0], $c[1], $c[2] );
	imagefilledrectangle ( $palette, $i*$fh, $i*$fh, ($i*$fh)+$fh, ($i*$fh)+$fh, $icol[$i] );
}
imagejpeg($newImg2,"final.jpg");
//$step_x = $resize_width;
//$step_y = $resize_height;
$step_x = $use_width/$resize_width;
$step_y = $use_height/$resize_height;
$step_x = $columns;
$step_y = $rows;
$step_width = ($use_width / $columns);
$step_height = ($use_height / $rows);

// print "Final dims: ".$step_x."x".$step_y;

$kinskode = '';

for($y=0;$y<$rows;$y++)
{
	$r_y = (round($y*$step_y)-($step_y/2))+$step_y;
	for ( $x=0;$x<$columns;$x++)
	{
		
		$r_x = (round($x*$step_x)-($step_x/2))+$step_x;
		$rgb = imagecolorat($newImg2, $x, $y);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;
		$result = imagecolorclosest ( $palette, $r, $g, $b );
		$result = imagecolorsforindex ( $palette, $result );
		$flip = array_flip ( $colors );
		$kinskode .= $flip[rgb2hex2rgb ( $result['red'] . ',' . $result['green'] . ',' .$result['blue']  )];
		
	}
	$kinskode .= PHP_EOL;
}
$return = array (
	'status'	=> 'success',
	'kinskode'	=>	$kinskode
);

if ( !empty ( $return ) )
	if ( isset ( $_GET['callback'] ) )
		echo preg_replace ( "/[^a-z0-9\040\-\_]/i", '', $_GET['callback'] ) . '(' . json_encode ( $return ) . ')';
	else
		echo json_encode ( $return );

imagedestroy($newImg2);
imagedestroy($newImg);
imagedestroy($palette);