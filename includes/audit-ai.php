<?php
/**
 * AI narrative layer for the website audit tool (DeepSeek API).
 * Only ever fed real, already-scraped facts (checks + on-page text),
 * never invents data the mechanical scan didn't actually observe.
 */
function auditDeepseekCall(string $systemPrompt, string $userPrompt, int $maxTokens = 350, bool $json = false): ?string {
    if (!defined('DEEPSEEK_API_KEY') || DEEPSEEK_API_KEY === '') return null;

    $payload = [
        'model' => 'deepseek-chat',
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ],
        'temperature' => 0.4,
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
 * Returns ['executive_summary'=>, 'category_narratives'=>[cat=>text], 'roadmap_narrative'=>, 'closing_note'=>] or null.
 */
function auditGenerateFullReport(string $url, array $checks, array $categoryScores, string $title, string $metaDesc, int $wordCount, int $pagesScanned): ?array {
    $byCategory = [];
    foreach ($checks as $c) { $byCategory[$c['category']][] = $c; }

    $factsText = "Site: $url\nPage title: \"$title\"\nMeta description: \"$metaDesc\"\nHomepage word count: $wordCount\nPages scanned: $pagesScanned\n\n";
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

    $prompt = "You are a senior website auditor writing a detailed, plain-English report for a business owner. "
        . "Base EVERY claim strictly on the facts below, never invent metrics, traffic, revenue, or competitor data you weren't given. "
        . "If a category has few issues, say so briefly rather than padding.\n\n"
        . "FACTS FROM A LIVE SCAN:\n$factsText\n"
        . "Respond with ONLY a JSON object with this exact shape:\n"
        . "{\n"
        . '  "executive_summary": "3-5 sentence plain-English overview of the site\'s overall health and the single biggest opportunity",' . "\n"
        . '  "category_narratives": { one key per category in ' . $namesJson . ', each a 2-4 sentence narrative synthesizing that category\'s findings (not just listing them) },' . "\n"
        . '  "roadmap_narrative": "3-4 sentences framing how to sequence fixes: what to do first and why, referencing real findings",' . "\n"
        . '  "closing_note": "1-2 sentence encouraging closing statement"' . "\n"
        . "}";

    $raw = auditDeepseekCall(
        'You are a meticulous, honest website auditor. You only write about facts you were given. Output valid JSON only, no markdown fences.',
        $prompt, 1800, true
    );
    if (!$raw) return null;
    $parsed = json_decode($raw, true);
    if (!is_array($parsed) || empty($parsed['executive_summary'])) return null;
    return $parsed;
}
