<?php 

namespace killua;

final class Crypt0 {
    
    // a string of all alphanumeric characters
	// used for random string generation
	public static $S     = [];
	public static $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	public static $key   = 'yj21i6d6cO35efgDH43G4Pe3XSo7aEUcb4wiee1R574bdUY77Iv787fFta7Fwp86pA6b8U6'; 
	
    public static function encode_url( string $str ) : string {
		$i       = -1;
	    $encoded = '';

	    while( ($i++) < mb_strlen( $str ) - 1 ){
		    if( $str[$i] == ' ' ){
			    $encoded .= '+';
		    }
		    else if( (ord( $str[$i] ) <= 57 && ord( $str[$i] ) >= 48)  || 
				     (ord( $str[$i] ) >= 65 && ord( $str[$i] ) <= 90)  || 
				     (ord( $str[$i] ) >= 97 && ord( $str[$i] ) <= 122) || 
				     ( $str[$i] == '-' || $str[$i] == '_' || $str[$i] == '.' || $str[$i] == '~' ) ){

			    $encoded .= $str[$i];
		    } else{
			    $encoded .= '%';
				$encoded .= self::dec_2_hex( (int) ord( $str[$i] ) );
		    }
	    }
	    return $encoded;
    }


    public static function decode_url( string $str ) : string {
		$i       = -1;
	    $decoded = '';
	}
	

    public static function signature_generate() : string {
	    $length 	= strlen( self::$chars );
	    $signature  = '';
	    $i			= 0;
	    $fp         = fopen( '/dev/urandom', 'rb' );
	    $f          = fread( $fp, strlen( self::$chars ) );

	    while( ( $i++ ) < $length ) {
		    if( $i % 4 == 0 ){
			    $signature .= dechex( ord( $f[$i] ) );
		    } else{
			    $signature .= self::$chars[ mt_rand( 0, $length - 1 ) ];
		    }
	    }
	    return substr( $signature, 0, strlen( $signature ) - 5 );
    }


	public static function dec_2_hex( int $dec_num ) : string {
		$hex       = '';
		$remainder = 0;
		
		while( (int) $dec_num != 0 ){
			$remainder = $dec_num % 16;
			if( $remainder <= 9 ){
				$hex .= chr( $remainder + 48 );
			} else{
				$hex .= chr( $remainder + ( (48 << 1) - 9 ) );
			}
			$dec_num /= 16;
		}
		return strrev( $hex );
	}


	public static function hex_2_dec( string $hex_string ) : int {
		$d          = 0;
		$num        = 0;
		$i          = strlen( $hex_string );
		$hex_string = strrev( $hex_string );
		
		# remove leading '0x | 0X'
		if ( strpos( $hex_string, '0x' ) === 0 || strpos( $hex_string, '0X' ) === 0 ) {
			$hex_string = substr( $hex_string, 2 );
		}
		
		while( ($i--) > 0 ){
			if( ord( $hex_string[$i] ) >= 65 && ord( $hex_string[$i] ) <= 90 ){
				$d    = ord( $hex_string[$i] ) - ord('A') + 10;
				$num += (int) ($d * (16 ** $i));
			}
			else if( ord( $hex_string[$i] ) >= 97 && ord( $hex_string[$i] ) <= 122 ){
				$d    = ord( $hex_string[$i] ) - ord('a') + 10;
				$num += (int) ($d * (16 ** $i));
			}
			else{
				$num += (int) ((int) $hex_string[$i] * (16 ** $i));
			}
		}
		return $num;
	}

	# ROT13 cipher
	public static function rot13 ( string $str ) : string {
		$i      = -1;
		$newStr = '';
		while ( ($i++) < mb_strlen( $str ) - 1 ) {
			if ( (ord($str[$i]) >= ord('A') && ord($str[$i]) <= ord('M') ) 
			  || (ord($str[$i]) >= ord('a') && ord($str[$i]) <= ord('m') ) ) {
				$newStr .= chr( ord( $str[$i] ) + 13 );
			}
			else if ( (ord($str[$i]) >= ord('N') && ord($str[$i]) <= ord('Z')) 
				   || (ord($str[$i]) >= ord('n') && ord($str[$i]) <= ord('z')) ) {
				$newStr .= chr( ord( $str[$i] ) - 13 );
			}
			else{
				$newStr .= $str[$i];
			}
		}
		return $newStr;
	}
	
	# RC4
	public function rc_crypt0( $key, &$S, $message ) {
		$crypted = '';
		for ( $i = 0 ; $i < 256 ; $i++ ) {
			$S[$i] = (int) $i;
		}
		$j = 0;
		for ( $i = 0 ; $i < 256 ; $i++ ) {
			$j = ( $j + $S[$i] + ord( $key[ $i % strlen( $key ) ] ) ) % 256;
			$temp  = $S[$i];
			$S[$i] = $S[$j];
			$S[$j] = $temp;
		}
		$i =  0;
		$j =  0;
		$x = -1;
		while ( ($x++) < strlen( $message ) - 1 ) {
			$i = ( $i + 1 ) % 256;
			$j = ( $j + $S[$i] ) % 256;
			$temp    = $S[$i];
			$S[$i]   = $S[$j];
			$S[$j]   = $temp;
			$K       = $S[( $S[$i] + $S[$j] ) % 256];
			$crypted[$x] = (string) chr( ord( $message[$x] ) ^ $K ); # XOR the current plaintext digit with the current 'S' digit
		}

		return $crypted;
	}
	
	
	public function code( $msg ) {
		$res = '';
		for ( $i = 0 ; $i < strlen( $msg ) ; $i++ ) {
			$res[$i] = chr( (ord( $msg[$i] ) ^ 0x05) % 256 );
		}
		return $res;
	}


	public function cry0( string $msg, int $num ){
		$str = '';
		for ( $i = 0 ; $i < strlen( $msg ) ; $i++ ) {
			$str[$i] = chr( ord( $msg[$i] ) - $num );
		}
		return $str;
	}


	public function cry1( string $msg, int $num ){
		$str = '';
		for ( $i = 0 ; $i < strlen( $msg ) ; $i++ ) {
			$str[$i] = chr( ord( $msg[$i] ) + $num );
		}
		return $str;
	}
	
	
    public static function dec_2_bin( int $dec_num ) : string {
        $i   = -1;
        $bin = '';

        while( $dec_num ){
            $bin .= (string) ($dec_num & 1);
            $dec_num >>= 1;
        }
        return strrev( $bin );
	}


	public function rnd_slt( string &$msg ){
		
	}
}

//echo \killua\Crypt0::dec_2_hex( 346777 );
//echo \killua\Crypt0::hex_2_dec( '3a3' );
//echo \killua\Crypt0::dec_2_hex( '931' );
//echo \killua\Crypt0::dec_2_bin( 231 );
//$a = \killua\Crypt0::code( 'what is that?' );
//echo \killua\Crypt0::code( $a );
//$a = \killua\Crypt0::cry0( 'what is that', 4 );
//echo \killua\Crypt0::cry1( $a, 4 );

//$key  = \killua\Crypt0::$key;
//$msg0 = 'what the fuck man? my h0use is f0ken dead!';
//$msg0 = "Holy sh1t man! that's fo0-ken awesome!?!$";

//$a0   = \killua\Crypt0::rc_crypt0( $key, \killua\Crypt0::$S, $msg0 );
//echo $a0 . "\x0a";

//$b0 = \killua\Crypt0::rc_crypt0( $key, \killua\Crypt0::$S, $a0 );
//echo $b0 . "\x0a";

?>


