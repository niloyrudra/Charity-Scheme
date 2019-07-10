<?php

function example() {
?>
    <form id="myForm" name="myform" action="<?php echo esc_attr( admin_url('admin-post.php') ); ?>" method="POST">
        <input type="hidden" name="action" value="save_my_custom_form" />
        <select id="brandSel" size="1">
            <option selected="selected" value="">-- Select Brand --</option>
            <option>Abba</option>
            <option>AG Hair</option>
            <option>Abbacc</option>
            <option>AGWW Hair</option>
        </select>

        <input type="submit" value="submit" />
    </form>
<?php
}


// Enqueue Scripts For Charity Scheme Plugin
if( ! function_exists( 'charity_scheme_scripts_enqueuing' ) ) :

    function charity_scheme_scripts_enqueuing() {
        wp_enqueue_script( 'charity-js', plugin_dir_url( __FILE__ ) . '/charity-scheme.js' );
    }

endif;
add_action( 'wp_enqueue_scripts', 'charity_scheme_scripts_enqueuing' );

// AJAX Callback Functionality And Sending E-mail To Administrator
function save_charity_data_form() {
    
    $name = wp_strip_all_tags( $_POST[ 'name' ] );
    $instituteType = wp_strip_all_tags( $_POST[ 'instituteType' ] );
    $postTypeID = wp_strip_all_tags( $_POST[ 'postTypeID' ] );
    $donnerID = wp_strip_all_tags( $_POST[ 'donnerID' ] );
    $country = wp_strip_all_tags( $_POST[ 'country' ] );
    $county = wp_strip_all_tags( $_POST[ 'county' ] );
    $city = wp_strip_all_tags( $_POST[ 'city' ] );

    $charityType = ( $postTypeID == 'edu_institutions' ? __( 'Education' ) : __( 'Sports' ) );
    $donnerUserName = ucfirst( get_userdata( (int)$donnerID )->user_login );
    $donnerDisplayName = ucfirst( get_userdata( (int)$donnerID )->display_name );
    $donnerEmail = get_userdata( (int)$donnerID )->user_email;
    $donnerRole = ucfirst( implode( ', ', get_userdata( (int)$donnerID )->roles ) );
    
    $content = __( 'This Charity Scheme is on <b>' ) . $charityType . __( '</b>.<br />It\'s a ' ) . $instituteType . __( ', located at ' ) . $city . ', ' . $county . ', ' . $country . __( '.<br /><b><u>Donner:</u></b><br /><i>UserName</i> : <b>' ) . $donnerUserName . __( '</b>.<br /><i>Display Name</i> : <b>' ) . $donnerDisplayName . __( '</b>.<br /><i>E-mail</i> : <b>< ') . $donnerEmail . __( ' ></b>.<br /><i>User Role</i> : <b>') . $donnerRole . '</b>.';
       
    $msgContent = 'This Charity Scheme is on ' . $charityType . '. It\'s a ' . $instituteType . ', located at ' . $city . ', ' . $county . ', ' . $country . '.\nDonner: \nUserName : ' . $donnerUserName . '.\nDisplay Name : ' . $donnerDisplayName . '.\nE-mail : < ' . $donnerEmail . ' >.\nUser Role : ' . $donnerRole . '.';

    // Array for WP_INSERT_POST
    $args = [
        'post_title'        => $name,
        'post_content'      => $content,
        'post_type'         => 'charity_donations',
        'post_status'       => 'publish',
        'post_author'       => (int)$donnerID
    ];

    $charityID = wp_insert_post( $args );
    
    if( $charityID !== 0 ) {

        // Update User metaData
        update_user_meta( (int)$donnerID, '_donate_charity_key', $name );

        // Variables for Email
        $to = get_bloginfo( 'admin_email' );
        $subject = __( 'Charity Donation Scheme | ' ) . $name . __( '[ ' ) . $instituteType . __( ' ] | By - ' ) . $donnerDisplayName;
        $message = $msgContent;

        $headers[] = __( 'From: ' ) . get_bloginfo( 'name' ) . ' <' . $to . '>';
        $headers[] = __( 'Reply-To: ' ) . $donnerDisplayName . ' <' . $donnerEmail . '>';
        $headers[] = 'Content-Type: text/html: charset=UTF-8';

        // Triggering Email Submission
        wp_mail( $to, $subject, $message, $headers );  // wp_mail( $to, $subject, $message, '', array( '' ) ); the last array for attaching atachments

        echo $charityID; // Return either 1 or 0

    } else {
        echo 0;
    }


    die();

}
add_action( 'admin_post_nopriv_save_charity_donation_data_form', 'save_charity_data_form' );
add_action( 'admin_post_save_charity_donation_data_form', 'save_charity_data_form' );

