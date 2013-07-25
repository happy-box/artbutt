<?php

/**
 * artbutt.php
 * This launches the IRC bot
 */

error_reporting ( E_ALL );

define('ABSOLUTE_PATH', dirname(__FILE__) . '/');
define('INCLUDE_PATH', ABSOLUTE_PATH . 'includes/');
define('CORE_PATH', ABSOLUTE_PATH . 'core/');
define('MODULE_PATH', ABSOLUTE_PATH . 'modules/');
define('LOG_PATH', ABSOLUTE_PATH . 'logs/');

if ( ! defined ( 'STDIN' ) ) { define ( 'STDIN', fopen ( 'php://stdin', 'r' ) ); }
stream_set_blocking( STDIN, false );


require_once INCLUDE_PATH . 'mootyconf.php';
require_once INCLUDE_PATH . 'bashful.php';
require_once INCLUDE_PATH . 'deer.php';

require_once CORE_PATH . 'defines.php';

require_once CORE_PATH . 'ircine.php';
require_once CORE_PATH . 'ircine.core.php';
require_once CORE_PATH . 'ircine.module.php';
require_once CORE_PATH . 'ircine.connection.php';
require_once CORE_PATH . 'ircine.input.php';

function load_mooty ( $serverconfig )
{
	
	Mootyconf::read($serverconfig, 'plugins.conf') or die (  $usage . Bashful::c('bold','red') . '        ERROR:' . Bashful::reset() . ' Could not read config ' . $conf . '.' . PHP_EOL . PHP_EOL );
	
}

$conf = 'deerkins.conf';

$usage = PHP_EOL . '        USAGE: php deerkins.php ' . Bashful::bold() . '-c deer.conf' . Bashful::unbold() . PHP_EOL;

$opts = getopt ( 'c:h::' );

if ( isset ( $opts['h'] ) )
	die ( $usage . PHP_EOL );

if ( isset ( $opts['c'] ) && $opts['c'] != ''  )
	$conf = $opts['c'];
else
	die ( $usage . PHP_EOL );

load_mooty ( $conf );

$ircine = new IRCine ( $conf );

echo "Bot has been shut down\r\n";

exit ( );
