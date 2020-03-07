# (MODX)EvolutionCMS.snippets.ddMenuBuilder changelog


## Version 2.1 (2020-03-07)
* \+ Snippet: All templates has the following placeholders:
	* \+ `[+totalAllChildren+]` — total number of displayed children at all levels.
	* \+ `[+totalThisLevelChildren+]` — total number of displayed immediate children.
	* \+ `[+level+]` — item level in menu.
* \* `\ddMenuBuilder`
	* \+ `\ddMenuBuilder::generate`:
		* \+ Return the following 2 counters:
			* \+ `$result['totalAll']` — total number of items displayed at all levels.
			* \+ `$result['totalThisLevel']` — total number of items displayed at this level.
		* \+ Added the parameter `$params->level` for internal use only.
		* \* Returns `stdClass` instead of `arrayAssociative`.
	* \* `\ddMenuBuilder::prepareProviderParams`:
		* \+ The `$params->providerParams` parameter can be set as `stdClass` too.
		* \* Returns `stdClass` instead of `arrayAssociative`.
	* \* `\ddMenuBuilder::$templates`: Now it's `stdClass`.
* \+ Composer.json.
* \+ CHANGELOG: Small improvements.
* \+ README:
	* \+ Requires.
	* \+ Documentation → Installation.


## Version 2.0 (2019-06-13)
* \* Attention! Backward compatibility is broken!
* \* Attention! (MODX)EvolutionCMS.libraries.ddTools >= 0.24.1 is required.
* \* Template parameters refactoring.
* \+ Added templates for unpublished items:
	* \+ `$templates['itemUnpub']` — The menu item template for unpublished document. Default: `$templates['item']`.
	* \+ `$templates['itemUnpubActive']` — The menu item template for unpublished document which is one of the parents to the current document when the current document doesn't displayed in the menu (e. g. excluded by the `depth` parameter). Default: `$templates['itemActive']`.
* \* `\ddTools::$modx` is used instead of global `$modx`.
* \* Fixed an error when docs that must be hidden will be showed.
* \* Refactoring, other small changes.


## Version 1.13b (2018-10-17)
* \* Attention! PHP >= 5.6 is required.
* \* Snippet:
	* \* Wrong type of `providerParams` was fixed.
* \* `\ddMenuBuilder`:
	* \* Small refactoring.
	* \* Optimization:
		* \- `\ddMenuBuilder::generate`: Redudnand `array_merge` removed,
		* \- `\ddMenuBuilder::generate`: Убран проход по всем документам в дереве который определял где находится активный документ.


## Version 1.12 (2017-08-30)
* \* Attention! (MODX)EvolutionCMS.libraries.ddTools >= 0.20 is required.
* \* Menu item active status is no logner depends on the `show_in_menu` children flag.
* \+ Added JSON format support for the `providerParams` and `placeholders` parameters.


## Version 1.11 (2016-11-25)
* \* Attention! PHP >= 5.4 is required.
* \* Attention! (MODX)EvolutionCMS.libraries.ddTools >= 0.16.1 is required.
* \+ Added an ability to pass ids of the selected documents to output.
* \* Short array syntax is used because it's more convenient.
* \* `\ddMenuBuilder`:
	* \* Unpublished docs will be used if needed.
	* \* `\ddMenuBuilder::generate`:
		* \* Now takes custom `where` clauses instead of parent id.
		* \* Refactoring parameters style.
* \* Other minor changes.


## Version 1.10 (2016-09-12)
* \* Attention! MODXEvo >= 1.1 is required.
* \+ Added an ability to pass additional data into a `tpls_outer` template (see the `placeholders` parameter).
* \+ Added support of `@CODE:` keyword prefix in the snippet templates.


