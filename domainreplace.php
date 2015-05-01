<?php
/*
Plugin Name: Domain Replace
Plugin URI: http://duogeek.com/products/plugins/domain-replace/
Description: Changes URL in the shortest and fastest way.
Version: 1.3.8
Author: DuoGeek
Author URI: http://duogeek.com
Author Email: duogeek.dev@gmail.com
License: GPLv2 or later

  Copyright 2014 DuoGeek

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) wp_die( __( 'Sorry hackers! This is not your place!', 'df' ) );

if( ! defined( 'DUO_PLUGIN_URI' ) ) define( 'DUO_PLUGIN_URI', plugin_dir_url( __FILE__ ) );;

require 'duogeek/duogeek-panel.php';

define( 'BRAND', 'Domain Replace Admin Panel' );


register_activation_hook( __FILE__, 'dr_plugin_activate' );
function dr_plugin_activate() {
    update_option( 'dr_plugin_do_activation_redirect', true );
}

add_action( 'admin_init', 'dr_plugin_redirect' );
function dr_plugin_redirect() {
    if ( get_option( 'dr_plugin_do_activation_redirect', false ) ) {
        delete_option( 'dr_plugin_do_activation_redirect' );
        wp_redirect( admin_url( DUO_SETTINGS_PAGE ) );
    }
}


add_filter( 'duogeek_submenu_pages', 'dr_option_menu' );

function dr_option_menu( $submenus )
{
    $submenus[] = array(
        'title' => 'Domain Replace Options',
        'menu_title' => 'Domain Replace Options',
        'capability' => 'manage_options',
        'slug' => 'dr-convert',
        'function' => 'dr_options'
    );

    return $submenus;
}

function dr_options()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    if (isset($_POST['option_save'])) {
        $options = $_POST;


        if (!isset($_POST['a2rc_nonce_val'])) {
            $msg = "You are not allowed to make this change&res=error";
        } elseif (!wp_verify_nonce($_POST['a2rc_nonce_val'], 'a2rc_nonce')) {
            $msg = "You are not allowed to make this change&res=error";
        } else {
            update_option('dr_options', $options);
            $msg = 'Data Saved';
        }

        wp_redirect(admin_url('admin.php?page=dr-convert&msg=' . str_replace(' ', '+', $msg)));
    }

    $options = get_option('dr_options', true);


    if (!isset($options['options']['newurl']) || $options['options']['newurl'] == ''){
        $options = array();
        $options['options'] = array();
        $options['options']['newurl'] = get_site_url();
    }


    if (isset($_REQUEST['replace_url']) && $_REQUEST['replace_url'] == 'now') {

        if( $options['options']['oldurl'] == "" || $options['options']['newurl'] == "" ){
            $msg = "Please save OLD and NEW URLs first&res=error";
        }else{
            global $wpdb;

            $db = "Tables_in_" . DB_NAME;
            $tables = $wpdb->get_results('SHOW TABLES');
            foreach ($tables as $q) {
                $table = $q->$db;
                $cols = $wpdb->get_results('SHOW COLUMNS FROM ' . $table);

                $query = "UPDATE $table SET ";
                foreach ($cols as $col) {
                    $query .= $col->Field . " = REPLACE({$col->Field}, '{$options['options']['oldurl']}/', '{$options['options']['newurl']}/'), ";
                }
                $query = rtrim($query, ", ");
                $wpdb->query($query);
            }
            $msg = "All URLs are replaced.";
        }


        wp_redirect( admin_url( 'admin.php?page=dr-convert&msg=' . str_replace( ' ', '+', $msg ) ) );
    }
    ?>
    <div class="wrap duo_prod_panel">
        <h2><?php echo BRAND ?></h2>
        <?php if (isset($_REQUEST['msg']) && $_REQUEST['msg'] != '') { ?>
            <div class="<?php echo isset( $_REQUEST['res'] ) ? $_REQUEST['res'] : 'updated' ?>">
                <p>
                    <?php echo str_replace('+', ' ', $_REQUEST['msg']); ?>
                </p>
            </div>
        <?php } ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox">
                        <h3 class="hndle">Instruction</h3>
                        <div class="inside">
                            <p><strong>DO NOT click on "Replace URL Now" before you save your OLD and NEW URLs.</strong></p>
                            <p>For detailed instructions and video tutorial, please visit the <a href="http://duogeek.com/products/plugins/domain-replace/" target="_blank">plugin page.</a></p>
                        </div>
                    </div>
                    <div class="postbox">
                        <h3 class="hndle">Save your URLs</h3>
                        <div class="inside">
                            <form action="<?php echo admin_url( 'admin.php?page=dr-convert&noheader=true' ) ?>" method="post">
                                <?php wp_nonce_field('a2rc_nonce', 'a2rc_nonce_val'); ?>
                                <table cellpadding="5" cellspacing="5">
                                    <tr>
                                        <th>OLD URL [Without Trailing Slash]</th>
                                        <td>
                                            <input type="text" name="options[oldurl]" value="<?php echo isset($options['options']['oldurl']) && $options['options']['oldurl'] != '' ? $options['options']['oldurl'] : '' ?>">
                                            The URL of your OLD website, it will be replaced with the NEW one!
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>NEW URL [Without Trailing Slash]</th>
                                        <td>
                                            <input type="text" name="options[newurl]" value="<?php echo $options['options']['newurl'] ?>">
                                            The URL of your NEW website, OLD URL will be replaced with this one!
                                        </td>
                                    </tr>
                                </table>
                                <p><input type="submit" class="button button-primary" name="option_save" value="Save" style="width: 100px; text-align: center;"></p>
                            </form>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle">Replace Domain URL</h3>
                        <div class="inside">
                            <p style="color: red"><b>USE WITH CAUTION !!!<br>Please read the following before replacing.</b></p>
                            <ul>
                                <li>► Take a backup of entire current database. You may already have one in your backup when you moved the site from old domain.</li>
                                <li>► This is an expensive query if you have a quite large database. In that case, please deactivate all plugins and activate default WordPress theme.</li>
                                <li>► Fingers crossed !</li>
                            </ul>
                            <p><br /><a class="button button-primary" href="<?php echo admin_url('admin.php?page=dr-convert&replace_url=now&noheader=true') ?>">Replace URL Now</a>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="postbox-container" id="postbox-container-1">
                    <?php do_action( 'dg_settings_sidebar', 'free', 'dr-free' ); ?>
                </div>
            </div>
        </div>
    </div>


<?php

}
