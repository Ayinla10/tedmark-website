<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
$pageTitle = 'Business Health Checker';
$pageDesc  = 'Take our free 10-question business health assessment and get a personalized score and recommendations for your business.';
$pageHasDarkHero = true;

try {
    $settingsRows = fetchAll("SELECT `key`, `value` FROM settings");
    $cfg = array_column($settingsRows, 'value', 'key');
} catch(Exception $e) { $cfg = []; }
function toolcfg($cfg, $key, $default='') { return htmlspecialchars($cfg[$key] ?? $default); }

require_once __DIR__ . '/../includes/header.php';
?>

<!-- ===== HERO ===== -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container">
        <div class="tm-page-hero-inner" style="text-align:center;max-width:640px;margin:0 auto;">
            <div class="tm-badge tm-fade" style="animation-delay:.05s">
                <i class="fa-solid fa-heart-pulse"></i> Free Assessment
            </div>
            <h1 class="tm-page-hero-title tm-fade" style="animation-delay:.1s"><?= toolcfg($cfg,'tool_health_h1','Business Health Checker') ?></h1>
            <p class="tm-page-hero-desc tm-fade" style="animation-delay:.15s">
                <?= toolcfg($cfg,'tool_health_subtext','10 questions. 3 minutes. Get a detailed score of how your business is performing and where to improve.') ?>
            </p>
        </div>
    </div>
</section>

