<?php
/**
 * Project Planner page template.
 *
 * @package itrs-ai
 */

get_header();
$form_status = isset($_GET['form_status']) ? sanitize_text_field(wp_unslash($_GET['form_status'])) : '';
?>
<section class="mx-auto w-full max-w-6xl px-4 py-14 md:px-8 md:py-20">
    <div class="grid gap-8 md:grid-cols-[0.9fr_1.1fr] md:items-start">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article class="rounded-4xl border border-brand/20 bg-white p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-brand"><?php echo esc_html(get_the_title()); ?></p>
            <h1 class="mt-3 font-display text-4xl font-semibold text-slate-900"><?php the_title(); ?></h1>
            <div class="itrs-content mt-4">
                <?php the_content(); ?>
            </div>
        </article>

        <article class="rounded-4xl border border-brand/20 bg-white p-8 shadow-[0_20px_55px_-36px_rgba(243,130,80,0.55)]">
            <?php if ('success' === $form_status) : ?>
                <p class="mb-6 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">Thank you. Your project plan request has been submitted.</p>
            <?php elseif ('recaptcha_failed' === $form_status) : ?>
                <p class="mb-6 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">Bot detection failed. Please try again.</p>
            <?php elseif ('' !== $form_status) : ?>
                <p class="mb-6 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">Could not submit the form. Please check required fields and try again.</p>
            <?php endif; ?>

            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="grid gap-4">
                <input type="hidden" name="action" value="itrs_submit_lead" />
                <input type="hidden" name="form_type" value="planner" />
                <input type="hidden" name="recaptcha_token" value="" />
                <?php wp_nonce_field('itrs_submit_lead', 'itrs_form_nonce'); ?>

                <label class="grid gap-1 text-sm font-medium text-slate-700">Full Name
                    <input type="text" name="name" required class="rounded-xl border border-brand/25 bg-amber-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-brand" />
                </label>
                <label class="grid gap-1 text-sm font-medium text-slate-700">Email Address
                    <input type="email" name="email" required class="rounded-xl border border-brand/25 bg-amber-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-brand" />
                </label>
                <label class="grid gap-1 text-sm font-medium text-slate-700">Phone
                    <input type="text" name="phone" class="rounded-xl border border-brand/25 bg-amber-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-brand" />
                </label>
                <label class="grid gap-1 text-sm font-medium text-slate-700">Company
                    <input type="text" name="company" class="rounded-xl border border-brand/25 bg-amber-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-brand" />
                </label>
                <label class="grid gap-1 text-sm font-medium text-slate-700">Project Type
                    <select name="project_type" class="rounded-xl border border-brand/25 bg-amber-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-brand">
                        <option value="Web Application">Web Application</option>
                        <option value="Mobile Application">Mobile Application</option>
                        <option value="Web + Mobile">Web + Mobile</option>
                        <option value="Other">Other</option>
                    </select>
                </label>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-1 text-sm font-medium text-slate-700">Estimated Budget
                        <input type="text" name="budget" placeholder="e.g. USD 5,000 - 15,000" class="rounded-xl border border-brand/25 bg-amber-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-brand" />
                    </label>
                    <label class="grid gap-1 text-sm font-medium text-slate-700">Expected Timeline
                        <input type="text" name="timeline" placeholder="e.g. 8-12 weeks" class="rounded-xl border border-brand/25 bg-amber-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-brand" />
                    </label>
                </div>
                <label class="grid gap-1 text-sm font-medium text-slate-700">Project Details
                    <textarea name="message" required rows="5" class="rounded-xl border border-brand/25 bg-amber-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-brand"></textarea>
                </label>

                <button type="submit" class="mt-2 inline-flex w-fit rounded-xl bg-brand px-6 py-3 text-sm font-semibold text-white transition hover:brightness-110">Submit Project Plan</button>
            </form>
        </article>
        <?php endwhile; endif; ?>
    </div>
</section>
<?php
get_footer();
