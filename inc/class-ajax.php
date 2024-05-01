<?php

class CPforms_API{
    private $db_handler;

    public function __construct(){
        add_action( 'rest_api_init', array( $this, 'register_cpforms_endpoints' ) );
        
        $this->db_handler = new CPforms_DB();
    }

    function register_cpforms_endpoints(){
        $rest_api_namespace = 'cpforms/v1';

        register_rest_route( $rest_api_namespace, '/submit', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'submit_form_data' ),
        ) );
    
        register_rest_route( $rest_api_namespace, '/data', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'get_form_data' )
        ) );
    }

    function submit_form_data( WP_REST_Request $request ){
        $params = $request->get_params();
        $nonce = $request->get_header('X-CP-Nonce');

         // Verify the nonce
        if ( ! wp_verify_nonce($nonce, 'cpforms_nonce' ) ) {
            return new WP_Error( 'invalid_nonce',
                esc_html__( 'Invalid nonce.', 'cpforms' ), 
                array('status' => 403)
            );
        }

        // Access specific parameters sent in the request
        $name = isset( $params['full_name'] ) ? sanitize_text_field( $params['full_name'] ) : '';
        $email = isset( $params['email'] ) ? sanitize_email( $params['email'] ) : '';
        $message = isset( $params['message'] ) ? sanitize_textarea_field( $params['message'] ) : '';

        // check required fields
        if ( empty( $name ) || empty( $email ) || empty( $message ) ){
            return new WP_Error( 'missing_fields', 
                esc_html__( 'Required Fields Are Missing!', 'cpforms' ), 
                array( 'status' => 400 )
            );
        }

        // check is valid email or not
        if ( ! is_email( $email ) ){
            return new WP_Error( 'invalid_email', 
                esc_html__( 'Sorry Invalid Email!', 'cpforms' ), 
                array( 'status' => 400 )
            );
        }

        // insert into database
        $response = $this->db_handler->insert_data_to_my_table( $name, $email, $message );

        // check wordpress db response
        if ( false === $response ){
            return new WP_Error( 'internal_error', 
                esc_html__( 'Something Went Wrong!', 'cpforms' ), 
                array( 'status' => 500 )
            );
        }

        return rest_ensure_response( $response );
    }

    function get_form_data( WP_REST_Request $request ){

        $nonce = $request->get_header('X-CP-Nonce');

         // Verify the nonce
        if ( ! wp_verify_nonce($nonce, 'cpforms_nonce' ) ) {
            return new WP_Error( 'invalid_nonce',
                esc_html__( 'Invalid nonce.', 'cpforms' ), 
                array('status' => 403)
            );
        }

        $page = $request->get_param('page') ? absint( $request->get_param('page') ) : 1;
        $per_page = $request->get_param('per_page') ? absint( $request->get_param('per_page') ) : 5;
        $orderby = $request->get_param('orderby') ? sanitize_text_field( $request->get_param('orderby') ) : 'id';
        $order = $request->get_param('order') ? sanitize_text_field( $request->get_param('order') ) : 'ASC';
        $search = $request->get_param('search') ? sanitize_text_field( $request->get_param('search') ) : '';

        $results = $this->db_handler->get_my_table_data($page >= 1 ? $page : 1, $per_page, $orderby, $order, $search);

        $response = array(
            'page' => $page,
            'per_page' => $per_page,
            'data' => $results['data'],
            'size' => $results['size'],
        );

        return rest_ensure_response( $response );
    }
}

new CPforms_API();
