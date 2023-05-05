# (MODX)EvolutionCMS.snippets.ddMenuBuilder

Simple and flexible template-driven menu builder.
Initially inspired by combination of the Wayfinder and Ditto advantages with significant code simplification.


## Requires

* PHP >= 5.6
* [(MODX)EvolutionCMS](https://github.com/evolution-cms/evolution) >= 1.1
* [(MODX)EvolutionCMS.libraries.ddTools](https://code.divandesign.ru/modx/ddtools) >= 0.59


## Installation


### 1. Elements → Snippets: Create a new snippet with the following data

1. Snippet name: `ddMenuBuilder`.
2. Description: `<b>2.1.1</b> Simple and flexible template-driven menu builder.`.
3. Category: `Core → Navigation`.
4. Parse DocBlock: `no`.
5. Snippet code (php): Insert content of the `ddMenuBuilder_snippet.php` file from the archive.


### 2. Elements → Manage Files:

1. Create a new folder `assets/snippets/ddMenuBuilder/`.
2. Extract the archive to the folder (except `ddMenuBuilder_snippet.php`).


## Parameters description


### Data provider parameters

Providers get documents data to output.

* `provider`
	* Desctription: Name of the provider that will be used to fetch documents.
	* Valid values:
		* `'parent'`
		* `'select'`
	* Default value: `'parent'`
	
* `providerParams`
	* Desctription: Parameters to be passed to the provider.
	* Valid values:
		* `stringJsonObject` — as [JSON](https://en.wikipedia.org/wiki/JSON)
		* `stringHjsonObject` — as [HJSON](https://hjson.github.io/)
		* `stringQueryFormatted` — as [Query string](https://en.wikipedia.org/wiki/Query_string)
		* It can also be set as native PHP object or array (e. g. for calls through `\DDTools\Snippet::runSnippet` or `$modx->runSnippet`):
			* `arrayAssociative`
			* `object`
	* Default value: —


#### Providers → Parent (``&provider=`parent` ``)

Select children documents from required parent(s).

* `providerParams->parentIds`
	* Desctription: Parent IDs — the starting points for the menu. Specify '0' to start from the site root.
	* Valid values:
		* `array`
		* `stringCommaSepareted`
	* Default value: `'0'`
	
* `providerParams->parentIds[i]`
	* Desctription: Parent ID.
	* Valid values: `integerDocumentID`
	* **Required**
	
* `providerParams->depth`
	* Desctription: The depth of documents to build the menu.
	* Valid values: `integer`
	* Default value: `1`


#### Providers → Select (``&provider=`select` ``)

Just output selected documents.

* `providerParams->ids`
	* Desctription: Document IDs.
	* Valid values:
		* `array`
		* `stringCommaSepareted`
	* **Required**
	
* `providerParams->ids[i]`
	* Desctription: Document IDs.
	* Valid values: `integerDocumentID`
	* **Required**


### General parameters

* `sortDir`
	* Desctription: The sorting direction (by `menuindex` field).
	* Valid values:
		* `'ASC'`
		* `'DESC'`
	* Default value: `'ASC'`
	
* `showPublishedOnly`
	* Desctription: Show only published documents.
	* Valid values:
		* `0`
		* `1`
	* Default value: `1`
	
* `showInMenuOnly`
	* Desctription: Show only documents visible in the menu.
	* Valid values:
		* `0`
		* `1`
	* Default value: `1`


### Template parameters

* `templates`
	* Desctription: Templates.  
		Placeholders available in all templates:
		* `[+id+]`
		* `[+menutitle+]` — will be equal to `[+pagetitle+]` if empty.
		* `[+pagetitle+]`
		* `[+published+]`
		* `[+isfolder+]`
		* `[+totalAllChildren+]`
		* `[+totalThisLevelChildren+]`
		* `[+level+]`
	* Valid values:
		* `stringJsonObject` — as [JSON](https://en.wikipedia.org/wiki/JSON)
		* `stringHjsonObject` — as [HJSON](https://hjson.github.io/)
		* `stringQueryFormatted` — as [Query string](https://en.wikipedia.org/wiki/Query_string)
		* It can also be set as native PHP object or array (e. g. for calls through `\DDTools\Snippet::runSnippet` or `$modx->runSnippet`):
			* `arrayAssociative`
			* `object`
	* Default value: —


#### Item templates

* `templates->item`
	* Desctription: The menu item template.
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
	* Default value: `'@CODE:<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'`
	
* `templates->itemHere`
	* Desctription: The menu item template for the current document.
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
	* Default value: `'@CODE:<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'`
	
* `templates->itemActive`
	* Desctription: The menu item template for a document which is one of the parents to the current document when the current document doesn't displayed in the menu (e. g. excluded by the `depth` parameter).
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
	* Default value: = `templates->itemHere`
	
* `templates->itemUnpub`
	* Desctription: The menu item template for unpublished document (when `showPublishedOnly` == `0`).
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
	* Default value: = `templates->item`
	
* `templates->itemUnpubActive`
	* Desctription: The menu item template for unpublished document which is one of the parents to the current document when the current document doesn't displayed in the menu (e. g. excluded by the `depth` parameter).
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
	* Default value: = `templates->itemActive`


#### Parent item templates

* `templates->itemParent`
	* Desctription: The menu item template for documents which has a children displayed in menu.
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
	* Default value: `'@CODE:<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>'`
	
* `templates->itemParentHere`
	* Desctription: The menu item template for the current document when it has children displayed in menu.
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
	* Default value: `'@CODE:<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>'`
	
* `templates->itemParentActive`
	* Desctription: The menu item template for a document which has the current document as one of the children.
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
	* Default value: = `templates->itemParentHere`
	
* `templates->itemParentUnpub`
	* Desctription: The menu item template for unpublished documents which has a children displayed in menu.
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
	* Default value: = `templates->itemParent`
	
* `templates->itemParentUnpubActive`
	* Desctription: The menu item template for an unpublished document which has the current document as one of the children.
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
	* Default value: = `templates->itemParentActive`


#### Outer template

* `templates->outer`
	* Desctription: Wrapper template.  
		Available placeholders:
		* `[+children+]` — Generated HTML with all items.
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
	* Default value: `'@CODE:<ul>[+children+]</ul>'`
	
* `placeholders`
	* Desctription:
		Additional data has to be passed into the `templates->outer`.  
		Arrays are supported too: `some[a]=one&some[b]=two` => `[+some.a+]`, `[+some.b+]`; `some[]=one&some[]=two` => `[+some.0+]`, `[some.1]`.
	* Valid values:
		* `stringJsonObject` — as [JSON](https://en.wikipedia.org/wiki/JSON)
		* `stringHjsonObject` — as [HJSON](https://hjson.github.io/)
		* `stringQueryFormatted` — as [Query string](https://en.wikipedia.org/wiki/Query_string)
		* It can also be set as native PHP object or array (e. g. for calls through `\DDTools\Snippet::runSnippet` or `$modx->runSnippet`):
			* `arrayAssociative`
			* `object`
	* Default value: —


## Examples


### Providers → Parent

```html
[[ddMenuBuilder?
	&provider=`parent`
	&providerParams=`{
		"parentId": 1,
		"depth": 2
	}`
]]
```


### Providers → Select

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


### Pass additional data into outer chunk (the `placeholders` parameter)

```html
[[ddMenuBuilder?
	&templates=`
		"outer": "@CODE:<ul class=\"[+class+]\">[+children+]</ul>[+somePlaceholder2+]"
	`
	&placeholders=`{
		"class": "someClass",
		"somePlaceholder2": "<p>Some value for placeholder.</p>"
	}`
]]
```


### Using Query string instead of JSON

JSON syntax is more clear than Query string, but sometimes it's not convenient. For example, if you want to pass JSON string as string.

```html
[[ddMenuBuilder?
	&provider=`parent`
	&providerParams=`parentId=1&depth=2`
	&templates=`outer=general_nav`
	&placeholders=`pladeholder1={"someName": "someValue"}&pladeholder2={"name": "John"}`
]]
```


## Links

* [Home page](http://code.divandesign.ru/modx/ddmenubuilder)
* [Telegram chat](https://t.me/dd_code)
* [Packagist](https://packagist.org/packages/dd/evolutioncms-snippets-ddmenubuilder)
* [GitHub](https://github.com/DivanDesign/EvolutionCMS.snippets.ddMenuBuilder)


<link rel="stylesheet" type="text/css" href="https://raw.githack.com/DivanDesign/CSS.ddMarkdown/master/style.min.css" />