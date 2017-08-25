<?php
/**
 * Returns the first image attached to a post.
 * @return string The image URL.
 */
function get_post_first_image() {
    global $post, $posts;

    $first_img = '';

    ob_start();
    ob_end_clean();

    $output    = preg_match_all('/<img.+src =[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);

    return ! empty( $matches[1][0] ) ? $matches[1][0] : NULL ;
}
?>