// Registering Custom Post Types
if( ! function_exists( 'custom_post_types_generator' ) ) {

    function custom_post_types_generator() {

        // Array Of Multiple Custom Post Types
        $post_types = [
            'edu_institutions' => [
                'name'                  => __( 'Educational Institutions' ),
                'singular_name'         => __( 'Educational Institution' ),
                'short_name'            => __( 'Institution' ),
                'menu_icon'             => __( 'dashicons-book' )
            ],
            'sport_clubs' => [
                'name'                  => __( 'Sport Clubs' ),
                'singular_name'         => __( 'Sport Club' ),
                'short_name'            => __( 'Club' ),
                'menu_icon'             => __( 'dashicons-groups' )
            ],
            'charity_donations' => [
                'name'                  => __( 'Charity Donations' ),
                'singular_name'         => __( 'Charity Donation' ),
                'short_name'            => __( 'Donation' ),
                'menu_icon'             => __( 'dashicons-smiley' )
            ]
        ];

        // Generating Multiple Custom Post Types
        if( $post_types ) {
            foreach ($post_types as $post_type_key => $post_type_value) {
                
                register_post_type( $post_type_key, [
                    'labels'            => [
                        'name'                          => $post_type_value[ 'name' ],
                        'singular_name'                 => $post_type_value[ 'singular_name' ],
                        'plural_name'                   => $post_type_value[ 'name' ],
                        'menu_name'                     => $post_type_value[ 'name' ],
                        'add_new'                       => __( 'Add New ' ) . $post_type_value[ 'short_name' ],
                        'edit_item'                     => __( 'Edit ' ) . $post_type_value[ 'short_name' ],
                        'all_items'                     => __( 'All ' ) . $post_type_value[ 'name' ],
                    ],
                    'public'            => true,
                    'has_archive'       => true,
                    'show_ui'           => true,
                    'show_in_admin_bar' => true,
                    'show_in_menu'      => true,
                    'show_in_nav_menus' => true,
                    'show_in_rest'      => true,
                    'supports'          => [ 'title', 'editor', 'author', 'thumbnail' ],
                    'menu_icon'         => $post_type_value[ 'menu_icon' ],
                    'hierarchical'      => true,
                    'capability_type'   => 'post'
                ] );

            }
        }

        // Array OF Taxonomies to Education Post Types
        $edu_taxonomies = [
            'country'     => [
                'name'                  => __( 'Countries' ),
                'singular_name'         => __( 'Country' ),
            ],
            'county'     => [
                'name'                  => __( 'Counties' ),
                'singular_name'         => __( 'County' ),
            ],
            'city'     => [
                'name'                  => __( 'Cities' ),
                'singular_name'         => __( 'City' ),
            ]
        ];

        // Registering Taxonomies For Multiple Custom Post Types
        foreach ( $edu_taxonomies as $taxonimy_ID => $taxonimy_name ) {
            register_taxonomy( $taxonimy_ID, [ 'edu_institutions', 'sport_clubs' ], [
                'labels'            => [
                    'name'                      => $taxonimy_name[ 'name' ],
                    'singular_name'             => $taxonimy_name[ 'singular_name' ],
                    'plural_name'               => $taxonimy_name[ 'name' ],
                    'menu_name'                 => $taxonimy_name[ 'name' ],
                    'search_items'              => __( 'Search ' ) . $taxonimy_name[ 'name' ],
                    'all_items'                 => __( 'All ' ) . $taxonimy_name[ 'name' ],
                    'parent_item'               => __( 'Parent ' ) . $taxonimy_name[ 'singular_name' ],
                    'parent_item_colon'         => __( 'Parent ' ) . $taxonimy_name[ 'singular_name' ] . ':',
                    'edit_item'                 => __( 'Edit ' ) . $taxonimy_name[ 'singular_name' ],
                    'update_item'               => __( 'Update ' ) . $taxonimy_name[ 'singular_name' ],
                    'add_new_item'              => __( 'Add New ' ) . $taxonimy_name[ 'singular_name' ],
                    'new_item_name'             => __( 'New ' ) . $taxonimy_name[ 'name' ] . __( ' Name' ),
                ],
                'public'            => true,
                'hierarchical'      => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => [ 'slug' => $taxonimy_ID ],
            ] );
        }

        // Array Of Single Taxonomies For a Particular Custom Post Types
        $single_taxonomies = [
            
            'edu_type'      => [
                    'cpt'               => 'edu_institutions',
                    'name'              => __( 'Education Types' ),
                    'singular_name'     => __( 'Education Type' )
                ],
            'sport_type'      => [
                    'cpt'               => 'sport_clubs',
                    'name'              => __( 'Sports Types' ),
                    'singular_name'     => __( 'Education Type' )
                ]
        ];

        // Registering Single Taxonomies For a Particular Custom Post Types
        if( $single_taxonomies ) {

            foreach ($single_taxonomies as $taxo_key => $taxo_value) {
                
                register_taxonomy( $taxo_key, $taxo_value[ 'cpt' ], [
                    'labels'            => [
                        'name'                      => $taxo_value[ 'name' ],
                        'singular_name'             => $taxo_value[ 'singular_name' ],
                        'plural_name'               => $taxo_value[ 'name' ],
                        'menu_name'                 => $taxo_value[ 'name' ],
                        'search_items'              => __( 'Search ' ) . $taxo_value[ 'name' ],
                        'all_items'                 => __( 'All ' ) . $taxo_value[ 'name' ],
                        'parent_item'               => __( 'Parent ' ) . $taxo_value[ 'singular_name' ],
                        'parent_item_colon'         => __( 'Parent ' ) . $taxo_value[ 'singular_name' ] . __(  ':' ),
                        'edit_item'                 => __( 'Edit ' ) . $taxo_value[ 'singular_name' ],
                        'update_item'               => __( 'Update ' ) . $taxo_value[ 'singular_name' ],
                        'add_new_item'              => __( 'Add New ' ) . $taxo_value[ 'singular_name' ],
                        'new_item_name'             => __( 'New ' )  . $taxo_value[ 'singular_name' ] .  __( ' Name' ),
                    ],
                    'public'            => true,
                    'hierarchical'      => true,
                    'show_ui'           => true,
                    'show_admin_column' => true,
                    'query_var'         => true,
                    'rewrite'           => [ 'slug' => $taxo_key ],
                ] );

            }


        }


    }

}
add_action( 'init', 'custom_post_types_generator' );


