<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Smart Service Recommender';
$pageDesc  = 'Answer 6 questions about your business and get personalized Tedmark service recommendations.';
$pageHasDarkHero = true;
require_once __DIR__ . '/../includes/header.php';

$questions = [
    ['name'=>'website',       'label'=>'What is your current website situation?',          'icon'=>'fa-globe',
     'options'=>['none'=>'I don\'t have a website','old'=>'I have an outdated or bad website','basic'=>'I have a basic website that works','good'=>'I have a good website already']],
    ['name'=>'tracking',      'label'=>'How do you track your business operations?',        'icon'=>'fa-chart-bar',
     'options'=>['none'=>'No system, I just remember things','manual'=>'Spreadsheets and manual records','software'=>'I use some software but it\'s not connected','automated'=>'I have a proper system in place']],
    ['name'=>'communication', 'label'=>'How does your team communicate and coordinate?',   'icon'=>'fa-comments',
     'options'=>['scattered'=>'WhatsApp groups and informal channels','email'=>'Mainly email, sometimes calls','mixed'=>'Mix of tools, not ideal','structured'=>'We have a proper system']],
    ['name'=>'customers',     'label'=>'How do you manage your customers and leads?',       'icon'=>'fa-users',
     'options'=>['none'=>'No system, I just remember them','manual'=>'Spreadsheet or basic notes','crm'=>'Basic CRM or contact list','automated'=>'Full CRM with automation']],
    ['name'=>'sales',         'label'=>'Where do most of your sales happen?',              'icon'=>'fa-bag-shopping',
     'options'=>['offline'=>'Entirely offline / in person','social'=>'Mainly through social media','website'=>'Through my website','omni'=>'Multiple channels including online store']],
    ['name'=>'marketing',     'label'=>'How would you describe your digital marketing?',   'icon'=>'fa-bullhorn',
     'options'=>['none'=>'I don\'t do digital marketing','weak'=>'Occasional posts but no strategy','active'=>'Active on social, some paid ads','strong'=>'Strong strategy with measurable results']],
];
?>

<!-- ===== HERO ===== -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1677442135703-1787eea5ce01?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container">
        <div class="tm-page-hero-inner" style="text-align:center;max-width:640px;margin:0 auto;">
            <div class="tm-badge tm-fade" style="animation-delay:.05s">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Free Tool
            </div>
            <h1 class="tm-page-hero-title tm-fade" style="animation-delay:.1s">Find Exactly What<br>Your Business Needs</h1>
            <p class="tm-page-hero-desc tm-fade" style="animation-delay:.15s">
                Answer 6 quick questions and get personalized service recommendations based on your situation.
            </p>
        </div>
    </div>
</section>

<!-- ===== FORM ===== -->
<section style="padding:80px 0;background:#f8fafc;">
    <div class="tm-container">
        <div style="max-width:720px;margin:0 auto;">

            <form id="recommender-form" style="display:flex;flex-direction:column;gap:24px;" class="tm-fade">
                <?php foreach ($questions as $i => $q): ?>
                <div style="background:#fff;border-radius:20px;box-shadow:0 2px 16px rgba(0,0,0,.05);padding:32px;">
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:22px;">
                        <div style="width:44px;height:44px;background:#16a34a;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;">
                            <i class="fa-solid <?= $q['icon'] ?>"></i>
                        </div>
                        <div>
                            <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;">Question <?= $i + 1 ?> of <?= count($questions) ?></div>
                            <h3 style="font-size:17px;font-weight:800;color:#0f172a;margin:0;"><?= htmlspecialchars($q['label']) ?></h3>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <?php foreach ($q['options'] as $val => $label): ?>
                        <label style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-radius:12px;border:2px solid #e2e8f0;cursor:pointer;transition:all .2s;" class="sr-option">
                            <div style="width:20px;height:20px;border-radius:50%;border:2px solid #cbd5e1;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .2s;" class="sr-radio">
                                <div style="width:8px;height:8px;border-radius:50%;background:#fff;opacity:0;transition:opacity .2s;" class="sr-dot"></div>
                            </div>
                            <input type="radio" name="<?= $q['name'] ?>" value="<?= $val ?>" style="display:none;">
                            <span style="font-size:14px;font-weight:500;color:#334155;"><?= htmlspecialchars($label) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <button type="submit" class="tm-btn-primary" style="justify-content:center;padding:18px;font-size:16px;">
                    <i class="fa-solid fa-wand-magic-sparkles" style="margin-right:8px;"></i>
                    Get My Personalized Recommendations
                </button>
            </form>

            <!-- Results -->
            <div id="recommendations-section" style="display:none;" class="tm-fade">

                <div style="text-align:center;margin-bottom:40px;padding:40px 0 0;">
                    <div style="width:64px;height:64px;background:#dcfce7;border-radius:20px;display:flex;align-items:center;justify-content:center;color:#16a34a;font-size:28px;margin:0 auto 16px;">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <h2 style="font-size:28px;font-weight:900;color:#0f172a;margin:0 0 10px;">Your Personalized Recommendations</h2>
                    <p style="color:#64748b;max-width:500px;margin:0 auto;">Based on your answers, here are the services that will have the highest impact on your business:</p>
                </div>

                <div id="recommendations" style="display:flex;flex-direction:column;gap:16px;margin-bottom:32px;"></div>

                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:36px;text-align:center;">
                    <h3 style="font-size:20px;font-weight:800;color:#0f172a;margin:0 0 8px;">Ready to get started?</h3>
                    <p style="color:#64748b;font-size:14px;margin:0 0 24px;">Book a free strategy session and we'll create a custom implementation plan for your business.</p>
                    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                        <a href="<?= SITE_URL ?>/consultation.php" class="tm-btn-primary">
                            Book Free Session <i class="fa-solid fa-arrow-right fa-xs"></i>
                        </a>
                        <a href="<?= SITE_URL ?>/services.php" class="tm-btn-outline">
                            View All Services
                        </a>
                    </div>
                </div>

                <div style="text-align:center;margin-top:24px;">
                    <button onclick="location.reload()" style="background:none;border:none;color:#16a34a;font-weight:500;cursor:pointer;font-size:14px;">
                        <i class="fa-solid fa-rotate-left" style="margin-right:6px;"></i> Start Over
                    </button>
                </div>

            </div>
        </div>
    </div>
