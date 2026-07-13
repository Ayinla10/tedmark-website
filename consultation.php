<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Book a Free Consultation';
$pageDesc  = 'Book a free 30-minute strategy session with Tedmark Digital. We\'ll assess your business needs and map out a digital transformation plan.';

$success = $error = '';
$package = htmlspecialchars($_GET['package'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $business = trim($_POST['business'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $pkg      = trim($_POST['package'] ?? '');
    $challenge= trim($_POST['challenge'] ?? '');

    if ($name && $email && $phone) {
        try {
            insert('consultations', [
                'name'=>$name,'email'=>$email,'phone'=>$phone,
                'business_name'=>$business,'industry'=>$industry,
                'package_interest'=>$pkg,'main_challenge'=>$challenge,
                'status'=>'pending','created_at'=>date('Y-m-d H:i:s')
            ]);
        } catch(Exception $e){}
        $success = "Thanks $name! We've received your booking and will contact you within 2 hours to confirm your consultation time.";
    } else {
        $error = 'Please fill in your name, email, and phone number.';
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<!-- DARK HERO (always dark — distinct from every other page hero on the site) -->
<section class="tm2-dark-hero">
    <div class="tm2-dark-badge"><i class="fa-solid fa-calendar-check"></i> 100% Free · No Obligation</div>
    <h1>Let's Map Out Your<br><em>Digital Transformation</em></h1>
    <p>One focused 30-minute call. No pitch deck, no pressure — just a clear plan for what your business needs next.</p>
</section>

<!-- TIMELINE -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-timeline">
            <?php
            $steps = [
                ['icon'=>'fa-solid fa-pen','title'=>'1. You Book','desc'=>'Fill in the short form below — takes under 2 minutes.'],
                ['icon'=>'fa-solid fa-phone','title'=>'2. We Call You','desc'=>'We confirm a time within 2 hours and call at your convenience.'],
                ['icon'=>'fa-solid fa-map','title'=>'3. Get Your Roadmap','desc'=>'Walk away with a clear, prioritised plan — yours to keep either way.'],
            ];
            foreach($steps as $i => $s): ?>
            <div class="tm2-timeline-step">
                <div class="tm2-timeline-num"><i class="<?= $s['icon'] ?>"></i></div>
                <h3><?= $s['title'] ?></h3>
                <p><?= $s['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SINGLE-COLUMN CENTERED BOOKING FORM -->
<section class="tm2-section" style="padding-top:0;">
    <div class="tm2-container" style="max-width:640px;">
        <div class="tm2-card" style="padding:36px;">
            <?php if($success): ?>
            <div style="text-align:center;padding:24px 8px;">
                <div class="tm2-card-icon" style="margin:0 auto 16px;width:64px;height:64px;font-size:1.5rem;">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h2 class="tm2-h2" style="font-size:1.3rem;margin-bottom:10px;">Booking Confirmed!</h2>
                <p class="tm2-sub"><?= htmlspecialchars($success) ?></p>
            </div>
            <?php else: ?>
            <h2 class="tm2-h2" style="font-size:1.3rem;margin-bottom:6px;">Book Your Session</h2>
            <p class="tm2-sub" style="margin-bottom:28px;">We'll contact you within 2 hours to confirm a time that works for you.</p>

            <?php if($error): ?>
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px;margin-bottom:20px;">
                <span style="color:#dc2626;font-size:0.875rem;font-weight:600;"><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="tm2-grid tm2-grid-2" style="margin-bottom:16px;">
                    <div>
                        <label class="tm2-form-label">Full Name *</label>
                        <input type="text" name="name" class="tm2-form-input" placeholder="Your name" required>
                    </div>
                    <div>
                        <label class="tm2-form-label">Business Name</label>
                        <input type="text" name="business" class="tm2-form-input" placeholder="Your company">
                    </div>
                </div>
                <div class="tm2-grid tm2-grid-2" style="margin-bottom:16px;">
                    <div>
                        <label class="tm2-form-label">Email Address *</label>
                        <input type="email" name="email" class="tm2-form-input" placeholder="your@email.com" required>
                    </div>
                    <div>
                        <label class="tm2-form-label">Phone / WhatsApp *</label>
                        <input type="tel" name="phone" class="tm2-form-input" placeholder="+233 ..." required>
                    </div>
                </div>
                <div class="tm2-grid tm2-grid-2" style="margin-bottom:16px;">
                    <div>
                        <label class="tm2-form-label">Industry</label>
                        <select name="industry" class="tm2-form-input">
                            <option value="">Select industry</option>
                            <?php foreach(['Retail','Healthcare','Education','Logistics','NGO/Nonprofit','Food &amp; Beverage','Finance','Events','Other'] as $ind): ?>
                            <option><?= $ind ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="tm2-form-label">Package Interest</label>
                        <select name="package" class="tm2-form-input">
                            <option value="">Not sure yet</option>
                            <option <?= $package==='Starter'?'selected':'' ?>>Starter</option>
                            <option <?= $package==='Growth'?'selected':'' ?>>Growth</option>
                            <option <?= $package==='Enterprise'?'selected':'' ?>>Enterprise</option>
                            <option>Custom</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:24px;">
                    <label class="tm2-form-label">What's your main challenge?</label>
                    <textarea name="challenge" class="tm2-form-input tm2-form-textarea" style="min-height:100px;" placeholder="Briefly describe what's slowing your business down..."></textarea>
                </div>
                <button type="submit" class="tm2-btn tm2-btn-primary" style="width:100%;justify-content:center;font-size:1rem;padding:15px;">
                    Book Free Consultation <i class="fa-solid fa-calendar-check fa-xs"></i>
                </button>
                <p style="text-align:center;font-size:0.78rem;color:var(--muted);margin-top:12px;"><i class="fa-solid fa-lock fa-2xs"></i> Your information is private and never shared.</p>
            </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- TESTIMONIAL STRIP (horizontal, below — not beside the form) -->
<section class="tm2-section" style="padding-top:0;">
    <div class="tm2-container" style="max-width:640px;">
        <div class="tm2-cta-band" style="text-align:left;display:flex;gap:20px;align-items:flex-start;">
            <div style="display:flex;gap:2px;flex-shrink:0;">
                <?php for($i=0;$i<5;$i++): ?><i class="fa-solid fa-star" style="color:var(--accent);font-size:0.9rem;"></i><?php endfor; ?>
            </div>
            <div>
                <p style="font-size:0.9rem;color:var(--text);line-height:1.6;font-style:italic;margin-bottom:8px;">"The free consultation alone gave us more clarity than 6 months of trying to figure it out ourselves."</p>
                <div style="font-size:0.82rem;font-weight:600;color:var(--text);">Ama Boateng <span style="color:var(--muted);font-weight:400;">— Founder, StyleHouse GH</span></div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
