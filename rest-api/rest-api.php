<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Metrics_Endpoints
{
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics' ];


    //See https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
    public function add_api_routes() {
        $namespace = 'zume-metrics/v1';

        register_rest_route(
            $namespace, '/dt-public/public_endpoint', [
                'methods'  => "GET",
                'callback' => [ $this, 'public_endpoint' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function public_endpoint( WP_REST_Request $request ) {

        $country_only = [ 100000000, 100001073, 100024783, 100047360, 100054604, 100056004, 100056005, 100089588, 100131318, 100222390, 100238556, 100241760, 100243745, 100245356, 100247244, 100253792, 100254764, 100260851, 100341224, 100343035, 100367575, 100380097 ];
        # admin 1 for little highly divided countries
        #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
        $admin_1_countries = [ 100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707, 100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795, 100054605, 100253456, 100342975, 100074571 ];
        # admin 3 for big countries
        #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
        $admin_3_countries = [ 100050711, 100219347, 100089589, 100074576, 100259978, 100018514 ];

        global $wpdb;
        $distinct_church_locations = $wpdb->get_results( "
            SELECT DISTINCT( CASE
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $country_only ) . " ) THEN lg.admin0_grid_id
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_1_countries ) . " ) AND lg.admin1_grid_id IS NOT NULL THEN lg.admin1_grid_id
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_3_countries ) . " ) AND lg.admin3_grid_id IS NOT NULL THEN lg.admin3_grid_id
                WHEN lg.level = 1 THEN lg.admin1_grid_id
                ELSE lg.admin2_grid_id
            END ) as grid_id
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->posts p on ( p.ID = pm.post_id AND p.post_type = 'groups' )
            INNER JOIN $wpdb->postmeta pm2 on ( pm.post_id = pm2.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church' )
            INNER JOIN $wpdb->dt_location_grid lg ON (
                lg.grid_id = pm.meta_value
                AND (
                   lg.admin0_grid_id IN ( " . dt_array_to_sql( $country_only ) . " )
                   OR
                   lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_1_countries ) . " ) AND lg.admin1_grid_id IS NOT NULL
                   OR
                   lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_3_countries ) . " ) AND lg.admin3_grid_id IS NOT NULL
                   OR
                   lg.level = 1 AND lg.grid_id NOT IN ( SELECT lg22.admin1_grid_id FROM $wpdb->dt_location_grid lg22 WHERE lg22.level = 2 AND lg22.admin1_grid_id = lg.grid_id )
                   OR
                   lg.level >= 2 AND lg.admin0_grid_id NOT IN ( " . dt_array_to_sql( $admin_1_countries ) . " ) AND lg.admin0_grid_id NOT IN ( " . dt_array_to_sql( $admin_3_countries ) . " )
                )
            )
            WHERE pm.meta_key = 'location_grid'
        ", ARRAY_A );

        $unique_church_locations = sizeof( $distinct_church_locations );

        //unique locations for trainings that have completed the session 9
        $distinct_training_locations = $wpdb->get_results( "
            SELECT DISTINCT( CASE
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $country_only ) . " ) THEN lg.admin0_grid_id
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_1_countries ) . " ) AND lg.admin1_grid_id IS NOT NULL THEN lg.admin1_grid_id
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_3_countries ) . " ) AND lg.admin3_grid_id IS NOT NULL THEN lg.admin3_grid_id
                WHEN lg.level = 1 THEN lg.admin1_grid_id
                ELSE lg.admin2_grid_id
            END ) as grid_id
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->posts p on ( p.ID = pm.post_id AND p.post_type = 'trainings' )
            LEFT JOIN $wpdb->postmeta pm2 on ( pm.post_id = pm2.post_id AND pm2.meta_key = 'zume_group_id' )
            INNER JOIN $wpdb->usermeta um on ( um.meta_key = pm2.meta_value )
            INNER JOIN $wpdb->dt_location_grid lg ON (
                lg.grid_id = pm.meta_value
                AND (
                   lg.admin0_grid_id IN ( " . dt_array_to_sql( $country_only ) . " )
                   OR
                   lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_1_countries ) . " ) AND lg.admin1_grid_id IS NOT NULL
                   OR
                   lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_3_countries ) . " ) AND lg.admin3_grid_id IS NOT NULL
                   OR
                   lg.level = 1 AND lg.grid_id NOT IN ( SELECT lg22.admin1_grid_id FROM $wpdb->dt_location_grid lg22 WHERE lg22.level = 2 AND lg22.admin1_grid_id = lg.grid_id )
                   OR
                   lg.level >= 2 AND lg.admin0_grid_id NOT IN ( " . dt_array_to_sql( $admin_1_countries ) . " ) AND lg.admin0_grid_id NOT IN ( " . dt_array_to_sql( $admin_3_countries ) . " )
                )
            )
            WHERE um.meta_value LIKE '%\"session_9\";b:1;%'
            OR um.meta_value LIKE '%\"session_10\";b:1;%'
            AND pm.meta_key = 'location_grid'
        ", ARRAY_A );

        $unique_training_locations = sizeof( $distinct_training_locations );

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
