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
 * Check whether SMTP delivery is enabled.
 */
function itrs_ai_smtp_enabled(): bool
{
    if (! defined('ITRS_SMTP_ENABLED')) {
        return false;
    }

    return true === constant('ITRS_SMTP_ENABLED') || '1' === (string) constant('ITRS_SMTP_ENABLED');
}

/**
 * Get an SMTP setting from constants.
 */
function itrs_ai_smtp_setting(string $name, string $default = ''): string
{
    return defined($name) ? (string) constant($name) : $default;
}

/**
 * Configure PHPMailer to use SMTP.
 *
 * @param PHPMailer $phpmailer PHPMailer instance provided by WordPress.
 */
function itrs_ai_configure_smtp($phpmailer): void
{
    if (! itrs_ai_smtp_enabled()) {
        return;
    }

    $host = trim(itrs_ai_smtp_setting('ITRS_SMTP_HOST'));
    $username = trim(itrs_ai_smtp_setting('ITRS_SMTP_USERNAME'));
    $password = itrs_ai_smtp_setting('ITRS_SMTP_PASSWORD');
    $port = (int) itrs_ai_smtp_setting('ITRS_SMTP_PORT', '587');
    $secure = strtolower(trim(itrs_ai_smtp_setting('ITRS_SMTP_SECURE', 'tls')));

    if ('' === $host || '' === $username || '' === $password) {
        return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host = $host;
    $phpmailer->Port = $port > 0 ? $port : 587;
    $phpmailer->SMTPAuth = true;
    $phpmailer->Username = $username;
    $phpmailer->Password = $password;
    $phpmailer->CharSet = 'UTF-8';

    if ('ssl' === $secure || 'tls' === $secure) {
        $phpmailer->SMTPSecure = $secure;
    }
}
add_action('phpmailer_init', 'itrs_ai_configure_smtp');

/**
 * Use configured SMTP sender email when enabled.
 */
function itrs_ai_mail_from(string $original): string
{
    if (! itrs_ai_smtp_enabled()) {
        return $original;
    }

    $from = trim(itrs_ai_smtp_setting('ITRS_SMTP_FROM_EMAIL'));
    return is_email($from) ? $from : $original;
}
add_filter('wp_mail_from', 'itrs_ai_mail_from');

/**
 * Use configured SMTP sender name when enabled.
 */
function itrs_ai_mail_from_name(string $original): string
{
    if (! itrs_ai_smtp_enabled()) {
        return $original;
    }

    $name = trim(itrs_ai_smtp_setting('ITRS_SMTP_FROM_NAME'));
    return '' !== $name ? $name : $original;
}
add_filter('wp_mail_from_name', 'itrs_ai_mail_from_name');

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
 * Build branded HTML email for lead notifications.
 *
 * @param string               $form_type     contact|planner.
 * @param array<string,string> $fields        Sanitized lead fields.
 * @param string               $email_subject Already formatted subject.
 * @return string
 */
function itrs_ai_build_lead_email_html(string $form_type, array $fields, string $email_subject): string
{
    $brand_name = (string) get_bloginfo('name');
    if ('' === trim($brand_name)) {
        $brand_name = 'ITRS Nepal';
    }

    $logo_uri = function_exists('itrs_ai_logo_uri') ? itrs_ai_logo_uri() : '';
    $has_logo = '' !== trim((string) $logo_uri);
    $submitted_at = wp_date('M j, Y g:i A');

    $rows = [
        ['label' => 'Name', 'value' => $fields['name'] ?? ''],
        ['label' => 'Email', 'value' => $fields['email'] ?? ''],
        ['label' => 'Phone', 'value' => $fields['phone'] ?? ''],
        ['label' => 'Company', 'value' => $fields['company'] ?? ''],
    ];

    if ('planner' === $form_type) {
        $rows[] = ['label' => 'Project Type', 'value' => $fields['project_type'] ?? ''];
        $rows[] = ['label' => 'Budget', 'value' => $fields['budget'] ?? ''];
        $rows[] = ['label' => 'Timeline', 'value' => $fields['timeline'] ?? ''];
    }

    $details_html = '';
    foreach ($rows as $row) {
        if ('' === trim((string) $row['value'])) {
            continue;
        }

        $details_html .= '<tr>'
            . '<td style="padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#475569;font-size:14px;font-weight:600;width:160px;">' . esc_html($row['label']) . '</td>'
            . '<td style="padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#0f172a;font-size:14px;">' . esc_html((string) $row['value']) . '</td>'
            . '</tr>';
    }

    $message = isset($fields['message']) ? (string) $fields['message'] : '';
    $message_html = nl2br(esc_html($message));

    return '<!doctype html>'
        . '<html><body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;padding:24px 10px;">'
        . '<tr><td align="center">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:720px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">'
        . '<tr><td style="padding:22px 24px;background:linear-gradient(120deg,#0f172a 0%,#1e293b 100%);">'
        . ($has_logo ? '<img src="' . esc_url($logo_uri) . '" alt="' . esc_attr($brand_name) . '" style="height:48px;max-width:180px;display:block;background:#ffffff;border-radius:8px;padding:4px;" />' : '')
        . '<div style="margin-top:10px;color:#ffffff;font-size:21px;font-weight:700;line-height:1.2;">' . esc_html($brand_name) . '</div>'
        . '<div style="margin-top:4px;color:#cbd5e1;font-size:13px;">New ' . esc_html('planner' === $form_type ? 'Project Planner' : 'Contact') . ' submission</div>'
        . '</td></tr>'
        . '<tr><td style="padding:20px 24px 8px 24px;color:#0f172a;font-size:18px;font-weight:700;">' . esc_html($email_subject) . '</td></tr>'
        . '<tr><td style="padding:0 24px 16px 24px;color:#64748b;font-size:13px;">Received: ' . esc_html($submitted_at) . '</td></tr>'
        . '<tr><td style="padding:0 24px 16px 24px;">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">'
        . $details_html
        . '</table>'
        . '</td></tr>'
        . '<tr><td style="padding:0 24px 24px 24px;">'
        . '<div style="color:#475569;font-size:13px;font-weight:700;margin-bottom:8px;">Message</div>'
        . '<div style="padding:14px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;color:#0f172a;font-size:14px;line-height:1.6;">' . $message_html . '</div>'
        . '</td></tr>'
        . '</table>'
        . '</td></tr></table>'
        . '</body></html>';
}

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

    // Honeypot trap for simple bot submissions.
    $website = isset($_POST['website']) ? trim((string) sanitize_text_field(wp_unslash($_POST['website']))) : '';
    if ('' !== $website) {
        wp_safe_redirect(add_query_arg('form_status', 'spam_blocked', wp_get_referer() ?: home_url('/')));
        exit;
    }

    // Basic per-IP rate limiting to reduce repeated spam bursts.
    $remote_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
    $rate_key = 'itrs_form_rate_' . md5($remote_ip . '|' . $form_type);
    $attempts = (int) get_transient($rate_key);

    if ($attempts >= 5) {
        wp_safe_redirect(add_query_arg('form_status', 'rate_limited', wp_get_referer() ?: home_url('/')));
        exit;
    }

    set_transient($rate_key, $attempts + 1, 15 * MINUTE_IN_SECONDS);

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

    $lead_recipient = defined('ITRS_LEAD_RECIPIENT_EMAIL')
        ? (string) constant('ITRS_LEAD_RECIPIENT_EMAIL')
        : (string) get_option('admin_email');

    if (is_email($lead_recipient)) {
        $email_subject = sprintf('[ITRS] %s from %s', 'planner' === $form_type ? 'Project Planner' : 'Contact Form', $name);
        $email_fields = [
            'name'         => $name,
            'email'        => $email,
            'phone'        => $phone,
            'company'      => $company,
            'project_type' => $project_type,
            'budget'       => $budget,
            'timeline'     => $timeline,
            'message'      => $message,
        ];

        $email_body = itrs_ai_build_lead_email_html($form_type, $email_fields, $email_subject);
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . sanitize_email($email),
        ];

        wp_mail($lead_recipient, $email_subject, $email_body, $headers);
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
