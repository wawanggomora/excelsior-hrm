<?php
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() { 
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}


//enqueue script to sub-menu page
add_action( 'admin_enqueue_scripts', 'enqueue_my_scripts' );
function enqueue_my_scripts($hook) {
    wp_enqueue_style( 'datatable-css', '//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css' );
    wp_enqueue_script('datatable-js', '//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js');

    wp_enqueue_script('jscustom', get_stylesheet_directory_uri() . '/hrm/department/department.js');
    wp_localize_script('jscustom', 'ajax', array(
       'url' => admin_url('admin-ajax.php')
    ));

    $user = wp_get_current_user();
    $screen = get_current_screen();

    if ( $screen->id == 'edit-employees' || $screen->id == 'employees') {
        wp_enqueue_style( 'employees-acf-style', get_stylesheet_directory_uri() . '/cpt/style.css' );
    }

    if ( $screen->id == 'employees' && in_array( 'supervisor', (array) $user->roles )) {
        wp_enqueue_script( 'employees-edit-js', get_stylesheet_directory_uri() . '/hrm/manager/script.js' );
        wp_enqueue_style( 'edit-users-style', get_stylesheet_directory_uri() . '/hrm/manager/style.css' );
    }

    if ( $screen->id == 'toplevel_page_employee-file-manager') {
        wp_enqueue_script( 'employees-edit-js', get_stylesheet_directory_uri() . '/hrm/file-manager/script.js' );
    }


    
    if ( $hook == 'users.php' && in_array( 'supervisor', (array) $user->roles )) {
        wp_enqueue_style( 'edit-users-style', get_stylesheet_directory_uri() . '/hrm/manager/style.css' );
    }


    if ( $hook == 'profile.php' ) {
        wp_enqueue_style( 'employee-profile-style', get_stylesheet_directory_uri() . '/hrm/employee/style.css' );
    }
}

//set excelsior employee capabilities
/*add_action( 'init', 'excelsior_set_capability' );
function excelsior_set_capability(){
    include "admin/capabilities.php";
}*/

//create new admin menu
add_action( 'admin_menu', 'excelsior_hrm_options_page' );
function excelsior_hrm_options_page() {

    $user = wp_get_current_user();

    if ( in_array( 'agent', (array) $user->roles ) ) {
        add_menu_page(
            __( 'Dashboard', 'my-textdomain' ),
            __( 'Dashboard', 'my-textdomain' ),
            'view_employee_dashboard',
            'employee-dashboard',
            'employee_dashboard_contents',
            'dashicons-dashboard',
            1
        );


        add_menu_page(
            __( 'File Manager', 'my-textdomain' ),
            __( 'File Manager', 'my-textdomain' ),
            'view_employee_dashboard',
            'employee-file-manager',
            'employee_filemanager_contents',
            'dashicons-media-document',
            2
        );

    } else if ( in_array( 'supervisor', (array) $user->roles ) || in_array( 'administrator', (array) $user->roles ) ) {

    	add_menu_page(
    		__( 'Excelsior HRM', 'my-textdomain' ),
    		__( 'Excelsior HRM', 'my-textdomain' ),
    		'manage_hrm',
    		'excelsior-hrm',
    		'my_admin_page_contents',
    		'dashicons-admin-users',
    		3
    	);

        add_submenu_page( 'excelsior-hrm', 'Dashboard', 'Dashboard',
            'manage_hrm', 'excelsior-hrm');
        add_submenu_page( 'excelsior-hrm', 'Departments', 'Departments',
            'manage_hrm', 'department-page', 'my_department_contents');
        add_submenu_page( 'excelsior-hrm', 'Employees', 'Employees',
            'manage_hrm', 'employees-page', 'my_employees_contents');
    }
}

add_action('admin_menu', 'remove_admin_menu_links');
function remove_admin_menu_links(){
    $user = wp_get_current_user();
    if( in_array( 'supervisor', (array) $user->roles ) || in_array( 'agent', (array) $user->roles ) ) {
        remove_menu_page('index.php');
    }
}

function my_department_contents() { 
    include "hrm/department/department.php";
}

add_action('wp_ajax_add_department','add_department');
add_action('wp_ajax_nopriv_add_department','add_department');
function add_department(){
    global $wpdb;
    $dept_name = $_POST['dept_name'];

    $response = $wpdb->insert('hrm_department', array('dept_name' => $dept_name));
    $departments = $wpdb->get_results("SELECT * FROM hrm_department");
    wp_send_json( $departments);
}


