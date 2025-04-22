# SeoArticle Plugin for Cotonti

The **SeoArticle** plugin enhances the SEO capabilities of the [Cotonti CMF's](https://github.com/Cotonti/Cotonti) Pages module by adding meta tags, Open Graph, Twitter Card, Schema.org structured data, keyword extraction, reading time estimation, and related articles functionality.

## Tested
Cotonti CMF v.0.9.26 beta on PHP v.8.2.25
## File Structure

```
plugins/seoarticle/
├── inc/
│   └── seoarticle.functions.php       # Functions for keyword extraction and reading time estimation
├── lang/
│   └── seoarticle.ru.lang.php         # Russian translations and stop words for keyword extraction
│   └── seoarticle.en.lang.php         # English translations and stop words for keyword extraction
├── seoarticle.header.tags.php         # Overrides HEADER_META_DESCRIPTION and HEADER_META_KEYWORDS
├── seoarticle.page.main.php           # Adds Open Graph, Twitter Card, and Schema.org meta tags
├── seoarticle.page.tags.php           # Defines page tags (e.g., PAGE_READ_TIME, PAGE_AUTHOR, RELATED_ROW_*)
└── seoarticle.setup.php               # Plugin configuration and setup
```

### File Descriptions

- **seoarticle.setup.php**: Defines plugin metadata (name, version, author) and dependencies.
- **seoarticle.header.tags.php**: Overrides `{HEADER_META_DESCRIPTION}` and `{HEADER_META_KEYWORDS}` for meta tags, ensuring single-line descriptions and keyword extraction.
- **seoarticle.page.main.php**: Generates Open Graph, Twitter Card, and Schema.org meta tags, including title, description, keywords, author, and image.
- **seoarticle.functions.php**: Contains `cot_extract_keywords` (extracts keywords with stop words filtering) and `cot_estimate_read_time` (estimates reading time, handles HTML/BBCode).
- **seoarticle.ru.lang.php**: Provides Russian translations for labels (e.g., "Related articles") and stop words for keyword extraction.
- **seoarticle.en.lang.php**: Provides English translations for labels (e.g., "Related articles") and stop words for keyword extraction.
- **seoarticle.page.tags.php**: Adds tags like `{PAGE_READ_TIME}`, `{PAGE_AUTHOR}`, and `{RELATED_ROW_*}` for related articles (See seoarticle.page.tags.php ).

## Features

- **Meta Tags**:
  - Generates `<meta name="description">` and `<meta name="keywords">` based on `page_metadesc`, `page_keywords`, or extracted from `page_text`.
  - Ensures single-line descriptions without line breaks.
- **Open Graph and Twitter Card**:
  - Adds `og:title`, `og:description`, `og:image`, `twitter:card`, etc., for social media sharing.
- **Schema.org**:
  - Includes structured data for articles (`headline`, `description`, `keywords`, `author`, `image`).
- **Keyword Extraction**:
  - Automatically extracts keywords from `page_text` using `cot_extract_keywords` if `page_keywords` is empty.
  - Supports stop words (e.g., "и, в, на") defined in `seoarticle.ru.lang.php`.
- **Reading Time Estimation**:
  - Calculates reading time with `cot_estimate_read_time` (200 words/minute, minimum 1 minute).
  - Handles HTML and BBCode in `page_text`.
- **Related Articles**:
  - Displays related articles with title, description, and image (`page_link_main_image`).
- **Author Information**:
  - Shows author name from `cot_users` or "Unknown author" if not found.

## Installation

### Prerequisites

- Cotonti CMF (tested with Siena).
- Pages module enabled.
- MySQL/MariaDB database.

### Steps

1. **Download the Plugin**:

   - Clone or download the repository from GitHub:

     ```bash
     git clone https://github.com/webitproff/seoarticle.git
     ```
   - Or download the ZIP and extract to `/plugins/seoarticle/`.

2. **Copy Files**:

   - Place the `seoarticle` folder in `/plugins/` of your Cotonti installation:

     ```
     public_html/plugins/seoarticle/
     ```

3. **Install the Plugin**:

   - Go to **Administration → Extensions** in the Cotonti admin panel.
   - Find **SeoArticle** in the list and click **Install**.
   - The plugin will register itself using `seoarticle.setup.php`.

4. **Configure Extra Fields**:

   - Go to **Administration → Structure → Pages → Extra fields**.
   - Add an extra field for images:
     - **Code**: `link_main_image`
     - **Type**: URL
     - **Description**: Main image for Open Graph and related articles
   - Ensure `page_metadesc`, `page_metatitle`, and `page_keywords` are available (default in Cotonti).

5. **Update Templates**:

   - Open `/themes/cleancot/header.tpl` and ensure it includes:

     ```html
     <meta name="description" content="{HEADER_META_DESCRIPTION}" />
     <meta name="keywords" content="{HEADER_META_KEYWORDS}" />
     ```
     also add the code below if it is not in this template:
     ```
     <!-- IF {PHP.out.meta} -->{PHP.out.meta}<!-- ENDIF -->
     ```
   - Open `/themes/cleancot/page.tpl` ([download theme](https://github.com/webitproff/cot-CleanCot)) and add:

     ```html
				<!-- IF {PHP|cot_plugin_active('seoarticle')} -->
				<!-- IF {RELATED_PAGES} -->
				<div class="related">
					<!-- BEGIN: MAIN.RELATED_PAGES.RELATED_ROW -->
					<a href="{RELATED_ROW_URL}">
						<div class="position-relative overflow-hidden rounded-5 shadow-bottom" style="aspect-ratio: 2 / 1;">
							<!-- Условие Cotonti: проверка наличия главного изображения через экстраполяцию -->
							<!-- IF {RELATED_ROW_LINK_MAIN_IMAGE} -->
								<!-- Главное изображение страницы из Cotonti, адаптивное с обрезкой -->
								<img src="{RELATED_ROW_LINK_MAIN_IMAGE}" alt="{RELATED_ROW_TITLE}" class="img-fluid object-fit-cover">
							<!-- Альтернатива: если главного изображения нет -->
							<!-- ELSE -->
								<!-- Дефолтное изображение из темы Cotonti -->
								<img src="{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/img/cotonti-cleancot.webp" alt="{PAGE_TITLE}" class="img-fluid object-fit-cover">
							<!-- Конец условия изображения -->
							<!-- ENDIF -->
						</div>
						<h3 class="h5 mb-0">{RELATED_ROW_TITLE}</h3>
						<p>{RELATED_ROW_DESC}</p>
					</a>
					<!-- END: MAIN.RELATED_PAGES.RELATED_ROW -->
				</div>
				<!-- ENDIF -->
				<!-- ENDIF -->
     ```
and

```
<!-- IF {PHP|cot_plugin_active('seoarticle')} -->
  {PAGE_READ_TIME}
<!-- ENDIF -->
```


6. **Clear Cache**:

   - go to **Administration → Cache → Clear cache**.

7. **Test the Plugin**:

   - Create or edit a page in **Administration → Pages**.
   - Fill `page_metadesc`, `page_keywords`, `page_metatitle`, and `page_link_main_image` (optional).
   - View the page and check:
     - `<meta name="description">` and `<meta name="keywords">` in `<head>`.
     - Open Graph and Schema.org tags.
     - Reading time (`{PAGE_READ_TIME}`), author (`{PAGE_AUTHOR}`), and related articles.

## Recommendations

- **Use Meta Fields**:

  - Always fill `page_metadesc`, `page_keywords`, and `page_metatitle` in the page editor for precise SEO control.
  - Use `page_link_main_image` for better social media previews.


- **Stop Words**:

  - Extend stop words in `/plugins/seoarticle/lang/seoarticle.ru.lang.php` for better keyword extraction:

    ```php
    $L['seoarticle_stop_words'] = 'и,в,на,с,по,для,это,как,что,а,у,к,от,до,при,о,без,если';
    ```

- **Database Optimization**:

  - Ensure indexes on `cot_pages` (`page_id`, `page_ownerid`, `page_cat`) and `cot_users` (`user_id`) for faster queries.

- **Testing**:

  - Use browser developer tools to verify meta tags and structured data.
  - Test related articles by creating multiple pages in the same category.

## Warnings

- **Template Conflicts**:

  - Ensure `{HEADER_META_DESCRIPTION}` and `{HEADER_META_KEYWORDS}` are not duplicated in `header.tpl` to avoid multiple meta tags.
  - Check `<head>` for duplicate `<meta name="description">` or `<meta name="keywords">`.

- **Extra Fields**:

  - If `page_link_main_image` is not configured, the plugin falls back to `/images/default.jpg`. Set a valid default image in `seoarticle.page.main.php` if needed.

- **Performance**:

  - Keyword extraction (`cot_extract_keywords`) may be slow for very long `page_text`. Limit text length or cache results if necessary.

- **Language Support**:

  - The plugin includes only Russian translations (`seoarticle.ru.lang.php`). Add other languages (e.g., `seoarticle.en.lang.php`) for multilingual sites.

- **Dependencies**:

  - Requires the Pages module. Ensure it is enabled before installation.
  - Database queries assume `cot_users` and `cot_structure` tables are intact.

## License

BSD License. See LICENSE for details.

## Author

webitproff 22 April 2025

## See also:

1.  **[Userarticles](https://github.com/webitproff/cot-userarticles) for CMF Cotonti**
   The plugin for CMF Cotonti displays a list of users with the number of their articles and a detailed list of articles for each user.

2. **[Export to Excel via PhpSpreadsheet](https://github.com/webitproff/cot-excel_export) for CMF Cotonti**
   Exporting Articles to Excel from the Database in Cotonti via PhpSpreadsheet.Composer is not required for installation.
   
3. **[Import from Excel via PhpSpreadsheet](https://github.com/webitproff/cot-excelimport-PhpSpreadsheet_No-Composer) for CMF Cotonti**
  The plugin for importing articles from Excel for all Cotonti-based websites.Composer is not required for installation.
   
4. **[The CleanCot theme for CMF Cotonti](https://github.com/webitproff/cot-CleanCot)**
   Modern Bootstrap theme on v.5.3.3 for Cotonti Siena v.0.9.26 without outdated (legacy) mode. Only relevant tags!

   
#############
# Русский
#############

# Плагин SeoArticle для Cotonti

Плагин **SeoArticle** расширяет SEO-возможности модуля Pages в Cotonti CMF, добавляя мета-теги, Open Graph, Twitter Card, структурированные данные Schema.org, извлечение ключевых слов, оценку времени чтения и функционал связанных статей.

## Протестировано
Cotonti CMF v.0.9.26 beta на PHP v.8.2.25

## Структура файлов

```
plugins/seoarticle/
├── inc/
│   └── seoarticle.functions.php       # Функции для извлечения ключевых слов и оценки времени чтения
├── lang/
│   └── seoarticle.ru.lang.php         # Русские переводы и стоп-слова для извлечения ключевых слов
│   └── seoarticle.en.lang.php         # Английские переводы и стоп-слова для извлечения ключевых слов
├── seoarticle.header.tags.php         # Переопределяет HEADER_META_DESCRIPTION и HEADER_META_KEYWORDS
├── seoarticle.page.main.php           # Добавляет мета-теги Open Graph, Twitter Card и Schema.org
├── seoarticle.page.tags.php           # Определяет теги страницы (например, PAGE_READ_TIME, PAGE_AUTHOR, RELATED_ROW_*)
└── seoarticle.setup.php               # Конфигурация и установка плагина
```

### Описание файлов

- **seoarticle.setup.php**: Определяет метаданные плагина (название, версия, автор) и зависимости.
- **seoarticle.header.tags.php**: Переопределяет `{HEADER_META_DESCRIPTION}` и `{HEADER_META_KEYWORDS}` для мета-тегов, обеспечивая описание в одну строку и извлечение ключевых слов.
- **seoarticle.page.main.php**: Генерирует мета-теги Open Graph, Twitter Card и Schema.org, включая заголовок, описание, ключевые слова, автора и изображение.
- **seoarticle.functions.php**: Содержит `cot_extract_keywords` (извлекает ключевые слова с фильтрацией стоп-слов) и `cot_estimate_read_time` (оценивает время чтения, обрабатывает HTML/BBCode).
- **seoarticle.ru.lang.php**: Предоставляет русские переводы для меток (например, «Связанные статьи») и стоп-слова для извлечения ключевых слов.
- **seoarticle.en.lang.php**: Предоставляет английские переводы для меток (например, «Related articles») и стоп-слова для извлечения ключевых слов.
- **seoarticle.page.tags.php**: Добавляет теги, такие как `{PAGE_READ_TIME}`, `{PAGE_AUTHOR}` и `{RELATED_ROW_*}` для связанных статей (см. seoarticle.page.tags.php).

## Возможности

- **Мета-теги**:
  - Генерирует `<meta name="description">` и `<meta name="keywords">` на основе `page_metadesc`, `page_keywords` или извлечённых из `page_text`.
  - Обеспечивает описание в одну строку без переносов.
- **Open Graph и Twitter Card**:
  - Добавляет `og:title`, `og:description`, `og:image`, `twitter:card` и др. для шаринга в соцсетях.
- **Schema.org**:
  - Включает структурированные данные для статей (`headline`, `description`, `keywords`, `author`, `image`).
- **Извлечение ключевых слов**:
  - Автоматически извлекает ключевые слова из `page_text` с помощью `cot_extract_keywords`, если `page_keywords` пусто.
  - Поддерживает стоп-слова (например, «и, в, на»), определённые в `seoarticle.ru.lang.php` или `seoarticle.en.lang.php`.
- **Оценка времени чтения**:
  - Рассчитывает время чтения с помощью `cot_estimate_read_time` (200 слов/минута, минимум 1 минута).
  - Обрабатывает HTML и BBCode в `page_text`.
- **Связанные статьи**:
  - Отображает связанные статьи с заголовком, описанием и изображением (`page_link_main_image`).
- **Информация об авторе**:
  - Показывает имя автора из `cot_users` или «Неизвестный автор», если автор не найден.

## Установка

### Требования

- Cotonti CMF (протестировано на Siena).
- Модуль Pages включён.
- База данных MySQL/MariaDB.

### Шаги

1. **Скачайте плагин**:

   - Клонируйте или скачайте репозиторий с GitHub:

     ```bash
     git clone https://github.com/webitproff/seoarticle.git
     ```
   - Или скачайте ZIP и распакуйте в `/plugins/seoarticle/`.

2. **Скопируйте файлы**:

   - Поместите папку `seoarticle` в `/plugins/` вашей установки Cotonti:

     ```
     public_html/plugins/seoarticle/
     ```

3. **Установите плагин**:

   - Перейдите в **Администрирование → Расширения** в админ-панели Cotonti.
   - Найдите **SeoArticle** в списке и нажмите **Установить**.
   - Плагин зарегистрируется с помощью `seoarticle.setup.php`.

4. **Настройте дополнительные поля**:

   - Перейдите в **Администрирование → Структура → Страницы → Дополнительные поля**.
   - Добавьте дополнительное поле для изображений:
     - **Код**: `link_main_image`
     - **Тип**: URL
     - **Описание**: Главное изображение для Open Graph и связанных статей
   - Убедитесь, что поля `page_metadesc`, `page_metatitle` и `page_keywords` доступны (по умолчанию в Cotonti).

5. **Обновите шаблоны**:

   - Откройте `/themes/cleancot/header.tpl` и убедитесь, что он содержит:

     ```html
     <meta name="description" content="{HEADER_META_DESCRIPTION}" />
     <meta name="keywords" content="{HEADER_META_KEYWORDS}" />
     ```
также добавьте код, если его нет в шаблоне header.tpl:
     ```
     <!-- IF {PHP.out.meta} -->{PHP.out.meta}<!-- ENDIF -->
     ```
     
   - Откройте `/themes/cleancot/page.tpl` ([скачатть тему](https://github.com/webitproff/cot-CleanCot)) и добавьте:

     ```html
				<!-- IF {PHP|cot_plugin_active('seoarticle')} -->
				<!-- IF {RELATED_PAGES} -->
				<div class="related">
					<!-- BEGIN: MAIN.RELATED_PAGES.RELATED_ROW -->
					<a href="{RELATED_ROW_URL}">
						<div class="position-relative overflow-hidden rounded-5 shadow-bottom" style="aspect-ratio: 2 / 1;">
							<!-- Условие Cotonti: проверка наличия главного изображения через экстраполяцию -->
							<!-- IF {RELATED_ROW_LINK_MAIN_IMAGE} -->
								<!-- Главное изображение страницы из Cotonti, адаптивное с обрезкой -->
								<img src="{RELATED_ROW_LINK_MAIN_IMAGE}" alt="{RELATED_ROW_TITLE}" class="img-fluid object-fit-cover">
							<!-- Альтернатива: если главного изображения нет -->
							<!-- ELSE -->
								<!-- Дефолтное изображение из темы Cotonti -->
								<img src="{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/img/cotonti-cleancot.webp" alt="{PAGE_TITLE}" class="img-fluid object-fit-cover">
							<!-- Конец условия изображения -->
							<!-- ENDIF -->
						</div>
						<h3 class="h5 mb-0">{RELATED_ROW_TITLE}</h3>
						<p>{RELATED_ROW_DESC}</p>
					</a>
					<!-- END: MAIN.RELATED_PAGES.RELATED_ROW -->
				</div>
				<!-- ENDIF -->
				<!-- ENDIF -->
  
     ```
     и в тело статьи:
```
<!-- IF {PHP|cot_plugin_active('seoarticle')} -->
  {PAGE_READ_TIME}
<!-- ENDIF -->
```

6. **Очистите кэш**:

   - Перейдите в **Администрирование → Кэш → Очистить кэш**.

7. **Протестируйте плагин**:

   - Создайте или отредактируйте страницу в **Администрирование → Страницы**.
   - Заполните `page_metadesc`, `page_keywords`, `page_metatitle` и `page_link_main_image` (опционально).
   - Просмотрите страницу и проверьте:
     - `<meta name="description">` и `<meta name="keywords">` в `<head>`.
     - Теги Open Graph и Schema.org.
     - Время чтения (`{PAGE_READ_TIME}`), автора (`{PAGE_AUTHOR}`) и связанные статьи.

## Рекомендации

- **Используйте мета-поля**:

  - Всегда заполняйте `page_metadesc`, `page_keywords` и `page_metatitle` в редакторе страниц для точного SEO-контроля.
  - Используйте `page_link_main_image` для улучшения превью в соцсетях.

- **Стоп-слова**:

  - Расширьте стоп-слова в `/plugins/seoarticle/lang/seoarticle.ru.lang.php` для улучшения извлечения ключевых слов:

    ```php
    $L['seoarticle_stop_words'] = 'и,в,на,с,по,для,это,как,что,а,у,к,от,до,при,о,без,если';
    ```

- **Оптимизация базы данных**:

  - Убедитесь, что индексы на `cot_pages` (`page_id`, `page_ownerid`, `page_cat`) и `cot_users` (`user_id`) созданы для ускорения запросов.

- **Тестирование**:

  - Используйте инструменты разработчика браузера для проверки мета-тегов и структурированных данных.
  - Протестируйте связанные статьи, создав несколько страниц в одной категории.

## Предупреждения

- **Конфликты шаблонов**:

  - Убедитесь, что `{HEADER_META_DESCRIPTION}` и `{HEADER_META_KEYWORDS}` не дублируются в `header.tpl`, чтобы избежать множественных мета-тегов.
  - Проверьте `<head>` на наличие дублирующихся `<meta name="description">` или `<meta name="keywords">`.

- **Дополнительные поля**:

  - Если `page_link_main_image` не настроено, плагин использует `/images/default.jpg`. Укажите действительное изображение по умолчанию в `seoarticle.page.main.php`, если нужно.

- **Производительность**:

  - Извлечение ключевых слов (`cot_extract_keywords`) может быть медленным для длинных `page_text`. Ограничьте длину текста или кэшируйте результаты при необходимости.

- **Поддержка языков**:

  - Плагин включает переводы на русский (`seoarticle.ru.lang.php`) и английский (`seoarticle.en.lang.php`). Добавьте другие языки для мультиязычных сайтов.

- **Зависимости**:

  - Требуется модуль Pages. Убедитесь, что он включён перед установкой.
  - Запросы к базе данных предполагают наличие таблиц `cot_users` и `cot_structure`.

## Лицензия

BSD License. См. LICENSE для деталей.

## Автор

webitproff, 22 апреля 2025
