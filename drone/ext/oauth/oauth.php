<?php


namespace Drone;



class OAuthException extends \Exception {

}

class OAuthConsumer {
  public $key;
  public $secret;

  function __construct($key, $secret, $callback_url=NULL) {
    $this->key = $key;
    $this->secret = $secret;
    $this->callback_url = $callback_url;
  }

  function __toString() {
    return "OAuthConsumer[key=$this->key,secret=$this->secret]";
  }
}

class OAuthToken {

  public $key;
  public $secret;





  function __construct($key, $secret) {
    $this->key = $key;
    $this->secret = $secret;
  }





  function to_string() {
    return "oauth_token=" .
           OAuthUtil::urlencode_rfc3986($this->key) .
           "&oauth_token_secret=" .
           OAuthUtil::urlencode_rfc3986($this->secret);
  }

  function __toString() {
    return $this->to_string();
  }
}





abstract class OAuthSignatureMethod {




  abstract public function get_name();











  abstract public function build_signature($request, $consumer, $token);









  public function check_signature($request, $consumer, $token, $signature) {
    $built = $this->build_signature($request, $consumer, $token);


    if (strlen($built) == 0 || strlen($signature) == 0) {
      return false;
    }

    if (strlen($built) != strlen($signature)) {
      return false;
    }


    $result = 0;
    for ($i = 0; $i < strlen($signature); $i++) {
      $result |= ord($built{$i}) ^ ord($signature{$i});
    }

    return $result == 0;
  }
}








class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod {
  function get_name() {
    return "HMAC-SHA1";
  }

  public function build_signature($request, $consumer, $token) {
    $base_string = $request->get_signature_base_string();
    $request->base_string = $base_string;

    $key_parts = array(
      $consumer->secret,
      ($token) ? $token->secret : ""
    );

    $key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
    $key = implode('&', $key_parts);

    return OAuthBase::encode(hash_hmac('sha1', $base_string, $key, true));
  }
}






class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod {
  public function get_name() {
    return "PLAINTEXT";
  }










  public function build_signature($request, $consumer, $token) {
    $key_parts = array(
      $consumer->secret,
      ($token) ? $token->secret : ""
    );

    $key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
    $key = implode('&', $key_parts);
    $request->base_string = $key;

    return $key;
  }
}









abstract class OAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod {
  public function get_name() {
    return "RSA-SHA1";
  }







  protected abstract function fetch_public_cert(&$request);





  protected abstract function fetch_private_cert(&$request);

  public function build_signature($request, $consumer, $token) {
    $base_string = $request->get_signature_base_string();
    $request->base_string = $base_string;


    $cert = $this->fetch_private_cert($request);


    $privatekeyid = openssl_get_privatekey($cert);


    $ok = openssl_sign($base_string, $signature, $privatekeyid);


    openssl_free_key($privatekeyid);

    return OAuthBase::encode($signature);
  }

  public function check_signature($request, $consumer, $token, $signature) {
    $decoded_sig = OAuthBase::decode($signature);

    $base_string = $request->get_signature_base_string();


    $cert = $this->fetch_public_cert($request);


    $publickeyid = openssl_get_publickey($cert);


    $ok = openssl_verify($base_string, $decoded_sig, $publickeyid);


    openssl_free_key($publickeyid);

    return $ok == 1;
  }
}

class OAuthRequest {
  protected $parameters;
  protected $http_method;
  protected $http_url;

  public $base_string;
  public static $version = '1.0';
  public static $POST_INPUT = 'php://input';

  function __construct($http_method, $http_url, $parameters=NULL) {
    $parameters = ($parameters) ? $parameters : array();
    $parameters = array_merge( OAuthUtil::parse_parameters(parse_url($http_url, PHP_URL_QUERY)), $parameters);
    $this->parameters = $parameters;
    $this->http_method = $http_method;
    $this->http_url = $http_url;
  }





