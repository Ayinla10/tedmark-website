<?php
/**
 * AI narrative layer for the website audit tool (DeepSeek API).
 * Only ever fed real, already-scraped facts (checks + on-page text),
 * never invents data the mechanical scan didn't actually observe.
 */

/** Market context presets — calibrates recommendations (contact channel, payments, directories, compliance). */
const AUDIT_MARKET_PRESETS = [
    'ghana' => [
        'region' => 'Ghana, West Africa',
        'currency' => 'GHS (Ghanaian Cedis)',
        'primary_contact' => 'WhatsApp (dominant B2B channel in Ghana)',
        'payment_context' => 'Mobile Money (MoMo) — MTN, Vodafone Cash, AirtelTigo',
        'local_seo_note' => 'Google Maps presence critical; Ghana Yellow Pages and BusinessGhana.com for local citations',
        'directory_sites' => 'GhanaYello, BusinessGhana.com, Clutch.co, TechPoint Africa',
        'social_priority' => 'WhatsApp, Facebook, Instagram, LinkedIn',
        'compliance_note' => 'Ghana Data Protection Act 2012 (Act 843) applies to all websites collecting user data',
        'competition_note' => 'Most Ghanaian competitors have weak SEO and no AI-readiness — strong opportunity window',
    ],
];

function auditDeepseekCall(string $systemPrompt, string $userPrompt, int $maxTokens = 350, bool $json = false): ?string {
    if (!defined('DEEPSEEK_API_KEY') || DEEPSEEK_API_KEY === '') return null;

    $payload = [
        'model' => 'deepseek-chat',
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ],
        'temperature' => 0.3,
        'max_tokens'  => $maxTokens,
    ];
    if ($json) $payload['response_format'] = ['type' => 'json_object'];

    $ch = curl_init('https://api.deepseek.com/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 45,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . DEEPSEEK_API_KEY,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($raw === false || $code !== 200) {
        error_log('DeepSeek call failed, http=' . $code . ' curl_error=' . curl_error($ch));
        return null;
    }
    $data = json_decode($raw, true);
    return $data['choices'][0]['message']['content'] ?? null;
}

/** Short 2-3 sentence + 3-action teaser summary (used on the pre-unlock page). */
function auditGenerateSummary(string $url, array $checks, string $title, string $metaDesc, int $wordCount): ?string {
    $failsAndWarns = array_values(array_filter($checks, fn($c) => $c['status'] !== 'pass'));
    usort($failsAndWarns, fn($a,$b) => $b['weight'] <=> $a['weight']);
    $issueLines = [];
    foreach (array_slice($failsAndWarns, 0, 12) as $c) {
        $issueLines[] = "- [{$c['status']}] {$c['category']}: {$c['label']} — {$c['detail']}";
    }
    $issuesText = implode("\n", $issueLines) ?: 'No significant issues found.';

    $prompt = "You are a website auditor writing a short, plain-English executive summary for a business owner (not a developer). "
        . "Base your analysis ONLY on the facts given below. Do not invent metrics, traffic numbers, or claims you weren't given.\n\n"
        . "Site: $url\nPage title: \"$title\"\nMeta description: \"$metaDesc\"\nHomepage word count: $wordCount\n\n"
        . "Issues found by the scan:\n$issuesText\n\n"
        . "Write:\n1. A 2-3 sentence plain-English summary of the site's overall state.\n"
        . "2. Exactly 3 prioritized next actions, each one sentence, ordered by impact.\n"
        . "Keep it concise, no headers, no markdown, plain text with the 3 actions as a numbered list.";

    return auditDeepseekCall('You are a concise, honest website auditor. Never fabricate data.', $prompt, 350);
}

/**
 * Full multi-section narrative report for the unlocked report page.
 * Returns ['executive_summary'=>, 'category_narratives'=>[cat=>text], 'roadmap_narrative'=>,
 *          'closing_note'=>, 'tedmark_opportunity'=>['summary','services_needed','lead_score','lead_score_reason']] or null.
 */
