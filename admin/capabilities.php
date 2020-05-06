<?php 

function hrm_manager_role_key() {
    return 'hrm_manager';
}

function hrm_manager_capability() {
    return array(
        'manage_organization',
        'view_employee_dashboard',

    );
}

function hrm_employee_capability() {
    return array(
        'view_employee_dashboard',
    );
}

function hrm_employee_role_key() {
    return 'hrm_employee';
}

function hrm_get_roles( $role = false ) {
    $roles = array(
        hrm_employee_role_key() => 'Employee',
        hrm_manager_role_key()  => 'Manager',
    );

    if ( $role ) {
        return $roles[$role];
    }

    return $roles;
}

function hrm_set_capability() {
    hrm_set_manager_capability();
    hrm_set_employee_capability();
    hrm_set_administrator_capability();
}

function hrm_set_manager_capability() {
    $role = get_role( hrm_manager_role_key() );
    
    
    foreach ( hrm_manager_capability() as $key => $cap ) {
        $role->add_cap( $cap );
    }
}

function hrm_set_employee_capability() {
    $role = get_role( hrm_employee_role_key() );

    foreach ( hrm_employee_capability() as $key => $cap ) {
        $role->add_cap( $cap );
    }
}

function hrm_set_administrator_capability() {
    $role = get_role( 'administrator' );

    foreach ( hrm_manager_capability() as $key => $cap ) {
        $role->add_cap( $cap );
    }
}
