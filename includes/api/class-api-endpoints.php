<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class API_Endpoints {

    protected static $instance = null;
    private $namespace = 'pre-order-ultra/v1';

    private function __construct() {
        // Register REST routes
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Get Instance
     *
     * @return API_Endpoints
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register REST API Routes
     */
    public function register_routes() {
        // Route: /wp-json/pre-order-ultra/v1/subscriptions
        register_rest_route( $this->namespace, '/subscriptions', array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_subscription' ),
                'permission_callback' => array( $this, 'permissions_check' ),
                'args'                => $this->get_subscription_args(),
            ),
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_subscriptions' ),
                'permission_callback' => array( $this, 'permissions_check' ),
                'args'                => array(
                    'product_id' => array(
                        'validate_callback' => function( $param, $request, $key ) {
                            return is_numeric( $param );
                        }
                    ),
                ),
            ),
        ) );

        // Route: /wp-json/pre-order-ultra/v1/subscriptions/<id>
        register_rest_route( $this->namespace, '/subscriptions/(?P<id>\d+)', array(
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_subscription' ),
                'permission_callback' => array( $this, 'permissions_check' ),
                'args'                => array(
                    'id' => array(
                        'validate_callback' => function( $param, $request, $key ) {
                            return is_numeric( $param );
                        }
                    ),
                    'name' => array(
                        'required'          => false,
                        'validate_callback' => function( $param, $request, $key ) {
                            return is_string( $param ) && ! empty( $param );
                        }
                    ),
                    'email' => array(
                        'required'          => false,
                        'validate_callback' => function( $param, $request, $key ) {
                            return filter_var( $param, FILTER_VALIDATE_EMAIL );
                        }
                    ),
                    'phone_number' => array(
                        'required'          => false,
                        'validate_callback' => function( $param, $request, $key ) {
                            return is_string( $param );
                        }
                    ),
                ),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_subscription' ),
                'permission_callback' => array( $this, 'permissions_check' ),
                'args'                => array(
                    'id' => array(
                        'validate_callback' => function( $param, $request, $key ) {
                            return is_numeric( $param );
                        }
                    ),
                ),
            ),
        ) );

        // Route: /wp-json/pre-order-ultra/v1/subscriptions/<id>/mark-notified
        register_rest_route( $this->namespace, '/subscriptions/(?P<id>\d+)/mark-notified', array(
            array(
                'methods'             => WP_REST_Server::EDITABLE, // Accepts PUT/PATCH
                'callback'            => array( $this, 'mark_subscription_notified' ),
                'permission_callback' => array( $this, 'permissions_check' ),
                'args'                => array(
                    'id' => array(
                        'validate_callback' => function( $param, $request, $key ) {
                            return is_numeric( $param );
                        }
                    ),
                ),
            ),
        ) );

        // Route: /wp-json/pre-order-ultra/v1/subscriptions/bulk-mark-notified
        register_rest_route( $this->namespace, '/subscriptions/bulk-mark-notified', array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'bulk_mark_notified' ),
                'permission_callback' => array( $this, 'permissions_check' ),
                'args'                => array(
                    'ids' => array(
                        'required'          => true,
                        'type'              => 'array',
                        'validate_callback' => function( $param, $request, $key ) {
                            foreach ( $param as $id ) {
                                if ( ! is_numeric( $id ) ) {
                                    return false;
                                }
                            }
                            return true;
                        }
                    ),
                ),
            ),
        ) );

        register_rest_route( $this->namespace, '/subscriptions/unsubscribe', array(
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'unsubscribe' ),
                'permission_callback' => array( $this, 'permissions_check_unsubscribe' ),
                'args'                => array(
                    'email'      => array(
                        'required'          => true,
                        'validate_callback' => function( $param, $request, $key ) {
                            return filter_var( $param, FILTER_VALIDATE_EMAIL );
                        }
                    ),
                    'product_id' => array(
                        'required'          => true,
                        'validate_callback' => function( $param, $request, $key ) {
                            return is_numeric( $param ) && wc_get_product( intval( $param ) );
                        }
                    ),
                ),
            ),
        ) );

        register_rest_route( $this->namespace, '/subscriptions/statistics', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_statistics' ),
                'permission_callback' => array( $this, 'permissions_check' ),
            ),
        ) );
    }

    /**
     * Define Subscription Arguments for Creation
     *
     * @return array
     */
    private function get_subscription_args() {
        return array(
            'name'         => array(
                'required'          => true,
                'validate_callback' => function( $param, $request, $key ) {
                    return is_string( $param ) && ! empty( $param );
                }
            ),
            'email'        => array(
                'required'          => true,
                'validate_callback' => function( $param, $request, $key ) {
                    return filter_var( $param, FILTER_VALIDATE_EMAIL );
                }
            ),
            'phone_number' => array(
                'required'          => false,
                'validate_callback' => function( $param, $request, $key ) {
                    return is_string( $param );
                }
            ),
            'product_id'   => array(
                'required'          => true,
                'validate_callback' => function( $param, $request, $key ) {
                    return is_numeric( $param ) && wc_get_product( intval( $param ) );
                }
            ),
        );
    }

    /**
     * Permissions Check for API Access
     *
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function permissions_check( $request ) {
        // For public access, you might want to adjust permissions accordingly.
        // For now, we'll allow public access to subscribe.
        // Modify this if certain endpoints should require authentication.

        // Example: Allowing only authenticated users to delete subscriptions
        $route = $request->get_route();
        if ( strpos( $route, '/subscriptions/' ) !== false && $request->get_method() === 'DELETE' ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                return new WP_Error( 'rest_forbidden', __( 'You cannot perform this action.', 'pre-order-ultra' ), array( 'status' => 403 ) );
            }
        }

        return true;
    }

    /**
     * Permissions check for the unsubscribe endpoint.
     *
     * @param WP_REST_Request $request The REST API request.
     * @return bool|WP_Error True if the request has permission, WP_Error otherwise.
     */
    public function permissions_check_unsubscribe( $request ) {
        // Since the unsubscribe endpoint is intended for public access,
        // ensure that the request contains the necessary parameters and that they are valid.
        // Additional security measures like rate limiting can be implemented here if needed.

        // Optionally, you can implement simple rate limiting to prevent abuse.
        // For example, limit the number of unsubscribe requests from a single IP address.
        /*
        $ip_address = $request->get_header( 'X-Forwarded-For' ) ?: $request->get_header( 'REMOTE_ADDR' );
        $transient_key = 'pre_order_ultra_unsubscribe_' . md5( $ip_address );
        $attempts = get_transient( $transient_key );

        if ( $attempts && $attempts >= 5 ) {
            return new WP_Error(
                'rest_rate_limit_exceeded',
                __( 'Rate limit exceeded. Please try again later.', 'pre-order-ultra' ),
                array( 'status' => 429 )
            );
        }

        // Increment the attempt count
        set_transient( $transient_key, $attempts ? $attempts + 1 : 1, MINUTE_IN_SECONDS * 5 );
        */

        // Ensure that the required parameters are present
        $params = $request->get_params();

        if ( empty( $params['email'] ) || empty( $params['product_id'] ) ) {
            return new WP_Error(
                'rest_missing_parameters',
                __( 'Missing email or product_id parameter.', 'pre-order-ultra' ),
                array( 'status' => 400 )
            );
        }

        // Validate email format
        if ( ! is_email( $params['email'] ) ) {
            return new WP_Error(
                'rest_invalid_email',
                __( 'Invalid email format.', 'pre-order-ultra' ),
                array( 'status' => 400 )
            );
        }

        // Validate product_id is a positive integer
        if ( ! is_numeric( $params['product_id'] ) || intval( $params['product_id'] ) <= 0 ) {
            return new WP_Error(
                'rest_invalid_product_id',
                __( 'Invalid product_id parameter.', 'pre-order-ultra' ),
                array( 'status' => 400 )
            );
        }

        // Optional: Verify that the product exists
        if ( ! wc_get_product( intval( $params['product_id'] ) ) ) {
            return new WP_Error(
                'rest_product_not_found',
                __( 'Product not found.', 'pre-order-ultra' ),
                array( 'status' => 404 )
            );
        }

        // All checks passed; allow the request
        return true;
    }


    /**
     * Create a New Subscription
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function create_subscription( $request ) {
        $parameters = $request->get_json_params();

        $subscription_manager = Subscription_Manager::get_instance();

        // Check if user is logged in
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();

            // Check for existing subscription
            if ( $subscription_manager->subscription_exists( $user_id, intval( $parameters['product_id'] ) ) ) {
                return new WP_REST_Response( array( 'message' => __( 'You have already subscribed to this product.', 'pre-order-ultra' ) ), 200 );
            }

            $data = array(
                'user_id'      => $user_id,
                'name'         => sanitize_text_field( $parameters['name'] ),
                'email'        => sanitize_email( $parameters['email'] ),
                'phone_number' => isset( $parameters['phone_number'] ) ? sanitize_text_field( $parameters['phone_number'] ) : '',
                'product_id'   => intval( $parameters['product_id'] ),
            );
        } else {
            // Guest subscription
            $subscription_manager = Subscription_Manager::get_instance();

            if ( $subscription_manager->guest_subscription_exists( sanitize_email( $parameters['email'] ), intval( $parameters['product_id'] ) ) ) {
                return new WP_REST_Response( array( 'message' => __( 'You have already subscribed to this product.', 'pre-order-ultra' ) ), 200 );
            }

            $data = array(
                'name'         => sanitize_text_field( $parameters['name'] ),
                'email'        => sanitize_email( $parameters['email'] ),
                'phone_number' => isset( $parameters['phone_number'] ) ? sanitize_text_field( $parameters['phone_number'] ) : '',
                'product_id'   => intval( $parameters['product_id'] ),
            );
        }

        $subscription_id = $subscription_manager->add_subscription( $data );

        if ( $subscription_id ) {
            return new WP_REST_Response( array( 'message' => __( 'Thank you! You will be notified when this product is available.', 'pre-order-ultra' ) ), 201 );
        } else {
            return new WP_REST_Response( array( 'message' => __( 'An error occurred. Please try again later.', 'pre-order-ultra' ) ), 500 );
        }
    }

    /**
     * Retrieve Subscriptions
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_subscriptions( $request ) {
        $product_id = $request->get_param( 'product_id' );

        $subscription_manager = Subscription_Manager::get_instance();
        $subscriptions = $subscription_manager->get_subscriptions_by_product( intval( $product_id ) );

        $data = array();

        foreach ( $subscriptions as $subscription ) {
            $data[] = array(
                'id'              => $subscription->id,
                'user_id'         => $subscription->user_id,
                'name'            => $subscription->name,
                'email'           => $subscription->email,
                'phone_number'    => $subscription->phone_number,
                'product_id'      => $subscription->product_id,
                'subscription_date'=> $subscription->subscription_date,
                'status'          => $subscription->status,
            );
        }

        return new WP_REST_Response( $data, 200 );
    }

    public function update_subscription( $request ) {
        $subscription_id = intval( $request->get_param( 'id' ) );
        $parameters = $request->get_json_params();
    
        $subscription_manager = Subscription_Manager::get_instance();
    
        // Retrieve existing subscription
        $subscription = $subscription_manager->get_subscription( $subscription_id );
        if ( ! $subscription ) {
            return new WP_REST_Response( array( 'message' => __( 'Subscription not found.', 'pre-order-ultra' ) ), 404 );
        }
    
        // Prepare data for update
        $data = array();
        if ( isset( $parameters['name'] ) ) {
            $data['name'] = sanitize_text_field( $parameters['name'] );
        }
        if ( isset( $parameters['email'] ) ) {
            $data['email'] = sanitize_email( $parameters['email'] );
        }
        if ( isset( $parameters['phone_number'] ) ) {
            $data['phone_number'] = sanitize_text_field( $parameters['phone_number'] );
        }
    
        // Update subscription
        $updated = $subscription_manager->update_subscription_details( $subscription_id, $data );
    
        if ( $updated ) {
            return new WP_REST_Response( array( 'message' => __( 'Subscription updated successfully.', 'pre-order-ultra' ) ), 200 );
        } else {
            return new WP_REST_Response( array( 'message' => __( 'Failed to update subscription.', 'pre-order-ultra' ) ), 500 );
        }
    }

    /**
     * Delete a Subscription
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function delete_subscription( $request ) {
        $subscription_id = intval( $request->get_param( 'id' ) );

        $subscription_manager = Subscription_Manager::get_instance();

        // Update subscription status to 'deleted'
        $updated = $subscription_manager->update_subscription_status( $subscription_id, 'deleted' );

        if ( $updated ) {
            return new WP_REST_Response( array( 'message' => __( 'Subscription deleted successfully.', 'pre-order-ultra' ) ), 200 );
        } else {
            return new WP_REST_Response( array( 'message' => __( 'Failed to delete subscription.', 'pre-order-ultra' ) ), 500 );
        }
    }

    /**
     * Mark a Subscription as Notified
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function mark_subscription_notified( $request ) {
        $subscription_id = intval( $request->get_param( 'id' ) );

        $subscription_manager = Subscription_Manager::get_instance();

        // Update subscription status to 'notified'
        $updated = $subscription_manager->update_subscription_status( $subscription_id, 'notified' );

        if ( $updated ) {
            return new WP_REST_Response( array( 'message' => __( 'Subscription marked as notified.', 'pre-order-ultra' ) ), 200 );
        } else {
            return new WP_REST_Response( array( 'message' => __( 'Failed to update subscription status.', 'pre-order-ultra' ) ), 500 );
        }
    }

    // 
    public function bulk_mark_notified( $request ) {
        $ids = array_map( 'intval', $request->get_param( 'ids' ) );

        $subscription_manager = Subscription_Manager::get_instance();
        $results = array();

        foreach ( $ids as $id ) {
            $updated = $subscription_manager->update_subscription_status( $id, 'notified' );
            if ( $updated ) {
                $results[] = array( 'id' => $id, 'status' => 'notified' );
            } else {
                $results[] = array( 'id' => $id, 'status' => 'failed' );
            }
        }

        return new WP_REST_Response( array( 'results' => $results ), 200 );
    }

    // 
    public function unsubscribe( $request ) {
        $email = sanitize_email( $request->get_param( 'email' ) );
        $product_id = intval( $request->get_param( 'product_id' ) );

        $subscription_manager = Subscription_Manager::get_instance();

        $deleted = $subscription_manager->delete_subscription_by_email_product( $email, $product_id );

        if ( $deleted ) {
            return new WP_REST_Response( array( 'message' => __( 'You have been unsubscribed successfully.', 'pre-order-ultra' ) ), 200 );
        } else {
            return new WP_REST_Response( array( 'message' => __( 'Subscription not found or already unsubscribed.', 'pre-order-ultra' ) ), 404 );
        }
    }

    public function get_statistics( $request ) {
        $subscription_manager = Subscription_Manager::get_instance();
    
        $total = $subscription_manager->get_total_subscriptions();
        $active = $subscription_manager->get_status_count( 'active' );
        $notified = $subscription_manager->get_status_count( 'notified' );
        $deleted = $subscription_manager->get_status_count( 'deleted' );
    
        $stats = array(
            'total_subscriptions'    => $total,
            'active_subscriptions'   => $active,
            'notified_subscriptions' => $notified,
            'deleted_subscriptions'  => $deleted,
        );
    
        return new WP_REST_Response( $stats, 200 );
    }
}