function auditGenerateFullReport(
    string $url, array $checks, array $categoryScores, string $title, string $metaDesc,
    int $wordCount, int $pagesScanned, array $technologyStack = [], string $market = 'ghana'
): ?array {
    $ctx = AUDIT_MARKET_PRESETS[$market] ?? AUDIT_MARKET_PRESETS['ghana'];

    $byCategory = [];
    foreach ($checks as $c) { $byCategory[$c['category']][] = $c; }

    $factsText = "Site: $url\nPage title: \"$title\"\nMeta description: \"$metaDesc\"\nHomepage word count: $wordCount\n"
        . "Pages scanned: $pagesScanned\nDetected technology: " . (implode(', ', $technologyStack) ?: 'none detected') . "\n\n";
    foreach ($byCategory as $cat => $catChecks) {
        $score = $categoryScores[$cat] ?? 0;
        $factsText .= "=== $cat (score $score/100) ===\n";
        foreach ($catChecks as $c) {
            $factsText .= "- [{$c['status']}] {$c['label']}: {$c['detail']}\n";
        }
        $factsText .= "\n";
    }

    $categoryNames = array_keys($byCategory);
    $namesJson = json_encode($categoryNames);

    $systemPrompt = "You are a senior digital audit analyst at Tedmark Digital Agency, a technology and digital growth consultancy. "
        . "You specialise in SEO, technical infrastructure, UX, content strategy, conversion optimisation, and digital authority building.\n\n"
        . "CLIENT MARKET CONTEXT:\n"
        . "- Region: {$ctx['region']}\n- Currency: {$ctx['currency']}\n- Primary contact channel: {$ctx['primary_contact']}\n"
        . "- Payment context: {$ctx['payment_context']}\n- Local SEO priority: {$ctx['local_seo_note']}\n"
        . "- Key directories to recommend: {$ctx['directory_sites']}\n- Social platform priority: {$ctx['social_priority']}\n"
        . "- Compliance considerations: {$ctx['compliance_note']}\n- Competitive landscape: {$ctx['competition_note']}\n\n"
        . "Apply this market context to recommendations where relevant (contact channels, payment mentions, directories, compliance). "
        . "NEVER fabricate data, metrics, traffic numbers, or claims beyond what's given. Be specific: name the actual issue and page, not vague generalities. "
        . "Output valid JSON only, no markdown fences.";

    $prompt = "Analyse this live website scan and produce a report. "
        . "Base EVERY claim strictly on the facts below. If a category has few issues, say so briefly rather than padding.\n\n"
        . "FACTS FROM A LIVE SCAN:\n$factsText\n"
        . "Respond with ONLY a JSON object with this exact shape:\n"
        . "{\n"
        . '  "executive_summary": "3-5 sentence plain-English overview of the site\'s overall health and the single biggest opportunity",' . "\n"
        . '  "category_narratives": { one key per category in ' . $namesJson . ', each a 2-4 sentence narrative synthesizing that category\'s findings (not just listing them) },' . "\n"
        . '  "roadmap_narrative": "3-4 sentences framing how to sequence fixes: what to do first and why, referencing real findings",' . "\n"
        . '  "closing_note": "1-2 sentence encouraging closing statement",' . "\n"
        . '  "tedmark_opportunity": {' . "\n"
        . '    "summary": "2-3 sentences on this site\'s digital maturity and growth potential in ' . $ctx['region'] . '",' . "\n"
        . '    "services_needed": [list of 2-5 relevant service types this site needs, e.g. "SEO overhaul", "Content strategy", "Conversion optimisation"],' . "\n"
        . '    "lead_score": integer 1-10 where 10 = ideal prospect for a digital agency (worse sites with real budget signals score higher, not lower),' . "\n"
        . '    "lead_score_reason": "1-2 sentences on why this score, grounded in the actual findings"' . "\n"
        . '  }' . "\n"
        . "}";

    $raw = auditDeepseekCall($systemPrompt, $prompt, 2200, true);
    if (!$raw) return null;
    $parsed = json_decode($raw, true);
    if (!is_array($parsed) || empty($parsed['executive_summary'])) return null;
    return $parsed;
}
