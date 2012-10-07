<?php

class Error extends IRCine_Module {
	
	function call ( $processed_data )
	{
	
		$this->log ( Bashful::c('bold', 'red') . 'ABORTING!' . Bashful::reset() . ' ' . $processed_data['message'] );
	
	}

}
