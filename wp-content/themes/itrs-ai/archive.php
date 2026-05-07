<?php
/**
 * Archive template.
 *
 * @package itrs-ai
 */

get_header();
?>
<section class="mx-auto w-full max-w-7xl px-4 py-14 md:px-8 md:py-20">
    <div class="mb-10">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand">Archive</p>
        <h1 class="mt-2 font-display text-4xl font-semibold text-slate-900 md:text-5xl"><?php the_archive_title(); ?></h1>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article class="rounded-3xl border border-brand/20 bg-white p-7">
                    <h2 class="text-2xl font-semibold text-slate-900"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-700"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 24)); ?></p>
                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <article class="rounded-3xl border border-dashed border-brand/30 bg-white p-8 md:col-span-2">
                <p class="text-sm text-slate-700">No posts found for this archive.</p>
            </article>
        <?php endif; ?>
    </div>
</section>
<?php
get_footer();
