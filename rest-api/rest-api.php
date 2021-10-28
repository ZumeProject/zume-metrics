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

        $country_only = [ 100000000, 100001073, 100024783, 100047360, 100054604, 100056004, 100056005, 100089588, 100131318, 100222390, 100238556, 100241760, 100243745, 100245356, 100247244, 100253792, 100254764, 100260851, 100341224, 100343035, 100367575, 100380097 ];
        $admin_1_countries = [ 100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707, 100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795, 100054605, 100253456, 100342975, 100074571, 100001074, 100001519, 100002788, 100002896, 100002901, 100018100, 100024581, 100024587, 100024909, 100024916, 100041079, 100055703, 100056006, 100056014, 100072552, 100130426, 100131154, 100132213, 100132221, 100132628, 100132775, 100222391, 100222967, 100231206, 100231221, 100238557, 100238819, 100240981, 100241004, 100241015, 100241376, 100243746, 100247245, 100248823, 100249195, 100249812, 100249853, 100253438, 100254765, 100259807, 100306566, 100309569, 100314694, 100314700, 100340252, 100341218, 100341239, 100341889, 100341992, 100343036, 100343138, 100350956, 100350960, 100351536, 100351542, 100352861, 100363965, 100367576, 100367947, 100380048, 100380091 ];
        $admin_2_countries = [ 100000001, 100000364, 100001091, 100002260, 100002800, 100002910, 100003491, 100005723, 100005813, 100017364, 100018011, 100018104, 100024620, 100024784, 100024928, 100025352, 100041091, 100041355, 100041402, 100041471, 100047361, 100050338, 100053495, 100053847, 100054276, 100054543, 100055730, 100055819, 100056020, 100056133, 100072535, 100072563, 100072668, 100072856, 100074143, 100074514, 100088242, 100089023, 100089567, 100130431, 100130478, 100131072, 100131170, 100131319, 100131698, 100131733, 100131777, 100131824, 100131864, 100132227, 100132604, 100133694, 100134422, 100385182, 100219316, 100222418, 100222718, 100222975, 100231234, 100231299, 100233158, 100233347, 100235142, 100235196, 100238572, 100238826, 100238987, 100240594, 100241027, 100241387, 100241446, 100241717, 100241749, 100241761, 100243784, 100245357, 100247331, 100248384, 100248458, 100249200, 100249754, 100249816, 100249866, 100253277, 100253577, 100253616, 100253793, 100254606, 100254765, 100255729, 100259822, 100259917, 100260160, 100260852, 100262889, 100306583, 100306693, 100309648, 100309849, 100314438, 100314675, 100314708, 100317719, 100322810, 100340266, 100340602, 100341225, 100341436, 100341608, 100341899, 100341995, 100342182, 100342287, 100342297, 100342370, 100342663, 100343063, 100343145, 100343572, 100343599, 100350531, 100350967, 100351558, 100351851, 100352871, 100352901, 100356776, 100363308, 100364199, 100367399, 100367583, 100367953, 100367977, 100379984, 100380053, 100380099, 100380454, 100385027, 100385110, 100385185 ];
        $admin_3_countries = [ 100050711, 100219347, 100089589, 100074576, 100259978, 100018514 ];


        global $wpdb;
        $distinct_church_locations = $wpdb->get_results( "
            SELECT Distinct( CASE
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $country_only ) . " ) THEN lg.admin0_grid_id
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_1_countries ) . " ) AND lg.admin1_grid_id IS NOT NULL THEN lg.admin1_grid_id
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_2_countries ) . " ) AND lg.admin2_grid_id IS NOT NULL THEN lg.admin2_grid_id
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_3_countries ) . " ) AND lg.admin3_grid_id IS NOT NULL THEN lg.admin3_grid_id
                ELSE NULL
            END
            ) as grid_id
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
                   lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_2_countries ) . " ) AND lg.admin2_grid_id IS NOT NULL
                   OR
                   lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_3_countries ) . " ) AND lg.admin3_grid_id IS NOT NULL
                )
            )
            WHERE pm.meta_key = 'location_grid'
        ", ARRAY_A );

        $unique_church_locations = sizeof( $distinct_church_locations );

        //unique locations for trainings that have completed the session 9
        $distinct_training_locations = $wpdb->get_results( "
            SELECT Distinct( CASE
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $country_only ) . " ) THEN lg.admin0_grid_id
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_1_countries ) . " ) AND lg.admin1_grid_id IS NOT NULL THEN lg.admin1_grid_id
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_2_countries ) . " ) AND lg.admin2_grid_id IS NOT NULL THEN lg.admin2_grid_id
                WHEN lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_3_countries ) . " ) AND lg.admin3_grid_id IS NOT NULL THEN lg.admin3_grid_id
                ELSE NULL
            END
            ) as grid_id
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
                   lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_2_countries ) . " ) AND lg.admin2_grid_id IS NOT NULL
                   OR
                   lg.admin0_grid_id IN ( " . dt_array_to_sql( $admin_3_countries ) . " ) AND lg.admin3_grid_id IS NOT NULL
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
