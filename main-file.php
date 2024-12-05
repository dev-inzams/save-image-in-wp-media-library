<?php

function add_plugin_images_to_media_library() {
    $upload_dir = wp_upload_dir();
    $image_paths = array(
        'assets/img/technuxt-logo.png',
        'assets/img/amarpay.png',
        'assets/temp-img/nuxt_panel_template_light_paste.png',
        'assets/temp-img/nuxt_panel_template_dark_yellow.png',
        'assets/temp-img/nuxt_panel_template_dark_paste.png',
        'assets/temp-img/nuxt_panel_template_dark_green.png',
    );

    foreach ($image_paths as $relative_path) {
        $file_path = plugin_dir_path(__FILE__) . $relative_path;
        $filename = basename($file_path);

        if (file_exists($file_path)) {
            // Check if the image already exists
            $attachment_id = image_exists_in_media_library($filename);

            if (!$attachment_id) {
                // Copy file to the uploads directory
                $upload_file = wp_upload_bits($filename, null, file_get_contents($file_path));

                if (!$upload_file['error']) {
                    $filetype = wp_check_filetype($filename, null);
                    $attachment = array(
                        'guid' => $upload_file['url'],
                        'post_mime_type' => $filetype['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );

                    // Insert the attachment
                    $attachment_id = wp_insert_attachment($attachment, $upload_file['file']);

                    // Generate the metadata for the attachment and update the database record
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
                    wp_update_attachment_metadata($attachment_id, $attach_data);
                }
            }
        }
    }
}

function image_exists_in_media_library($filename) {
    global $wpdb;
    $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid LIKE '%%%s%%' AND post_type='attachment'", $filename);
    return $wpdb->get_var($query);
}

add_action('admin_init', 'add_plugin_images_to_media_library');






function nuxt_panel_get_image_id($image_name, $class = '') {
    $image_id = image_exists_in_media_library($image_name);

    if ($image_id) {
        echo wp_get_attachment_image($image_id, 'full',false, array('class' =>$class));
    } else {
        echo '<p>Image not found in media library.</p>';
    }
}
