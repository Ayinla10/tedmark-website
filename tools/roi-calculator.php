<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'ROI Calculator: How Much Can You Save?';
$pageDesc  = 'Calculate exactly how much time and money your business is losing to manual processes, and the ROI of automating them.';
$pageHasDarkHero = true;
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ===== HERO ===== -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container">
        <div class="tm-page-hero-inner" style="text-align:center;max-width:640px;margin:0 auto;">
            <div class="tm-badge tm-fade" style="animation-delay:.05s">
                <i class="fa-solid fa-calculator"></i> Free Calculator
            </div>
            <h1 class="tm-page-hero-title tm-fade" style="animation-delay:.1s">Calculate Your<br>Hidden Losses</h1>
            <p class="tm-page-hero-desc tm-fade" style="animation-delay:.15s">
                Find out exactly how much your manual processes are costing you, and what automation would save.
            </p>
        </div>
    </div>
</section>

<!-- ===== CALCULATOR ===== -->
<section style="padding:80px 0;background:#f8fafc;">
    <div class="tm-container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;max-width:1100px;margin:0 auto;" class="roi-grid">

            <!-- Inputs -->
            <div class="tm-fade">
                <div style="background:#fff;border-radius:20px;box-shadow:0 4px 32px rgba(0,0,0,.07);padding:40px;">
                    <h2 style="font-size:22px;font-weight:800;color:#0f172a;margin:0 0 32px;">Enter your business details</h2>

                    <form id="roi-form" style="display:flex;flex-direction:column;gap:28px;">

                        <div>
                            <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:10px;text-transform:uppercase;letter-spacing:.04em;">
                                Employees doing manual tasks
                            </label>
                            <div style="position:relative;">
                                <input type="number" id="roi-staff" min="1" max="500" value="5"
                                    style="width:100%;padding:14px 56px 14px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:16px;font-weight:500;color:#0f172a;box-sizing:border-box;outline:none;transition:border .2s;"
                                    oninput="syncSlider('roi-staff','slider-staff');calculateROI()">
                                <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:13px;color:#94a3b8;font-weight:500;">people</span>
                            </div>
                            <input type="range" id="slider-staff" min="1" max="100" value="5" style="width:100%;margin-top:10px;accent-color:#16a34a;"
                                oninput="syncInput('slider-staff','roi-staff');calculateROI()">
                        </div>

                        <div>
                            <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:10px;text-transform:uppercase;letter-spacing:.04em;">
                                Manual task hours per employee per week
                            </label>
                            <div style="position:relative;">
                                <input type="number" id="roi-hours" min="1" max="40" value="15"
                                    style="width:100%;padding:14px 64px 14px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:16px;font-weight:500;color:#0f172a;box-sizing:border-box;outline:none;transition:border .2s;"
                                    oninput="syncSlider('roi-hours','slider-hours');calculateROI()">
                                <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:13px;color:#94a3b8;font-weight:500;">hrs/wk</span>
                            </div>
                            <input type="range" id="slider-hours" min="1" max="40" value="15" style="width:100%;margin-top:10px;accent-color:#16a34a;"
                                oninput="syncInput('slider-hours','roi-hours');calculateROI()">
                        </div>

                        <div>
                            <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:10px;text-transform:uppercase;letter-spacing:.04em;">
                                Average employee hourly rate (USD)
                            </label>
                            <div style="position:relative;">
                                <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-weight:500;">$</span>
                                <input type="number" id="roi-rate" min="1" max="200" value="12"
                                    style="width:100%;padding:14px 16px 14px 28px;border:2px solid #e2e8f0;border-radius:12px;font-size:16px;font-weight:500;color:#0f172a;box-sizing:border-box;outline:none;transition:border .2s;"
                                    oninput="calculateROI()">
                            </div>
                            <p style="font-size:12px;color:#94a3b8;margin:6px 0 0;">Estimate: ~$8–25/hr for most small businesses</p>
                        </div>

                        <div>
                            <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:10px;text-transform:uppercase;letter-spacing:.04em;">
                                % of tasks that could be automated
                            </label>
                            <div style="position:relative;">
                                <input type="number" id="roi-inefficiency" min="10" max="90" value="60"
                                    style="width:100%;padding:14px 32px 14px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:16px;font-weight:500;color:#0f172a;box-sizing:border-box;outline:none;transition:border .2s;"
                                    oninput="syncSlider('roi-inefficiency','slider-ineff');calculateROI()">
                                <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-weight:500;">%</span>
                            </div>
                            <input type="range" id="slider-ineff" min="10" max="90" value="60" style="width:100%;margin-top:10px;accent-color:#16a34a;"
                                oninput="syncInput('slider-ineff','roi-inefficiency');calculateROI()">
                            <p style="font-size:12px;color:#94a3b8;margin:6px 0 0;">Most businesses: 50–70% of manual tasks can be automated</p>
                        </div>

                    </form>
                </div>
            </div>

            <!-- Results -->
            <div class="tm-fade" style="animation-delay:.1s">
                <div style="display:flex;flex-direction:column;gap:16px;">

                    <!-- Main saving -->
                    <div style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);border-radius:20px;padding:32px;">
                        <p style="font-size:12px;font-weight:600;color:#4ade80;text-transform:uppercase;letter-spacing:.08em;margin:0 0 8px;">Annual Savings with Automation</p>
                        <div id="roi-savings" style="font-size:48px;font-weight:900;color:#fff;line-height:1;margin-bottom:6px;">$0</div>
                        <p style="color:#64748b;font-size:14px;margin:0;">Estimated money recovered each year</p>
                    </div>

                    <!-- Stats row -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:16px;padding:22px;">
                            <p style="font-size:11px;font-weight:600;color:#16a34a;text-transform:uppercase;letter-spacing:.06em;margin:0 0 6px;">Hours Recovered</p>
                            <div id="roi-hours-saved" style="font-size:32px;font-weight:900;color:#15803d;line-height:1;margin-bottom:4px;">0</div>
                            <p style="font-size:12px;color:#64748b;margin:0;">hours per year</p>
                        </div>
                        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:16px;padding:22px;">
                            <p style="font-size:11px;font-weight:600;color:#b45309;text-transform:uppercase;letter-spacing:.06em;margin:0 0 6px;">Efficiency Gain</p>
                            <div id="roi-percent" style="font-size:32px;font-weight:900;color:#b45309;line-height:1;margin-bottom:4px;">0%</div>
                            <p style="font-size:12px;color:#64748b;margin:0;">productivity improvement</p>
                        </div>
                    </div>

                    <!-- What you could do -->
                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
                        <p style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin:0 0 14px;">What you could do with that time</p>
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#334155;">
                                <i class="fa-solid fa-circle-check" style="color:#16a34a;"></i> Focus on revenue-generating activities
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#334155;">
                                <i class="fa-solid fa-circle-check" style="color:#16a34a;"></i> Improve customer service quality
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#334155;">
                                <i class="fa-solid fa-circle-check" style="color:#16a34a;"></i> Expand to new markets and customers
                            </div>
                        </div>
                    </div>

                    <!-- Investment -->
                    <div style="background:#020917;border-radius:16px;padding:24px;">
                        <p style="color:#475569;font-size:13px;margin:0 0 14px;">Typical automation investment at Tedmark:</p>
                        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                            <span style="color:#fff;font-weight:500;font-size:14px;">Setup cost</span>
                            <span style="color:#4ade80;font-weight:600;font-size:14px;">$2,000 – $15,000</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:16px;">
                            <span style="color:#fff;font-weight:500;font-size:14px;">Monthly maintenance</span>
                            <span style="color:#4ade80;font-weight:600;font-size:14px;">$150 – $500</span>
                        </div>
                        <div style="height:1px;background:rgba(255,255,255,.08);margin-bottom:14px;"></div>
                        <p style="color:#4ade80;font-size:13px;font-weight:600;margin:0;">
                            <i class="fa-solid fa-bolt" style="margin-right:6px;"></i> Most clients see ROI within 3–6 months.
                        </p>
                    </div>

                    <a href="<?= SITE_URL ?>/consultation.php" class="tm-btn-primary" style="text-align:center;justify-content:center;">
                        Get a Custom ROI Analysis <i class="fa-solid fa-arrow-right fa-xs"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
