<?php
// Проверяет, что скрипт запущен внутри Cotonti, иначе останавливает выполнение с ошибкой "Wrong URL".
// стандартная защита ядра Cotonti.
defined('COT_CODE') or die('Wrong URL');

/* ====================
[BEGIN_COT_EXT]
Hooks=page.main
[END_COT_EXT]
==================== */
// Конфигурация плагина: хук page.main выполняется при рендеринге страницы модуля page.

/**
 * SEO Article: Обработка страницы перед рендерингом
 * Filename: seoarticle.page.main.php
 * @package SeoArticle
 * @copyright (c) webitproff 2025
 * Version=1.0.0
 * Date=2025-04-22
 * @license https://github.com/Cotonti/Cotonti/blob/master/License.txt
 */

// Подключает файл функций плагина seoarticle (например, cot_extract_keywords для ключевых слов, cot_estimate_read_time).
// нужно для обработки ключевых слов и других функций.
require_once cot_incfile('seoarticle', 'plug');

// Подключает языковой файл плагина для переводов (например, Cot::$L['seoarticle_unknown_author']).
// Подключаем файл локализации, например /plugins/seoarticle/lang/seoarticle.ru.lang.php
require_once cot_langfile('seoarticle', 'plug');

// Делает глобальными переменные базы данных: $db (объект DB), $db_structure (таблица cot_structure), $db_users (таблица cot_users).
// Нужно для запросов к cot_structure (категория) и cot_users (автор). 
global $db, $db_structure, $db_users;

// Задаёт изображение для Open Graph и Schema.org: берёт $pag['page_link_main_image'] (экстраполе page_link_main_image в cot_pages), если оно существует и не пустое.
// Проверяет isset(), чтобы избежать предупреждений, если экстраполе не добавлено в админке (Администрирование → Структура → Экстраполя).
// Если используете другое экстраполе (например, page_image), замените 'page_link_main_image'. Для другого дефолта измените путь.
$seo_image = (isset($pag['page_link_main_image']) && !empty($pag['page_link_main_image'])) ? $pag['page_link_main_image'] : Cot::$cfg['mainurl'] . '/images/default.jpg';

// Задаёт локаль для Open Graph (og:locale), берёт из настроек Cotonti или 'ru_RU' по умолчанию.
// Если нужна другая локаль (например, en_US), замените 'ru_RU'.
$locale = isset(Cot::$cfg['locale']) ? Cot::$cfg['locale'] : 'ru_RU';

// Извлекает никнейм владельца страницы из таблицы cot_users по $pag['page_ownerid'] (поле page_ownerid в cot_pages).
// Запрашивает user_name, где user_id = page_ownerid. Если пользователь не найден или user_name пуст, использует перевод 'seoarticle_unknown_author' или 'Неизвестный автор'.
$owner_id = $pag['page_ownerid'] ?? 0;
$author_name = ($owner_id > 0) ? $db->query("SELECT user_name FROM $db_users WHERE user_id = ?", [$owner_id])->fetchColumn() : '';
$author_name = !empty($author_name) ? htmlspecialchars($author_name) : (Cot::$L['seoarticle_unknown_author'] ?? 'Неизвестный автор');

// Задаёт заголовок для Open Graph и Schema.org: берёт $pag['page_metatitle'] (поле page_metatitle) или $pag['page_title'] (поле page_title).
// Если мета-заголовок не нужен, можно использовать только $pag['page_title'].
$page_title = !empty($pag['page_metatitle']) ? $pag['page_metatitle'] : ($pag['page_title'] ?? '');

// Задаёт описание: берёт $pag['page_metadesc'] (поле page_metadesc, обрезает до 160 символов) или очищенный текст из $pag['page_text'] (поле page_text).
// Очищает HTML-теги, декодирует сущности, убирает переносы строк и лишние пробелы для вывода в одну строку. Используется для og:description и Schema.org description.
$page_desc = !empty($pag['page_metadesc']) ? $pag['page_metadesc'] : strip_tags(html_entity_decode($pag['page_text'] ?? ''));
// Удаляет переносы строк, табуляцию и лишние пробелы, заменяя их на одиночный пробел
$page_desc = preg_replace('/[\'"`]+/', '', $page_desc);
$page_desc = preg_replace('/\s+/', ' ', trim($page_desc));
// Обрезает до 160 символов
$page_desc = cot_string_truncate($page_desc, 160);

// Задаёт ключевые слова: берёт $pag['page_keywords'] (поле page_keywords), если не пусто, или генерирует из $pag['page_text'] с помощью cot_extract_keywords.
// Обрезает до 255 символов. Используется в Schema.org и других тегах.
$page_keywords = !empty($pag['page_keywords']) ? cot_string_truncate($pag['page_keywords'], 255) : cot_extract_keywords($pag['page_text'] ?? '', 10);

