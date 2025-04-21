<?php
/**
 * SEO Article plugin functions
 * @package SeoArticle
 * @copyright (c) webitproff 2025
 * Version=1.0.0
 * Date=2025-04-22
 * @license https://github.com/Cotonti/Cotonti/blob/master/License.txt
 */

defined('COT_CODE') or die('Wrong URL');

/**
 * Extracts keywords from text
 * @param string $text Input text
 * @param int $limit Maximum number of keywords to return
 * @return string Comma-separated keywords
 */
function cot_extract_keywords($text, $limit = 10)
{
    if (empty($text)) {
        return '';
    }

    // Load stop words from language file
    $stop_words = !empty(Cot::$L['seoarticle_stop_words']) ? explode(',', Cot::$L['seoarticle_stop_words']) : [];
    $stop_words = array_map('trim', $stop_words);
    $stop_words = array_map('mb_strtolower', $stop_words);

    // Clean text: remove HTML, decode entities, convert to lowercase
    $text = strip_tags(html_entity_decode($text));
    $text = mb_strtolower($text, 'UTF-8');

    // Replace punctuation with spaces and normalize spaces
    $text = preg_replace('/[.,!?;:"\'\(\)\[\]{}<>\n\r\t]/u', ' ', $text);
    $text = preg_replace('/\s+/u', ' ', trim($text));

    // Split into words
    $words = explode(' ', $text);
    $word_count = [];

    // Count word frequencies
    foreach ($words as $word) {
        $word = trim($word);
        if (mb_strlen($word) > 2 && !in_array($word, $stop_words)) {
            $word_count[$word] = isset($word_count[$word]) ? $word_count[$word] + 1 : 1;
        }
    }

    // Sort by frequency and limit
    arsort($word_count);
    $keywords = array_keys(array_slice($word_count, 0, $limit, true));

    // Return comma-separated keywords
    return implode(', ', $keywords);
}

/**
 * Estimates reading time for text
 * @param string $text Input text
 * @return int Estimated reading time in minutes
 */
function cot_estimate_read_time($text)
{
    if (empty($text)) {
        return 1; // Если текст пуст, возвращаем 1 минуту
    }

    // Удаляем HTML-теги и BB-коды
    $text = strip_tags($text);
    $text = preg_replace('/\[.*?\]/', '', $text);
    $text = trim($text);

    if (empty($text)) {
        return 1; // Если после очистки текст пуст, возвращаем 1 минуту
    }

    // Подсчитываем слова (для UTF-8, включая русский текст)
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $word_count = count($words);

    // Средняя скорость чтения: 200 слов в минуту
    $minutes = ceil($word_count / 200);

    return max(1, $minutes); // Минимальное время — 1 минута
}