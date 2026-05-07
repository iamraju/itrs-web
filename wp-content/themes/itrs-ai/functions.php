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
    add_post_type_support('page', 'excerpt');
    remove_action('wp_head', 'rel_canonical');

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

    // Enqueue reCAPTCHA v3 if site key is configured.
    $recaptcha_site_key = itrs_ai_get_recaptcha_site_key();
    if ($recaptcha_site_key) {
        wp_enqueue_script(
            'recaptcha-v3',
            'https://www.google.com/recaptcha/api.js?render=' . esc_attr($recaptcha_site_key),
            [],
            null,
            false
        );

        wp_add_inline_script('recaptcha-v3', '
            grecaptcha.ready(function() {
                grecaptcha.execute("' . esc_attr($recaptcha_site_key) . '", {action: "form_submit"}).then(function(token) {
                    document.querySelectorAll("input[name=\'recaptcha_token\']").forEach(function(el) {
                        el.value = token;
                    });
                });
            });
        ');
    }
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
 * Get reCAPTCHA v3 site key from wp-config constants.
 */
function itrs_ai_get_recaptcha_site_key(): string
{
    return defined('RECAPTCHA_V3_SITE_KEY') ? constant('RECAPTCHA_V3_SITE_KEY') : '';
}

/**
 * Get reCAPTCHA v3 secret key from wp-config constants.
 */
function itrs_ai_get_recaptcha_secret_key(): string
{
    return defined('RECAPTCHA_V3_SECRET_KEY') ? constant('RECAPTCHA_V3_SECRET_KEY') : '';
}

/**
 * Verify reCAPTCHA v3 token.
 *
 * @param string $token The reCAPTCHA token from the form.
 * @return bool True if verification succeeds and score is acceptable, false otherwise.
 */
function itrs_ai_verify_recaptcha_token(string $token): bool
{
    $secret_key = itrs_ai_get_recaptcha_secret_key();
    if ('' === $secret_key) {
        return true; // Skip verification if keys not configured.
    }

    $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'body' => [
            'secret'   => $secret_key,
            'response' => $token,
        ],
    ]);

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    // Accept score >= 0.5 (0 = very likely bot, 1 = very likely human).
    return isset($result['success']) && $result['success'] && isset($result['score']) && $result['score'] >= 0.5;
}

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

    // Verify reCAPTCHA token if configured.
    $recaptcha_token = isset($_POST['recaptcha_token']) ? sanitize_text_field(wp_unslash($_POST['recaptcha_token'])) : '';
    if ('' !== itrs_ai_get_recaptcha_secret_key() && ! itrs_ai_verify_recaptcha_token($recaptcha_token)) {
        wp_safe_redirect(add_query_arg('form_status', 'recaptcha_failed', wp_get_referer() ?: home_url('/')));
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
