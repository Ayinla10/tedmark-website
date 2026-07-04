<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$pageTitle   = 'Contact Us';
$pageDesc    = 'Get in touch with Tedmark Digital Agency. Book a free consultation or send us a message about your business technology needs.';
$pageSeoPage = 'contact';

// Load settings
try {
    $rows = fetchAll("SELECT `key`, `value` FROM settings");
    $cfg  = array_column($rows, 'value', 'key');
} catch(Exception $e) { $cfg = []; }

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $subject  = trim($_POST['subject'] ?? '');
    $message  = trim($_POST['message'] ?? '');
    $honeypot = trim($_POST['website'] ?? ''); // hidden field — bots fill it, humans never see it
    $formTime = (int)($_POST['form_time'] ?? 0);
    $elapsed  = time() - $formTime;

    // Additional spam heuristics
    $combined     = $message . ' ' . $name . ' ' . $subject;
    $urlCount     = preg_match_all('/https?:\/\/|www\./i', $combined);
    $hasKeyword   = preg_match('/\b(seo|backlink|crypto|casino|viagra|forex)\b/i', $combined);
    $hasCyrillic  = preg_match('/[\x{0400}-\x{04FF}]/u', $combined);       // Russian/Ukrainian/etc script — never legitimate for this Ghana-English site
    $hasCJK       = preg_match('/[\x{4E00}-\x{9FFF}\x{3040}-\x{30FF}]/u', $combined); // Chinese/Japanese script
    $hasHtmlTag   = preg_match('/<a\s|<script|<\/a>/i', $combined);        // raw HTML links pasted into the message
    $botLikeName  = preg_match('/_[a-zA-Z0-9]{3,6}$/', $name);             // e.g. "SomeName_pjMl" — common bot-generated suffix
    $looksSpam    = $urlCount >= 2 || $hasKeyword || $hasCyrillic || $hasCJK || $hasHtmlTag || $botLikeName;

    if (!empty($honeypot) || $elapsed < 3 || $looksSpam) {
        // Silently pretend success — don't tip off the bot
        $success = 'Message sent! We\'ll get back to you within 24 hours.';
    } elseif ($name && $email && $message) {
        try {
            query(
                "INSERT INTO messages (name,email,phone,subject,message,ip,created_at) VALUES (?,?,?,?,?,?,?)",
                [$name, $email, $phone, $subject, $message, $_SERVER['REMOTE_ADDR']??'', date('Y-m-d H:i:s')]
            );
        } catch(Exception $e) { /* log silently */ }
        $success = 'Message sent! We\'ll get back to you within 24 hours.';
    } else {
        $error = 'Please fill in all required fields.';
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm2-page-hero">
    <div class="tm2-badge"><span></span> Get In Touch</div>
    <h1>Let's Talk About Your Business</h1>
    <p>We'd love to hear about your challenges and explore how we can help. No pressure, no hard sell.</p>
</section>

<!-- CONTACT SECTION -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-grid" style="grid-template-columns:1fr 1.6fr;align-items:start;">

            <!-- Info -->
            <div>
                <h2 class="tm2-h2" style="font-size:1.4rem;font-weight:700;margin-bottom:8px;">Contact Information</h2>
                <p class="tm2-sub" style="margin-bottom:32px;">Reach us via any of the channels below or fill in the form and we'll respond within 24 hours.</p>
                <?php
                $cfgPhone   = $cfg['site_phone']   ?? '+233 XX XXX XXXX';
                $cfgEmail   = $cfg['site_email']   ?? 'hello@tedmarkdigital.com';
                $cfgAddress = $cfg['site_address'] ?? 'Accra, Ghana';
                $contacts = [
                    ['icon'=>'fa-solid fa-phone','label'=>'Phone / WhatsApp','value'=>$cfgPhone,'href'=>'tel:'.preg_replace('/[^+\d]/','',$cfgPhone)],
                    ['icon'=>'fa-solid fa-envelope','label'=>'Email','value'=>$cfgEmail,'href'=>'mailto:'.$cfgEmail],
                    ['icon'=>'fa-solid fa-location-dot','label'=>'Location','value'=>$cfgAddress,'href'=>'#'],
                    ['icon'=>'fa-solid fa-clock','label'=>'Office Hours','value'=>'Mon – Fri, 8am – 6pm GMT','href'=>null],
                ];
                foreach($contacts as $c): ?>
                <div style="display:flex;gap:14px;margin-bottom:24px;align-items:flex-start;">
                    <div class="tm2-card-icon" style="margin-bottom:0;flex-shrink:0;"><i class="<?= $c['icon'] ?>"></i></div>
                    <div>
                        <div style="font-size:0.75rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;"><?= $c['label'] ?></div>
                        <?php if($c['href']): ?>
                        <a href="<?= $c['href'] ?>" style="font-size:0.95rem;font-weight:600;color:var(--text);text-decoration:none;"><?= $c['value'] ?></a>
                        <?php else: ?>
                        <div style="font-size:0.95rem;font-weight:600;color:var(--text);"><?= $c['value'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Social -->
                <div style="padding-top:24px;border-top:1px solid var(--border);">
                    <div style="font-size:0.75rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">Follow Us</div>
                    <div class="tm2-social-row">
                        <?php
                        $socials = [
                            ['href'=>$cfg['social_facebook']??'#','icon'=>'fa-brands fa-facebook-f'],
                            ['href'=>$cfg['social_linkedin']??'#','icon'=>'fa-brands fa-linkedin-in'],
                            ['href'=>$cfg['social_twitter']??'#','icon'=>'fa-brands fa-x-twitter'],
                            ['href'=>$cfg['social_instagram']??'#','icon'=>'fa-brands fa-instagram'],
                        ];
                        foreach($socials as $s): ?>
                        <a href="<?= htmlspecialchars($s['href']) ?>" class="tm2-social"><i class="<?= $s['icon'] ?>"></i></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="tm2-card" style="padding:32px;">
                <h3 style="font-size:1.2rem;margin-bottom:6px;">Send Us a Message</h3>
                <p style="margin-bottom:28px;">We reply within 24 hours on business days.</p>

                <?php if($success): ?>
                <div style="background:var(--accent-soft);border:1px solid var(--accent);border-radius:10px;padding:16px;margin-bottom:20px;display:flex;gap:10px;align-items:center;">
                    <i class="fa-solid fa-circle-check" style="color:var(--accent);font-size:1.1rem;"></i>
                    <span style="color:var(--text);font-size:0.9rem;font-weight:600;"><?= htmlspecialchars($success) ?></span>
                </div>
                <?php endif; ?>
                <?php if($error): ?>
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:16px;margin-bottom:20px;">
                    <span style="color:#dc2626;font-size:0.9rem;font-weight:600;"><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
                    <input type="hidden" name="form_time" value="<?= time() ?>">
                    <div class="tm2-grid tm2-grid-2" style="margin-bottom:16px;">
                        <div>
                            <label class="tm2-form-label">Full Name *</label>
                            <input type="text" name="name" class="tm2-form-input" placeholder="Your full name" required value="<?= htmlspecialchars($_POST['name']??'') ?>">
                        </div>
                        <div>
                            <label class="tm2-form-label">Email Address *</label>
                            <input type="email" name="email" class="tm2-form-input" placeholder="your@email.com" required value="<?= htmlspecialchars($_POST['email']??'') ?>">
                        </div>
                    </div>
                    <div class="tm2-grid tm2-grid-2" style="margin-bottom:16px;">
                        <div>
                            <label class="tm2-form-label">Phone / WhatsApp</label>
                            <input type="tel" name="phone" class="tm2-form-input" placeholder="+233 ..." value="<?= htmlspecialchars($_POST['phone']??'') ?>">
                        </div>
                        <div>
                            <label class="tm2-form-label">Subject</label>
                            <select name="subject" class="tm2-form-input">
                                <option value="">Select a topic</option>
                                <option value="Business Systems">Business Systems</option>
                                <option value="Web Development">Web Development</option>
                                <option value="Automation">Automation</option>
                                <option value="E-Commerce">E-Commerce</option>
                                <option value="Digital Marketing">Digital Marketing</option>
                                <option value="General Enquiry">General Enquiry</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom:24px;">
                        <label class="tm2-form-label">Your Message *</label>
                        <textarea name="message" class="tm2-form-input tm2-form-textarea" placeholder="Tell us about your business and what you need..." required><?= htmlspecialchars($_POST['message']??'') ?></textarea>
                    </div>
                    <button type="submit" class="tm2-btn tm2-btn-primary" style="width:100%;justify-content:center;font-size:1rem;padding:14px;">
                        Send Message <i class="fa-solid fa-paper-plane fa-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
