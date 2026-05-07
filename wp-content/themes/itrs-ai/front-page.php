<?php
/**
 * Front page template.
 *
 * @package itrs-ai
 */

get_header();
?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<section class="mx-auto w-full max-w-7xl px-4 pt-14 md:px-8 md:pt-20">
    <div class="relative overflow-hidden rounded-4xl border border-brand/20 shadow-[0_30px_90px_-45px_rgba(243,130,80,0.55)]">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?php echo esc_url(get_template_directory_uri() . '/assets/images/home-banner.jpg'); ?>');"></div>
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(15,23,42,0.88)_0%,rgba(15,23,42,0.68)_42%,rgba(15,23,42,0.4)_100%)]"></div>
        <div class="relative grid min-h-140 items-end gap-10 px-6 py-10 md:grid-cols-[1.1fr_0.9fr] md:px-12 md:py-14">
            <div class="max-w-3xl">
                <p class="inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-brand-soft">Creative Digital Agency</p>
                <h1 class="mt-6 font-display text-4xl font-semibold leading-tight text-white md:text-6xl">AI-Ready Web & Mobile Products Built for Your Business Goals</h1>
                <p class="mt-6 max-w-2xl text-base leading-relaxed text-slate-200 md:text-lg">We hand-craft high-performing digital products with clear strategy, modern architecture, and practical delivery. From concept to launch, your product is engineered to scale.</p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="<?php echo esc_url(itrs_ai_page_url('project-planner')); ?>" class="rounded-xl bg-brand px-6 py-3 text-sm font-semibold text-white transition hover:brightness-110">Plan Your Project</a>
                    <a href="<?php echo esc_url(itrs_ai_page_url('services')); ?>" class="rounded-xl border border-white/25 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Explore Services</a>
                </div>
            </div>

            <div class="md:justify-self-end md:self-end">
                <div class="grid gap-4 rounded-4xl border border-white/15 bg-white/12 p-5 text-white backdrop-blur-md sm:grid-cols-2 md:max-w-md">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                        <p class="text-3xl font-semibold text-white">100+</p>
                        <p class="mt-1 text-xs uppercase tracking-[0.14em] text-slate-300">Projects Delivered</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                        <p class="text-3xl font-semibold text-white">95%</p>
                        <p class="mt-1 text-xs uppercase tracking-[0.14em] text-slate-300">Client Satisfaction</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 sm:col-span-2">
                        <p class="text-lg font-medium text-white">Product Strategy + UX + Engineering + Support</p>
                        <p class="mt-2 text-sm text-slate-200">A single team that can own the complete lifecycle of your web and mobile product.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (trim((string) get_the_content()) !== '') : ?>
<section class="mx-auto mt-20 w-full max-w-7xl px-4 md:px-8">
    <div class="itrs-content">
        <?php the_content(); ?>
    </div>
</section>
<?php endif; ?>
<?php endwhile; endif; ?>

<section class="mx-auto mt-20 w-full max-w-7xl px-4 pb-10 md:px-8">
    <div class="mb-8 flex items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand">From The Blog</p>
            <h2 class="mt-2 font-display text-3xl font-semibold text-slate-900">Latest Insights</h2>
        </div>
        <a href="<?php echo esc_url(itrs_ai_blog_url()); ?>" class="text-sm font-semibold text-slate-700 hover:text-slate-900">View All Posts</a>
    </div>

    <?php
    $recent_posts = new \WP_Query([
        'post_type'           => 'post',
        'posts_per_page'      => 3,
        'ignore_sticky_posts' => true,
    ]);
    ?>

    <div class="grid gap-6 md:grid-cols-3">
        <?php if ($recent_posts->have_posts()) : ?>
            <?php while ($recent_posts->have_posts()) : $recent_posts->the_post(); ?>
                <article class="reveal rounded-3xl border border-brand/20 bg-white p-6">
                    <p class="text-xs uppercase tracking-[0.14em] text-slate-500"><?php echo esc_html(get_the_date()); ?></p>
                    <h3 class="mt-3 text-xl font-semibold text-slate-900"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-700"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?></p>
                </article>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <article class="rounded-3xl border border-dashed border-brand/30 bg-white p-8 md:col-span-3">
                <p class="text-sm text-slate-700">No blog posts yet. Publish your first post to populate this section.</p>
            </article>
        <?php endif; ?>
    </div>
</section>
<?php
get_footer();
