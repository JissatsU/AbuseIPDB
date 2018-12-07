<?php 
#zzzzzz

if( @version_compare( PHP_VERSION, '7.1.0' ) < 1 )
{
	die( "\x0a" . RED . "[ERROR]" . NONE . " - PHP version 7.1+ required!\x0a" );
}

if( @ini_get( 'register_argc_argv' ) == 0 )
{
	die( "\x0a" . RED . "[ERROR]" . NONE . " - Must set `register_argc_argv=1 in your php.ini`!\x0a" );
}

if( !function_exists( 'system' ) )
{
	die( "\x0a" . RED . "[ERROR]" . NONE . " - Please enable `system()` function!\x0a" );
}

function get_opt_num( ) : int
{	
	$optNum = 0;
	system( 'clear' );
	banner();
	fscanf( STDIN, "%d\n", $optNum );
	return $optNum;
}

function get_ip( ) : string
{
	$ip = "";
	echo "\n  " . GREEN . "[".RED."*".GREEN."]".NONE." IP: ";
	fscanf( STDIN, "%s", $ip );
	return $ip;
}

function get_days( ) : int
{
	$days = 0;
	echo "  " . GREEN . "[".RED."*".GREEN."]".NONE." DAYS: ";
	fscanf( STDIN, "%d", $days );
	return $days;
}

function get_categories( ) : string
{
	$categories = "";
	echo "  " . GREEN . "[".RED."*".GREEN."]".NONE." Categories: ";
	fscanf( STDIN, "%s", $categories );
	return $categories;
}

function get_comment( ) : string
{
	$comment = "";
	echo "  " . GREEN . "[".RED."*".GREEN."]".NONE." Comment: ";
	$comment = fgets( STDIN, 1600 );
	return $comment;
}

function get_network( ) : string
{
	$network = "";
	echo "\n  " . GREEN . "[".RED."*".GREEN."]".NONE." Network ex.( 129.150.69.85/20 ): ";
	fscanf( STDIN, "%s", $network );
	return $network;
}

function go_back( ) : bool 
{ 
	$back = ""; 
	echo "\n" . ltrim( BLUE . "[<-] " . NONE . "Back to menu? [y/n] " ); 
	fscanf ( STDIN, "%s", $back ); 
	return ( $back == "" || $back != "y" ) ? False : True ;
}

function choose_action( ) : int
{
	$act = 0; 
	echo "\n" . ltrim( "[".RED."*".NONE."] Action: " . BLUE ); 
	fscanf( STDIN, "%d", $act ); 
	return $act;
}

function ip_cats( $cat )
{	
	echo RED . $cat . ",";
}

?>
