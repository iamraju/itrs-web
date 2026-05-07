# reCAPTCHA v3 Setup Guide

## Summary of Changes

✅ **Implemented:**

- reCAPTCHA v3 protection on Contact Us and Project Planner forms
- Bot detection with score verification (0.5+ threshold)
- Existing CSRF protection via WordPress nonces remains in place
- Email sending already implemented and active (admin receives all lead submissions)

---

## What's Protected

### CSRF Protection (Already In Place)

- WordPress nonce tokens on both forms
- Server-side nonce verification before processing

### Bot Protection (NEW)

- reCAPTCHA v3 on form submissions
- Non-intrusive (no captcha challenge shown to humans)
- Automatic token generation on form load
- Server-side score verification (accepts scores ≥ 0.5)

### Email Notifications (Already In Place)

- Admin receives email on every form submission
- Includes all form data (name, email, phone, message, project details)
- Subjects: `[ITRS] Contact Form from {Name}` or `[ITRS] Project Planner from {Name}`

---

## How to Get reCAPTCHA v3 Keys

### 1. Go to Google Cloud Console

- Visit: https://console.cloud.google.com/

### 2. Create or Select a Project

- Click the project dropdown at the top
- Click "NEW PROJECT"
- Name: `ITRS Nepal` (or your preferred name)
- Click "CREATE"

### 3. Enable reCAPTCHA Admin API

- Go to: https://console.cloud.google.com/marketplace/product/google/recaptchaenterprise.googleapis.com
- Click "ENABLE"
- (If prompted, select your project)

### 4. Create reCAPTCHA Keys

- Go to: https://www.google.com/recaptcha/admin/
- Click "Create" or "+" to add a new site
- Fill in:
  - **Label:** `ITRS Nepal - Web`
  - **reCAPTCHA type:** Select "reCAPTCHA v3"
  - **Domains:**
    - Add your domain: `itrsnepal.com`
    - For local testing: `127.0.0.1` (if needed)
- Click "CREATE"

### 5. Copy Your Keys

- You'll see two keys:
  - **Site Key** (public, show in frontend)
  - **Secret Key** (keep private, use in backend)
- Copy both keys

---

## Add Keys to WordPress

Open `/Users/raju/www/itrs-new/wp-config.php` and add these lines **before** the line `/* That's all, stop editing! */`:

```php
/** reCAPTCHA v3 Keys (https://www.google.com/recaptcha/admin/) */
define('RECAPTCHA_V3_SITE_KEY', 'your_site_key_here');
define('RECAPTCHA_V3_SECRET_KEY', 'your_secret_key_here');
```

**Replace:**

- `your_site_key_here` with your actual Site Key
- `your_secret_key_here` with your actual Secret Key

### Example:

```php
define('RECAPTCHA_V3_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
define('RECAPTCHA_V3_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
```

---

## Testing

### 1. Local Testing (Without Keys Configured)

- Forms work without reCAPTCHA keys
- Verification is skipped if keys are not configured
- Nonce protection still active
- All leads captured and emailed

### 2. After Adding Keys

- Visit: http://127.0.0.1:8090/contact-us/
- Visit: http://127.0.0.1:8090/project-planner/
- Submit the form
- Check admin email: `admin@itrsnepal.com` (password: `admin@123`)
- Bot submissions will be rejected with "Bot detection failed" message

### 3. Verify Email Receipt

- Default admin email: `admin@itrsnepal.com`
- All submissions include:
  - Name, Email, Phone, Message
  - For Project Planner: Company, Project Type, Budget, Timeline

---

## Key Features

| Feature             | Status      | Details                                        |
| ------------------- | ----------- | ---------------------------------------------- |
| CSRF Protection     | ✅ Enabled  | WordPress nonces prevent form tampering        |
| Bot Protection      | ✅ Enabled  | reCAPTCHA v3 (configurable)                    |
| Email Notifications | ✅ Enabled  | Admin notified on all submissions              |
| Lead Storage        | ✅ Enabled  | All leads saved as private posts in WordPress  |
| User Experience     | ✅ Seamless | No challenges shown to humans, silent for bots |

---

## Troubleshooting

### "Bot detection failed" message appears

- Check if Secret Key is correct in wp-config.php
- Verify domain is allowed in Google reCAPTCHA console
- Check browser console for JavaScript errors (F12 > Console)

### Emails not received

- Check WordPress admin email setting: Settings > General
- Check email logs/spam folder
- Verify `wp_mail()` is configured (usually works on live servers)
- On local dev, ensure your mail provider is configured or use a plugin like Mailhog

### Keys not loading (silently skipped)

- This is expected if keys are not configured in wp-config.php
- Forms still work with CSRF protection
- Leads are still captured and emailed

---

## Deployment Checklist

- [ ] Get reCAPTCHA v3 keys from Google
- [ ] Add keys to wp-config.php
- [ ] Test both forms locally
- [ ] Verify email receipt to admin
- [ ] Add production domain to Google reCAPTCHA console
- [ ] Update wp-config.php on live server with production keys
- [ ] Test forms on live site

---

## Security Notes

1. **Never commit Secret Key to git** - keep it in wp-config.php only
2. **Rotate keys periodically** for security best practices
3. **Monitor reCAPTCHA analytics** in Google Cloud console for abuse patterns
4. **Score threshold (0.5)** can be adjusted in `functions.php` if needed:
   - Higher score (0.8) = stricter, fewer false positives
   - Lower score (0.3) = looser, catches more bots

---

## Support

For questions about reCAPTCHA: https://developers.google.com/recaptcha/docs/v3