@media (max-width:768px) { .roi-grid { grid-template-columns:1fr !important; } }
input[type=number]:focus { border-color:#16a34a !important; }
</style>

<script>
function syncSlider(inputId, sliderId) {
    document.getElementById(sliderId).value = document.getElementById(inputId).value;
}
function syncInput(sliderId, inputId) {
    document.getElementById(inputId).value = document.getElementById(sliderId).value;
}
function calculateROI() {
    const staff  = parseFloat(document.getElementById('roi-staff').value) || 0;
    const hours  = parseFloat(document.getElementById('roi-hours').value) || 0;
    const rate   = parseFloat(document.getElementById('roi-rate').value) || 0;
    const pct    = parseFloat(document.getElementById('roi-inefficiency').value) || 0;

    const annualManual = staff * hours * 52 * rate;
    const savings      = annualManual * (pct / 100);
    const hoursSaved   = Math.round(staff * hours * 52 * (pct / 100));
    const efficiency   = Math.min(Math.round(pct * 0.9), 85);

    document.getElementById('roi-savings').textContent    = '$' + Math.round(savings).toLocaleString();
    document.getElementById('roi-hours-saved').textContent = hoursSaved.toLocaleString();
    document.getElementById('roi-percent').textContent     = efficiency + '%';
}
calculateROI();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
