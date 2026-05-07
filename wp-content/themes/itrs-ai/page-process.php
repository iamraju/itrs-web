<?php
/**
 * Process page template.
 *
 * @package itrs-ai
 */

get_header();
?>
<section class="mx-auto w-full max-w-6xl px-4 py-14 md:px-8 md:py-20">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article class="rounded-4xl border border-brand/20 bg-white p-8 md:p-12">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-brand"><?php echo esc_html(get_the_title()); ?></p>
            <h1 class="mt-3 font-display text-4xl font-semibold text-slate-900 md:text-5xl"><?php the_title(); ?></h1>
            <div class="itrs-content mt-6">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; endif; ?>
</section>
<?php
get_footer();