## Version 1.9 (2015-12-28)
* \* Attention! (MODX)EvolutionCMS.libraries.ddTools >= 0.15 is required.
* \* Snippet:
	* \* Вместо прямого обращения к полю `$modx->config` используется метод `$modx->getConfig`.
	* \* Следующие параметры были переименованы (старые имена поддерживаются, но не рекомендуются к использованию):
		* \* `tplRow` → `tpls_item`.
		* \* `tplHere` → `tpls_itemHere`.
		* \* `tplActive` → `tpls_itemActive`.
		* \* `tplParentRow` → `tpls_itemParent`.
		* \* `tplParentHere` → `tpls_itemParentHere`.
		* \* `tplParentActive` → `tpls_itemParentActive`.
		* \* `tplUnpubParentRow` → `tpls_itemParentUnpub`.
		* \* `tplUnpubParentActive` → `tpls_itemParentUnpubActive`.
		* \* `tplWrap` → `tpls_outer`.
	* \* Параметр `tpls_itemParentHere` по умолчанию равен `<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>` (значение по умолчанию больше не зависит от параметра `tpls_itemParent`). Решение неоднозначное, подумать.
* \* `\ddMenuBuilder` обновлён до 2.0:
	* \* Теперь это обычный объект, поля и методы не статические.
	* \* Публичный только метод `generate`, остальные поля и методы приватные.
	* \- Удалено поле `$table`, вместо него используется `\ddTools::$tables['site_content']`.
	* \* Поле `\ddMenuBuilder::$id` переименовано в `\ddMenuBuilder::$hereDocId`.
	* \+ Добавлены значения по умолчанию для полей `sortDir` и `where`.
	* \+ Значения шаблонов по умолчанию хранятся в поле `\ddMenuBuilder::$templates`.
	* \* Переименованы шаблоны.
	* \+ Добавлен конструктор.
	* \* Обработка параметров `showPublishedOnly`, `showInMenuOnly` и формирование SQL-условия вынесены из сниппета в конструктор класса `\ddMenuBuilder`.
	* \* Обработка значений шаблонов по умолчанию вынесена из сниппета в конструктор класса `\ddMenuBuilder`.
	* \* Подключение библиотеки `modx.ddTools` вынесено в конструктор.
	* \* Вместо прямого обращения к полю `$modx->config` используется метод `$modx->getConfig`.
	* \* Файл `assets/snippets/ddMenuBuilder/ddmenubuilder.class.php` переименован в `assets/snippets/ddMenuBuilder/ddMenuBuilder.class.php`.


## Version 1.8 (2015-02-05)
* \* Snippet:
	* \* Плэйсхолдер `[+wrapper+]` во всех шаблонах заменён на `[+children+]`.
* \* `\ddMenuBuilder`:
	* \+ Добавлен метод `\ddMenuBuilder::getOutputTemplate`.
	* \* Метод `\ddMenuBuilder::generate`:
		* \* Переменная `$children` должна быть определена.
		* \* Для проверки наличия дочерних документов используется `empty` вместо простого логического значения (т.к. пустой массив также означает отсутствие детей).
		* \* Рефакторинг:
			* \* Один `return` вместо нескольких.
			* \* Переменная `$tpl` объявляется в любом случае.
			* \* Элемент массива `str` объявляется в самом начале, таким образом, он всегда существует.
			* \* Код определения шаблона для вывода документа вынесен в отдельный метод.
			* \* Обработка пустого `menutitle` документа делается только если документ будет выводиться.
			* \* Определение «активности» текущего документа объеденено в одно условие и перенесено после парсинга.
		* \* Парсинг текущего пункта меню делается только если шаблон определён (если не определён, значит выводить не надо).
		* \* Всегда возвращает массив.
		* \* Поля результирующего массива переименованы:
			* \* `act` → `hasActive`.
			* \* `str` → `outputString`.
		* \* В массиве документа поле `wrapper` переименовано в `children`.
		* \* В результирующем массиве в любом случае будут поля `hasActive` и `outputString`.
		* \* Переменная `$doc` в любом случае будет содержать поле `children` с массивом дочерних документов, в случае если их нет или не нужно выводить, `$doc['children']['outputString']` будет равняться пустой строке.
		* \* Определение шаблона и прочие операции, связанные с выводом, производятся только если в этом есть смысл.
		* \- Удалена переменная `$sql`.
* \* Удалены устаревшие комментарии, исправлено оформление кода и прочие незначительные изменения.


## Version 1.7 (2012-10-17)
* \+ The first release.


<style>ul{list-style:none;}</style>