<!-- ===== TOOL ===== -->
<section style="padding:80px 0;background:#f8fafc;">
    <div class="tm-container">
        <div style="max-width:720px;margin:0 auto;">

            <!-- Progress -->
            <div style="margin-bottom:40px;" class="tm-fade">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <span id="step-label" style="font-size:13px;font-weight:500;color:#64748b;">Step 1 of 10</span>
                    <span id="score-preview" style="font-size:13px;font-weight:500;color:#16a34a;display:none;">Score: <span id="running-score">0</span>/100</span>
                </div>
                <div style="height:6px;background:#e2e8f0;border-radius:99px;overflow:hidden;">
                    <div id="health-progress" style="height:100%;background:linear-gradient(90deg,#16a34a,#4ade80);border-radius:99px;width:10%;transition:width .5s ease;"></div>
                </div>
            </div>

            <div style="background:#fff;border-radius:20px;box-shadow:0 4px 32px rgba(0,0,0,.07);padding:40px;" class="tm-fade">
                <form id="health-form">
                    <?php
                    $questions = [
                        ['q'=>'How do you currently track your sales and orders?', 'options'=>[['l'=>'Manual spreadsheets or paper','s'=>0],['l'=>'Basic software (Excel/Google Sheets)','s'=>3],['l'=>'Dedicated sales software','s'=>7],['l'=>'Fully integrated CRM system','s'=>10]]],
                        ['q'=>'How is your team\'s internal communication managed?', 'options'=>[['l'=>'Informal WhatsApp groups','s'=>0],['l'=>'Email + some tools','s'=>3],['l'=>'Structured tools (Slack, Teams)','s'=>7],['l'=>'Integrated comms + project management','s'=>10]]],
                        ['q'=>'How do you manage your finances and invoicing?', 'options'=>[['l'=>'Manual records / no system','s'=>0],['l'=>'Spreadsheets','s'=>3],['l'=>'Basic accounting software','s'=>7],['l'=>'Full accounting + ERP integration','s'=>10]]],
                        ['q'=>'How do customers find and contact your business?', 'options'=>[['l'=>'Mostly word of mouth','s'=>0],['l'=>'Basic website or social media','s'=>3],['l'=>'Active digital presence','s'=>7],['l'=>'Optimized omni-channel strategy','s'=>10]]],
                        ['q'=>'How do you handle customer service and follow-ups?', 'options'=>[['l'=>'Manually and reactively','s'=>0],['l'=>'Basic contact tracking','s'=>3],['l'=>'CRM with reminders','s'=>7],['l'=>'Automated follow-ups + help desk','s'=>10]]],
                        ['q'=>'How much of your regular work is automated?', 'options'=>[['l'=>'0%, everything is manual','s'=>0],['l'=>'10–25% automated','s'=>3],['l'=>'25–60% automated','s'=>7],['l'=>'60%+ automated','s'=>10]]],
                        ['q'=>'How do you manage your inventory or project pipeline?', 'options'=>[['l'=>'No formal system','s'=>0],['l'=>'Basic spreadsheet tracking','s'=>3],['l'=>'Dedicated software','s'=>7],['l'=>'Real-time automated system','s'=>10]]],
                        ['q'=>'How do you make business decisions?', 'options'=>[['l'=>'Gut feeling','s'=>0],['l'=>'Looking at past records','s'=>3],['l'=>'Regular reports/dashboards','s'=>7],['l'=>'Data-driven with live analytics','s'=>10]]],
                        ['q'=>'How does your business handle employee management?', 'options'=>[['l'=>'Informally / no system','s'=>0],['l'=>'Basic HR admin','s'=>3],['l'=>'HR software in use','s'=>7],['l'=>'Automated HR + payroll system','s'=>10]]],
                        ['q'=>'How would you describe your overall digital maturity?', 'options'=>[['l'=>'Very early, mostly offline','s'=>0],['l'=>'Some digital tools, not integrated','s'=>3],['l'=>'Good digital presence, room to improve','s'=>7],['l'=>'Highly digital and optimized','s'=>10]]],
                    ];
                    foreach ($questions as $qi => $q): ?>
                    <div data-step="<?= $qi ?>" style="<?= $qi > 0 ? 'display:none' : '' ?>">
                        <div style="text-align:center;margin-bottom:32px;">
                            <div style="width:52px;height:52px;background:#dcfce7;border-radius:16px;display:flex;align-items:center;justify-content:center;color:#16a34a;font-size:20px;font-weight:900;margin:0 auto 16px;"><?= $qi + 1 ?></div>
                            <h2 style="font-size:20px;font-weight:800;color:#0f172a;margin:0;"><?= htmlspecialchars($q['q']) ?></h2>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:32px;">
                            <?php foreach ($q['options'] as $oi => $opt): ?>
                            <label style="display:flex;align-items:center;gap:16px;padding:16px 20px;border-radius:14px;border:2px solid #e2e8f0;cursor:pointer;transition:all .2s;" class="hc-option">
                                <div style="width:22px;height:22px;border-radius:50%;border:2px solid #cbd5e1;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .2s;" class="hc-radio">
                                    <div style="width:10px;height:10px;border-radius:50%;background:#fff;opacity:0;transition:opacity .2s;" class="hc-dot"></div>
                                </div>
                                <input type="radio" name="q<?= $qi ?>" value="<?= $opt['s'] ?>" style="display:none;" required>
                                <span style="font-weight:500;color:#334155;flex:1;"><?= htmlspecialchars($opt['l']) ?></span>
                                <span style="font-size:12px;font-weight:600;color:#cbd5e1;" class="hc-pts"><?= $opt['s'] ?> pts</span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div style="display:flex;gap:12px;">
                            <?php if ($qi > 0): ?>
                            <button type="button" data-back style="flex:1;padding:14px;border:2px solid #e2e8f0;background:#fff;border-radius:12px;font-size:15px;font-weight:600;color:#475569;cursor:pointer;transition:all .2s;">
                                <i class="fa-solid fa-arrow-left fa-xs" style="margin-right:6px;"></i> Back
                            </button>
                            <?php else: ?>
                            <div style="flex:1;"></div>
                            <?php endif; ?>
                            <?php if ($qi < count($questions) - 1): ?>
                            <button type="button" data-next style="flex:1;padding:14px;background:#16a34a;color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:600;cursor:pointer;transition:background .2s;">
                                Next Question <i class="fa-solid fa-arrow-right fa-xs" style="margin-left:6px;"></i>
                            </button>
                            <?php else: ?>
                            <button type="button" id="finish-btn" onclick="calculateHealth()" style="flex:1;padding:14px;background:#16a34a;color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:600;cursor:pointer;transition:background .2s;">
                                See My Results <i class="fa-solid fa-arrow-right fa-xs" style="margin-left:6px;"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </form>

                <!-- Results -->
                <div id="health-results" style="display:none;">
                    <div style="text-align:center;margin-bottom:40px;">
                        <div style="position:relative;display:inline-flex;align-items:center;justify-content:center;width:160px;height:160px;margin-bottom:20px;">
                            <svg width="160" height="160" viewBox="0 0 160 160" style="transform:rotate(-90deg);">
                                <circle cx="80" cy="80" r="70" fill="none" stroke="#e2e8f0" stroke-width="12"/>
                                <circle id="score-ring" cx="80" cy="80" r="70" fill="none" stroke="#16a34a" stroke-width="12" stroke-linecap="round" stroke-dasharray="439.6" stroke-dashoffset="439.6" style="transition:stroke-dashoffset 1s ease;"/>
                            </svg>
                            <div style="position:absolute;text-align:center;">
                                <div id="final-score" style="font-size:42px;font-weight:900;color:#0f172a;line-height:1;"></div>
                                <div style="font-size:12px;color:#94a3b8;margin-top:2px;">out of 100</div>
                            </div>
                        </div>
                        <div id="grade-badge" style="display:inline-flex;align-items:center;gap:8px;padding:8px 20px;border-radius:99px;font-size:16px;font-weight:600;margin-bottom:16px;"></div>
                        <h2 id="grade-title" style="font-size:24px;font-weight:800;color:#0f172a;margin:0 0 10px;"></h2>
                        <p id="grade-desc" style="color:#64748b;max-width:480px;margin:0 auto;"></p>
                    </div>

                    <div style="margin-bottom:32px;">
                        <h3 style="font-weight:800;color:#0f172a;font-size:16px;margin:0 0 16px;">Recommended next steps:</h3>
                        <div id="health-recommendations" style="display:flex;flex-direction:column;gap:10px;"></div>
                    </div>

                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:16px;padding:28px;text-align:center;margin-bottom:24px;">
                        <p style="font-weight:800;color:#0f172a;margin:0 0 6px;">Want a detailed analysis and action plan?</p>
                        <p style="color:#64748b;font-size:14px;margin:0 0 20px;">Book a free strategy session and we'll walk through your results together.</p>
                        <a href="<?= SITE_URL ?>/consultation" class="tm-btn-primary">
                            Book Free Session <i class="fa-solid fa-arrow-right fa-xs"></i>
                        </a>
                    </div>

                    <div style="text-align:center;">
                        <button onclick="location.reload()" style="background:none;border:none;color:#16a34a;font-weight:500;cursor:pointer;font-size:14px;">
                            <i class="fa-solid fa-rotate-left" style="margin-right:6px;"></i> Retake Assessment
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
.hc-option:hover { border-color:#86efac !important; background:#f0fdf4 !important; }
.hc-option.selected { border-color:#16a34a !important; background:#f0fdf4 !important; }
.hc-option.selected .hc-radio { border-color:#16a34a !important; background:#16a34a !important; }
.hc-option.selected .hc-dot { opacity:1 !important; }
.hc-option.selected .hc-pts { color:#16a34a !important; }
@keyframes shake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-5px)} 75%{transform:translateX(5px)} }
</style>

<script>
// Radio selection styling
document.querySelectorAll('.hc-option').forEach(label => {
    label.addEventListener('click', () => {
        const input = label.querySelector('input[type=radio]');
        const name = input.name;
        document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
            r.closest('label').classList.remove('selected');
        });
        input.checked = true;
        label.classList.add('selected');
    });
});

