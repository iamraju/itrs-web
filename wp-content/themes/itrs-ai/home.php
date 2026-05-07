<?php
/**
 * Blog index template.
 *
 * @package itrs-ai
 */

get_header();
?>
<section class="mx-auto w-full max-w-7xl px-4 py-14 md:px-8 md:py-20">
    <div class="mb-10">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand">Blog</p>
        <h1 class="mt-2 font-display text-4xl font-semibold text-slate-900 md:text-5xl">Engineering & Product Insights</h1>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article class="rounded-3xl border border-brand/20 bg-white p-7">
                    <p class="text-xs uppercase tracking-[0.14em] text-slate-500"><?php echo esc_html(get_the_date()); ?></p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-900"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-700"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 26)); ?></p>
                    <a href="<?php the_permalink(); ?>" class="mt-5 inline-flex text-sm font-semibold text-brand">Read Post</a>
                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <article class="rounded-3xl border border-dashed border-brand/30 bg-white p-8 md:col-span-2">
                <p class="text-sm text-slate-700">No blog posts yet.</p>
            </article>
        <?php endif; ?>
    </div>

    <div class="mt-10">
        <?php the_posts_pagination([
            'mid_size' => 2,
            'prev_text' => __('Previous', 'itrs-ai'),
            'next_text' => __('Next', 'itrs-ai'),
        ]); ?>
    </div>
</section>
<?php
get_footer();
