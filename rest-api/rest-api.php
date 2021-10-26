<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Metrics_Endpoints
{
    /**
     * @todo Set the permissions your endpoint needs
     * @link https://github.com/DiscipleTools/Documentation/blob/master/theme-core/capabilities.md
     * @var string[]
     */
    public $permissions = [ 'access_contacts', 'dt_all_access_contacts', 'view_project_metrics' ];


    //See https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
    public function add_api_routes() {
        $namespace = 'zume-metrics/v1';

        register_rest_route(
            $namespace, '/endpoint', [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'private_endpoint' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            ]
        );
        register_rest_route(
            $namespace, '/dt-public/public_endpoint', [
                'methods'  => "GET",
                'callback' => [ $this, 'public_endpoint' ],
                'permission_callback' => '__return_true',
            ]
        );
    }


    public function private_endpoint( WP_REST_Request $request ) {

        // @todo run your function here



        return true;
    }

    public function public_endpoint( WP_REST_Request $request ) {

        $list = Zume_App_Heatmap::query_saturation_list();
//        $list2 = Zume_App_Heatmap::query_flat_grid_by_level( "a2" );

        $location_ids_that_count = array_map( function ( $l ) {
            return $l["grid_id"];
        }, $list);

        global $wpdb;
        $distinct_used_ids = $wpdb->get_results( "
            SELECT DISTINCT(pm.meta_value) as grid_id
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->posts p on ( p.ID = pm.post_id AND p.post_type = 'groups' )
            INNER JOIN $wpdb->postmeta pm2 on ( pm.post_id = pm2.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church' )
            WHERE pm.meta_key = 'location_grid'
        ", ARRAY_A );

        $unique_church_locations = 0;
        foreach( $distinct_used_ids as $id_row ){
            if ( in_array( $id_row["grid_id"], $location_ids_that_count ) ){
                $unique_church_locations++;
            }
        }

        //unique locations for trainings that have completed the session 9
        $distinct_training_locations = $wpdb->get_results( "
            SELECT DISTINCT(pm.meta_value) as grid_id
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->posts p on ( p.ID = pm.post_id AND p.post_type = 'trainings' )
            LEFT JOIN $wpdb->postmeta pm2 on ( pm.post_id = pm2.post_id AND pm2.meta_key = 'zume_group_id' )
            INNER JOIN $wpdb->usermeta um on ( um.meta_key = pm2.meta_value )
            WHERE um.meta_value LIKE '%\"session_9\";b:1;%'
            AND pm.meta_key = 'location_grid'
        ", ARRAY_A );

        $unique_training_locations = 0;
        foreach ( $distinct_training_locations as $location ){
            if ( in_array( $location["grid_id"], $location_ids_that_count ) ){
                $unique_training_locations++;
            }
        }

        return [
            "unique_church_locations" => $unique_church_locations,
            "unique_training_locations" => $unique_training_locations,
        ];
    }

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }
    public function has_permission(){
        $pass = false;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }
}
Zume_Metrics_Endpoints::instance();