// Adding Meta Boxes
add_action( 'add_meta_boxes', 'attaching_custom_meta_boxes_to_cpt' );
add_action( 'save_post', 'save_meta_box_data' );
// Sortable Column Setup Hook For Educational Institutions
add_action( 'manage_edu_institutions_posts_columns', 'reset_columns_for_edu_institutions' );
// add_filter( 'manage_edit-edu_institutions_sortable_columns', 'setting_sortable_columns_for_edu_institutions' );
// add_filter( 'manage_edit-edu_institutions_columns', 'setting_sortable_columns_for_edu_institutions' );

// Sortable Column Setup Hook For Educational Institutions
add_action( 'manage_sport_clubs_posts_columns', 'reset_columns_for_sport_clubs' );

if( ! function_exists( 'attaching_custom_meta_boxes_to_cpt' ) ) {
    function attaching_custom_meta_boxes_to_cpt() {
        add_meta_box(
            'cpt_meta_box_id',
            __( 'Information' ),
            'custom_meta_boxes_fields_callback',
            [ 'edu_institutions' ],
            'normal', // advance, side, normal
            'default' // hight, default, low
        );
    }
}

if( ! function_exists( 'custom_meta_boxes_fields_callback' ) ) {
    function custom_meta_boxes_fields_callback( $post ) {

        wp_nonce_field( 'metabox_generator', 'metabox_generator_nonce' );

        $data = get_post_meta( $post->ID, '_cmb_info', true );
        
        $Street         = @$data[ 'street' ] ?? '';
        $Town           = @$data[ 'town' ] ?? '';
        $Postcode       = @$data[ 'postcode' ] ?? '';
        $Contact        = @$data[ 'contact' ] ?? '';
        $Email          = @$data[ 'email' ] ?? '';
        $Phone          = @$data[ 'phone' ] ?? '';
        $Minor_group    = @$data[ 'minor_group' ] ?? '';
        $NF_type        = @$data[ 'nf_type' ] ?? '';
        $Staffs         = @$data[ 'staff' ] ?? '';
        $Students       = @$data[ 'stdnt' ] ?? '';


        
        echo '<label for="cmb_street">' . __( 'Street Name/Number:' ) . '</label><input type="text" class="widefat" size="25" name="cmb_street" id="cmb_street" value="' . esc_attr( $Street ) . '" />';
        echo '<label for="cmb_town">' . __( 'Town:' ) . '</label><input type="text" class="widefat" size="25" name="cmb_town" id="cmb_town" value="' . esc_attr( $Town ) . '" />';
        echo '<label for="cmb_postcode">' . __( 'Post-code:' ) . '</label><input type="text" class="widefat" size="25" name="cmb_postcode" id="cmb_postcode" value="' . esc_attr( $Postcode ) . '" />';
        echo '<label for="cmb_contact">' . __( 'Contact Name:' ) . '</label><input type="text" class="widefat" size="25" name="cmb_contact" id="cmb_contact" value="' . esc_attr( $Contact ) . '" />';
        echo '<label for="cmb_email">' . __( 'Email:' ) . '</label><input type="email" class="widefat" size="25" name="cmb_email" id="cmb_email" value="' . esc_attr( $Email ) . '" />';    
        echo '<label for="cmb_phone">' . __( 'Phone Number:' ) . '</label><input type="text" class="widefat" size="25" name="cmb_phone" id="cmb_phone" value="' . esc_attr( $Phone ) . '" />';    
        echo '<label for="cmb_minor_group">' . __( 'Minor Group:' ) . '</label><input type="text" class="widefat" size="25" name="cmb_minor_group" id="cmb_minor_group" value="' . esc_attr( $Minor_group ) . '" />';
        echo '<label for="cmb_nf_type">' . __( 'NF Type:' ) . '</label><input type="text" class="widefat" size="25" name="cmb_nf_type" id="cmb_nf_type" value="' . esc_attr( $NF_type ) . '" />';
        echo '<label for="cmb_staff">' . __( 'Number of Staff:' ) . '</label><input type="number" class="widefat" size="25" name="cmb_staffs" id="cmb_staff" value="' . esc_attr( $Staffs ) . '" />';
        echo '<label for="cmb_stdnt">' . __( 'Number of Students:' ) . '</label><input type="number" class="widefat" size="25" name="cmb_stdnt" id="cmb_stdnt" value="' . esc_attr( $Students ) . '" />';

    }
}

