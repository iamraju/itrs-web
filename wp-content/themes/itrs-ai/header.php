<?php
/**
 * Theme header.
 *
 * @package itrs-ai
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-amber-50 text-slate-800 antialiased'); ?>>
<?php wp_body_open(); ?>
<div class="relative min-h-screen overflow-x-hidden">
    <header class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/90 backdrop-blur-xl">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_20%_20%,rgba(243,130,80,0.35),transparent_35%),radial-gradient(circle_at_75%_0%,rgba(59,130,246,0.3),transparent_40%)]"></div>
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-4 md:px-8">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="group flex items-center gap-3">
                <?php if ('' !== itrs_ai_logo_uri()) : ?>
                    <img src="<?php echo esc_url(itrs_ai_logo_uri()); ?>" alt="<?php bloginfo('name'); ?>" class="h-11 w-auto rounded-md bg-white/90 p-1" />
                <?php else : ?>
                    <div class="grid h-11 w-11 place-items-center rounded-2xl bg-brand shadow-[0_10px_35px_-15px_rgba(243,130,80,0.9)]">
                        <span class="font-display text-lg font-bold text-white">IT</span>
                    </div>
                <?php endif; ?>
                <div>
                    <p class="font-display text-xl leading-none tracking-tight text-white">ITRS Nepal</p>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Web & Mobile Studio</p>
                </div>
            </a>

            <button id="mobile-menu-toggle" class="inline-flex rounded-xl border border-white/20 p-2 text-white md:hidden" aria-label="Toggle menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5m-16.5 5.25h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <nav class="hidden items-center gap-6 md:flex">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'flex items-center gap-6 text-sm font-medium text-slate-200',
                    'fallback_cb'    => 'itrs_ai_primary_fallback_menu',
                ]);
                ?>
                <a href="<?php echo esc_url(itrs_ai_page_url('project-planner')); ?>" class="rounded-xl bg-brand px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110">Plan Your Project</a>
            </nav>
        </div>

        <div id="mobile-menu" class="hidden border-t border-white/10 bg-slate-950 px-4 py-4 md:hidden">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'space-y-3 text-sm font-medium text-slate-100',
                'fallback_cb'    => 'itrs_ai_primary_fallback_menu_mobile',
            ]);
            ?>
        </div>
    </header>

    <main class="bg-[linear-gradient(180deg,#fff7ef_0%,#fffaf3_45%,#fff8f2_100%)]">
