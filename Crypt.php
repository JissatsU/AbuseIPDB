<?php 

namespace killua;

final class Crypt0 {
    
    // a string of all alphanumeric characters
    // used for random string generation
    public static $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
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
		    }
		    else{
			    $encoded .= '%';
			    //$encoded .= self::$alnum[ ord( $str[$i] ) >> 4 ] . '';
				//$encoded .= self::$alnum[ ord( $str[$i] ) & 15 ] . '';
				$encoded   .= self::dec_2_hex( (int) ord( $str[$i] ) );
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
			}
			else{
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


    public static function dec_2_bin( int $dec_num ) : string {
        $i   = -1;
        $bin = '';

        while( $dec_num ){
            $bin .= (string) ($dec_num & 1);
            $dec_num >>= 1;
        }
        return strrev( $bin );
    }
}

//echo \killua\Crypt0::dec_2_hex( 346777 );
echo \killua\Crypt0::hex_2_dec( '3a3' );
?>
