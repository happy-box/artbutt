<?php

class Starpower extends IRCine_Module {

	var $star_power = false;
	
	function star_power ( $processed_data )
	{
		
		$msg = explode ( ' ', $this->strip_command ( $processed_data['message'] ) );
		
		$cmd = strtolower ( array_shift ( $msg ) );
		
		if ( $cmd == 'activate' )
			return $this->activate ( $processed_data['nick'], $processed_data['recepient'] );
			
		if ( $cmd == 'deactivate' )
			return $this->deactivate ( $processed_data['recepient'] );
			
		return $this->status ( $processed_data['recepient'] );
	}
	
	
	function activate ( $nick, $to )
	{
		
		if ( $this->star_power && $this->star_power != strtoupper ( $nick ) )
			$this->say ( $to, '★ STAR POWER OF ' . $this->star_power . ' STOLEN IN THE FORM OF A ' . chr(2) . strtoupper ( $nick ) . chr(2) . ' ★', IRCINE_PRIO_MEDIUM );
		elseif ( $this->star_power && $this->star_power == strtoupper ( $nick ) )
			$this->say ( $to, '★ ' . chr(2) . strtoupper ( $nick ) . chr(2) . ' IS ALREADY STAR POWERED ★', IRCINE_PRIO_MEDIUM );
		else
			$this->say ( $to, '★ IN THE FORM OF A ' . chr(2) . strtoupper ( $nick ) . chr(2) . ' ★', IRCINE_PRIO_MEDIUM );
		
		$this->star_power = strtoupper ( $nick );
	}
	
	function deactivate ( $to )
	{
		
		$this->star_power = false;
		$this->say ( $to, '★ ' . chr(2) . 'STAR POWER DEACTIVATED' . chr(2) . ' ★', IRCINE_PRIO_MEDIUM );
	}
	
	function status ( $to )
	{
		
		if ( $this->star_power )
			$this->say ( $to, '★ ' . chr(2) . strtoupper ( $this->star_power ) . chr(2) . ' IS CURRENTLY STAR POWERED ★', IRCINE_PRIO_MEDIUM );
		else
			$this->say ( $to, 'STAR POWER CURRENTLY DEACTIVATED.', IRCINE_PRIO_MEDIUM );
		
	}

}