if( ! function_exists( 'save_meta_box_data' ) ) {
    function save_meta_box_data( $post_id ) {

        if ( ! isset( $_POST['metabox_generator_nonce'] ) ) return $post_id;
        $nonce = $_POST['metabox_generator_nonce'];
        if ( ! wp_verify_nonce( $nonce, 'metabox_generator' ) ) return $post_id;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;
            
        $data = [
            'street'        => isset( $_POST['cmb_street'] ) ? sanitize_text_field( $_POST['cmb_street'] ) : '',
            'town'          => isset( $_POST['cmb_town'] ) ? sanitize_text_field( $_POST['cmb_town'] ) : '',
            'postcode'      => isset( $_POST['cmb_postcode'] ) ? sanitize_text_field( $_POST['cmb_postcode'] ) : '',
            'contact'       => isset( $_POST['cmb_contact'] ) ? sanitize_text_field( $_POST['cmb_contact'] ) : '', 
            'email'         => isset( $_POST['cmb_email'] ) ? sanitize_text_field( $_POST['cmb_email'] ) : '',
            'phone'         => isset( $_POST['cmb_phone'] ) ? sanitize_text_field( $_POST['cmb_phone'] ) : '',
            'minor_group'   => isset( $_POST['cmb_minor_group'] ) ? sanitize_text_field( $_POST['cmb_minor_group'] ) : '',
            'nf_type'       => isset( $_POST['cmb_nf_type'] ) ? sanitize_text_field( $_POST['cmb_nf_type'] ) : '',
            'staff'       => isset( $_POST['cmb_staff'] ) ? sanitize_text_field( $_POST['cmb_staff'] ) : '',
            'stdnt'       => isset( $_POST['cmb_stdnt'] ) ? sanitize_text_field( $_POST['cmb_stdnt'] ) : ''
        ];
 
        // Update the meta field.
        update_post_meta( $post_id, '_cmb_info', $data );

    }
}

if( ! function_exists( 'reset_columns_for_edu_institutions' ) ) {
    function reset_columns_for_edu_institutions( $columns ) {

        $title = $columns[ 'title' ];
        $date = $columns[ 'date' ];

        unset( $columns[ 'date' ] );

        $columns[ 'title' ] = __( 'Institution\'s Name' );
        // $columns[ 'country' ] = __( 'Countries' );
        // $columns[ 'county' ] = __( 'Counties' );
        // $columns[ 'city' ] = __( 'Cities' );
        // $columns[ 'edu_type' ] = __( 'Education Types' );

        return $columns;

    }
}
// if( ! function_exists( 'setting_sortable_columns_for_edu_institutions' ) ) {
//     function setting_sortable_columns_for_edu_institutions( $columns ) {
//         var_dump($columns );
//         $columns[ 'country' ] = 'country';
//         $columns[ 'county' ] = 'county';
//         $columns[ 'city' ] = 'city';
//         $columns[ 'edu_type' ] = 'edu_type';

//         return $columns;

//     }
// }
if( ! function_exists( 'reset_columns_for_sport_clubs' ) ) {
    function reset_columns_for_sport_clubs( $columns ) {

        $title = $columns[ 'title' ];
        $date = $columns[ 'date' ];

        unset( $columns[ 'date' ] );

        $columns[ 'title' ] = __( 'Clubs' );
        
        return $columns;

    }
}


