<?php

return [
/* middleware applied to the menu builder routes.
   keep "web" (session + CSRF) and an authentication middleware here,
   otherwise anyone can create, edit and delete your menus */

   'middleware' => ['web', 'auth'],

/* name of a Gate ability the user must have to use the builder, e.g. 'manage-menus'
   (define it with Gate::define('manage-menus', fn ($user) => $user->is_admin)). null = no check */

   'gate' => null,

/* cache the menus returned by Menu::get() / Menu::getByName(). The cache is cleared when a menu
   or one of its items is changed through the models (the builder always does) */

   'cache' => [
       'enabled' => false,
       'store' => null, // null = default cache store
       'ttl' => 3600, // seconds
   ],

/* you can set your own table prefix here */
    'table_prefix' => 'admin_',

/* you can set your own table names */
    'table_name_menus' => 'menus',

    'table_name_items' => 'menu_items',

/* you can set your route path*/
    'route_path' => '/harimayco/',

/* here you can make menu items visible to specific roles */
    'use_roles' => false,

/* If use_roles = true above then must set the table name, primary key and title field to get roles details */
    'roles_table' => 'roles',

    'roles_pk' => 'id', // primary key of the roles table

    'roles_title_field' => 'name', // display name (field) of the roles table
];