add_action('wp_ajax_get_department','get_department');
add_action('wp_ajax_nopriv_get_department','get_department');
function get_department(){
    global $wpdb;

    $departments = $wpdb->get_results("SELECT * FROM hrm_department");
    wp_send_json( $departments);
}

// admin page content
function my_admin_page_contents() {

    global $wpdb;

    $user = wp_get_current_user();

    ?>
    <div class="wrap">
        <h2>Welcome <?php echo $user->display_name; ?>!</h2>
    </div>

    <?php
    
}

// employee profile page content
function employee_dashboard_contents() {

    include "hrm/employee/dashboard.php";
    
}

// employee profile page content
function employee_filemanager_contents() {

    global $wpdb;

    $user = wp_get_current_user();

    ?>

    <div class="wrap">
        <h2>File manager</h2>
    </div>

    <?php

    $employee_id = get_user_meta($user->ID, 'employee_id', true);

    if( have_rows('employee_file_manager', $employee_id) ):

    ?>

    <table id="example">
        <thead>
            <th>File Name</th>
            <th>Date Uploaded</th>
            <th>Action</th>
        </thead>
        <tbody>
            <?php while( have_rows('employee_file_manager', $employee_id) ): the_row();
                $document = get_sub_field('employee_document');
            ?>
                <tr>
                    <td><?php echo $document['title'];?></td>
                    <td><?php echo date('F d, Y',strtotime($document['date']));?></td>
                    <td><a href="<?php echo $document['url'];?>" target="_blank">Download</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php
    endif;
}

// employees page content
function my_employees_contents() {

    global $wpdb;

    $results = $wpdb->get_results("
        SELECT u.*
        FROM wp_users u, wp_usermeta m
        WHERE u.ID = m.user_id
        AND m.meta_key LIKE 'wp_capabilities'
        AND (m.meta_value LIKE '%agent%' OR m.meta_value LIKE '%supervisor%')
    ");
    
    ?>

    <div class="admin-page-container" style="width: 95%; margin: 0 auto;">
        <h1> Employees</h1>

        <table id="myTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Email</th>
                    <th>Position</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $results as $user) : ?>
                    <tr>
                        <td><?php echo $user->display_name; ?></td>
                        <td><?php echo $user->user_email; ?></td>
                        <td>Test Position</td>
                        <td><a href="#">Time Entries</a> <a href="#">Edit</a> <a href="#">Delete</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    
}


/*
 * Create new custom post type post on new user registration
 */
add_action( 'user_register', 'create_employee_cpt', 10, 1 );
function create_employee_cpt( $user_id )
{
    // Get user info
    $user_info = get_userdata( $user_id );
    $user_name = $user_info->display_name;

    if (!$user_name) {
        $user_name = $user_info->user_nicename;
    }

    // Create a new post
    $user_post = array(
        'post_title'   => $user_name,
        'post_type'    => 'employees',
        'post_status'       =>  'publish'
    );


    echo '<script>console.log("PHP error: ' . $user_info->roles . '")</script>';

    // Insert the post into the database if agent or supervisor
    if(in_array('agent',$user_info->roles) || in_array('supervisor',$user_info->roles)){
        $post_id = wp_insert_post( $user_post );
        add_user_meta( $user_id, 'employee_id', $post_id );
    }

    // $field_key = "field_5e99d416b503b";
    // update_field( $field_key, $user_info->user_email, $post_id );

}

add_action('before_delete_post', 'my_deleted_post');
function my_deleted_post($post_id){ 
    global $wpdb;

    if ( "employees" == get_post_type( $post_id ) ) {
        $result = $wpdb->get_results("SELECT user_id FROM wp_usermeta WHERE meta_key = 'employee_id' AND meta_value = $post_id");

        if($result){
            wp_delete_user($result[0]->user_id);
        }
        
    }
}

add_action( 'profile_update', 'my_profile_update', 10, 2 );
function my_profile_update( $user_id, $old_user_data ) {
    global $wpdb;

    $post_id = get_user_meta( $user_id, "employee_id", true );
    $user_data = get_userdata( $user_id );

    $result = $wpdb->update( "wp_posts", array( 'post_title' => $user_data->display_name  ), array( 'ID' => $post_id ));
}