/**
 * 
 *  ============
 *  SHORTCODES
 *  ============
 * 
 */

 if( ! shortcode_exists( 'charity' ) ) :

    add_shortcode( 'charity', 'generate_shortcode_for_charity_scheme_option' );
    if( !function_exists( 'generate_shortcode_for_charity_scheme_option' ) ) :

        function generate_shortcode_for_charity_scheme_option( $args = [], $content = null ) {
            $selected = '';
            $args = [
                'public'   => true,
                '_builtin' => false,
            ];
             
            $output = 'objects'; // names or objects, note names is the default
            $operator = 'and'; // 'and' or 'or'
             
            $post_types = get_post_types( $args, $output, $operator );

            // Satrt HTML Body section...
            ob_start();
        ?>
                <div class="charity-scheme-form-content" style="padding:140px 0;">
                    <div class="form-content">
                        <div class="row">
                        <form action="/a" method="post">
                            <h4 style="text-transform:uppercase;"><?php echo __( 'Schools or Sports Charity Scheme:' ); ?></h4>
                            <select name="charity_schemes" id="charity_schemes">
                                <option value=""><?php echo __( '...Please Select an Option' ) ?></option>
                            <?php           
                                $selected = @$_POST[ 'charity_schemes' ] ? $_POST[ 'charity_schemes' ] : '';
                                foreach ( $post_types  as $post_type ) {
                                    if ( $post_type->name == 'edu_institutions' || $post_type->name == 'sport_clubs' ) { ?>
                                    <option value="<?php echo $post_type->name; ?>" <?php echo ( $selected == $post_type->name ? 'selected="selected"' : '' ); ?>><?php echo $post_type->label; ?></option>
                                <?php
                                    }
                                }
                            ?>  </select>
                            <br />
                                <input type="submit" name="sub_btn" id="sub_btn" value="Proceed" />
                            </form>
                        </div><!-- /.row -->
                    
                    <?php if( $selected ) { // Start Second Form ?>

                        <div class="row">

                    <?php
                        $Custom_post_type = $selected;
                        
                        if( $Custom_post_type ) $taxonomies = get_object_taxonomies( $Custom_post_type, 'objects' );
                        
                        if( $taxonomies ) {
                    ?>
                            <form action="/a/b" method="post">
                    <?php
                                foreach( $taxonomies as $taxonomy ) {
                    ?>
                                    <br />
                                    <div class="row">
                                        <h6 style="text-transform:uppercase;"><?php echo $taxonomy->label . ':'; ?></h6>
                                    <?php
                                        $args = array(
                                            'show_option_all'    => __( '...Please Select a(an) ' ) . $taxonomy->labels->singular_name,
                                            'show_option_none'   => '',
                                            'option_none_value'  => '-1',
                                            'orderby'            => 'ID',
                                            'order'              => 'ASC',
                                            'show_count'         => 0,
                                            'hide_empty'         => 1,
                                            'child_of'           => 0,
                                            'exclude'            => '',
                                            'include'            => '',
                                            'echo'               => 1,
                                            'selected'           => 0,
                                            'hierarchical'       => 0,
                                            'name'               => $taxonomy->name,
                                            'id'                 => '',
                                            'class'              => 'postform',
                                            'depth'              => 0,
                                            'tab_index'          => 0,
                                            'taxonomy'           => $taxonomy->name,
                                            'hide_if_empty'      => false,
                                            // 'value_field'	     => 'term_id',
                                            'value_field'	     => 'name',
                                        );
                            
                                        $taxo_lists = wp_dropdown_categories( $args );
                                    ?>
                                    </div><!-- /.row -->
                                <?php } ?>
                                <input type="hidden" name="cpt_name" value="<?php echo $Custom_post_type; ?>">
                                <br />
                                <input type="submit" name="option_btn" id="option_btn" value="Get <?php echo ( $Custom_post_type == 'edu_institutions' ? 'Institutions' : 'Clubs' ); ?>">
                            </form>
                        <?php } ?>
                        
                    <?php } // End Second Form ?>
                    
                    </div> <!-- /.form-content -->

                </div> <!-- /.charity-scheme-form-content -->    
        <?php

            return ob_get_clean();
        
        }

    endif;

 endif;


