<?php
// Проверяет, что скрипт запущен внутри Cotonti, иначе останавливает выполнение с ошибкой "Wrong URL".
defined('COT_CODE') or die('Wrong URL');

/* ====================
[BEGIN_COT_EXT]
Hooks=page.tags
Tags=page.tpl:{PAGE_URL},{PAGE_AUTHOR},{RELATED_PAGES},{RELATED_ROW_URL},{RELATED_ROW_TITLE},{RELATED_ROW_DESC},{RELATED_ROW_LINK_MAIN_IMAGE},{PAGE_READ_TIME}
[END_COT_EXT]
==================== */
// Конфигурация плагина: хук page.tags обрабатывает теги для модуля page; указаны теги для шаблона page.tpl.
// Если добавляете новый тег, добавь его в Tags и в $t->assign ниже.

/**
 * SEO Article: 
 * Filename: seoarticle.page.tags.php
 * @package SeoArticle
 * @copyright (c) webitproff 2025
 * Version=1.0.0
 * Date=2025-04-22
 * @license https://github.com/Cotonti/Cotonti/blob/master/License.txt
 */

// Делает глобальными переменные базы данных: $db (объект DB), $db_pages (таблица cot_pages), $db_x (префикс таблиц), $db_users (таблица cot_users).
// Нужно для SQL-запросов к cot_pages и cot_users. Не удаляйте.
global $db, $db_pages, $db_x, $db_users;

// Подключает файл функций плагина seoarticle (например, cot_estimate_read_time для PAGE_READ_TIME).
require_once cot_incfile('seoarticle', 'plug');

// Подключает языковой файл плагина для перевода (например, Cot::$L['seoarticle_read_time'], Cot::$L['seoarticle_unknown_author']).
// нужно для вывода PAGE_READ_TIME и PAGE_AUTHOR.
require_once cot_langfile('seoarticle', 'plug');

// Задаёт ID страницы из $pag['page_id'] (поле page_id в cot_pages), или 0, если его нет, для безопасности.
// Используется в PAGE_URL и для исключения текущей страницы в связанных страницах. Не меняй, если не нужен другой ID.
$page_id = $pag['page_id'] ?? 0;

// Вычисляет время чтения для $pag['page_text'] (поле page_text в cot_pages) с помощью cot_estimate_read_time.
// Если page_text пуст, возвращает 1 минуту. Если текст в другом поле (например, page_text_prsed), замените 'page_text'.
$read_time = cot_estimate_read_time($pag['page_text'] ?? '');

// Извлекает никнейм владельца страницы из таблицы cot_users по $pag['page_ownerid'] (поле page_ownerid в cot_pages).
// Запрашивает user_name, где user_id = page_ownerid. Если пользователь не найден или user_name пуст, использует перевод 'seoarticle_unknown_author' или 'Неизвестный автор'.
$owner_id = $pag['page_ownerid'] ?? 0;
$owner_name = ($owner_id > 0) ? $db->query("SELECT user_name FROM $db_users WHERE user_id = ?", [$owner_id])->fetchColumn() : '';
$owner_name = !empty($owner_name) ? htmlspecialchars($owner_name) : (Cot::$L['seoarticle_unknown_author'] ?? 'Неизвестный автор');

// Назначает теги для шаблона page.tpl, чтобы они отображались (например, {PAGE_URL} в ссылке).
// Массив: ключ — имя тега, значение — данные из $pag, $owner_name или вычисленные значения.
$t->assign([
    // Тег для URL страницы: берёт канонический URL (COT_ABSOLUTE_URL + $out['canonical_uri']) или URL по $page_id.
    // $out['canonical_uri'] формируется Cotonti (например, /news/welcome). Если нужен только ID, замените на cot_url('page', 'id=' . $page_id).
    'PAGE_URL' => !empty($out['canonical_uri']) ? COT_ABSOLUTE_URL . $out['canonical_uri'] : cot_url('page', 'id=' . $page_id),
    
    // Тег для автора: использует $owner_name (никнейм из cot_users по page_ownerid или перевод 'seoarticle_unknown_author').
    // Если нужно другое поле (например, user_email), замените user_name в запросе. Если автор не нужен, удалите строку.
    'PAGE_AUTHOR' => $owner_name,
    
    // Тег для времени чтения: объединяет $read_time (в минутах) и перевод "мин чтения" (Cot::$L['seoarticle_read_time']).
    // Если время чтения не нужно, удалите строку. Выводится, например, как "5 мин чтения".
    'PAGE_READ_TIME' => $read_time . ' ' . Cot::$L['seoarticle_read_time'],
]);

// Запрашивает до двух связанных страниц из cot_pages, где page_cat совпадает с текущей ($pag['page_cat'], например, 'news').
// Исключает текущую страницу ($page_id), берёт только опубликованные (page_state = 0), сортирует по дате (page_date, новые первыми).
// Если связанные страницы не нужны, удалите этот блок и теги RELATED_*.
$related = $db->query("SELECT * FROM $db_pages 
    WHERE page_cat = ? AND page_id != ? AND page_state = 0 
    ORDER BY page_date DESC LIMIT 2", 
    [$pag['page_cat'] ?? '', $page_id]
)->fetchAll();

// Проверяет, есть ли связанные страницы (если $related не пуст).
// Если связанных страниц нет, блок пропускается, и {RELATED_PAGES} в page.tpl будет пустым.
if (count($related) > 0) {
    // Устанавливает тег RELATED_PAGES в true, чтобы показать блок связанных страниц в page.tpl.
    // Если не используется {RELATED_PAGES}, удалите строку.
    $t->assign('RELATED_PAGES', true);
    
    // Перебирает связанные страницы из $related для вывода их данных.
    // Каждая итерация — одна страница. Если связанные страницы не нужны, удалите блок foreach.
    foreach ($related as $rel) {
        // Назначает теги для связанной страницы, используемые в блоке MAIN.RELATED_PAGES.RELATED_ROW в page.tpl.
        $t->assign([
            // URL связанной страницы: формирует URL по $rel['page_id'] (поле page_id).
            // Для канонического URL замени на COT_ABSOLUTE_URL . $rel['page_alias'] (если используются алиасы).
            'RELATED_ROW_URL' => cot_url('page', 'id=' . ($rel['page_id'] ?? 0)),
            
            // Заголовок связанной страницы: берёт $rel['page_title'] (поле page_title) или пустую строку.
            // Если заголовок не нужен, удалите строку.
            'RELATED_ROW_TITLE' => $rel['page_title'] ?? '',
            
            // Описание: обрезает $rel['page_text'] (поле page_text) до 100 символов.
            // Если текст в другом поле (например, page_text_prsed), замените 'page_text'. Если описание не нужно, удалите.
            'RELATED_ROW_DESC' => cot_string_truncate($rel['page_text'] ?? '', 100),
            
            // Изображение: берёт $rel['page_link_main_image'] (поле page_link_main_image), если оно существует и не пустое, или дефолтное изображение.
            // Проверяет isset(), чтобы избежать предупреждений, если поле не добавлено в cot_pages. Для другого дефолта измените путь.
            'RELATED_ROW_LINK_MAIN_IMAGE' => (isset($rel['page_link_main_image']) && !empty($rel['page_link_main_image'])) ? $rel['page_link_main_image'] : Cot::$cfg['mainurl'] . '/images/default.jpg',
        ]);
        
        // Обрабатывает блок MAIN.RELATED_PAGES.RELATED_ROW в page.tpl для каждой связанной страницы.
        // Если блок не используется в шаблоне, удалите строку.
        $t->parse('MAIN.RELATED_PAGES.RELATED_ROW');
    }
}