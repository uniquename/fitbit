<?php
/**
 * Fitbit API Client
 *
 * Adopted from - https://github.com/heyitspavel/fitbitphp
 */

class FitBit {

  /**
   * API Constants
   */
  private $authHost = 'www.fitbit.com';
  private $apiHost = 'api.fitbit.com';

  private $baseApiUrl;
  private $authUrl;
  private $requestTokenUrl;
  private $accessTokenUrl;

  /**
   * Class Variables
   */
  // @todo: this doesn't have to be public, but allow
  // outside modules to work with it or allow
  // necessary commands.
  public $oauth;
  protected $oauthToken, $oauthSecret;

  protected $userId = '-';

  // Defaults to English measurements.
  protected $metric = 1;
  protected $userAgent = 'Fitbit';
  protected $debug;

  protected $clientDebug;


  /**
   * @param string $consumerKey Application consumer key for Fitbit API
   * @param string $consumerSecret Application secret
   * @param int $debug Debug mode (0/1) enables OAuth internal debug
   * @param string $userAgent User-agent to use in API calls
   */
  public function __construct($consumerKey, $consumerSecret, $debug = 1, $userAgent = null) {
    // @todo: Allow https and httpsApi to be set here.
    $this->initUrls();

    $this->consumerKey = $consumerKey;
    $this->consumerSecret = $consumerSecret;
    $this->oauth = new OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);

    $this->debug = $debug;
    if (isset($userAgent)) {
      $this->userAgent = $userAgent;
    }

    if ($debug) {
      $this->oauth->enableDebug();
    }
  }

  /**
   * Initialize the relevant fitbit urls.
   *
   * @param boolean $https
   *  Whether ot not we are using ssl urls for oauth.
   * @param boolean $httpsApi
   *  Whether ot not we are using ssl urls for basic api requests.
   */
  private function initUrls($https = TRUE, $httpsApi = FALSE) {
    if ($httpsApi) {
      $this->baseApiUrl = 'https://' . $this->apiHost . '/1/';
    }
    else {
      $this->baseApiUrl = 'http://' . $this->apiHost . '/1/';
    }

    $protocol = $https ? 'https://' : 'http://';
    $this->authUrl = $protocol . $this->authHost . '/oauth/authorize';
    $this->requestTokenUrl = $protocol . $this->apiHost . '/oauth/request_token';
    $this->accessTokenUrl = $protocol . $this->apiHost . '/oauth/access_token';
  }

  /**
   * Get one single parameter from the object.
   *
   * @param string $type
   *  The name of the parameter to grab.
   *
   * @return mixed
   *  The parameter if it exists, otherwise NULL.
   */
  public function getParam($type) {
    return isset($this->{$type}) ? $this->{$type} : NULL;
  }

  /**
   * @return OAuth debugInfo object for previous call. Debug should be enabled in __construct
   */
  public function oauthDebug() {
    return $this->oauth->debugInfo;
  }

  /**
   * @return OAuth debugInfo object for previous client_customCall. Debug should be enabled in __construct
   */
  public function clientOauthDebug() {
    return $this->clientDebug;
  }

  /**
   * Sets OAuth token/secret. Use if library used in internal calls without session handling
   *
   * @param  $token
   * @param  $secret
   * @return void
   */
  public function setOAuthDetails($token, $secret) {
    $this->oauthToken = $token;
    $this->oauthSecret = $secret;

    $this->oauth->setToken($this->oauthToken, $this->oauthSecret);
  }

  /**
   * Set Fitbit userId for future API calls
   *
   * @param  $userId 'XXXXX'
   * @return void
   */
  public function setUser($userId) {
    $this->userId = $userId;
  }

  /**
   * Set Unit System for all future calls (see http://wiki.fitbit.com/display/API/API-Unit-System)
   * 0 (Metric), 1 (en_US), 2 (en_GB)
   *
   * @param int $metric
   * @return void
   */
  public function setMetric($metric) {
    $this->metric = $metric;
  }

  /**
   * Make custom call to any API endpoint
   *
   * @param string $url Endpoint url after '.../1/'
   * @param array $parameters Request parameters
   * @param string $method (OAUTH_HTTP_METHOD_GET, OAUTH_HTTP_METHOD_POST, OAUTH_HTTP_METHOD_PUT, OAUTH_HTTP_METHOD_DELETE)
   * @param array $userHeaders Additional custom headers
   * @return object
   */
  public function request($url, $parameters, $method, $userHeaders = array()) {
    $headers = $this->getHeaders();
    $headers = array_merge($headers, $userHeaders);

    try {
      $this->oauth->fetch($this->baseApiUrl . $url, $parameters, $method, $headers);
    }
    catch (Exception $e) {
    }

    $response = $this->oauth->getLastResponse();
    $responseInfo = $this->oauth->getLastResponseInfo();
    $fullResponse = (object) array(
      'code' => $responseInfo['http_code'],
      'response' => $response,
    );
    return $fullResponse;
  }

  /**
   * Make custom call to any API endpoint, signed with consumerKey only (on behalf of CLIENT)
   *
   * @param string $url Endpoint url after '.../1/'
   * @param array $parameters Request parameters
   * @param string $method (OAUTH_HTTP_METHOD_GET, OAUTH_HTTP_METHOD_POST, OAUTH_HTTP_METHOD_PUT, OAUTH_HTTP_METHOD_DELETE)
   * @param array $userHeaders Additional custom headers
   * @return object
   */
  public function clientRequest($url, $parameters, $method, $userHeaders = array()) {
    $OAuthConsumer = new OAuth($this->consumerKey, $this->consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);

    if ($debug) {
      $OAuthConsumer->enableDebug();
    }

    $headers = $this->getHeaders();
    $headers = array_merge($headers, $userHeaders);

    try {
      $OAuthConsumer->fetch($this->baseApiUrl . $url, $parameters, $method, $headers);
    }
    catch (Exception $e) {
    }

    $response = $OAuthConsumer->getLastResponse();
    $responseInfo = $OAuthConsumer->getLastResponseInfo();
    $this->clientDebug = print_r($OAuthConsumer->debugInfo, true);
    $fullResponse = (object) array(
      'code' => $responseInfo['http_code'],
      'response' => $response,
    );
    return $fullResponse;
  }

  /**
   * @return array
   */
  private function getHeaders() {
    $headers = array();
    $headers['User-Agent'] = $this->userAgent;

    // @todo: Rework how the metrics are set
    if ($this->metric == 1) {
      $headers['Accept-Language'] = 'en_US';
    }
    else if ($this->metric == 2) {
      $headers['Accept-Language'] = 'en_GB';
    }
    return $headers;
  }
}

/**
 * Fitbit API communication exception
 */
class FitBitException extends Exception {
  public $fbMessage = '';
  public $httpcode;

  public function __construct($code, $fbMessage = null, $message = null) {

    $this->fbMessage = $fbMessage;
    $this->httpcode = $code;

    if (isset($fbMessage) && !isset($message)) {
      $message = $fbMessage;
    }

    // @todo: Is this necessary?
    try {
      $code = (int) $code;
    }
    catch (Exception $e) {
      $code = 0;
    }

    parent::__construct($message, $code);
  }
}