if( ! shortcode_exists( 'charity-type' ) ) :

    add_shortcode( 'charity-type', 'generate_charity_type_shortcode' );
    if( ! function_exists( 'generate_charity_type_shortcode' ) ) :

        function generate_charity_type_shortcode( $atts = [], $content = null ) {

            ob_start();
                
                $item_type = $_POST[ 'cpt_name' ] == 'edu_institutions' ? 'edu_type' : 'sport_club';
                    
                if( isset( $_POST[ 'option_btn' ] ) ) { // Start of INNER - IF Statement...
                
                    echo 'Charity Type: ' . $_POST[ 'cpt_name' ] . '<br />Country: ' . $_POST[ 'country' ] . '<br />County: ' . $_POST[ 'county' ] . '<br />City: ' . $_POST[ 'city' ] . '<br />Type: ' . $_POST[ $item_type ] . '<br />';
                    echo get_current_user_id() . '<br />';

                    $myposts = get_posts(
                        array(
                            'showposts' => -1,
                            'post_type' => $_POST[ 'cpt_name' ],
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'country',
                                    'field' => 'slug',
                                    'terms' => $_POST[ 'country' ]
                                ),
                                array(
                                    'taxonomy' => 'county',
                                    'field' => 'slug',
                                    'terms' => $_POST[ 'county' ]
                                ),
                                array(
                                    'taxonomy' => 'city',
                                    'field' => 'slug',
                                    'terms' => $_POST[ 'city' ]
                                ),
                                array(
                                    'taxonomy' => $item_type,
                                    'field' => 'slug',
                                    'terms' => $_POST[ $item_type ]
                                )
                            )
                        )
                    );
                        
                    if( $myposts ) { ?>
                        <div class="row">
                            <h6 style="text-transform:uppercase;">
                            <?php
                                echo ( $_POST[ 'cpt_name' ] == 'edu_institutions' ? 'Institutions:' : 'Clubs:' );
                                ?>                        
                            </h6>
                            <form id="charity-donation-data-form" action="#" method="post" data-url="<?php echo esc_attr( admin_url('admin-post.php') ); ?>">
                                <select name="donate_options" id="donate_options">
                                    <option value="">...Please Select a(an) <?php echo esc_html( $_POST[ $item_type ] ); ?></option> 
                                <?php
                                foreach ($myposts as $mypost) { ?>
                                    <option value="<?php
                                    echo $mypost->post_title;
                                    ?>" <?php echo ( @$_POST[ 'donate_options' ] == $mypost->post_title ? 'selected="selected"' : '' ); ?>><?php
                                    echo $mypost->post_title;
                                    ?></option>
                                <?php } ?>
                                </select>
                                <input type="hidden" name="cpt_user_id" id="cpt_user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>">
                                <input type="hidden" name="selected_cpt_name" id="selected_cpt_name" value="<?php echo esc_attr( $_POST[ 'cpt_name' ] ); ?>">
                                <input type="hidden" name="selected_country" id="selected_country" value="<?php echo esc_attr( $_POST[ 'country' ] ); ?>">
                                <input type="hidden" name="selected_county" id="selected_county" value="<?php echo esc_attr( $_POST[ 'county' ] ); ?>">
                                <input type="hidden" name="selected_city" id="selected_city" value="<?php echo esc_attr( $_POST[ 'city' ] ); ?>">
                                <input type="hidden" name="selected_type" id="selected_type" value="<?php echo esc_attr( $_POST[ $item_type ] ); ?>">
                                <br />
                                <input type="submit" name="item_seclect_btn" value="Select">
                            </form>

                        </div><!-- /row -->
                    <?php

                    } // End of $myposts - IF Statement...

                
                } // End of INNER - IF Statement...

                ?>
            </div>

            <?php // End of - IF Statement...

            return ob_get_clean();

        }

    endif;

endif;


// Shortcode Generator [charity-schemes]
if( ! shortcode_exists( 'charity-schemes' ) ) {
    
    add_shortcode( 'charity-schemes', 'generate_shortcode_for_charity_scheme_dropdown_option' );
    
    if( !function_exists( 'generate_shortcode_for_charity_scheme_dropdown_option' ) ) {
        function generate_shortcode_for_charity_scheme_dropdown_option( $atts = [], $content = null ) {
    
            $HTML_contents = '';
            $selected = '';

            $args = [
                'public'   => true,
                '_builtin' => false,
            ];
             
            $output = 'objects'; // names or objects, note names is the default
            $operator = 'and'; // 'and' or 'or'
             
            $post_types = get_post_types( $args, $output, $operator );

                ob_start();
            ?>
    <div class="charity-scheme-form-content" style="padding:140px 0;">
        <div class="form-content">
            <div class="row">
            <form action="/charity-schemes/information" method="post">
                <select name="charity_schemes" id="charity_schemes">
                    <option value=""><?php echo __( '...Please Select an Option' ) ?></option>
    <?php           
            $selected = @$_POST[ 'charity_schemes' ] ? $_POST[ 'charity_schemes' ] : '';
            foreach ( $post_types  as $post_type ) {
                if ( $post_type->name == 'edu_institutions' OR $post_type->name == 'sport_clubs' ) { ?>
                <option value="<?php echo $post_type->name; ?>" <?php echo ($selected == $post_type->name ? 'selected="selected"' : ''); ?>><?php echo $post_type->label; ?></option>
            <?php
                }
            }
    ?>              </select>
            <br />
                <input type="submit" name="sub_btn" id="sub_btn" value="Proceed" />
            </form>
            </div><!-- /.row -->

         </div>
    </div>       
    <?php
            return ob_get_clean();
    
        }
    }

}