/*
 * Create custom directory hash for uploaded PDFs
 */
add_filter('wp_handle_upload_prefilter', 'excelsior_pre_upload');
add_filter('wp_handle_upload', 'excelsior_post_upload');

function excelsior_pre_upload($file){
    add_filter('upload_dir', 'excelsior_custom_upload_dir');
    return $file;
}

function excelsior_post_upload($fileinfo){
    remove_filter('upload_dir', 'excelsior_custom_upload_dir');
    return $fileinfo;
}

function excelsior_custom_upload_dir($path){    
    $extension = substr(strrchr($_POST['name'],'.'),1);
    if(!empty($path['error']) ||  $extension != 'pdf') { return $path; } //error or other filetype; do nothing. 
    $letter = md5(rand(97,122));
    $letter2 = md5(rand(97,122));
    $folder = '/' . $letter . '/' . $letter2;
    $mydir = $folder;
    $path['path'] = $path['path'] . $mydir;
    $path['url'] = $path['url'] . $mydir;
    return $path;
}

add_filter( 'rewrite_rules_array', 'cleanup_default_rewrite_rules' );
function cleanup_default_rewrite_rules( $rules ) {
    foreach ( $rules as $regex => $query ) {
        if ( strpos( $regex, 'attachment' ) || strpos( $query, 'attachment' ) ) {
            unset( $rules[ $regex ] );
        }
    }

    return $rules;
}


add_filter( 'attachment_link', 'cleanup_attachment_link' );
function cleanup_attachment_link( $link ) {
    return;
}


add_action( 'wp_ajax_user_punch_in', [ 'PunchIn', 'user_punch_in' ] );
add_action( 'wp_ajax_nopriv_user_punch_in', [ 'PunchIn', 'user_punch_in' ] );

class PunchIn {

    private static $_instance;

    public static function getInstance() {
        if ( ! self::$_instance ) {
            self::$_instance = new PunchIn();
        }

        return self::$_instance;
    }

    public static function user_punch_in() {
        $punch_id = self::getInstance()->punch_in();
        wp_send_json_success( array(
            'success'         => __( 'Attendance has been save successfully', 'Divi' ),
            'punch_id'        => $punch_id,
            'punch_in_status' => self::getInstance()->punch_in_status(),
        ) );
        die();
    }

    function punch_in() {
        
        global $wpdb;
        
        $user_id   = ( isset( $post['user_id'] ) && $post['user_id'] ) ? intval( $post['user_id'] ) : get_current_user_id();
        $table     = $wpdb->prefix . 'excelsior_attendance';
        $data      = array(
            'user_id'  => $user_id,
            'date'     => current_time( 'mysql' ),
            'punch_in' => current_time( 'mysql' ),
        );
        $format = array( '%d', '%s', '%s' );

        $insert = $wpdb->insert( $table, $data, $format );

        if ( $insert ) {
            return $wpdb->insert_id;
        }
    }


    function punch_in_status( $user_id = false ) {
        $current_time    = date( 'Y-m-d 00:00:00', strtotime( current_time( 'mysql' ) ) );
        $user_id         = $user_id ? absint( $user_id ) : get_current_user_id();
        $punch_in_status = 'enable';

        global $wpdb;
        $table = $wpdb->prefix . 'excelsior_attendance';

        $punch = $wpdb->get_row( 
            $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "excelsior_attendance WHERE `date` >= %s AND `user_id` = %d ORDER BY id DESC LIMIT 1", $current_time, $user_id ) 
        );
        
        $punch_in  = isset( $punch->punch_in ) ? $punch->punch_in : 0;
        $punch_out = isset( $punch->punch_out ) ? $punch->punch_out : 0;
        
        if ( $punch_in > $punch_out ) {
            $punch_in_status = 'disable';
        }

        return $punch_in_status;
    }

}

//customize cpt columns
add_filter( 'manage_employees_posts_columns', 'excelsior_employee_columns' );
function excelsior_employee_columns( $columns ) {
  
  
    $columns = array(
      'cb' => $columns['cb'],
      'image' => __( 'Image' ),
      'title' => __( 'Title' ),
      'price' => __( 'Price', 'smashing' ),
      'area' => __( 'Area', 'smashing' ),
    );
  
  
  return $columns;
}

?>