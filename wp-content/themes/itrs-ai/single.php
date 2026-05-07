<?php
/**
 * Single post template.
 *
 * @package itrs-ai
 */

get_header();
?>
<section class="mx-auto w-full max-w-4xl px-4 py-14 md:px-8 md:py-20">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article class="rounded-4xl border border-brand/20 bg-white p-7 md:p-10 shadow-[0_18px_50px_-32px_rgba(243,130,80,0.5)]">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand"><?php echo esc_html(get_the_date()); ?></p>
                <h1 class="mt-3 font-display text-4xl font-semibold text-slate-900"><?php the_title(); ?></h1>
                <div class="prose mt-8 max-w-none prose-headings:font-display prose-a:text-brand prose-p:text-slate-700 prose-li:text-slate-700">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    <?php endif; ?>
</section>
<?php
get_footer();