// Shortcode Generator [cs-info]
if( ! shortcode_exists( 'cs-info' ) ) {
    
    add_shortcode( 'cs-info', 'generate_shortcode_for_cs_info_dropdown_option' );
    
    if( !function_exists( 'generate_shortcode_for_cs_info_dropdown_option' ) ) {
        function generate_shortcode_for_cs_info_dropdown_option( $atts = [], $content = null ) {

            // THE PAGE ID...
            echo '<h3>ID: ~ ' . get_the_ID() . ' ~</h3>';
            echo '<h3>Slug: ~ ' . get_post_field( 'post_name', get_the_ID() ) . ' ~</h3>';
                ob_start();


            ?>
    <div class="charity-scheme-form-content" style="padding:140px 0;">
        <div class="form-content">
            <div class="row">
                
    <?php

            $Custom_post_type = @$_POST[ 'charity_schemes' ] ? $_POST[ 'charity_schemes' ] : '';

            if( $Custom_post_type ) $taxonomies = get_object_taxonomies( $Custom_post_type, 'objects' );

            if( $taxonomies ) {
                // var_dump($taxonomies);
                ?>
                <form action="/charity-schemes/Information" method="post">
                <?php
                foreach( $taxonomies as $taxonomy ) {
                    ?>
                    <br />
                <div class="row">
                    <h6 style="text-transform:uppercase;"><?php echo $taxonomy->label . ':'; ?></h6>
                    <?php
                    $args = array(
                        'show_option_all'    => __( '...Please Select a(an) ' ) . $taxonomy->labels->singular_name,
                        'show_option_none'   => '',
                        'option_none_value'  => '-1',
                        'orderby'            => 'ID',
                        'order'              => 'ASC',
                        'show_count'         => 0,
                        'hide_empty'         => 1,
                        'child_of'           => 0,
                        'exclude'            => '',
                        'include'            => '',
                        'echo'               => 1,
                        'selected'           => 0,
                        'hierarchical'       => 0,
                        'name'               => $taxonomy->name,
                        'id'                 => '',
                        'class'              => 'postform',
                        'depth'              => 0,
                        'tab_index'          => 0,
                        'taxonomy'           => $taxonomy->name,
                        'hide_if_empty'      => false,
                        // 'value_field'	     => 'term_id',
                        'value_field'	     => 'name',
                    );

                    $taxo_lists = wp_dropdown_categories( $args );

                    ?>
                </div><!-- /.row -->

            <?php
                } ?>
                <input type="hidden" name="cpt_name" value="<?php echo $Custom_post_type; ?>">
                <br />
                <input type="submit" name="option_btn" id="option_btn" value="Get <?php echo ( $Custom_post_type == 'edu_institutions' ? 'Institutions' : 'Clubs' ); ?>">
            </form>
            <?php }
            ?>

                </div>
            <?php

                $item_type = $_POST[ 'cpt_name' ] == 'edu_institutions' ? 'edu_type' : 'sport_club';
                
                if( isset( $_POST[ 'option_btn' ] ) ) { // Start of INNER - IF Statement...
                 
                echo 'Country: ' . $_POST[ 'country' ] . '<br />County: ' . $_POST[ 'county' ] . '<br />City: ' . $_POST[ 'city' ] . '<br />Type: ' . $_POST[ $item_type ] . '<br />';
                // echo 'Country: ' . get_post( $_POST[ 'country' ], ARRAY_A )['post_title'] . '<br />County: ' . get_post( $_POST[ 'county' ], ARRAY_A )['post_title'] . '<br />City: ' . get_post( $_POST[ 'city' ], ARRAY_A )['post_title'] . '<br />Type: ' . get_post( $_POST[ $item_type ], ARRAY_A )['post_title'] . '<br />';
                // echo 'Country: ' . get_post( $_POST[ 'country' ], ARRAY_A )['post_title'] . '<br />County: ' . get_post( $_POST[ 'county' ], ARRAY_A )['post_title'] . '<br />City: ' . get_post( $_POST[ 'city' ], ARRAY_A )['post_title'] . '<br />Type: ' . get_post( $_POST[ $item_type ], ARRAY_A )['post_title'] . '<br />';

                        $myposts = get_posts(
                            array(
                                'showposts' => -1,
                                'post_type' => $_POST[ 'cpt_name' ],
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => 'country',
                                        'field' => 'slug',
                                        'terms' => $_POST[ 'country' ]
                                    ),
                                    array(
                                        'taxonomy' => 'county',
                                        'field' => 'slug',
                                        'terms' => $_POST[ 'county' ]
                                    ),
                                    array(
                                        'taxonomy' => 'city',
                                        'field' => 'slug',
                                        'terms' => $_POST[ 'city' ]
                                    ),
                                    array(
                                        'taxonomy' => $item_type,
                                        'field' => 'slug',
                                        'terms' => $_POST[ $item_type ]
                                    )
                                )
                            )
                        );
                        
                        if( $myposts ) { ?>
                        <div class="row">
                            <h6 style="text-transform:uppercase;">
                            <?php
                                echo ( $_POST[ 'cpt_name' ] == 'edu_institutions' ? 'Institutions:' : 'Clubs:' );
                                ?>                        
                            </h6>
                            <form action="/user-profile" method="post">
                                <select name="donate_options" id="donate_options">
                                    <option value="">...Please Select a(an) <?php echo esc_html( $_POST[ $item_type ] ); ?></option> 
                                <?php
                                foreach ($myposts as $mypost) { ?>
                                    <option value="<?php
                                    echo $mypost->post_title;
                                    ?>" <?php echo ( @$_POST[ 'item_opts' ] == $mypost->post_title ? 'selected="selected"' : '' ); ?>><?php
                                    echo $mypost->post_title;
                                    ?></option>
                                <?php } ?>
                                </select>
                                <input type="hidden" name="selected_country" id="selected_country" value="<?php echo esc_attr( $_POST[ 'country' ] ); ?>">
                                <input type="hidden" name="selected_county" id="selected_county" value="<?php echo esc_attr( $_POST[ 'county' ] ); ?>">
                                <input type="hidden" name="selected_city" id="selected_city" value="<?php echo esc_attr( $_POST[ 'city' ] ); ?>">
                                <input type="hidden" name="selected_type" id="selected_type" value="<?php echo esc_attr( $_POST[ $item_type ] ); ?>">
                                <br />
                                <input type="submit" name="item_seclect_btn" value="Select">
                            </form>

                        </div><!-- /row -->
                    <?php
                        }
                   
                } // End of INNER - IF Statement...

                ?>
            </div>

            <?php // End of - IF Statement...

            return ob_get_clean();
    
        }

    }

}

