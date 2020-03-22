<?php
  function xamin_do_reinstall($params = array())
  {
    global $current_user, $wpdb;

    // only admins can reset; double-check
    if (!current_user_can('administrator')) {
      return false;
    }

    // make sure the function is available to us
    if (!function_exists('wp_install')) {
      require ABSPATH . '/wp-admin/includes/upgrade.php';
    }

    // save values that need to be restored after reset
    // todo: use params to determine what gets restored after reset
    $blogname = get_option('blogname');
    $blog_public = get_option('blog_public');
    $wplang = get_option('wplang');
    $siteurl = get_option('siteurl');
    $home = get_option('home');
    //$snapshots = $this->get_snapshots();

    $active_plugins = get_option('active_plugins');
    $active_theme = wp_get_theme();

    if (!empty($params['reactivate_webhooks'])) {
      $wpwh1 = get_option('wpwhpro_active_webhooks');
      $wpwh2 = get_option('wpwhpro_activate_translations');
      $wpwh3 = get_option('ironikus_webhook_webhooks');
    }

    // for WP-CLI
    if (!$current_user->ID) {
      $tmp = get_users(array('role' => 'administrator', 'order' => 'ASC', 'order_by' => 'ID'));
      if (empty($tmp[0]->user_login)) {
        return new WP_Error(1, 'Reset failed. Unable to find any admin users in database.');
      }
      $current_user = $tmp[0];
    }

    // delete custom tables with WP's prefix
    $prefix = str_replace('_', '\_', $wpdb->prefix);
    $tables = $wpdb->get_col("SHOW TABLES LIKE '{$prefix}%'");
    foreach ($tables as $table) {
      $wpdb->query("DROP TABLE $table");
    }

    // supress errors for WP_CLI
    // todo: find a better way to supress errors and send/not send email on reset
    $result = @wp_install($blogname, $current_user->user_login, $current_user->user_email, $blog_public, '', md5(rand()), $wplang);
    $user_id = $result['user_id'];

    // restore user pass
    $query = $wpdb->prepare("UPDATE {$wpdb->users} SET user_pass = %s, user_activation_key = '' WHERE ID = %d LIMIT 1", array($current_user->user_pass, $user_id));
    $wpdb->query($query);

    // restore rest of the settings including WP Reset's
    update_option('siteurl', $siteurl);
    update_option('home', $home);
    // update_option('wp-reset', $this->options);
    // update_option('wp-reset-snapshots', $snapshots);

    // remove password nag
    if (get_user_meta($user_id, 'default_password_nag')) {
      update_user_meta($user_id, 'default_password_nag', false);
    }
    if (get_user_meta($user_id, $wpdb->prefix . 'default_password_nag')) {
      update_user_meta($user_id, $wpdb->prefix . 'default_password_nag', false);
    }

    // $meta = $this->get_meta();
    // $meta['reset_count']++;
    // $this->update_options('meta', $meta);

    // reactivate theme
    if (!empty($params['reactivate_theme'])) {
      switch_theme($active_theme->get_stylesheet());
    }

    // reactivate WP Reset
    if (!empty($params['reactivate_wpreset'])) {
      activate_plugin(plugin_basename(__FILE__));
    }

    // reactivate WP Webhooks
    if (!empty($params['reactivate_webhooks'])) {
      activate_plugin('wp-webhooks/wp-webhooks.php');
      activate_plugin('wpwh-wp-reset-webhook-integration/wpwhpro-wp-reset-webhook-integration.php');

      update_option('wpwhpro_active_webhooks', $wpwh1);
      update_option('wpwhpro_activate_translations', $wpwh2);
      update_option('ironikus_webhook_webhooks', $wpwh3);
    }

    // reactivate all plugins
    if (!empty($params['reactivate_plugins'])) {
      foreach ($active_plugins as $plugin_file) {
        activate_plugin($plugin_file);
      }
    }
    wp_clear_auth_cookie();
     wp_set_auth_cookie($user_id);
    // if (!$this->is_cli_running()) {
    //   // log out and log in the old/new user
    //   // since the password doesn't change this is potentially unnecessary
    //   wp_clear_auth_cookie();
    //   wp_set_auth_cookie($user_id);

    //   wp_redirect(admin_url() . '?wp-reset=success');
    //   exit;
    // }

    xamin_do_delete_uploads(); 
    return true; 
  }  

function xamin_do_delete_uploads()
{
    $upload_dir = wp_get_upload_dir();
    $delete_count = 0;

    xamin_delete_folder($upload_dir['basedir'], $upload_dir['basedir']);

    do_action('wp_reset_delete_uploads', $delete_count);

    return $delete_count;
} 

function xamin_delete_folder($folder, $base_folder)
{
$files = array_diff(scandir($folder), array('.', '..'));

foreach ($files as $file) {
  if (is_dir($folder . DIRECTORY_SEPARATOR . $file)) {
    xamin_delete_folder($folder . DIRECTORY_SEPARATOR . $file, $base_folder);
  } else {
    $tmp = @unlink($folder . DIRECTORY_SEPARATOR . $file);
    $delete_count = $delete_count + (int) $tmp;
  }
} // foreach

if ($folder != $base_folder) {
  $tmp = @rmdir($folder);
  $delete_count = $delete_count + (int) $tmp;
  return $tmp;
} else {
  return true;
}
} 
?>