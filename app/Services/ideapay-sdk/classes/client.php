<?php

namespace Pay\Classes;
use Exception;
use GuzzleHttp;

/**
 * Client Handler
 *
 * @package Pay\Classes
 */
class Client {

    /**
     * @var string $id
     */
    protected $id;

    /**
     * @var string $secret
     */
    protected $secret;

    /**
     * @var string $access_token
     */
    protected $access_token;

    /**
     * @var string $expires_in
     */
    protected $expires_in;

    /**
     * @var string $token_type
     */
    protected $token_type;

    /**
     * @var string $live_uri
     */
    private $live_uri = 'https://api.ideapay.ph/';

    /**
     * @var string $sandbox_uri
     */
    private $sandbox_uri = 'https://sandbox.ideapay.ph/';

    /**
     * @var bool $sandbox
     */
    private $sandbox = false;

    /**
     * @var object $client
     */
    private $client;

    /**
     * @var string $grant
     */
    private $grant = 'client_credentials';

    /**
     * @var string $origin
     */
    private $origin = null;

    /**
     * @var string $endpoint
     */
    protected $endpoint = null;

    /**
     * @var object $instance
     */
    protected static $instance = null;

    /**
     * @var array $default_headers
     */
    protected $default_headers = array(
        'Accept'        =>  'application/json'
    );

    /**
     * @var array $headers
     */
    protected $headers = array();

    /**
     * @var array $payload
     */
    protected $payload = array();

    /**
     * @var string $payment_id
     */
    public $payment_id = '';

    /**
     * Client constructor.
     *
     * @param array $args
     * @return Client $this
     */
    public function __construct($args = array()) {
        $this->_init_args($args);
        $this->_init_session();
        $this->live();

        return $this;
    }

    /**
     * Toggle client to use live configuration.
     */
    public function live() {
        $this->sandbox = false;
        $this->client = new GuzzleHttp\Client(['base_uri' => $this->live_uri . 'oauth', 'allow_redirects' => ['track_redirects' => true]]);
    }

    /**
     * Toggle client to use sandbox configuration.
     */
    public function sandbox() {
        $this->sandbox = true;
        $this->client = new GuzzleHttp\Client(['base_uri' => $this->sandbox_uri . 'oauth', 'allow_redirects' => ['track_redirects' => true]]);
    }

    /**
     * Check if client uses sandbox configuration.
     *
     * @return bool
     */
    public function is_sandbox() {
        return $this->sandbox;
    }

    /**
     * Fetch client instance.
     *
     * @param mixed $args
     * @return object|Client
     */
    public static function instance($args) {
        if(is_null(self::$instance)) {
            self::$instance = new self($args);
        }

        return self::$instance;
    }

    /**
     * Initialize class arguments.
     *
     * @param mixed $args
     * @return Client $this
     */
    private function _init_args($args) {
        if(is_array($args)) {
            if(! empty($args['client_id'])) $this->id = $args['client_id'];

            if(! empty($args['client_secret'])) $this->secret = $args['client_secret'];

            if(! empty($args['headers']) && is_array($args['headers'])) {
                $this->headers = array_merge($this->default_headers, $args['headers']);
            } else {
                $this->headers = $this->default_headers;
            }
        }

        return $this;
    }

    /**
     * Initiate client session values.
     */
    private function _init_session() {
        if((function_exists('session_status') && session_status() != PHP_SESSION_ACTIVE) || ! session_id()) {
            session_start();
        }

        $this->access_token = isset($_SESSION[$this->id . '_access_token']) ? $_SESSION[$this->id . '_access_token'] : null;
        $this->token_type = isset($_SESSION[$this->id . '_token_type']) ? $_SESSION[$this->id . '_token_type'] : null;
        $this->expires_in = isset($_SESSION[$this->id . '_expires_in']) ? $_SESSION[$this->id . '_expires_in'] : null;
    }

    /**
     * Fetch client credentials.
     *
     * @return array
     */
    public function get_credentials() {
        return array(
            'client_id'     =>  $this->id,
            'client_secret' =>  $this->secret
        );
    }

    /**
     * Set client credentials.
     *
     * @param string $id
     * @param string $secret
     * @return Client $this
     */
    public function set_credentials($id, $secret) {
        $this->set_client_id($id);
        $this->set_client_secret($secret);

        return $this;
    }

