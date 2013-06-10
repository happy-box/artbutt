<?php

include 'deer.class.php';

$deer = new Deer ( );

if ( isset ( $_POST['name'] ) )
{
	$max_rows = 20;
	$max_cols = 30;

	$kins_deer = $deer->fix_deer ( $max_rows );
	$irc_deer = $deer->parse_deer ( $kins_deer );

	$deername = $deer->write_deer ( strtolower ( $deer->sanitize_deer ( $_POST['name'] ) ), $deer->sanitize_deer ( $_POST['creator'] ), $kins_deer, $irc_deer );

	$output = array (
		'name'		=>	$deername,
		'raw'		=>	$kins_deer,
		'ircraw'	=>	$irc_deer
	);

	echo json_encode ( $output );

}