  public static function from_request($http_method=NULL, $http_url=NULL, $parameters=NULL) {
    $scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")
              ? 'http'
              : 'https';
    $http_url = ($http_url) ? $http_url : $scheme .
                              '://' . $_SERVER['SERVER_NAME'] .
                              ':' .
                              $_SERVER['SERVER_PORT'] .
                              $_SERVER['REQUEST_URI'];
    $http_method = ($http_method) ? $http_method : $_SERVER['REQUEST_METHOD'];





    if (!$parameters) {

      $request_headers = OAuthUtil::get_headers();


      $parameters = OAuthUtil::parse_parameters($_SERVER['QUERY_STRING']);



      if ($http_method == "POST"
          &&  isset($request_headers['Content-Type'])
          && strstr($request_headers['Content-Type'],
                     'application/x-www-form-urlencoded')
          ) {
        $post_data = OAuthUtil::parse_parameters(
          Func::wpFilesystem()->get_contents(self::$POST_INPUT)
        );
        $parameters = array_merge($parameters, $post_data);
      }



      if (isset($request_headers['Authorization']) && substr($request_headers['Authorization'], 0, 6) == 'OAuth ') {
        $header_parameters = OAuthUtil::split_header(
          $request_headers['Authorization']
        );
        $parameters = array_merge($parameters, $header_parameters);
      }

    }

    return new OAuthRequest($http_method, $http_url, $parameters);
  }




  public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL) {
    $parameters = ($parameters) ?  $parameters : array();
    $defaults = array("oauth_version" => OAuthRequest::$version,
                      "oauth_nonce" => OAuthRequest::generate_nonce(),
                      "oauth_timestamp" => OAuthRequest::generate_timestamp(),
                      "oauth_consumer_key" => $consumer->key);
    if ($token)
      $defaults['oauth_token'] = $token->key;

    $parameters = array_merge($defaults, $parameters);

    return new OAuthRequest($http_method, $http_url, $parameters);
  }

  public function set_parameter($name, $value, $allow_duplicates = true) {
    if ($allow_duplicates && isset($this->parameters[$name])) {

      if (is_scalar($this->parameters[$name])) {


        $this->parameters[$name] = array($this->parameters[$name]);
      }

      $this->parameters[$name][] = $value;
    } else {
      $this->parameters[$name] = $value;
    }
  }

  public function get_parameter($name) {
    return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
  }

  public function get_parameters() {
    return $this->parameters;
  }

  public function unset_parameter($name) {
    unset($this->parameters[$name]);
  }





  public function get_signable_parameters() {

    $params = $this->parameters;



    if (isset($params['oauth_signature'])) {
      unset($params['oauth_signature']);
    }

    return OAuthUtil::build_http_query($params);
  }








  public function get_signature_base_string() {
    $parts = array(
      $this->get_normalized_http_method(),
      $this->get_normalized_http_url(),
      $this->get_signable_parameters()
    );

    $parts = OAuthUtil::urlencode_rfc3986($parts);

    return implode('&', $parts);
  }




  public function get_normalized_http_method() {
    return strtoupper($this->http_method);
  }





  public function get_normalized_http_url() {
    $parts = parse_url($this->http_url);

    $scheme = (isset($parts['scheme'])) ? $parts['scheme'] : 'http';
    $port = (isset($parts['port'])) ? $parts['port'] : (($scheme == 'https') ? '443' : '80');
    $host = (isset($parts['host'])) ? strtolower($parts['host']) : '';
    $path = (isset($parts['path'])) ? $parts['path'] : '';

    if (($scheme == 'https' && $port != '443')
        || ($scheme == 'http' && $port != '80')) {
      $host = "$host:$port";
    }
    return "$scheme://$host$path";
  }




  public function to_url() {
    $post_data = $this->to_postdata();
    $out = $this->get_normalized_http_url();
    if ($post_data) {
      $out .= '?'.$post_data;
    }
    return $out;
  }




  public function to_postdata() {
    return OAuthUtil::build_http_query($this->parameters);
  }




  public function to_header($realm=null) {
    $first = true;
	if($realm) {
      $out = 'Authorization: OAuth realm="' . OAuthUtil::urlencode_rfc3986($realm) . '"';
      $first = false;
    } else
      $out = 'Authorization: OAuth';

    $total = array();
    foreach ($this->parameters as $k => $v) {
      if (substr($k, 0, 5) != "oauth") continue;
      if (is_array($v)) {
        throw new OAuthException('Arrays not supported in headers');
      }
      $out .= ($first) ? ' ' : ',';
      $out .= OAuthUtil::urlencode_rfc3986($k) .
              '="' .
              OAuthUtil::urlencode_rfc3986($v) .
              '"';
      $first = false;
    }
    return $out;
  }

  public function __toString() {
    return $this->to_url();
  }


  public function sign_request($signature_method, $consumer, $token) {
    $this->set_parameter(
      "oauth_signature_method",
      $signature_method->get_name(),
      false
    );
    $signature = $this->build_signature($signature_method, $consumer, $token);
    $this->set_parameter("oauth_signature", $signature, false);
  }

  public function build_signature($signature_method, $consumer, $token) {
    $signature = $signature_method->build_signature($this, $consumer, $token);
    return $signature;
  }




  private static function generate_timestamp() {
    return time();
  }




  private static function generate_nonce() {
    $mt = microtime();
    $rand = mt_rand();

    return md5($mt . $rand);
  }
}

