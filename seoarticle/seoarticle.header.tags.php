<?php
// Проверяет, что скрипт запущен внутри Cotonti, иначе останавливает выполнение с ошибкой "Wrong URL".
defined('COT_CODE') or die('Wrong URL');

/* ====================
[BEGIN_COT_EXT]
Hooks=header.tags
[END_COT_EXT]
==================== */
// Конфигурация плагина: хук header.tags выполняется при формировании тегов <head> в шаблоне header.tpl.

/**
 * SEO Article: Переопределение HEADER_META_DESCRIPTION и HEADER_META_KEYWORDS
 * Filename: seoarticle.header.tags.php
 * @package SeoArticle
 * @copyright (c) webitproff 2025
 * Version=1.0.0
 * Date=2025-04-22
 * @license https://github.com/Cotonti/Cotonti/blob/master/License.txt
 */

// Подключает файл функций плагина seoarticle (например, cot_string_truncate, cot_extract_keywords).
// нужно для обработки описания и ключевых слов.
require_once cot_incfile('seoarticle', 'plug');

// Задаёт описание: берёт $pag['page_metadesc'] (поле page_metadesc, обрезает до 160 символов) или очищенный текст из $pag['page_text'] (поле page_text).
// Очищает HTML-теги, декодирует сущности, убирает переносы строк и лишние пробелы для вывода в одну строку.
$page_desc = !empty($pag['page_metadesc']) ? $pag['page_metadesc'] : strip_tags(html_entity_decode($pag['page_text'] ?? ''));
// Удаляет кавычки (" ` '), переносы строк, табуляцию и лишние пробелы, заменяя их на одиночный пробел
$page_desc = preg_replace('/[\'"`]+/', '', $page_desc);
$page_desc = preg_replace('/\s+/', ' ', trim($page_desc));
// Обрезает до 160 символов
$page_desc = cot_string_truncate($page_desc, 160);

// Задаёт ключевые слова: берёт $pag['page_keywords'] (поле page_keywords), если не пусто, или генерирует из $pag['page_text'] с помощью cot_extract_keywords.
// Обрезает до 255 символов для использования в HEADER_META_KEYWORDS.
$page_keywords = !empty($pag['page_keywords']) ? cot_string_truncate($pag['page_keywords'], 255) : cot_extract_keywords($pag['page_text'] ?? '', 10);

// Переопределяет HEADER_META_DESCRIPTION и HEADER_META_KEYWORDS для использования в шаблоне header.tpl.
$t->assign([
    'HEADER_META_DESCRIPTION' => $page_desc,
    'HEADER_META_KEYWORDS' => $page_keywords
]);
