<?php
/**
 * ddMenuBuilder
 * @version 2.0 (2019-06-13)
 * 
 * @see README.md
 * 
 * Data provider parameters:
 * @param $provider {'parent'|'select'} — Name of the provider that will be used to fetch documents. Default: 'parent'.
 * @param $providerParams {stirngJsonObject|stringQueryFormated} — Parameters to be passed to the provider. The parameter must be set as JSON (https://en.wikipedia.org/wiki/JSON) or Query string (https://en.wikipedia.org/wiki/Query_string).
 * When $provider == 'parent' =>
 * @param $providerParams['parentIds'] {array|stringCommaSepareted} — Parent IDs — the starting points for the menu. Specify '0' to start from the site root. Default: '0'.
 * @param $providerParams['parentIds'][i] {integerDocumentID} — Parent ID. @required
 * @param $providerParams['depth'] {integer} —  The depth of documents to build the menu. Default: 1.
 * @example &providerParams=`{"parentId": 1, "depth": 2}`.
 * @example &providerParams=`parentId=1&depth=2`.
 * When $provider == 'select' =>
 * @param $providerParams['ids'] {array|stringCommaSepareted} — Document IDs. @required
 * @param $providerParams['ids'][i] {integerDocumentID} — Document ID. @required
 * @example &providerParams=`{"ids": [1, 2, 3]}`.
 * @example &providerParams=`ids=1,2,3`.
 * 
 * General parameters:
 * @param $sortDir {'ASC'|'DESC'} — The sorting direction (by “menuindex” field). Default: 'ASC'.
 * @param $showPublishedOnly {0|1} — Show only published documents. Default: 1.
 * @param $showInMenuOnly {0|1} — Show only documents visible in the menu. Default: 1.
 * 
 * Template parameters:
 * @param $templates {stirngJsonObject|stringQueryFormated} — Templates. All templates can be set as chunk name or code via “@CODE:” prefix. Placeholders available in all templates: [+id+], [+menutitle+] (will be equal to [+pagetitle+] if empty), [+pagetitle+], [+published+], [+isfolder+], [+totalAllChildren+], [+totalThisLevelChildren+], [+level+].
 * @param $templates['item'] {stringChunkName|string} — The menu item template. Default: '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'.
 * @param $templates['itemHere'] {stringChunkName|string} — The menu item template for the current document. Default: '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'.
 * @param $templates['itemActive'] {stringChunkName|string} — The menu item template for a document which is one of the parents to the current document when the current document doesn't displayed in the menu (e. g. excluded by the “depth” parameter). Default: $templates['itemHere'].
 * 
 * @param $templates['itemUnpub'] {stringChunkName|string} — The menu item template for unpublished document. Default: $templates['item'].
 * @param $templates['itemUnpubActive'] {stringChunkName|string} — The menu item template for unpublished document which is one of the parents to the current document when the current document doesn't displayed in the menu (e. g. excluded by the “depth” parameter). Default: $templates['itemActive'].
 * 
 * @param $templates['itemParent'] {stringChunkName|string} — The menu item template for documents which has a children displayed in menu. Default: '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>';.
 * @param $templates['itemParentHere'] {stringChunkName|string} — The menu item template for the current document when it has children displayed in menu. Default: '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>'.
 * @param $templates['itemParentActive'] {stringChunkName|string} — The menu item template for a document which has the current document as one of the children. Default: $templates['itemParentHere'].
 * 
 * @param $templates['itemParentUnpub'] {stringChunkName|string} — The menu item template for unpublished documents which has a children displayed in menu. Default: $templates['itemParent'].
 * @param $templates['itemParentUnpubActive'] {stringChunkName|string} — The menu item template for an unpublished document which has the current document as one of the children. Default: $templates['itemParentActive'].
 * 
 * @param $templates['outer'] {stringChunkName|string} — Wrapper template. Available placeholders: [+children+]. Default: '<ul>[+children+]</ul>'.
 * 
 * @param $placeholders {stirngJsonObject|stringQueryFormated} — Additional data as query string has to be passed into “templates['outer']”. The parameter must be set as JSON (https://en.wikipedia.org/wiki/JSON) or Query string (https://en.wikipedia.org/wiki/Query_string). Default: —.
 * @example &placeholders=`{"pladeholder1": "value1", "pagetitle", "My awesome pagetitle!"}`.
 * @example &placeholders=`pladeholder1=value1&pagetitle=My awesome pagetitle!`.
 * 
 * @link http://code.divandesign.biz/modx/ddmenubuilder
 * 
 * @copyright 2009–2019 DivanDesign {@link http://www.DivanDesign.biz }
 */

//Подключаем класс (ddTools подключится там)
require_once(
	$modx->getConfig('base_path') .
	'assets/snippets/ddMenuBuilder/ddMenuBuilder.class.php'
);

//Prepare template params
$templates = ddTools::encodedStringToArray($templates);

$templates['outer'] =
	isset($templates['outer']) ?
	$modx->getTpl($templates['outer']) :
	'<ul>[+children+]</ul>'
;

$ddMenuBuilder_params = [
	'templates' => $templates
];

//Направление сортировки
if (isset($sortDir)){
	$ddMenuBuilder_params['sortDir'] = $sortDir;
}
//По умолчанию будут только опубликованные документы
if (isset($showPublishedOnly)){
	$ddMenuBuilder_params['showPublishedOnly'] = $showPublishedOnly;
}
//По умолчанию будут только документы, у которых стоит галочка «показывать в меню»
if (isset($showInMenuOnly)){
	$ddMenuBuilder_params['showInMenuOnly'] = $showInMenuOnly;
}

$ddMenuBuilder = new ddMenuBuilder($ddMenuBuilder_params);

//Prepare provider params
$providerParams = ddTools::encodedStringToArray($providerParams);

//Генерируем меню
$result = $ddMenuBuilder->generate($ddMenuBuilder->prepareProviderParams([
	//Parent by default
	'provider' =>
		isset($provider) ?
		$provider :
		'parent'
	,
	'providerParams' => $providerParams
]));

//Данные, которые необоходимо передать в шаблон
if (!empty($placeholders)){
	$placeholders = ddTools::encodedStringToArray($placeholders);
}else{
	$placeholders = [];
}

$placeholders['children'] = $result->outputString;
$placeholders['totalAllChildren'] = $result->totalAll;
$placeholders['totalThisLevelChildren'] = $result->totalThisLevel;

return ddTools::parseText([
	'text' => $templates['outer'],
	'data' => $placeholders
]);
?>