class OAuthServer {
  protected $timestamp_threshold = 300;
  protected $version = '1.0';
  protected $signature_methods = array();

  protected $data_store;

  function __construct($data_store) {
    $this->data_store = $data_store;
  }

  public function add_signature_method($signature_method) {
    $this->signature_methods[$signature_method->get_name()] =
      $signature_method;
  }







  public function fetch_request_token(&$request) {
    $this->get_version($request);

    $consumer = $this->get_consumer($request);


    $token = NULL;

    $this->check_signature($request, $consumer, $token);


    $callback = $request->get_parameter('oauth_callback');
    $new_token = $this->data_store->new_request_token($consumer, $callback);

    return $new_token;
  }





  public function fetch_access_token(&$request) {
    $this->get_version($request);

    $consumer = $this->get_consumer($request);


    $token = $this->get_token($request, $consumer, "request");

    $this->check_signature($request, $consumer, $token);


    $verifier = $request->get_parameter('oauth_verifier');
    $new_token = $this->data_store->new_access_token($token, $consumer, $verifier);

    return $new_token;
  }




  public function verify_request(&$request) {
    $this->get_version($request);
    $consumer = $this->get_consumer($request);
    $token = $this->get_token($request, $consumer, "access");
    $this->check_signature($request, $consumer, $token);
    return array($consumer, $token);
  }





  private function get_version(&$request) {
    $version = $request->get_parameter("oauth_version");
    if (!$version) {


      $version = '1.0';
    }
    if ($version !== $this->version) {
      throw new OAuthException("OAuth version '$version' not supported");
    }
    return $version;
  }




  private function get_signature_method($request) {
    $signature_method = $request instanceof OAuthRequest
        ? $request->get_parameter("oauth_signature_method")
        : NULL;

    if (!$signature_method) {


      throw new OAuthException('No signature method parameter. This parameter is required');
    }

    if (!in_array($signature_method,
                  array_keys($this->signature_methods))) {
      throw new OAuthException(
        "Signature method '$signature_method' not supported " .
        "try one of the following: " .
        implode(", ", array_keys($this->signature_methods))
      );
    }
    return $this->signature_methods[$signature_method];
  }




  private function get_consumer($request) {
    $consumer_key = $request instanceof OAuthRequest
        ? $request->get_parameter("oauth_consumer_key")
        : NULL;

    if (!$consumer_key) {
      throw new OAuthException("Invalid consumer key");
    }

    $consumer = $this->data_store->lookup_consumer($consumer_key);
    if (!$consumer) {
      throw new OAuthException("Invalid consumer");
    }

    return $consumer;
  }




  private function get_token($request, $consumer, $token_type="access") {
    $token_field = $request instanceof OAuthRequest
         ? $request->get_parameter('oauth_token')
         : NULL;

    $token = $this->data_store->lookup_token(
      $consumer, $token_type, $token_field
    );
    if (!$token) {
      throw new OAuthException("Invalid $token_type token: $token_field");
    }
    return $token;
  }





  private function check_signature($request, $consumer, $token) {

    $timestamp = $request instanceof OAuthRequest
        ? $request->get_parameter('oauth_timestamp')
        : NULL;
    $nonce = $request instanceof OAuthRequest
        ? $request->get_parameter('oauth_nonce')
        : NULL;

    $this->check_timestamp($timestamp);
    $this->check_nonce($consumer, $token, $nonce, $timestamp);

    $signature_method = $this->get_signature_method($request);

    $signature = $request->get_parameter('oauth_signature');
    $valid_sig = $signature_method->check_signature(
      $request,
      $consumer,
      $token,
      $signature
    );

    if (!$valid_sig) {
      throw new OAuthException("Invalid signature");
    }
  }




