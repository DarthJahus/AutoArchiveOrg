<?php
/*
Plugin Name: Archive.org Auto Archiver
Description: Automatically requests archiving of new articles on Archive.org upon publishing.
Version:     1.4
Author:      Jahus
Author URI:  https://jahus.net
License:     Unlicense
*/

function archive_org_settings_menu() {
    add_options_page(
        'Archive.org Auto Archiver Settings',
        'Archive.org Auto Archiver',
        'manage_options',
        'archive-org-settings',
        'archive_org_settings_page'
    );
}
add_action('admin_menu', 'archive_org_settings_menu');

function archive_org_settings_page() {
    ?>
    <div class="wrap">
        <h2>Archive.org Auto Archiver Settings</h2>
		<p>Please provide your Archive.org API keys below. You can obtain these keys from your <a href="https://archive.org/account/s3.php" target="_blank">Archive.org S3 Account Page</a>.</p>

        <form method="post" action="options.php">
            <?php settings_fields('archive-org-settings-group'); ?>
            <?php do_settings_sections('archive-org-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Archive.org Access Key:</th>
                    <td>
                        <input type="password" name="archive_org_access_key" value="<?php echo esc_attr(get_option('archive_org_access_key')); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Archive.org Secret Key:</th>
                    <td>
                        <input type="password" name="archive_org_secret_key" value="<?php echo esc_attr(get_option('archive_org_secret_key')); ?>" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function archive_org_register_settings() {
    register_setting('archive-org-settings-group', 'archive_org_access_key');
    register_setting('archive-org-settings-group', 'archive_org_secret_key');
}
add_action('admin_init', 'archive_org_register_settings');


function archive_org_request($new_status, $old_status, $post) {
    $access_key = get_option('archive_org_access_key');
    $secret_key = get_option('archive_org_secret_key');

    if ($access_key && $secret_key && $new_status == 'publish' && $old_status != 'publish') {
		$api_key = $access_key . ':' . $secret_key;
		
        $archive_api_url = 'https://web.archive.org/save';
        $headers = array(
            'Accept' => 'application/json',
            'Authorization' => 'LOW ' . $api_key,
        );
        $data = array(
            'url' => get_permalink($post->ID),
        );

        $response = wp_safe_remote_post($archive_api_url, array(
            'headers' => $headers,
            'body' => $data,
        ));

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
            error_log('Archive.org archiving request failed.');
        } else {
			add_action('admin_notices', 'archive_org_success_notice');
		}
    }
}
add_action('transition_post_status', 'archive_org_request', 10, 3);

function archive_org_success_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('Archiving request sent successfully!', 'archive-org-auto-archiver'); ?></p>
    </div>
    <?php
}

?>