    /**
     * Fetch client ID
     *
     * @return string
     */
    public function get_client_id() {
        return $this->id;
    }

    /**
     * Set client ID
     *
     * @param string $client_id
     */
    public function set_client_id($client_id) {
        $this->id = $client_id;
    }

    /**
     * Fetch client secret
     *
     * @return string
     */
    public function get_client_secret() {
        return $this->secret;
    }

    /**
     * Set client secret
     *
     * @param string $client_secret
     */
    public function set_client_secret($client_secret) {
        $this->secret = $client_secret;
    }

    /**
     * Fetch access token
     *
     * @return string
     */
    public function get_access_token() {
        return $this->access_token;
    }

    /**
     * Check if access token exists
     *
     * @return bool
     */
    public function has_access_token() {
        return isset($this->access_token);
    }

    /**
     * Set request origin
     *
     * @param string $origin
     */
    public function set_origin($origin = null) {
        if(! empty($origin)) {
            $this->origin = explode('?', $origin)[0];
        }
    }

    /**
     * Fetch request origin
     *
     * @return string
     */
    public function get_origin() {
        if($this->has_origin()) {
            return $this->origin;
        }

        $origin = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $origin = explode('?', $origin)[0];

        return $origin;
    }

    /**
     * Check if request origin is set
     *
     * @return bool
     */
    private function has_origin() {
        return ! empty($this->origin);
    }

    /**
     * Attach token to Authorization header
     *
     * @return array
     * @throws Exception
     */
    private function _get_authorization() {
        if(! $this->has_access_token()) {
            $this->authorize();
        }

        $h = $this->headers;
        $h['Authorization'] = $this->token_type . ' ' . $this->access_token;

        return $h;
    }

    /**
     * Execute OAuth request to resource provider and retrieve tokens.
     *
     * @param array $args
     * @return Client $this
     * @throws Exception
     */
    public function authorize($args = array()) {
        $this->_init_args($args);

        if(empty($this->id)) {
            throw new Exception('Client ID required');
        }

        if(empty($this->secret)) {
            throw new Exception('Client Secret required');
        }

        $headers = $this->headers;
        $headers['Authorization'] = 'Basic ' . base64_encode($this->id . ':' . $this->secret);

        $response = $this->client->post('oauth/token', array(
            'headers'   =>  $headers,
            'form_params'   =>  array('grant_type' => $this->grant)
        ));

        $json = json_decode($response->getBody());

        if(isset($json->error)) {
            header('Location: ' . $json->error_uri);
            exit;
        } else {
            $this->access_token = $json->access_token;
            $this->expires_in = $json->expires_in;
            $this->token_type = $json->token_type;

            // save to session for future access
            $_SESSION[$this->id . '_access_token'] = $this->access_token;
            $_SESSION[$this->id . '_token_type'] = $this->token_type;
            $_SESSION[$this->id . '_expires_in'] = $this->expires_in;
        }

        return $this;
    }

    /**
     * Fetch payment ID
     *
     * @return string
     */
    public function get_payment_id() {
        return $this->payment_id;
    }

    /**
     * Set payment ID
     *
     * @param string $payment_id
     */
    public function set_payment_id($payment_id) {
        $this->payment_id = $payment_id;
    }

    /**
     * Send request to resource provider
     *
     * @return string
     * @throws Exception
     */
    public function send() {
        $origin = $this->get_origin();

        if($this->endpoint == null) {
            throw new Exception('Endpoint missing');
        }

        try {
            $headers = $this->_get_authorization();
            $headers['Accept'] = 'application/json';
            $headers['Origin'] = $origin;

            $response = $this->client->post($this->endpoint, array(
                'headers'       =>  $headers,
                'form_params'   =>  $this->payload
            ));

            $json = json_decode($response->getBody());

            $ret = $this->callback($json);

            if($ret) {
                return $ret;
            } else {
                $redirect = '/#invalid_request';
            }
        } catch (GuzzleHttp\Exception\ClientException $e) {
            /**
             * error - error code
             * error_description - verbose
             */
            $json = json_decode(strval($e->getResponse()->getBody()));
            $redirect = $origin . '#' . $json->error;
        } catch (GuzzleHttp\Exception\ConnectException $e) {
            $redirect = $origin . '#connection_refused';
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $redirect;
    }
}