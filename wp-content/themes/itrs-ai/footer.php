<?php
/**
 * Theme footer.
 *
 * @package itrs-ai
 */
?>
    </main>

    <footer class="relative mt-20 border-t border-white/10 bg-slate-950/95">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_0%_0%,rgba(243,130,80,0.3),transparent_40%),radial-gradient(circle_at_100%_70%,rgba(16,185,129,0.18),transparent_42%)]"></div>
        <div class="mx-auto grid w-full max-w-7xl gap-10 px-4 py-14 md:grid-cols-3 md:px-8">
            <div>
                <h3 class="font-display text-2xl font-bold text-white">Build With Confidence</h3>
                <p class="mt-3 text-sm text-slate-300">A practical engineering team for web and mobile products, from kickoff to launch.</p>
            </div>

            <div>
                <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-400">Quick Links</h4>
                <?php
                wp_nav_menu([
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => 'mt-4 space-y-2 text-sm text-slate-200',
                    'fallback_cb'    => 'itrs_ai_footer_fallback_menu',
                ]);
                ?>
            </div>

            <div>
                <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-400">Start a Project</h4>
                <p class="mt-4 text-sm text-slate-300">Share your goals, scope, and timeline. We will respond with a focused execution plan.</p>
                <a href="<?php echo esc_url(itrs_ai_page_url('contact-us')); ?>" class="mt-5 inline-flex rounded-xl bg-brand px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110">Contact Us</a>
            </div>
        </div>

        <div class="border-t border-white/10 py-5">
            <p class="text-center text-xs tracking-[0.12em] text-slate-500">Copyright <?php echo esc_html(date('Y')); ?> ITRS Nepal. All rights reserved.</p>
        </div>
    </footer>
</div>

<?php wp_footer(); ?>
</body>
</html>