/**
 * The field on the editing screens.
 *
 * @param $user WP_User user object
 */

function wporg_usermeta_form_field_charity_scheme($user)
{
    ?>
    <h3>It's Your Charity Scheme</h3>
    <table class="form-table">
        <tr>
            <th>
                <label for="charity_donation">Your Charity Scheme</label>
            </th>
            <td>
                <input type="text"
                       class="regular-text ltr"
                       id="charity_donation"
                       name="charity_donation"
                       value="<?= esc_attr(get_user_meta($user->ID, '_donate_charity_key', true)); ?>"
                       title="Your Charity Scheme"
                       >
                <p class="description">
                    Here is your Charity Scheme if you filed out any in you Profile.
                </p>
            </td>
        </tr>
    </table>
    <?php
}
 


/**
 * The save action.
 *
 * @param $user_id int the ID of the current user.
 *
 * @return bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */

function wporg_usermeta_form_field_charity_scheme_update($user_id)
{
    // check that the current user have the capability to edit the $user_id
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    if ( ! isset( $_POST['donate_options'] ) ) {
        return false;
    }

    return update_user_meta( $user_id, '_donate_charity_key', $_POST['charity_donation'] );

}

// add the field to user's own profile editing screen
add_action(
    'edit_user_profile',
    'wporg_usermeta_form_field_charity_scheme'
);
     
// add the field to user profile editing screen
add_action(
    'show_user_profile',
    'wporg_usermeta_form_field_charity_scheme'
);
    
// add the save action to user's own profile editing screen update
add_action(
    'personal_options_update',
    'wporg_usermeta_form_field_charity_scheme_update'
);
     
// add the save action to user profile editing screen update
add_action(
    'edit_user_profile_update',
    'wporg_usermeta_form_field_charity_scheme_update'
);




/**
 * 
 *  ============
 *  MAIL TRAP
 *  ============
 * 
 */
function mailtrap($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host = 'smtp.mailtrap.io';
    $phpmailer->SMTPAuth = true;
    $phpmailer->Port = 2525;
    $phpmailer->Username = 'f9fbc11e592e64';
    $phpmailer->Password = '1c33a8ca575489';
}
  
add_action('phpmailer_init', 'mailtrap');



// REST_API Add Custom Fields
if( ! function_exists( 'charity_scheme_custom_field' ) ) {
    function charity_scheme_custom_field() {
        // global $authordata;
        register_rest_field( 'charity_donations', 'donner_name', [
            'get_callback'      => function() {
                return get_the_author();
            }
        ] );

        register_rest_field( 'user', 'charity_scheme', [
            'get_callback'      => function( $user ) {
                // var_dump($user);
                return get_user_meta( $user['id'], '_donate_charity_key', true );
            }
        ] );

    }
}
add_action( 'rest_api_init', 'charity_scheme_custom_field' );