</section>

<style>
.sr-option:hover { border-color:#86efac !important; background:#f0fdf4 !important; }
.sr-option.selected { border-color:#16a34a !important; background:#f0fdf4 !important; }
.sr-option.selected .sr-radio { border-color:#16a34a !important; background:#16a34a !important; }
.sr-option.selected .sr-dot { opacity:1 !important; }
</style>

<script>
// Radio styling
document.querySelectorAll('.sr-option').forEach(label => {
    label.addEventListener('click', () => {
        const input = label.querySelector('input[type=radio]');
        document.querySelectorAll(`input[name="${input.name}"]`).forEach(r => r.closest('label').classList.remove('selected'));
        input.checked = true;
        label.classList.add('selected');
    });
});

// Service map
const serviceMap = {
    website:       { none:'Website Development', old:'Website Redesign' },
    tracking:      { none:'Business Systems', manual:'Business Systems' },
    communication: { scattered:'Communication Infrastructure' },
    customers:     { none:'CRM System', manual:'CRM System' },
    sales:         { offline:'E-Commerce Platform' },
    marketing:     { none:'Digital Marketing', weak:'Digital Marketing' },
};

const serviceDetails = {
    'Website Development':        { icon:'fa-globe',                desc:'A modern, fast website that converts visitors into customers.',                color:'#dbeafe', iconColor:'#1d4ed8' },
    'Website Redesign':           { icon:'fa-wand-magic-sparkles',  desc:'Transform your outdated site into a powerful conversion machine.',           color:'#ede9fe', iconColor:'#7c3aed' },
    'Business Systems':           { icon:'fa-gears',                desc:'Automated tracking, reporting, and operational systems.',                     color:'#dcfce7', iconColor:'#15803d' },
    'Communication Infrastructure':{ icon:'fa-comments',            desc:'Unified communication tools for your team and clients.',                      color:'#fef3c7', iconColor:'#b45309' },
    'CRM System':                 { icon:'fa-users',                desc:'Customer relationship management to grow repeat business.',                   color:'#ffe4e6', iconColor:'#be123c' },
    'E-Commerce Platform':        { icon:'fa-bag-shopping',         desc:'Sell your products online, anywhere in the world.',                           color:'#ecfdf5', iconColor:'#059669' },
    'Digital Marketing':          { icon:'fa-bullhorn',             desc:'Attract and convert more customers online.',                                  color:'#fff7ed', iconColor:'#c2410c' },
    'Business Automation':        { icon:'fa-bolt',                 desc:'Automate repetitive processes to save time and money.',                       color:'#f0fdf4', iconColor:'#16a34a' },
};

document.getElementById('recommender-form').addEventListener('submit', (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));

    const seen = new Set();
    const recs = [];
    for (const [field, val] of Object.entries(data)) {
        if (serviceMap[field] && serviceMap[field][val]) {
            const name = serviceMap[field][val];
            if (!seen.has(name)) { seen.add(name); recs.push(name); }
        }
    }
    if (recs.length === 0) recs.push('Business Automation');

    document.getElementById('recommender-form').style.display = 'none';
    const section = document.getElementById('recommendations-section');
    section.style.display = 'block';

    document.getElementById('recommendations').innerHTML = recs.map((name, i) => {
        const s = serviceDetails[name] || serviceDetails['Business Automation'];
        return `
        <div style="display:flex;gap:20px;align-items:flex-start;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,.04);">
            <div style="width:52px;height:52px;background:${s.color};border-radius:14px;display:flex;align-items:center;justify-content:center;color:${s.iconColor};font-size:20px;flex-shrink:0;">
                <i class="fa-solid ${s.icon}"></i>
            </div>
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                    <span style="font-size:11px;font-weight:600;background:${s.color};color:${s.iconColor};padding:3px 10px;border-radius:99px;">Recommended</span>
                </div>
                <div style="font-size:17px;font-weight:800;color:#0f172a;margin-bottom:5px;">${name}</div>
                <div style="font-size:14px;color:#64748b;">${s.desc}</div>
            </div>
        </div>`;
    }).join('');

    section.scrollIntoView({ behavior:'smooth', block:'start' });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
