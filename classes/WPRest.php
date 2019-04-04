<?php

namespace WordPressRESTAPI;

use GuzzleHttp\Client;

class WPRest {

    /**
     * WPRest Instance
     */
    private static $instance;

    /**
     * Guzzle instance
     */
    private $guzzle;

    /**
     * Options
     */
    private $options;

    /**
     * Error
     *
     * The last requests error information
     */
    private $error;

    /**
     * Query
     *
     * The last URI queried with info about the request
     */
    private $query;

    /**
     * Headers
     *
     * Headers from the last request
     */
    private $headers;

    /**
     * Construct
     */
    private function __construct() {

        // Set Default Options
        $defaults = array(
            'base_uri' => 'https://cms.resknow.net/wp-json'
        );

        // Get options from config
        $options = ( get('site.wprest') ?: array() );

        // Set Options
        $this->options = array_merge($defaults, $options);

        // Create Guzzle HTTP Client instance
        $this->guzzle = new Client($this->options);

    }

    /**
     * Clone
     *
     * Private to prevent non-singleton instances
     */
    private function __clone() {}

    /**
     * Wakeup
     *
     * Private to prevent non-singleton instances
     */
    private function __wakeup() {}

    /**
     * Get
     *
     * @param string $endpoint WP API endpoint
     * @param array $args Additional URL params
     */
    public function get( $endpoint, $args = array() ) {

        // Get the full URL
        $url = $this->make_url($endpoint);

        // Setup Args
        if ( !empty($args) ) {
            $url .= $this->make_args($args);
        }

        // Get the Response from WordPress
        $response = $this->guzzle->get($url);

        // Save the last query array
        $this->query = array(
            'uri' => $url,
            'response' => $response
        );

        // Make sure the response is OK
        if ( $response->getStatusCode() !== 200 ) {

            // Save the error
            $this->error = array(
                'code' => $response->getStatusCode(),
                'response' => $response->getBody()
            );

            return false;
        }

        // Save Headers
        $this->headers = $response->getHeaders();

        // Make response array
        $data = json_decode($response->getBody(), true);

        return ( count($data > 1) ? $data : $data[0] );
    }

    /**
     * Save
     *
     * @param string $endpoint WP API endpoint
     * @param array $data Data to send
     */
    public function save( $endpoint, $data ) {

        // Get the full URL
        $url = $this->make_url($endpoint);

        // Get the Response from WordPress
        $response = $this->guzzle->post($url, array(
            'json' => $data
        ));

        return json_decode($response->getBody(), true);
    }

    /**
     * Get Headers
     */
    public function get_headers() {
        return $this->headers;
    }

    /**
     * Last Query
     *
     * An array containing information about the
     * last query
     */
    public function last_query() {
        return $this->query;
    }

    /**
     * Get Error
     *
     */
    public function get_error() {
        return $this->error;
    }

    /**
     * Make URL
     *
     * @param string $endpoint
     */
    private function make_url( $endpoint ) {
        return sprintf('%s/%s', $this->options['base_uri'], $endpoint);
    }

    /**
     * Make Args
     *
     * @param array $args
     */
    private function make_args( array $args ) {

        // Create query string
        $query_string = '';

        // Add args
        foreach ( $args as $key => $value ) {
            $query_string .= sprintf('&%s=%s', $key, $value);
        }

        // Remove the first &
        $query_string = ltrim($query_string, '&');

        return '?' . $query_string;

    }

    /**
     * Get Instance
     *
     * Returns an instance of this class
     */
    public static function get_instance() {

        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;

    }

}
