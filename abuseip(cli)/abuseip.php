<?php 

@define( "BLUE",     "\033[5;34m" );
@define( "RED",      "\033[5;31m" );
@define( "GREEN",    "\033[5;32m" );
@define( "NONE",     "\033[0m" );
@define( "ARGS",     array( "--api-key", "--verbose" ) );
@define( "CATFILE" , "categories.xml" );

include __DIR__."/utils.php";

$REGEX = array( 
		"/^([\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3})$/",
		"/(https:\/\/www\.abuseipdb\.com\/check\/)([\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3})/",
);

$urls = (object) array(
	     "home"         => "https://www.abuseipdb.com/",
	     "check_ip"     => "https://www.abuseipdb.com/check/",
	     "report_ip"    => "https://www.abuseipdb.com/report/json?",
	     "cidr_check"   => "https://www.abuseipdb.com/check-block/json?"
);

$banner = RED . "
                            ,-.
       ___,---.__          /'|`\          __,---,___
    ,-'    \`    `-.____,-'  |  `-.____,-'    //    `-.
  ,'        |           ~'\     /`~           |        `.
 /      ___//              `. ,'          ,  , \___      \
|    ,-'   `-.__   _         |        ,    __,-'   `-.    |
|   /          /\_  `   .    |    ,      _/\          \   |
\  |           \ \`-.___ \   |   / ___,-'/ /           |  /
 \  \           | `._   `\\  |  //'   _,' |           /  /
  `-.\         /'  _ `---'' , . ``---' _  `\         /,-'
     ``       /     \    ,='/ \`=.    /     \       ''
             |__   /|\_,--.,-.--,--._/|\   __|
             /  `./  \\`\ |  |  | /,//' \,'  \
            /   /     ||--+--|--+-/-|     \   \
           |   |     /'\_\_\ | /_/_/`\     |   |
            \   \__, \_     `~'     _/ .__/   /
             `-._,-'   `-._______,-'   `-._,-'

                      [ AbuseIPDB ]
                       By: scVnner
".NONE."
[".RED."+".NONE."] Options

  [".BLUE."1".NONE."] View attack categories
  [".BLUE."2".NONE."] Get recently reported abusers (ips)
  [".BLUE."3".NONE."] Choose action
  [".BLUE."4".NONE."] Exit

[".RED."*".NONE."] Choose option: ".BLUE."";

main( $argc, $argv );	#Start of program

function banner( )  : void { echo $GLOBALS['banner']; }

function parse_categories( string $file ) : array
{	
	$result     = array();
	$categories = @file_get_contents( $file );
	$data       = new SimpleXMLElement( $categories );

	$categories = $data->category;
	for( $i = 0 ; $i < sizeof( $categories ) ; $i++ )
	{
		$result[$i]['id']   = $categories[$i]['id'];
		$result[$i]['name'] = ltrim( ($categories[$i])->Name );
		$result[$i]['desc'] = ltrim( ($categories[$i])->Description );
	}
	return $result;
}

function display_categories( array $categories ) : void
{	
	$i = -1;
	while( ($i++) < sizeof( $categories ) - 1 )
	{	
		echo ( $i == 0 ) ? "\n" : "" ;
		echo GREEN . "  [".RED."ID".NONE.GREEN."]    - " . NONE . ( (object) $categories[ $i ] )->id   . "\x0a";
		echo GREEN . "  [".RED."NAME".NONE.GREEN."]  - " . NONE . ( (object) $categories[ $i ] )->name . "\x0a";
		echo GREEN . "  [".RED."DESC".NONE.GREEN."]  - " . NONE . ( (object) $categories[ $i ] )->desc . "\x0a\x0a";
	}
}

function parse_agents( string $agentsFile ) : array
{
	$agents = @explode( "\n", @file_get_contents( $agentsFile ) );
	return $agents;
}

function parse_options( int $argc, array $argv ) : object
{	
       $api_key = "";
       $verbose = False;
       $arg_N   = "";	# argument name
       $arg_V   = "";	# argument value
       $opts    = array();
	
	if( $argc > 3 ){
		die( "\x0a" . RED . "[ERROR]" . NONE . " - Too many arguments!\x0a" );
	}

	while( ( $argc-- ) > 1 )
	{	
		if( strpos( $argv[ $argc ], "=" ) !== False )
		{
			$arg_N = @explode( "=", $argv[ $argc ] )[0];
			$arg_V = @explode( "=", $argv[ $argc ] )[1];
		}
		else{
			$arg_N = $argv[ $argc ];
		}

		if( !in_array( $arg_N, ARGS ) )
		{
			die( "\x0a" . RED . "[ERROR]" . NONE . " - Invalid argument found! -> $arg_N\x0a" );
		}

		if( $arg_N == ARGS[0] && $arg_V != "" ) { $api_key = $arg_V; }
		if( $arg_N == ARGS[1] )                 { $verbose = True; }
	}
	
	$opts['api_key'] = $api_key;
	$opts['verbose'] = $verbose;
	return (object) $opts;
}

function init_req( string $url )
{	
	$agents  = parse_agents( 'u-agents.dat' );
	$curl    = curl_init( $url );
	$options = array(
		  	CURLOPT_USERAGENT       => $agents[ mt_rand( 0, sizeof( $agents ) - 1 ) ], # random user-agent
			CURLOPT_HEADER          => 0,                                              # do not display headers
			CURLOPT_HTTPHEADER      => array( 'Accept: application/json' ), 
			CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,                          # force HTTP/1.1
			CURLOPT_FRESH_CONNECT   => 1,                                              # do not use a cached connection
			CURLOPT_SSL_VERIFYPEER  => 1,
			CURLOPT_SSL_VERIFYHOST  => 2,
			CURLOPT_RETURNTRANSFER  => true
	);

	curl_setopt_array( $curl, $options );
	return $curl;
}

function report_abuser( string $url, string $api_key, string $ip, string $categories, string $comment )
{	
	$queryString = "";
	$queryString = http_build_query( array( 
				  "key"      => $api_key, "category" => $categories,
				  "comment"  => $comment, "ip"       => $ip 
	) );
	$prep = init_req( $url . $queryString );
	$resp = json_decode( curl_exec( $prep ) );
	return $resp;
}

function get_abuser_info( string $url, string $api_key, string $ip, int $days )
{
	$queryString = "";
	$queryString = http_build_query( array( 
				   "key"   => $api_key, 
				   "days"  => $days
	) );
	$url  = $url . $ip . "/json?";
	$prep = init_req( $url . $queryString );
	$resp = json_decode( curl_exec( $prep ) );
	return $resp;
}

function check_cidr( string $url, string $api_key, string $network, int $days )
{
	$queryString = "";
	$queryString = http_build_query( array( 
				   "network"  => $network, "key" => $api_key, 
				   "days"     => $days
	) );
	$prep = init_req( $url . $queryString );
	$resp = json_decode( curl_exec( $prep ) );
	return $resp;
}

function get_recently_reported( string $url ) : void
{
	$req   = init_req( $url );
	$resp  = explode( "\n", curl_exec( $req ) );
	curl_close( $req );

	for( $i = 0 ; $i < sizeof( $resp ); $i++ )
	{
		if( preg_match( ( $GLOBALS['REGEX'] )[1], $resp[$i] ) )
		{	
			$ip = substr( $resp[$i], strpos( $resp[$i], ">" ) + 1 );
			echo "  ".RED."[*]".NONE." ".BLUE."-".NONE." " . substr( $ip, 0, strpos( $ip, "</" ) ) . "\n";
		}
	}
}

function main( int $argc, array $argv ) : void
{		
	$optNum      = 0;
	$ip          = "";
	$url         = "";
	$toMenu      = "";
	$comment     = "";
	$categ       = "";
	$days        = "";
	$percent     = "";
	$network     = "";
	$opts        = parse_options( $argc, $argv );
	$categories  = parse_categories( CATFILE );

	if( $opts->api_key == NULL )
	{
		die( "\x0a" . RED . "[ERROR] - " . NONE . "No api key found!\x0a" );
	}

	while( $optNum != 4 )
	{	
		$optNum = get_opt_num();

		if( $optNum > 4 || $optNum <= 0 ){
			die( "\x0a" . RED . "[ERROR] - " . NONE . "Invalid option!\x0a" );
		}

		if( $optNum == 1 ){
			display_categories( $categories ); if( !go_back() ) { exit; }
		}
		
		else if( $optNum == 2 )
		{	
			$url = ( $GLOBALS['urls'] )->home;
			echo "\n";
			get_recently_reported( $url ); if( !go_back() ) { exit; }
		}

		else if( $optNum == 3 )
		{	
			echo NONE . "\n";
			echo "  [".BLUE."1".NONE."] Report IP\x0a";
			echo "  [".BLUE."2".NONE."] Check IP\x0a";
			echo "  [".BLUE."3".NONE."] Check CIDR\x0a";
			$action = choose_action( );
			
			if( ( $action <= 0 ) || ( $action > 3 ) )
			{
				die( "\x0a" . RED . "[ERROR] - " . NONE . "Action not supported!\x0a" );
			}

			if( $action == 1 )
			{	
				$ip      = get_ip();
				$categ   = get_categories();
				$comment = get_comment();

				$url  = ($GLOBALS['urls'])->report_ip;
				$resp = report_abuser( $url, $opts->api_key, $ip, $categ, $comment );

				if( !is_array( $resp ) && $resp->success )
				{
					echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE."IP      - " . $resp->ip;
					echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE."SUCCESS - " . $resp->success;
					echo "\n";
					if( !go_back() ) { exit; }
				}
				else{
					foreach( ( $resp[0] ) as $k => $v )
					{	
						$tab =  ( $k == "id" ) ? "\t\t" : "\t" ;

						if      ( $k == "links"   ) echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $k ) ."$tab- ".RED. $v->about;
						else if ( $k == "source"  ) echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $k ) ."$tab- ".RED. $v->parameter;
						else                        echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $k ) ."$tab- ".RED. $v;
					}
					echo "\n";
					if( !go_back() ) { exit; }
				}
			}
			
			else if( $action == 2 )
			{
				$ip   = get_ip();
				$days = get_days();
				
				$url  = ( $GLOBALS['urls'] )->check_ip;
				$resp = get_abuser_info( $url, $opts->api_key, $ip, (int) $days );

				if( is_array( $resp ) )
				{
					for( $i = 0 ; $i < sizeof( $resp ) ; $i++ )
					{	
						echo "\n";
						foreach( ( $resp[ $i ] ) as $k => $v )
						{	
							$tab     =  ( $k == "id" || $k == "ip" )     ? "\t\t\t  " : "\t\t  " ;
							$percent =  ( $k == "abuseConfidenceScore" ) ? "%"        : "" ;

							if      ( $k == "isWhitelisted" )        $tab = "\t  ";
							else if ( $k == "abuseConfidenceScore" ) $tab = "  ";
	
							if      ( $k == "links"    )   echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $k ) ."$tab- ".RED. $v->about;
							else if ( $k == "source"   )   echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $k ) ."$tab- ".RED. $v->parameter;
							else if ( $k == "category" ) { echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $k ) ."$tab- "; array_map( "ip_cats", $v ); }
							else                           echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $k ) ."$tab- ".RED. $v . $percent;
						}
					}
				}
				else{
					echo "\n";
					foreach( ( $resp ) as $k => $v )
					{	
						$tab     =  ( $k == "id" || $k == "ip" )         ? "\t\t\t  " : "\t\t  " ;
						$percent =  ( $k == "abuseConfidenceScore" )     ? "%"        : "" ;

						if      ( $k == "isWhitelisted" )        $tab = "\t  ";
						else if ( $k == "abuseConfidenceScore" ) $tab = "  ";
	
						if      ( $k == "links"    )   echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $k ) ."$tab- ".RED. $v->about;
						else if ( $k == "source"   )   echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $k ) ."$tab- ".RED. $v->parameter;
						else if ( $k == "category" ) { echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $k ) ."$tab- "; array_map( "ip_cats", $v ); }
						else                           echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $k ) ."$tab- ".RED. $v . $percent;
					}
				}
				echo "\n";
				if( !go_back() ) { exit; }
			}
			
			else if( $action == 3 )
			{
				$network = get_network( );
				$days    = get_days( );

				$url     = ( $GLOBALS['urls'] )->cidr_check;
				$resp    = check_cidr( $url, $opts->api_key, $network, $days );

				if( is_array( $resp ) && ($resp[0])->id )
				{
					foreach( ($resp[0]) as $errKey => $errVal )
					{
						$tab =  ( $errKey == "id" ) ? "\t\t" : "\t" ;

						if      ( $errKey == "links"   ) echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $errKey ) ."$tab- ".RED. $errVal->about;
						else if ( $errKey == "source"  ) echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $errKey ) ."$tab- ".RED. $errVal->parameter;
						else                             echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $errKey ) ."$tab- ".RED. $errVal;
					}
					echo "\n";
					if( !go_back() ) { exit; }
				}
				else{
					foreach( $resp as $succKey => $succVal )
					{	
						if( is_array( $succVal ) )
						{
							for( $i = 0 ; $i < sizeof( $succVal ) ; $i++ )
							{
								echo "\n";
								foreach( $succVal[ $i ] as $key => $val )
								{
									echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $key ) ." - ".RED. $val;
								}
							}
						}
						else{
							echo "\n" . GREEN . "[".RED."*".NONE.GREEN."] ".NONE. strtoupper( $succKey ) ." - ".RED. $succVal;
						}
					}
					echo "\n";
					if( !go_back() ) { exit; }
				}
			}
		}
	}
}


?>
