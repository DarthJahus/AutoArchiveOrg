<?php
/*
Plugin Name: Archive.org Auto Archiver
Description: Automatically requests archiving of new articles on Archive.org upon publishing.
Version:     1.0
Author:      Jahus
Author URI:  https://jahus.net
License:     Unlicense
*/

function archive_org_request($new_status, $old_status, $post) {
    if ($new_status == 'publish' && $old_status != 'publish') {
        $api_key = 'S3_access_key:S3_secret_key';

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
        }
    }
}
add_action('transition_post_status', 'archive_org_request', 10, 3);
?>
