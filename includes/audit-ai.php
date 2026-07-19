<?php
/**
 * AI narrative layer for the website audit tool (DeepSeek API).
 * Only ever fed real, already-scraped facts (checks + on-page text),
 * never invents data the mechanical scan didn't actually observe.
 */
function auditGenerateSummary(string $url, array $checks, string $title, string $metaDesc, int $wordCount): ?string {
    if (!defined('DEEPSEEK_API_KEY') || DEEPSEEK_API_KEY === '') return null;

    $failsAndWarns = array_values(array_filter($checks, fn($c) => $c['status'] !== 'pass'));
    usort($failsAndWarns, fn($a,$b) => $b['weight'] <=> $a['weight']);
    $issueLines = [];
    foreach (array_slice($failsAndWarns, 0, 12) as $c) {
        $issueLines[] = "- [{$c['status']}] {$c['category']}: {$c['label']} — {$c['detail']}";
    }
    $issuesText = implode("\n", $issueLines) ?: 'No significant issues found.';

    $prompt = "You are a website auditor writing a short, plain-English executive summary for a business owner (not a developer). "
        . "Base your analysis ONLY on the facts given below. Do not invent metrics, traffic numbers, or claims you weren't given.\n\n"
        . "Site: $url\n"
        . "Page title: \"$title\"\n"
        . "Meta description: \"$metaDesc\"\n"
        . "Homepage word count: $wordCount\n\n"
        . "Issues found by the scan:\n$issuesText\n\n"
        . "Write:\n1. A 2-3 sentence plain-English summary of the site's overall state.\n"
        . "2. Exactly 3 prioritized next actions, each one sentence, ordered by impact.\n"
        . "Keep it concise, no headers, no markdown, plain text with the 3 actions as a numbered list.";

    $ch = curl_init('https://api.deepseek.com/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . DEEPSEEK_API_KEY,
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a concise, honest website auditor. Never fabricate data.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.4,
            'max_tokens'  => 350,
        ]),
    ]);
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($raw === false || $code !== 200) return null;
    $data = json_decode($raw, true);
    return $data['choices'][0]['message']['content'] ?? null;
}
