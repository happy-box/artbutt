<?php

include 'deer.class.php';
$deer = new Deer ( );


$return = array ( );

if ( @!empty ( $_GET['deer'] ) )
{

	$return = $deer->get_deer ( $_GET['deer'] );

}
elseif ( @!empty ( $_GET['q'] ) )
{

	echo $deer->search_deer ( $_GET['q'] );

}
else
{
	$stop = 20;
	$extended = false;

	if ( isset ( $_GET['extended'] ) )
	{

		$stop = 1;
		$extended = true;

	}

	$return = $deer->paged_deer ( $_GET['start'], $stop, $extended );

}

if ( !empty ( $return ) )
	if ( isset ( $_GET['callback'] ) )
		echo $deer->sanitize_deer ( $_GET['callback'] ) . '(' . json_encode ( $return ) . ')';
	else
		echo json_encode ( $return );
