<?php
/**
 * Fallback index template.
 *
 * @package itrs-ai
 */

get_header();
?>
<section class="mx-auto w-full max-w-5xl px-4 py-14 md:px-8 md:py-20">
    <?php if (have_posts()) : ?>
        <div class="space-y-6">
            <?php while (have_posts()) : the_post(); ?>
                <article class="rounded-3xl border border-brand/20 bg-white p-7">
                    <h2 class="text-2xl font-semibold text-slate-900"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-700"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 24)); ?></p>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <article class="rounded-3xl border border-dashed border-brand/30 bg-white p-8">
            <p class="text-sm text-slate-700">No content published yet.</p>
        </article>
    <?php endif; ?>
</section>
<?php
get_footer();
