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

<!-- PAGE HERO -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container" style="text-align:center;position:relative;z-index:2;">
        <div class="tm-label" style="justify-content:center;">Free Consultation</div>
        <h1 style="font-size:clamp(2rem,4vw,2.8rem);font-weight:900;color:#fff;margin:16px 0 16px;line-height:1.2;">Book Your Free<br>30-Minute Strategy Session</h1>
        <p style="font-size:1.05rem;color:#94a3b8;max-width:560px;margin:0 auto;line-height:1.7;">No commitment, no hard sell. Just a focused conversation about your business challenges and how technology can solve them.</p>
    </div>
</section>

<!-- BOOKING SECTION -->
<section style="padding:80px 0;background:#f8fafc;">
    <div class="tm-container">
        <div style="display:grid;grid-template-columns:1fr 1.6fr;gap:56px;align-items:start;">

            <!-- Left: What to expect -->
            <div class="tm-fade">
                <h2 style="font-size:1.3rem;font-weight:800;color:#0f172a;margin-bottom:20px;">What to Expect</h2>
                <?php
                $expects = [
                    ['icon'=>'fa-solid fa-clock','title'=>'30 Minutes, No Fluff','desc'=>'A focused conversation about your business — no pitching, just problem-solving.'],
                    ['icon'=>'fa-solid fa-magnifying-glass','title'=>'Business Assessment','desc'=>'We\'ll identify the key gaps in your current operations and digital setup.'],
                    ['icon'=>'fa-solid fa-map','title'=>'Digital Roadmap','desc'=>'You\'ll leave with a clear picture of what you need, in what order, at what cost.'],
                    ['icon'=>'fa-solid fa-gift','title'=>'Free, No Strings','desc'=>'Completely free. Even if you don\'t work with us, the insights are yours to keep.'],
                ];
                foreach($expects as $e): ?>
                <div style="display:flex;gap:14px;margin-bottom:20px;">
                    <div style="width:40px;height:40px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="<?= $e['icon'] ?>" style="color:#16a34a;font-size:0.95rem;"></i>
                    </div>
                    <div>
                        <div style="font-size:0.9rem;font-weight:700;color:#0f172a;margin-bottom:4px;"><?= $e['title'] ?></div>
                        <div style="font-size:0.82rem;color:#64748b;line-height:1.5;"><?= $e['desc'] ?></div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Social proof -->
                <div style="background:#fff;border-radius:14px;padding:20px;border:1.5px solid #f1f5f9;margin-top:24px;">
                    <div style="display:flex;gap:2px;margin-bottom:10px;">
                        <?php for($i=0;$i<5;$i++): ?><i class="fa-solid fa-star" style="color:#f59e0b;font-size:0.85rem;"></i><?php endfor; ?>
                    </div>
                    <p style="font-size:0.85rem;color:#334155;line-height:1.6;margin-bottom:12px;font-style:italic;">"The free consultation alone gave us more clarity than 6 months of trying to figure it out ourselves."</p>
                    <div style="font-size:0.8rem;font-weight:700;color:#0f172a;">Ama Boateng<span style="color:#94a3b8;font-weight:400;"> — Founder, StyleHouse GH</span></div>
                </div>
            </div>

            <!-- Right: Form -->
            <div class="tm-card tm-fade" style="padding:36px;">
                <?php if($success): ?>
                <div style="text-align:center;padding:40px 20px;">
                    <div style="width:64px;height:64px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                        <i class="fa-solid fa-check" style="font-size:1.5rem;color:#16a34a;"></i>
                    </div>
                    <h3 style="font-size:1.2rem;font-weight:800;color:#0f172a;margin-bottom:8px;">Booking Confirmed!</h3>
                    <p style="font-size:0.9rem;color:#64748b;line-height:1.6;"><?= htmlspecialchars($success) ?></p>
                </div>
                <?php else: ?>
                <h3 style="font-size:1.2rem;font-weight:800;color:#0f172a;margin-bottom:6px;">Book Your Session</h3>
                <p style="font-size:0.875rem;color:#64748b;margin-bottom:28px;">We'll contact you within 2 hours to confirm a time that works for you.</p>

                <?php if($error): ?>
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px;margin-bottom:20px;">
                    <span style="color:#dc2626;font-size:0.875rem;font-weight:600;"><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div>
                            <label class="tm-form-label">Full Name *</label>
                            <input type="text" name="name" class="tm-form-input" placeholder="Your name" required>
                        </div>
                        <div>
                            <label class="tm-form-label">Business Name</label>
                            <input type="text" name="business" class="tm-form-input" placeholder="Your company">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div>
                            <label class="tm-form-label">Email Address *</label>
                            <input type="email" name="email" class="tm-form-input" placeholder="your@email.com" required>
                        </div>
                        <div>
                            <label class="tm-form-label">Phone / WhatsApp *</label>
                            <input type="tel" name="phone" class="tm-form-input" placeholder="+233 ..." required>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div>
                            <label class="tm-form-label">Industry</label>
                            <select name="industry" class="tm-form-input">
                                <option value="">Select industry</option>
                                <?php foreach(['Retail','Healthcare','Education','Logistics','NGO/Nonprofit','Food &amp; Beverage','Finance','Events','Other'] as $ind): ?>
                                <option><?= $ind ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="tm-form-label">Package Interest</label>
                            <select name="package" class="tm-form-input">
                                <option value="">Not sure yet</option>
                                <option <?= $package==='Starter'?'selected':'' ?>>Starter</option>
                                <option <?= $package==='Growth'?'selected':'' ?>>Growth</option>
                                <option <?= $package==='Enterprise'?'selected':'' ?>>Enterprise</option>
                                <option>Custom</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom:24px;">
                        <label class="tm-form-label">What's your main challenge?</label>
                        <textarea name="challenge" class="tm-form-input tm-form-textarea" style="min-height:100px;" placeholder="Briefly describe what's slowing your business down..."></textarea>
                    </div>
                    <button type="submit" class="tm-btn-primary" style="width:100%;justify-content:center;font-size:1rem;padding:15px;">
                        Book Free Consultation <i class="fa-solid fa-calendar-check fa-xs"></i>
                    </button>
                    <p style="text-align:center;font-size:0.78rem;color:#94a3b8;margin-top:12px;"><i class="fa-solid fa-lock fa-2xs"></i> Your information is private and never shared.</p>
                </form>
                <?php endif; ?>
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