// Step navigation
let currentStep = 0;
const steps = document.querySelectorAll('[data-step]');
const total = steps.length;

function showStep(n) {
    steps.forEach((s, i) => s.style.display = i === n ? 'block' : 'none');
    document.getElementById('health-progress').style.width = ((n + 1) / total * 100) + '%';
    document.getElementById('step-label').textContent = `Step ${n + 1} of ${total}`;
}

document.querySelectorAll('[data-next]').forEach(btn => {
    btn.addEventListener('click', () => {
        const step = steps[currentStep];
        const checked = step.querySelector(`input[name="q${currentStep}"]:checked`);
        if (!checked) {
            step.querySelectorAll('.hc-option').forEach(o => {
                o.style.animation = 'shake 0.3s ease';
                setTimeout(() => o.style.animation = '', 300);
            });
            return;
        }
        if (currentStep < total - 1) { currentStep++; showStep(currentStep); }
    });
});

document.querySelectorAll('[data-back]').forEach(btn => {
    btn.addEventListener('click', () => {
        if (currentStep > 0) { currentStep--; showStep(currentStep); }
    });
});

function calculateHealth() {
    const form = document.getElementById('health-form');
    let score = 0;
    for (let i = 0; i < total; i++) {
        const checked = form.querySelector(`input[name="q${i}"]:checked`);
        if (checked) score += parseInt(checked.value);
    }
    form.style.display = 'none';
    document.getElementById('health-results').style.display = 'block';
    document.getElementById('final-score').textContent = score;
    setTimeout(() => {
        const offset = 439.6 - (score / 100) * 439.6;
        document.getElementById('score-ring').style.strokeDashoffset = offset;
    }, 100);

    let grade, title, desc, color, stroke, icon;
    if (score >= 80) {
        grade = 'A: Excellent'; title = 'Your business is digitally mature!';
        color = 'background:#dcfce7;color:#15803d;'; stroke = '#16a34a';
        icon = 'fa-trophy'; desc = 'You\'re running a well-organized digital operation. Focus on optimization and advanced automation to stay ahead.';
    } else if (score >= 60) {
        grade = 'B: Good'; title = 'Solid foundation, room to optimize';
        color = 'background:#dbeafe;color:#1d4ed8;'; stroke = '#2563eb';
        icon = 'fa-thumbs-up'; desc = 'You have good systems in place. Strategic improvements in automation and integration will significantly boost your efficiency.';
    } else if (score >= 40) {
        grade = 'C: Fair'; title = 'Significant gaps to address';
        color = 'background:#fef3c7;color:#b45309;'; stroke = '#d97706';
        icon = 'fa-triangle-exclamation'; desc = 'Your business has potential but manual processes are creating bottlenecks. Targeted technology investment will pay off quickly.';
    } else {
        grade = 'D: Needs Work'; title = 'Your business needs digital help now';
        color = 'background:#fee2e2;color:#b91c1c;'; stroke = '#dc2626';
        icon = 'fa-circle-exclamation'; desc = 'You\'re leaving significant money and time on the table. The good news: even small improvements will have an immediate impact.';
    }

    document.getElementById('score-ring').style.stroke = stroke;
    const badge = document.getElementById('grade-badge');
    badge.style.cssText = badge.style.cssText + color;
    badge.innerHTML = `<i class="fa-solid ${icon}"></i> ${grade}`;
    document.getElementById('grade-title').textContent = title;
    document.getElementById('grade-desc').textContent = desc;

    const recs = [
        {title:'Implement a CRM System', desc:'Start tracking every customer interaction automatically.', icon:'fa-users'},
        {title:'Automate Your Invoicing', desc:'Stop sending manual invoices. Automate billing and follow-ups.', icon:'fa-file-invoice-dollar'},
        {title:'Set Up Communication Tools', desc:'Centralize your team communication in one platform.', icon:'fa-comments'},
        {title:'Build a Proper Website', desc:'Get a professional digital presence that generates leads 24/7.', icon:'fa-globe'},
        {title:'Deploy a Business Dashboard', desc:'See your key metrics in real-time. Stop guessing.', icon:'fa-chart-line'},
        {title:'Automate Reporting', desc:'Get weekly performance reports without manual compilation.', icon:'fa-clipboard-list'},
    ];
    const show = score < 60 ? recs : recs.slice(0, 3);
    document.getElementById('health-recommendations').innerHTML = show.map(r => `
        <div style="display:flex;gap:16px;padding:16px 20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;align-items:flex-start;">
            <div style="width:38px;height:38px;background:#dcfce7;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#16a34a;flex-shrink:0;">
                <i class="fa-solid ${r.icon}"></i>
            </div>
            <div>
                <div style="font-weight:600;color:#0f172a;margin-bottom:3px;">${r.title}</div>
                <div style="font-size:13px;color:#64748b;">${r.desc}</div>
            </div>
        </div>
    `).join('');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
