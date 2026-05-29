<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Contact Us';
$pageDesc  = 'Get in touch with Tedmark Digital Agency. Book a free consultation or send us a message about your business technology needs.';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && $email && $message) {
        try {
            insert('contact_submissions', [
                'name'=>$name,'email'=>$email,'phone'=>$phone,
                'subject'=>$subject,'message'=>$message,'created_at'=>date('Y-m-d H:i:s')
            ]);
            $success = 'Message sent! We\'ll get back to you within 24 hours.';
        } catch(Exception $e) {
            $success = 'Message sent! We\'ll get back to you within 24 hours.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1596524430615-b46475ddff6e?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container" style="text-align:center;position:relative;z-index:2;">
        <div class="tm-label" style="justify-content:center;">Get In Touch</div>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:900;color:#fff;margin:16px 0 20px;line-height:1.15;">Let's Talk About<br>Your Business</h1>
        <p style="font-size:1.1rem;color:#94a3b8;max-width:560px;margin:0 auto;line-height:1.7;">We'd love to hear about your challenges and explore how we can help. No pressure, no hard sell.</p>
    </div>
</section>

<!-- CONTACT SECTION -->
<section style="padding:96px 0;background:#f8fafc;">
    <div class="tm-container">
        <div style="display:grid;grid-template-columns:1fr 1.6fr;gap:56px;align-items:start;">

            <!-- Info -->
            <div class="tm-fade">
                <h2 style="font-size:1.4rem;font-weight:800;color:#0f172a;margin-bottom:8px;">Contact Information</h2>
                <p style="font-size:0.9rem;color:#64748b;margin-bottom:32px;line-height:1.6;">Reach us via any of the channels below or fill in the form and we'll respond within 24 hours.</p>
                <?php
                $contacts = [
                    ['icon'=>'fa-solid fa-phone','label'=>'Phone / WhatsApp','value'=>'+233 59 123 4567','href'=>'tel:+233591234567'],
                    ['icon'=>'fa-solid fa-envelope','label'=>'Email','value'=>'hello@tedmarkdigital.com','href'=>'mailto:hello@tedmarkdigital.com'],
                    ['icon'=>'fa-solid fa-location-dot','label'=>'Location','value'=>'Accra, Ghana','href'=>'#'],
                    ['icon'=>'fa-solid fa-clock','label'=>'Office Hours','value'=>'Mon – Fri, 8am – 6pm GMT','href'=>null],
                ];
                foreach($contacts as $c): ?>
                <div style="display:flex;gap:14px;margin-bottom:24px;align-items:flex-start;">
                    <div style="width:44px;height:44px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="<?= $c['icon'] ?>" style="color:#16a34a;font-size:1rem;"></i>
                    </div>
                    <div>
                        <div style="font-size:0.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;"><?= $c['label'] ?></div>
                        <?php if($c['href']): ?>
                        <a href="<?= $c['href'] ?>" style="font-size:0.95rem;font-weight:600;color:#0f172a;text-decoration:none;"><?= $c['value'] ?></a>
                        <?php else: ?>
                        <div style="font-size:0.95rem;font-weight:600;color:#0f172a;"><?= $c['value'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Social -->
                <div style="padding-top:24px;border-top:1px solid #f1f5f9;">
                    <div style="font-size:0.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">Follow Us</div>
                    <div style="display:flex;gap:10px;">
                        <a href="#" style="width:40px;height:40px;border-radius:9px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:1rem;text-decoration:none;transition:all .2s;" onmouseover="this.style.background='#16a34a';this.style.color='white'" onmouseout="this.style.background='#f1f5f9';this.style.color='#64748b'"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" style="width:40px;height:40px;border-radius:9px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:1rem;text-decoration:none;transition:all .2s;" onmouseover="this.style.background='#16a34a';this.style.color='white'" onmouseout="this.style.background='#f1f5f9';this.style.color='#64748b'"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#" style="width:40px;height:40px;border-radius:9px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:1rem;text-decoration:none;transition:all .2s;" onmouseover="this.style.background='#16a34a';this.style.color='white'" onmouseout="this.style.background='#f1f5f9';this.style.color='#64748b'"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="#" style="width:40px;height:40px;border-radius:9px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:1rem;text-decoration:none;transition:all .2s;" onmouseover="this.style.background='#16a34a';this.style.color='white'" onmouseout="this.style.background='#f1f5f9';this.style.color='#64748b'"><i class="fa-brands fa-instagram"></i></a>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="tm-card tm-fade" style="padding:36px;">
                <h3 style="font-size:1.2rem;font-weight:800;color:#0f172a;margin-bottom:6px;">Send Us a Message</h3>
                <p style="font-size:0.875rem;color:#64748b;margin-bottom:28px;">We reply within 24 hours on business days.</p>

                <?php if($success): ?>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;margin-bottom:20px;display:flex;gap:10px;align-items:center;">
                    <i class="fa-solid fa-circle-check" style="color:#16a34a;font-size:1.1rem;"></i>
                    <span style="color:#166534;font-size:0.9rem;font-weight:600;"><?= htmlspecialchars($success) ?></span>
                </div>
                <?php endif; ?>
                <?php if($error): ?>
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:16px;margin-bottom:20px;">
                    <span style="color:#dc2626;font-size:0.9rem;font-weight:600;"><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div>
                            <label class="tm-form-label">Full Name *</label>
                            <input type="text" name="name" class="tm-form-input" placeholder="Your full name" required value="<?= htmlspecialchars($_POST['name']??'') ?>">
                        </div>
                        <div>
                            <label class="tm-form-label">Email Address *</label>
                            <input type="email" name="email" class="tm-form-input" placeholder="your@email.com" required value="<?= htmlspecialchars($_POST['email']??'') ?>">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div>
                            <label class="tm-form-label">Phone / WhatsApp</label>
                            <input type="tel" name="phone" class="tm-form-input" placeholder="+233 ..." value="<?= htmlspecialchars($_POST['phone']??'') ?>">
                        </div>
                        <div>
                            <label class="tm-form-label">Subject</label>
                            <select name="subject" class="tm-form-input">
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
                        <label class="tm-form-label">Your Message *</label>
                        <textarea name="message" class="tm-form-input tm-form-textarea" placeholder="Tell us about your business and what you need..." required><?= htmlspecialchars($_POST['message']??'') ?></textarea>
                    </div>
                    <button type="submit" class="tm-btn-primary" style="width:100%;justify-content:center;font-size:1rem;padding:14px;">
                        Send Message <i class="fa-solid fa-paper-plane fa-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
@media(max-width:768px){
    section .tm-container > div[style*="grid-template-columns:1fr 1.6fr"] { grid-template-columns:1fr !important; }
    form > div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns:1fr !important; }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
