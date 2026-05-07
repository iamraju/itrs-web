<?php
/**
 * Theme helper functions.
 *
 * @package itrs-ai
 */

/**
 * Resolve a page URL from slug with a fallback hash.
 */
function itrs_ai_page_url(string $slug): string
{
    $page = get_page_by_path($slug);
    if ($page instanceof \WP_Post) {
        return get_permalink($page);
    }

    return '#';
}

/**
 * Resolve the blog index URL.
 */
function itrs_ai_blog_url(): string
{
    if ((int) get_option('page_for_posts') > 0) {
        return get_permalink((int) get_option('page_for_posts'));
    }

    return get_post_type_archive_link('post') ?: home_url('/blog/');
}

/**
 * Resolve logo path if a static logo file exists in theme root.
 */
function itrs_ai_logo_uri(): string
{
    $logo_file = get_template_directory() . '/logo.png';
    if (file_exists($logo_file)) {
        return get_template_directory_uri() . '/logo.png';
    }

    return '';
}
