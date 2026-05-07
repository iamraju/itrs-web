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

/**
 * Convert rich content into clean plain text for metadata.
 */
function itrs_ai_plain_text(string $content): string
{
    $text = wp_strip_all_tags(strip_shortcodes($content), true);
    $text = preg_replace('/\s+/u', ' ', $text);

    return trim((string) $text);
}

/**
 * Resolve a meta description for the current request.
 */
function itrs_ai_get_meta_description(): string
{
    if (is_singular()) {
        $post = get_queried_object();
        if ($post instanceof \WP_Post) {
            $excerpt = trim((string) $post->post_excerpt);
            if ('' !== $excerpt) {
                return wp_trim_words(itrs_ai_plain_text($excerpt), 28, '');
            }

            $content = itrs_ai_plain_text((string) $post->post_content);
            if ('' !== $content) {
                return wp_trim_words($content, 28, '');
            }
        }
    }

    if (is_home()) {
        $posts_page_id = (int) get_option('page_for_posts');
        if ($posts_page_id > 0) {
            $posts_page = get_post($posts_page_id);
            if ($posts_page instanceof \WP_Post) {
                $excerpt = trim((string) $posts_page->post_excerpt);
                if ('' !== $excerpt) {
                    return wp_trim_words(itrs_ai_plain_text($excerpt), 28, '');
                }
            }
        }
    }

    if (is_archive()) {
        $archive_description = itrs_ai_plain_text((string) get_the_archive_description());
        if ('' !== $archive_description) {
            return wp_trim_words($archive_description, 28, '');
        }
    }

    if (is_search()) {
        return sprintf(__('Search results for %s on %s.', 'itrs-ai'), get_search_query(), get_bloginfo('name'));
    }

    $tagline = trim((string) get_bloginfo('description'));
    if ('' !== $tagline) {
        return $tagline;
    }

    return sprintf(
        __('%s provides custom web and mobile application development services.', 'itrs-ai'),
        get_bloginfo('name')
    );
}

/**
 * Resolve a canonical URL for the current request.
 */
function itrs_ai_get_canonical_url(): string
{
    if (is_404()) {
        return '';
    }

    if (get_query_var('paged')) {
        return (string) get_pagenum_link((int) get_query_var('paged'));
    }

    if (is_front_page()) {
        return home_url('/');
    }

    if (is_home()) {
        $posts_page_id = (int) get_option('page_for_posts');
        if ($posts_page_id > 0) {
            return get_permalink($posts_page_id);
        }
    }

    if (is_singular()) {
        return (string) get_permalink();
    }

    if (is_category() || is_tag() || is_tax()) {
        $term = get_queried_object();
        if ($term instanceof \WP_Term) {
            return (string) get_term_link($term);
        }
    }

    if (is_post_type_archive()) {
        $post_type = get_query_var('post_type');
        if (is_array($post_type)) {
            $post_type = reset($post_type);
        }
        return (string) get_post_type_archive_link((string) $post_type);
    }

    if (is_author()) {
        return get_author_posts_url((int) get_query_var('author'));
    }

    return home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));
}

/**
 * Resolve a representative image for social previews.
 */
function itrs_ai_get_seo_image_url(): string
{
    if (is_singular() && has_post_thumbnail()) {
        $thumbnail = get_the_post_thumbnail_url(get_queried_object_id(), 'full');
        if (is_string($thumbnail) && '' !== $thumbnail) {
            return $thumbnail;
        }
    }

    $banner_file = get_template_directory() . '/assets/images/home-banner.jpg';
    if (is_front_page() && file_exists($banner_file)) {
        return get_template_directory_uri() . '/assets/images/home-banner.jpg';
    }

    $logo = itrs_ai_logo_uri();
    if ('' !== $logo) {
        return $logo;
    }

    if (file_exists($banner_file)) {
        return get_template_directory_uri() . '/assets/images/home-banner.jpg';
    }

    return '';
}

/**
 * Build structured data for the current request.
 *
 * @return array<int, array<string, mixed>>
 */
function itrs_ai_get_schema_data(): array
{
    $site_name = get_bloginfo('name');
    $site_url = home_url('/');
    $logo = itrs_ai_logo_uri();
    $canonical = itrs_ai_get_canonical_url();
    $description = itrs_ai_get_meta_description();
    $schema = [];

    $organization = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $site_name,
        'url' => $site_url,
    ];

    if ('' !== $logo) {
        $organization['logo'] = $logo;
    }

    $schema[] = $organization;

    if (is_front_page()) {
        $website = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $site_name,
            'url' => $site_url,
        ];

        if ('' !== $description) {
            $website['description'] = $description;
        }

        $schema[] = $website;
    }

    if (is_singular()) {
        $post = get_queried_object();
        if ($post instanceof \WP_Post) {
            $page_schema = [
                '@context' => 'https://schema.org',
                '@type' => is_single() ? 'Article' : 'WebPage',
                'headline' => get_the_title($post),
                'url' => $canonical,
                'description' => $description,
                'datePublished' => get_the_date(DATE_W3C, $post),
                'dateModified' => get_the_modified_date(DATE_W3C, $post),
            ];

            if ('' !== itrs_ai_get_seo_image_url()) {
                $page_schema['image'] = itrs_ai_get_seo_image_url();
            }

            $schema[] = $page_schema;
        }
    }

    return $schema;
}

/**
 * Output built-in SEO metadata when no dedicated SEO plugin is active.
 */
function itrs_ai_render_seo_meta(): void
{
    if (! apply_filters('itrs_ai_enable_builtin_seo', true)) {
        return;
    }

    if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('SEOPRESS_VERSION')) {
        return;
    }

    $title = wp_get_document_title();
    $description = itrs_ai_get_meta_description();
    $canonical = itrs_ai_get_canonical_url();
    $image = itrs_ai_get_seo_image_url();
    $og_type = is_single() ? 'article' : 'website';

    if ('' !== $description) {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    }

    if ('' !== $canonical) {
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
    }

    echo '<meta property="og:locale" content="' . esc_attr(str_replace('_', '-', get_locale())) . '">' . "\n";
    echo '<meta property="og:type" content="' . esc_attr($og_type) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";

    if ('' !== $description) {
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    }

    if ('' !== $canonical) {
        echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
    }

    if ('' !== $image) {
        echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
    }

    echo '<meta name="twitter:card" content="' . esc_attr('' !== $image ? 'summary_large_image' : 'summary') . '">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";

    $schema = itrs_ai_get_schema_data();
    if ([] !== $schema) {
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }
}
add_action('wp_head', 'itrs_ai_render_seo_meta', 1);

/**
 * Improve robots directives for low-value pages.
 *
 * @param array<string, bool> $robots Existing directives.
 * @return array<string, bool>
 */
function itrs_ai_filter_wp_robots(array $robots): array
{
    if (is_search() || is_404()) {
        $robots['noindex'] = true;
        $robots['nofollow'] = true;
    }

    return $robots;
}
add_filter('wp_robots', 'itrs_ai_filter_wp_robots');
