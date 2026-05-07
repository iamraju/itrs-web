<?php
/**
 * Theme bootstrap.
 *
 * @package itrs-ai
 */

if (! defined('ITRS_AI_VERSION')) {
    define('ITRS_AI_VERSION', '1.0.0');
}

require_once get_template_directory() . '/inc/theme-helpers.php';

/**
 * Configure theme supports and menus.
 */
function itrs_ai_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary' => __('Primary Menu', 'itrs-ai'),
        'footer'  => __('Footer Menu', 'itrs-ai'),
    ]);
}
add_action('after_setup_theme', 'itrs_ai_setup');

/**
 * Enqueue frontend assets.
 */
function itrs_ai_enqueue_assets(): void
{
    $style_file = get_template_directory() . '/assets/css/main.css';
    $script_file = get_template_directory() . '/assets/js/main.js';

    wp_enqueue_style(
        'itrs-ai-main',
        get_template_directory_uri() . '/assets/css/main.css',
        [],
        file_exists($style_file) ? (string) filemtime($style_file) : constant('ITRS_AI_VERSION')
    );

    wp_enqueue_script(
        'itrs-ai-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        file_exists($script_file) ? (string) filemtime($script_file) : constant('ITRS_AI_VERSION'),
        true
    );
}
add_action('wp_enqueue_scripts', 'itrs_ai_enqueue_assets');

/**
 * Register widget areas.
 */
function itrs_ai_widgets_init(): void
{
    register_sidebar([
        'name'          => __('Footer CTA', 'itrs-ai'),
        'id'            => 'footer-cta',
        'description'   => __('Optional footer call to action area.', 'itrs-ai'),
        'before_widget' => '<div class="rounded-2xl border border-white/20 bg-white/5 p-6">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="text-lg font-semibold mb-2">',
        'after_title'   => '</h3>',
    ]);
}
add_action('widgets_init', 'itrs_ai_widgets_init');

/**
 * Register lead capture post type for form submissions.
 */
function itrs_ai_register_post_types(): void
{
    register_post_type('itrs_lead', [
        'labels' => [
            'name'          => __('Leads', 'itrs-ai'),
            'singular_name' => __('Lead', 'itrs-ai'),
        ],
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'supports'           => ['title'],
        'menu_icon'          => 'dashicons-email-alt',
        'capability_type'    => 'post',
        'exclude_from_search'=> true,
    ]);
}
add_action('init', 'itrs_ai_register_post_types');

/**
 * Handle planner and contact form submissions.
 */