// Задаёт ID страницы из $pag['page_id'] (поле page_id в cot_pages), или 0, если его нет, для безопасности.
// Используется в $page_url как запасной вариант. Не меняйте, если не нужен другой ID.
$page_id = $pag['page_id'] ?? 0;

// Задаёт дату публикации из $pag['page_date'] (поле page_date в cot_pages), или текущую дату, если пусто.
// Используется в Schema.org (datePublished). Не меняйте, если не нужна другая дата.
$page_date = $pag['page_date'] ?? time();

// Задаёт дату обновления из $pag['page_updated'] (поле page_updated в cot_pages), или текущую дату, если пусто.
// Используется в Schema.org (dateModified). Не меняйте, если не нужна другая дата.
$page_updated = $pag['page_updated'] ?? time();

// Задаёт URL страницы: берёт канонический URL (COT_ABSOLUTE_URL + $out['canonical_uri']) или URL по $page_id.
// $out['canonical_uri'] формируется Cotonti (например, /news/welcome). Если нужен только ID, замените на cot_url('page', 'id=' . $page_id).
$page_url = !empty($out['canonical_uri']) ? COT_ABSOLUTE_URL . $out['canonical_uri'] : cot_url('page', 'id=' . $page_id);

// Извлекает название категории из таблицы cot_structure по $pag['page_cat'] (поле page_cat в cot_pages, например, 'news').
// Запрашивает structure_title (например, 'News'). Если категория не найдена, использует $pag['page_cat'].
$category_code = $pag['page_cat'] ?? '';
$category_name = !empty($category_code) ? $db->query("SELECT structure_title FROM $db_structure WHERE structure_code = ? AND structure_area = 'page'", [$category_code])->fetchColumn() : '';
$category_name = !empty($category_name) ? htmlspecialchars($category_name) : htmlspecialchars($category_code);

// Формирует HTML-код мета-тегов для Open Graph, Twitter Card и Schema.org, включая категорию и ключевые слова.
// Каждая строка — мета-тег или JSON-LD. Если какой-то блок (например, Twitter Card) не нужен, удалите его часть.
$meta_tags = '
    <!-- Open Graph -->
    <!-- Заголовок страницы для соцсетей -->
    <meta property="og:title" content="' . htmlspecialchars($page_title) . '">
    <!-- Описание страницы для соцсетей -->
    <meta property="og:description" content="' . htmlspecialchars($page_desc) . '">
    <!-- Тип контента (статья) -->
    <meta property="og:type" content="article">
    <!-- URL страницы -->
    <meta property="og:url" content="' . htmlspecialchars($page_url) . '">
    <!-- Изображение для соцсетей -->
    <meta property="og:image" content="' . htmlspecialchars($seo_image) . '">
    <!-- Альтернативный текст для изображения -->
    <meta property="og:image:alt" content="' . htmlspecialchars($page_title) . '">
    <!-- Название сайта -->
    <meta property="og:site_name" content="' . htmlspecialchars(Cot::$cfg['maintitle']) . '">
    <!-- Локаль страницы -->
    <meta property="og:locale" content="' . htmlspecialchars($locale) . '">
    <!-- Twitter Card -->
    <!-- Тип карточки Twitter (с большим изображением) -->
    <meta name="twitter:card" content="summary_large_image">
    <!-- Заголовок для Twitter -->
    <meta name="twitter:title" content="' . htmlspecialchars($page_title) . '">
    <!-- Описание для Twitter -->
    <meta name="twitter:description" content="' . htmlspecialchars($page_desc) . '">
    <!-- Изображение для Twitter -->
    <meta name="twitter:image" content="' . htmlspecialchars($seo_image) . '">
    <!-- Schema.org -->
    <!-- Структурированные данные для поисковиков -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Article",
      "headline": "' . htmlspecialchars($page_title) . '",
      "description": "' . htmlspecialchars($page_desc) . '",
      "keywords": "' . htmlspecialchars($page_keywords) . '",
      "articleSection": "' . $category_name . '",
      "author": {
        "@type": "Person",
        "name": "' . htmlspecialchars($author_name) . '"
      },
      "publisher": {
        "@type": "Organization",
        "name": "' . htmlspecialchars(Cot::$cfg['maintitle']) . '",
        "logo": {
          "@type": "ImageObject",
          "url": "' . Cot::$cfg['mainurl'] . '/images/logo.png"
        }
      },
      "datePublished": "' . cot_date('c', $page_date) . '",
      "dateModified": "' . cot_date('c', $page_updated) . '",
      "image": "' . htmlspecialchars($seo_image) . '",
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "' . htmlspecialchars($page_url) . '"
      }
    }
    </script>';

// Добавляет мета-теги в $out['meta'] для вывода в <head> через шаблон /themes/сleanсot/header.tpl смотреть https://github.com/webitproff/cot-CleanCot
// Подключать в header.tpl так: <!-- IF {PHP.out.meta} -->{PHP.out.meta}<!-- ENDIF -->
global $out;
$out['meta'] = (isset($out['meta']) ? $out['meta'] : '') . $meta_tags;
