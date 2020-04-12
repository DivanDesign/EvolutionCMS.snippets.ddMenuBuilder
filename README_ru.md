# (MODX)EvolutionCMS.snippets.ddMenuBuilder

Простой и гибкий сборщик меню, построенный на шаблонах.
Иначальная идея появилась, как комбинация преимуществ Wayfinder и Ditto с существенным упрощением кода.


## Requires

* PHP >= 5.6
* [(MODX)EvolutionCMS](https://github.com/evolution-cms/evolution) >= 1.1
* [(MODX)EvolutionCMS.libraries.ddTools](https://code.divandesign.biz/modx/ddtools) >= 0.24.1


## Документация


### Установка


#### 1. Элементы → Сниппеты: Создать новый сниппет со следующими параметрами:

1. Название сниппета: `ddMenuBuilder`.
2. Описание: `<b>2.1</b> Simple and flexible template-driven menu builder.`.
3. Категория: `Core → Navigation`.
4. Анализировать DocBlock: `no`.
5. Код сниппета (php): Вставьте содержимое файла `ddMenuBuilder_snippet.php` из архива.


#### 2. Элементы → Управление файлами:

1. Создайте папку `assets/snippets/ddMenuBuilder/`.
2. Извлеките содержимое архива в неё (за исключением файла `ddMenuBuilder_snippet.php`).


### Описание параметров


#### Параметры провайдера данных

Провайдеры получают данные документов для вывода.

* `provider`
	* Описание: Название провайдера, используемого для получения документов.
	* Допустимые значения:
		* `'parent'`
		* `'select'`
	* Значение по умолчанию: `'parent'`
	
* `providerParams`
	* Описание: Параметры провайдера.
	* Допустимые значения:
		* `stringJsonObject` — as [JSON](https://en.wikipedia.org/wiki/JSON)
		* `stringQueryFormated` — as [Query string](https://en.wikipedia.org/wiki/Query_string)
	* Значение по умолчанию: —


##### Провайдеры → Parent (``&provider=`parent` ``)

Находит необходимые дочерние документы заданных родителей.

* `providerParams->parentIds`
	* Описание: ID документов-родителей, в которых надо искать документы для вывода.
	* Допустимые значения:
		* `array`
		* `stringCommaSepareted`
	* Значение по умолчанию: `'0'`
	
* `providerParams->parentIds[i]`
	* Описание: ID документа-родителя.
	* Допустимые значения: `integerDocumentID`
	* **Required**
	
* `providerParams->depth`
	* Описание:	Глубина поиска дочерних документов.
	* Допустимые значения: `integer`
	* Значение по умолчанию: `1`


##### Провайдеры → Select (``&provider=`select` ``)

Просто выводит заданные документы.

* `providerParams->ids`
	* Описание: ID документов для вывода.
	* Допустимые значения:
		* `array`
		* `stringCommaSepareted`
	* **Required**
	
* `providerParams->ids[i]`
	* Описание: ID документа.
	* Допустимые значения: `integerDocumentID`
	* **Required**


#### Общие параметры

* `sortDir`
	* Описание: Направление сортировки (по полю `menuindex`).
	* Допустимые значения:
		* `'ASC'`
		* `'DESC'`
	* Значение по умолчанию: `'ASC'`
	
* `showPublishedOnly`
	* Описание: Выводить только опубликованные документы?
	* Допустимые значения:
		* `0`
		* `1`
	* Значение по умолчанию: `1`
	
* `showInMenuOnly`
	* Описание: Выводить только документы с галочкой «отображать в меню»?
	* Допустимые значения:
		* `0`
		* `1`
	* Значение по умолчанию: `1`


#### Шаблоны

* `templates`
	* Описание: Шаблоны.  
		Плейсхолдеры, доступные во всех шаблонах:
		* `[+id+]`
		* `[+menutitle+]` — если поле документа не заполнено, в плейсхолдер посдтавится значение `[+pagetitle+]`.
		* `[+pagetitle+]`
		* `[+published+]`
		* `[+isfolder+]`
		* `[+totalAllChildren+]`
		* `[+totalThisLevelChildren+]`
		* `[+level+]`
	* Допустимые значения:
		* `stringJsonObject` — as [JSON](https://en.wikipedia.org/wiki/JSON)
		* `stringQueryFormated` — as [Query string](https://en.wikipedia.org/wiki/Query_string)
	* Значение по умолчанию: —


##### Шаблоны пунктов меню

* `templates->item`
	* Описание: Шаблон пункта меню.
	* Допустимые значения:
		* `stringChunkName`
		* `string` — вместо чанка можно сразу передавать код, используя префикс `@CODE:`
	* Значение по умолчанию: `'@CODE:<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'`
	
* `templates->itemHere`
	* Описание: Шаблон пункта меню текущего документа (когда пользователь на этой странице).
	* Допустимые значения:
		* `stringChunkName`
		* `string` — вместо чанка можно сразу передавать код, используя префикс `@CODE:`
	* Значение по умолчанию: `'@CODE:<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'`
	
* `templates->itemActive`
	* Описание: Шаблон пункта меню документа, когда текущий документ (на котором находится пользователь) является дочерним к нему, но не отображается в меню (например, из-за параметра `depth`).
	* Допустимые значения:
		* `stringChunkName`
		* `string` — вместо чанка можно сразу передавать код, используя префикс `@CODE:`
	* Значение по умолчанию: = `templates->itemHere`
	
* `templates->itemUnpub`
	* Описание: Шаблон пункта меню неопубликованного документа (когда `showPublishedOnly` == `0`).
	* Допустимые значения:
		* `stringChunkName`
		* `string` — вместо чанка можно сразу передавать код, используя префикс `@CODE:`
	* Значение по умолчанию: = `templates->item`
	
* `templates->itemUnpubActive`
	* Описание: Шаблон неопубликованного пункта меню документа, когда текущий документ (на котором находится пользователь) является дочерним к нему, но не отображается в меню (например, из-за параметра `depth`).
	* Допустимые значения:
		* `stringChunkName`
		* `string` — вместо чанка можно сразу передавать код, используя префикс `@CODE:`
	* Значение по умолчанию: = `templates->itemActive`


##### Шаблоны пунктов-родителей (содержащих вложенное меню)

* `templates->itemParent`
	* Описание: Шаблон пункта меню, содержащего дочернее меню.
	* Допустимые значения:
		* `stringChunkName`
		* `string` — вместо чанка можно сразу передавать код, используя префикс `@CODE:`
	* Значение по умолчанию: `'@CODE:<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>'`
	
* `templates->itemParentHere`
	* Описание: Шаблон пункта меню текущего документа (когда пользователь на этой странице), содержащего дочернее меню.
	* Допустимые значения:
		* `stringChunkName`
		* `string` — вместо чанка можно сразу передавать код, используя префикс `@CODE:`
	* Значение по умолчанию: `'@CODE:<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>'`
	
* `templates->itemParentActive`
	* Описание: Шаблон пункта меню документа, когда один из дочерних пунктов является текущим. 
	* Допустимые значения:
		* `stringChunkName`
		* `string` — вместо чанка можно сразу передавать код, используя префикс `@CODE:`
	* Значение по умолчанию: = `templates->itemParentHere`
	
* `templates->itemParentUnpub`
	* Описание: Шаблон пункта меню неопубликованного документа, содержащего дочернее меню.
	* Допустимые значения:
		* `stringChunkName`
		* `string` — вместо чанка можно сразу передавать код, используя префикс `@CODE:`
	* Значение по умолчанию: = `templates->itemParent`
	
* `templates->itemParentUnpubActive`
	* Описание: Шаблон пункта меню неопубликованного документа, содержащего дочернее меню, когда один из дочерних пунктов является текущим. .
	* Допустимые значения:
		* `stringChunkName`
		* `string` — вместо чанка можно сразу передавать код, используя префикс `@CODE:`
	* Значение по умолчанию: = `templates->itemParentActive`


##### Шаблон внешней обертки меню

* `templates->outer`
	* Описание: Шаблон внешней обертки меню.  
		Доступные плейсхолдеры:
		* `[+children+]` — Сгенерированный код всех пунктов меню.
	* Допустимые значения:
		* `stringChunkName`
		* `string` — вместо чанка можно сразу передавать код, используя префикс `@CODE:`
	* Значение по умолчанию: `'@CODE:<ul>[+children+]</ul>'`
	
* `placeholders`
	* Описание:
		Дополнительные данные, которые необходимо передать в `templates->outer`.  
		Массивы также поддерживаются: `some[a]=one&some[b]=two` => `[+some.a+]`, `[+some.b+]`; `some[]=one&some[]=two` => `[+some.0+]`, `[some.1]`.
	* Допустимые значения:
		* `stringJsonObject` — в виде [JSON](https://en.wikipedia.org/wiki/JSON) объекта
		* `stringQueryFormated` — в виде [Query string](https://en.wikipedia.org/wiki/Query_string)
	* Значение по умолчанию: —


### Примеры


#### Провайдеры → Parent

```html
[[ddMenuBuilder?
	&provider=`parent`
	&providerParams=`{
		"parentId": 1,
		"depth": 2
	}`
]]
```


#### Провайдеры → Select

```html
[[ddMenuBuilder?
	&provider=`select`
	&providerParams=`{
		"ids": [
			1,
			2,
			3
		]
	}`
]]
```


#### Передача дополнительных данных в шаблон внешней обёртки (параметр `placeholders`)

```html
[[ddMenuBuilder?
	&templates=`
		"outer": "@CODE:<ul class="[+class+]">[+children+]</ul>[+somePlaceholder2+]"
	`
	&placeholders=`{
		"class": "someClass",
		"somePlaceholder2": "<p>Some value for placeholder.</p>"
	}`
]]
```


#### Использование Query string вместо JSON

Синтаксис JSON более нагляден, чем Query string, но иногда не очень удобен. Например, когда вы хотите передать JSON строку, как строку, чтобы она не парсилась, как объект.

```html
[[ddMenuBuilder?
	&provider=`parent`
	&providerParams=`parentId=1&depth=2`
	&templates=`outer=general_nav`
	&placeholders=`pladeholder1={"someName": "someValue"}&pladeholder2={"name": "John"}`
]]
```


## [Home page →](http://code.divandesign.biz/modx/ddmenubuilder)