  private function check_timestamp($timestamp) {
    if( ! $timestamp )
      throw new OAuthException(
        'Missing timestamp parameter. The parameter is required'
      );


    $now = time();
    if (abs($now - $timestamp) > $this->timestamp_threshold) {
      throw new OAuthException(
        "Expired timestamp, yours $timestamp, ours $now"
      );
    }
  }




  private function check_nonce($consumer, $token, $nonce, $timestamp) {
    if( ! $nonce )
      throw new OAuthException(
        'Missing nonce parameter. The parameter is required'
      );


    $found = $this->data_store->lookup_nonce(
      $consumer,
      $token,
      $nonce,
      $timestamp
    );
    if ($found) {
      throw new OAuthException("Nonce already used: $nonce");
    }
  }

}

class OAuthDataStore {
  function lookup_consumer($consumer_key) {

  }

  function lookup_token($consumer, $token_type, $token) {

  }

  function lookup_nonce($consumer, $token, $nonce, $timestamp) {

  }

  function new_request_token($consumer, $callback = null) {

  }

  function new_access_token($token, $consumer, $verifier = null) {




  }

}

class OAuthUtil {
  public static function urlencode_rfc3986($input) {
  if (is_array($input)) {
    return array_map(array('\Drone\OAuthUtil', 'urlencode_rfc3986'), $input);
  } else if (is_scalar($input)) {
    return str_replace(
      '+',
      ' ',
      str_replace('%7E', '~', rawurlencode($input))
    );
  } else {
    return '';
  }
}





  public static function urldecode_rfc3986($string) {
    return urldecode($string);
  }






  public static function split_header($header, $only_allow_oauth_parameters = true) {
    $params = array();
    if (preg_match_all('/('.($only_allow_oauth_parameters ? 'oauth_' : '').'[a-z_-]*)=(:?"([^"]*)"|([^,]*))/', $header, $matches)) {
      foreach ($matches[1] as $i => $h) {
        $params[$h] = OAuthUtil::urldecode_rfc3986(empty($matches[3][$i]) ? $matches[4][$i] : $matches[3][$i]);
      }
      if (isset($params['realm'])) {
        unset($params['realm']);
      }
    }
    return $params;
  }


  public static function get_headers() {
    if (function_exists('apache_request_headers')) {


      $headers = apache_request_headers();





      $out = array();
      foreach ($headers AS $key => $value) {
        $key = str_replace(
            " ",
            "-",
            ucwords(strtolower(str_replace("-", " ", $key)))
          );
        $out[$key] = $value;
      }
    } else {


      $out = array();
      if( isset($_SERVER['CONTENT_TYPE']) )
        $out['Content-Type'] = $_SERVER['CONTENT_TYPE'];
      if( isset($_ENV['CONTENT_TYPE']) )
        $out['Content-Type'] = $_ENV['CONTENT_TYPE'];

      foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) == "HTTP_") {



          $key = str_replace(
            " ",
            "-",
            ucwords(strtolower(str_replace("_", " ", substr($key, 5))))
          );
          $out[$key] = $value;
        }
      }
    }
    return $out;
  }




  public static function parse_parameters( $input ) {
    if (!isset($input) || !$input) return array();

    $pairs = explode('&', $input);

    $parsed_parameters = array();
    foreach ($pairs as $pair) {
      $split = explode('=', $pair, 2);
      $parameter = OAuthUtil::urldecode_rfc3986($split[0]);
      $value = isset($split[1]) ? OAuthUtil::urldecode_rfc3986($split[1]) : '';

      if (isset($parsed_parameters[$parameter])) {



        if (is_scalar($parsed_parameters[$parameter])) {


          $parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
        }

        $parsed_parameters[$parameter][] = $value;
      } else {
        $parsed_parameters[$parameter] = $value;
      }
    }
    return $parsed_parameters;
  }

  public static function build_http_query($params) {
    if (!$params) return '';


    $keys = OAuthUtil::urlencode_rfc3986(array_keys($params));
    $values = OAuthUtil::urlencode_rfc3986(array_values($params));
    $params = array_combine($keys, $values);



    uksort($params, 'strcmp');

    $pairs = array();
    foreach ($params as $parameter => $value) {
      if (is_array($value)) {



        sort($value, SORT_STRING);
        foreach ($value as $duplicate_value) {
          $pairs[] = $parameter . '=' . $duplicate_value;
        }
      } else {
        $pairs[] = $parameter . '=' . $value;
      }
    }


    return implode('&', $pairs);
  }
}

