<?php

/** FILE: Contains glue for chattering across the network. */

/** Look for a claim header on the given (HTML) page. */
function _clm_claim_discover($url, $timeout_bytes=3000) {
    $claim_str_dquote = 'rel="claim"';
    $claim_str_squote = 'rel=\'claim\'';

    extract(parse_url($url), EXTR_SKIP);
    
    if ( !isset($host) ) // Not an URL. This should never happen.
        return false;

    $path  = ( !isset($path) ) ? '/'          : $path;
    $path .= ( isset($query) ) ? '?' . $query : '';
    $port  = ( isset($port)  ) ? $port        : 80;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RANGE, "0-3000");
    $text = curl_exec($ch);      
    curl_close($ch);

    $claim_link_offset_dquote = strpos($text, $claim_str_dquote);
    $claim_link_offset_squote = strpos($text, $claim_str_squote);

    if ( $claim_link_offset_dquote || $claim_link_offset_squote ) {
        $quote = ($claim_link_offset_dquote) ? '"' : '\'';
        $claim_link_offset = $claim_link_offset_dquote + $claim_link_offset_squote;
        $claim_href_pos = @strpos($text, 'href=', $claim_link_offset);
        $claim_href_start = $claim_href_pos+6;
        $claim_href_end = @strpos($text, $quote, $claim_href_start);
        $claim_server_url_len = $claim_href_end - $claim_href_start;
        $claim_server_url = substr($text, $claim_href_start, $claim_server_url_len);

        // We may find rel="claim" but an incomplete claim URL
        if ( $claim_server_url_len > 0 ) { // We got it!
            return $claim_server_url;
        }
    }

    return false;
}

// Returns array with headers in $response[0] and body in $response[1]
function _clm_http_post($request, $host, $path, $port = 80) {
    global $wp_version;

    $http_request  = "POST $path HTTP/1.0\r\n";
    $http_request .= "Host: $host\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded; charset=" . get_option('blog_charset') . "\r\n";
    $http_request .= "Content-Length: " . strlen($request) . "\r\n";
    $http_request .= "User-Agent: WordPress/$wp_version | Akismet/2.0\r\n";
    $http_request .= "\r\n";
    $http_request .= $request;

    $response = '';
    if( false != ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
        fwrite($fs, $http_request);

        while ( !feof($fs) )
            $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);
    }
    return $response;
}

?>
