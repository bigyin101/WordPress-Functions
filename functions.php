<?php


/**
 * Get variables from the wp-config file and populate an array
 *
 * 
 */
function wpdm_wordpress_get_wpconfig_info( $path_to_file ) {
    try {
        if( file_exists( $path_to_file ) ) {
            if ( !$fh = fopen( $path_to_file, 'r' ) ) {
                die( "Cannot open wp-config.php." );
                echo "Cannot open wp-config.php.";
            }
            $wpc_WPDM_DB_PASSWORD = '';
            while ( !feof( $fh ) ) {
                $line = fgets( $fh );
                if  (preg_match( '/^\s*define\s*\(\s*\'DB_NAME\'\s*,\s*\'(.+?)\'/', $line, $match ) ) {
                    $wpc_DB_NAME = $match[1];
                } elseif ( preg_match( '/^\s*define\s*\(\s*\'DB_USER\'\s*,\s*\'(.+?)\'/', $line, $match ) ) {
                    $wpc_WPDM_DB_USER = $match[1];
                } elseif ( preg_match( '/^\s*define\s*\(\s*\'DB_PASSWORD\'\s*,\s*\'(.+?)\'/', $line, $match ) ) {
                    $wpc_WPDM_DB_PASSWORD = $match[1];
                } elseif ( preg_match( '/^\s*define\s*\(\s*\'DB_HOST\'\s*,\s*\'(.+?)\'/', $line, $match ) ) {
                    $wpc_WPDM_DB_HOST = $match[1];
                } elseif ( preg_match( '/^\s*\$table_prefix\s*=\s*\'(.+?)\'/', $line, $match ) ) {
                    $wpc_TABLE_PREFIX = $match[1];
                }
            } //end while
            fclose( $fh );
            return array( $wpc_DB_NAME, $wpc_WPDM_DB_USER, $wpc_WPDM_DB_PASSWORD,$wpc_WPDM_DB_HOST, $wpc_TABLE_PREFIX );
        }
    } catch ( Exception $e ) {
        echo 'Caught exception in function: ' . '<strong>' .  __FUNCTION__  . '</strong>:<br/>' , $e->getMessage(), '<br/>';
    }
}



/**
 * Get the latest version of wordpress download url
 * 
 *
 */
function wpdm_wordpress_get_latest_version_url() {
    try {
        // check if cURL is enabled
        if( wpdm_check_curl_enabled() == false) {
            echo '<p><strong>cURL</strong> is NOT installed or enabled, and this script needs cURL to work.<br/> 
            Please read the README file for further help on how to fix this.</p>';
            die();
        }

        $url = 'http://api.wordpress.org/core/version-check/1.6/';
        $c   = curl_init( $url );
        curl_setopt( $c, CURLOPT_RETURNTRANSFER, 1 );
        $page = curl_exec( $c );
        curl_close( $c );
        $ret = unserialize( $page );
        /*  echo '<pre>';
        echo $local;
        var_dump($ret);
        echo '</pre>';
        die();*/
        return $ret['offers'][0]['download'];
    } catch ( Exception $e ) {
        echo 'Caught exception in function: ' . '<strong>' .  __FUNCTION__  . '</strong>:<br/>' , $e->getMessage(), '<br/>';
    }
}



/**
 * Get the latest version number form WordPress
 *
 * 
 */
function wpdm_wordpress_get_version() {
    try {
        $wp_version = wpdm_wordpress_get_latest_version_url();
        $wp = str_replace( "wordpress-", "", basename( $wp_version, ".zip" ) . PHP_EOL );
        return $wp;
    } catch ( Exception $e ) {
        echo 'Caught exception in function: ' . '<strong>' .  __FUNCTION__  . '</strong>:<br/>' , $e->getMessage(), '<br/>';
    }
}



/**
 * Get the latest version number form WordPress.
 * If not connected to the internet, get the version
 * from the wordpress folder (wp-includes/version.php) instead
 * 
 */
function wpdm_get_wp_version( $path_to_file ) {
    try {
        if( file_exists( $path_to_file ) ) 
        {
            if ( !$fh = fopen( $path_to_file, 'r' ) ) {
                die( "Cannot open wp-config.php." );
                echo "Cannot open wp-config.php.";
            }
            while ( !feof( $fh ) ) {
                $line = fgets( $fh );
                if ( preg_match( '/^\s*\$wp_version\s*=\s*\'(.+?)\'/', $line, $match ) ) {
                    $wpc_WP_VERSION = $match[1];
                }
            } //end while
            fclose( $fh );
            return $wpc_WP_VERSION;
        } else {
            echo '';
        }

    } catch ( Exception $e ) {
        echo 'Caught exception in function: ' . '<strong>' .  __FUNCTION__  . '</strong>:<br/>' , $e->getMessage(), '<br/>';
    }
}



/**
 * Get wordpress salt keys
 * 
 */
function wpdm_wordpress_get_salt() {
    try {
    // check if cURL is enabled
    if( wpdm_check_curl_enabled() == false) {
        echo '<p><strong>cURL</strong> is NOT installed or enabled, and this script needs cURL to work.<br/> 
        Please read the README file for further help on how to fix this.</p>';
        die();
    }

    $url = 'https://api.wordpress.org/secret-key/1.1/salt/';
    $curlSession = curl_init();
    curl_setopt($curlSession, CURLOPT_URL, $url);
    curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

    $jsonData = json_decode(curl_exec($curlSession));
    curl_close($curlSession);

    } catch ( Exception $e ) {
        echo 'Caught exception in function: ' . '<strong>' .  __FUNCTION__  . '</strong>:<br/>' , $e->getMessage(), '<br/>';
    }
}



/**
 * Generates a random string.
 * This can be used to generate wordpress salt keys
 * if not connected to the internet.
 *
 */
function wpdm_random_string( $length = 64 ) {
    $secret_ar = str_split("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()-=+[]{};:<>,.?");
    $secret = '';
    for( $i=0; $i < $length; $i++ ) {
        $secret .= $secret_ar[rand( 0,85 )];
    }
    return substr( $secret,0,$length );
}



////

/**
 * Check if cURL is enabled
 * Used in wpdm_wordpress_get_latest_version_url()
 * 
 */
function wpdm_check_curl_enabled() {
    if ( !function_exists( 'curl_init' ) || !in_array( 'curl', get_loaded_extensions() ) ) {
        return false;
    } else {
        return true;
    }






?>