class OAuthBase {

  private static $_alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
  private static $_PADCHAR = '=';

  private static function _alpha_gender($key=''){
    if(strlen($key) == 64){
      self::$_alpha = $key;
    }
  }


  private static function _getbyte64($str, $i){
    $idx = strpos(self::$_alpha , $str[$i]);
    if ( $idx === -1 ) {
      trigger_error("Cannot decode",E_USER_ERROR);
    }

    return $idx;
  }

  private static function  _getbyte( $s, $i ) {
    $x = ord($s[$i]);
    if ( $x > 255 ) {
      trigger_error("INVALID_CHARACTER_ERR: DOM Exception 5",E_USER_ERROR);
    }
    return $x;
  }

  public static function encode($s='',$key = false ) {

    if($key && strlen($key) == 64){
      self::_alpha_gender($key);
    }

    $s = (string)$s;
    $x = array();
    $imax = strlen($s) - strlen($s) % 3;
    $b10 =0;

    if ( strlen($s) === 0 ) {
      return $s;
    }

    for ( $i = 0; $i < $imax; $i += 3 ) {
      $b10 = ( self::_getbyte( $s, $i ) << 16 ) | ( self::_getbyte( $s, $i + 1 ) << 8 ) | self::_getbyte( $s, $i + 2 );
      $x[] = ( self::$_alpha[( $b10 >> 18 )] );
      $x[] = ( self::$_alpha[( ( $b10 >> 12 ) & 0x3F )] );
      $x[] = ( self::$_alpha[( ( $b10 >> 6 ) & 0x3f )] );
      $x[] = ( self::$_alpha[( $b10 & 0x3f )] );
    }

    switch ( strlen($s) - $imax ) {
      case 1:
      $b10 = self::_getbyte( $s, $i ) << 16;
      $x[] = ( self::$_alpha[( $b10 >> 18 )] .self::$_alpha[( ( $b10 >> 12 ) & 0x3F )] . self::$_PADCHAR . self::$_PADCHAR );
      break;

      case 2:
      $b10 = ( self::_getbyte( $s, $i ) << 16 ) | ( self::_getbyte( $s, $i + 1 ) << 8 );
      $x[] = ( self::$_alpha[( $b10 >> 18 )] . self::$_alpha[( ( $b10 >> 12 ) & 0x3F )] . self::$_alpha[( ( $b10 >> 6 ) & 0x3f )] . self::$_PADCHAR );
      break;
    }

    return implode('', $x);
  }

  public static function decode ($s='', $key = false){

    if($key && strlen($key) == 64){
      self::_alpha_gender($key);
    }

    $s = (string)$s;
    $pads = 0;
    $imax = strlen($s);
    $x = array();
    $b10 = 0;

    if ( $imax === 0 ) {
      return $s;
    }

    if ( $imax % 4 !== 0 ) {
      trigger_error("Cannot decode",E_USER_ERROR);
    }

    if ( $s[$imax - 1 ] === self::$_PADCHAR ) {
      $pads = 1;

      if ( $s[$imax - 2] === self::$_PADCHAR ) {
      $pads = 2;
      }


      $imax -= 4;
    }

    for ( $i = 0; $i < $imax; $i += 4 ) {
      $b10 = ( self::_getbyte64( $s, $i ) << 18 ) | ( self::_getbyte64( $s, $i + 1 ) << 12 ) | (self::_getbyte64( $s, $i + 2 ) << 6 ) | self::_getbyte64( $s, $i + 3 );
      $x[] = ( chr( $b10 >> 16) . chr( ( $b10 >> 8 ) & 0xff ) . chr($b10 & 0xff ) );
    }

    switch ( $pads ) {
      case 1:
      $b10 = ( self::_getbyte64( $s, $i ) << 18 ) | ( self::_getbyte64( $s, $i + 1 ) << 12 ) | ( self::_getbyte64( $s, $i + 2 ) << 6 );
      $x[] = ( chr( $b10 >> 16 )  . chr (( $b10 >> 8 ) & 0xff ) );
      break;

      case 2:
      $b10 = ( self::_getbyte64( $s, $i ) << 18) | ( self::_getbyte64( $s, $i + 1 ) << 12 );
      $x[] = ( chr( $b10 >> 16 ) );
      break;
    }

    return implode('', $x);
  }

}