function itrs_ai_handle_lead_form_submission(): void
{
    if (! isset($_POST['itrs_form_nonce'])) {
        wp_safe_redirect(home_url('/'));
        exit;
    }

    if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['itrs_form_nonce'])), 'itrs_submit_lead')) {
        wp_safe_redirect(add_query_arg('form_status', 'invalid_nonce', wp_get_referer() ?: home_url('/')));
        exit;
    }

    $form_type = isset($_POST['form_type']) ? sanitize_text_field(wp_unslash($_POST['form_type'])) : 'contact';
    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

    if ('' === $name || '' === $email || '' === $message) {
        wp_safe_redirect(add_query_arg('form_status', 'missing_required', wp_get_referer() ?: home_url('/')));
        exit;
    }

    $project_type = isset($_POST['project_type']) ? sanitize_text_field(wp_unslash($_POST['project_type'])) : '';
    $budget = isset($_POST['budget']) ? sanitize_text_field(wp_unslash($_POST['budget'])) : '';
    $timeline = isset($_POST['timeline']) ? sanitize_text_field(wp_unslash($_POST['timeline'])) : '';
    $company = isset($_POST['company']) ? sanitize_text_field(wp_unslash($_POST['company'])) : '';

    $lead_id = wp_insert_post([
        'post_type'   => 'itrs_lead',
        'post_status' => 'private',
        'post_title'  => sprintf(
            '%s - %s',
            'planner' === $form_type ? __('Project Planner', 'itrs-ai') : __('Contact Inquiry', 'itrs-ai'),
            $name
        ),
    ]);

    if (is_wp_error($lead_id) || 0 === $lead_id) {
        wp_safe_redirect(add_query_arg('form_status', 'save_failed', wp_get_referer() ?: home_url('/')));
        exit;
    }

    update_post_meta($lead_id, '_itrs_form_type', $form_type);
    update_post_meta($lead_id, '_itrs_name', $name);
    update_post_meta($lead_id, '_itrs_email', $email);
    update_post_meta($lead_id, '_itrs_phone', $phone);
    update_post_meta($lead_id, '_itrs_message', $message);
    update_post_meta($lead_id, '_itrs_company', $company);
    update_post_meta($lead_id, '_itrs_project_type', $project_type);
    update_post_meta($lead_id, '_itrs_budget', $budget);
    update_post_meta($lead_id, '_itrs_timeline', $timeline);

    $admin_email = (string) get_option('admin_email');
    if (is_email($admin_email)) {
        $email_subject = sprintf('[ITRS] %s from %s', 'planner' === $form_type ? 'Project Planner' : 'Contact Form', $name);
        $email_body = "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\nCompany: {$company}\n";
        if ('planner' === $form_type) {
            $email_body .= "Project Type: {$project_type}\nBudget: {$budget}\nTimeline: {$timeline}\n";
        }
        $email_body .= "\nMessage:\n{$message}\n";

        wp_mail($admin_email, $email_subject, $email_body);
    }

    wp_safe_redirect(add_query_arg('form_status', 'success', wp_get_referer() ?: home_url('/')));
    exit;
}
add_action('admin_post_nopriv_itrs_submit_lead', 'itrs_ai_handle_lead_form_submission');
add_action('admin_post_itrs_submit_lead', 'itrs_ai_handle_lead_form_submission');

/**
 * Shared fallback links for menu output.
 *
 * @return array<int, array{label:string,slug:string}>
 */
function itrs_ai_default_nav_items(): array
{
    return [
        ['label' => __('Home', 'itrs-ai'), 'slug' => ''],
        ['label' => __('About Us', 'itrs-ai'), 'slug' => 'about-us'],
        ['label' => __('Services', 'itrs-ai'), 'slug' => 'services'],
        ['label' => __('Process', 'itrs-ai'), 'slug' => 'process'],
        ['label' => __('Project Planner', 'itrs-ai'), 'slug' => 'project-planner'],
        ['label' => __('Contact Us', 'itrs-ai'), 'slug' => 'contact-us'],
        ['label' => __('Blog', 'itrs-ai'), 'slug' => 'blog'],
    ];
}

/**
 * Render a desktop fallback navigation.
 */
function itrs_ai_primary_fallback_menu(): void
{
    echo '<ul class="menu flex items-center gap-6 text-sm font-medium text-slate-200">';
    foreach (itrs_ai_default_nav_items() as $item) {
        $url = '' === $item['slug'] ? home_url('/') : home_url('/' . $item['slug'] . '/');
        echo '<li><a href="' . esc_url($url) . '">' . esc_html($item['label']) . '</a></li>';
    }
    echo '</ul>';
}

/**
 * Render a mobile fallback navigation.
 */
function itrs_ai_primary_fallback_menu_mobile(): void
{
    echo '<ul class="menu space-y-3 text-sm font-medium text-slate-100">';
    foreach (itrs_ai_default_nav_items() as $item) {
        $url = '' === $item['slug'] ? home_url('/') : home_url('/' . $item['slug'] . '/');
        echo '<li><a href="' . esc_url($url) . '">' . esc_html($item['label']) . '</a></li>';
    }
    echo '</ul>';
}

/**
 * Render footer fallback navigation.
 */
function itrs_ai_footer_fallback_menu(): void
{
    echo '<ul class="menu mt-4 space-y-2 text-sm text-slate-200">';
    foreach (itrs_ai_default_nav_items() as $item) {
        $url = '' === $item['slug'] ? home_url('/') : home_url('/' . $item['slug'] . '/');
        echo '<li><a href="' . esc_url($url) . '">' . esc_html($item['label']) . '</a></li>';
    }
    echo '</ul>';
}
