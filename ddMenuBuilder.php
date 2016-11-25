<?php
/**
 * ddMenuBuilder.php
 * @version 1.11 (2016-11-25)
 * 
 * @desc Fresh, simple and flexible template-driven menu builder. Initially inspired by combination of the Wayfinder and Ditto advantages with significant code simplification.
 * 
 * @uses PHP >= 5.4.
 * @uses MODXEvo >= 1.1.
 * @uses MODXEvo.library.ddTools >= 0.16.1.
 * 
 * Data provider parameters:
 * @param $provider {'parent'|'select'} — Name of the provider that will be used to fetch documents. Default: 'parent'.
 * @param $providerParams {string_query} — Parameters to be passed to the provider. The parameter must be set as a query string,
 * When $provider == 'parent' =>
 * @param $providerParams['parentIds'] {array|string_commaSepareted} — Parent IDs — the starting points for the menu. Specify '0' to start from the site root. Default: '0'.
 * @param $providerParams['parentIds'][i] {integer_documentID} — Parent ID. @required
 * @param $providerParams['depth'] {integer} —  The depth of documents to build the menu. Default: 1.
 * e.g. $providerParams = 'parentId=1&depth=2'.
 * When $provider == 'select' =>
 * @param $providerParams['ids'] {array|string_commaSepareted} — Document IDs. @required
 * @param $providerParams['ids'][i] {integer_documentID} — Document ID. @required
 * 
 * General parameters:
 * @param $sortDir {'ASC'|'DESC'} — The sorting direction (by “menuindex” field). Default: 'ASC'.
 * @param $showPublishedOnly {0|1} — Show only published documents. Default: 1.
 * @param $showInMenuOnly {0|1} — Show only documents visible in the menu. Default: 1.
 * 
 * Template parameters:
 * All templates can be set as chunk name or code via “@CODE:” prefix.
 * Placeholders available in all templates: [+id+], [+menutitle+] (will be equal to [+pagetitle+] if empty), [+pagetitle+], [+published+], [+isfolder+].
 * @param $tpls_item {string_chunkName|string} — The menu item template. Default: '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'.
 * @param $tpls_itemHere {string_chunkName|string} — The menu item template for the current document. Default: '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'.
 * @param $tpls_itemActive {string_chunkName|string} — The menu item template for a document which is one of the parents to the current document when the current document doesn't displayed in the menu (e. g. excluded by the “depth” parameter). Default: $tpls_itemHere.
 * 
 * @param $tpls_itemParent {string_chunkName|string} — The menu item template for documents which has a children displayed in menu. Default: '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>';.
 * @param $tpls_itemParentHere {string_chunkName|string} — The menu item template for the current document when it has children displayed in menu. Default: '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>'.
 * @param $tpls_itemParentActive {string_chunkName|string} — The menu item template for a document which has the current document as one of the children. Default: $tpls_itemParentHere.
 * 
 * @param $tpls_itemParentUnpub {string_chunkName|string} — The menu item template for unpublished documents which has a children displayed in menu. Default: $tpls_itemParent.
 * @param $tpls_itemParentUnpubActive {string_chunkName|string} — The menu item template for an unpublished document which has the current document as one of the children. Default: $tpls_itemParentActive.
 * 
 * @param $tpls_outer {string_chunkName|string} — Wrapper template. Available placeholders: [+children+]. Default: '<ul>[+children+]</ul>'.
 * 
 * @param $placeholders {string_query} — Additional data as query string has to be passed into “tpls_outer”. E. g. “pladeholder1=value1&pagetitle=My awesome pagetitle!”. Default: —.
 * 
 * @link http://code.divandesign.biz/modx/ddmenubuilder/1.11
 * 
 * @copyright 2009–2016 DivanDesign {@link http://www.DivanDesign.biz }
 */

//Подключаем класс (ddTools подключится там)
require_once $modx->getConfig('base_path').'assets/snippets/ddMenuBuilder/ddMenuBuilder.class.php';

//Для обратной совместимости
extract(ddTools::verifyRenamedParams($params, [
	'tpls_item' => 'tplRow',
	'tpls_itemHere' => 'tplHere',
	'tpls_itemActive' => 'tplActive',
	'tpls_itemParent' => 'tplParentRow',
	'tpls_itemParentHere' => 'tplParentHere',
	'tpls_itemParentActive' => 'tplParentActive',
	'tpls_itemParentUnpub' => 'tplUnpubParentRow',
	'tpls_itemParentUnpubActive' => 'tplUnpubParentActive',
	'tpls_outer' => 'tplWrap'
]));

//Backward compatibility
if (
	isset($startId) &&
	is_numeric($startId)
){
	//По умолчанию на 1 уровень
	$providerParams = 'parentIds='.$startId.'&depth='.(isset($depth) && is_numeric($depth) ? $depth : 1);
}

$ddMenuBuilder_params = new stdClass();

//Задаём шаблоны
$ddMenuBuilder_params->templates = [];

if (isset($tpls_item)){$ddMenuBuilder_params->templates['item'] = $modx->getTpl($tpls_item);}
if (isset($tpls_itemHere)){$ddMenuBuilder_params->templates['itemHere'] = $modx->getTpl($tpls_itemHere);}
if (isset($tpls_itemActive)){$ddMenuBuilder_params->templates['itemActive'] = $modx->getTpl($tpls_itemActive);}

if (isset($tpls_itemParent)){$ddMenuBuilder_params->templates['itemParent'] = $modx->getTpl($tpls_itemParent);}
if (isset($tpls_itemParentHere)){$ddMenuBuilder_params->templates['itemParentHere'] = $modx->getTpl($tpls_itemParentHere);}
if (isset($tpls_itemParentActive)){$ddMenuBuilder_params->templates['itemParentActive'] = $modx->getTpl($tpls_itemParentActive);}

if (isset($tpls_itemParentUnpub)){$ddMenuBuilder_params->templates['itemParentUnpub'] = $modx->getTpl($tpls_itemParentUnpub);}
if (isset($tpls_itemParentUnpubActive)){$ddMenuBuilder_params->templates['itemParentUnpubActive'] = $modx->getTpl($tpls_itemParentUnpubActive);}

if (empty($ddMenuBuilder_params->templates)){unset($ddMenuBuilder_params->templates);}

$tpls_outer = (isset($tpls_outer)) ? $modx->getTpl($tpls_outer) : '<ul>[+children+]</ul>';

//Направление сортировки
if (isset($sortDir)){$ddMenuBuilder_params->sortDir = $sortDir;}
//По умолчанию будут только опубликованные документы
if (isset($showPublishedOnly)){$ddMenuBuilder_params->showPublishedOnly = $showPublishedOnly;}
//По умолчанию будут только документы, у которых стоит галочка «показывать в меню»
if (isset($showInMenuOnly)){$ddMenuBuilder_params->showInMenuOnly = $showInMenuOnly;}

$ddMenuBuilder = new ddMenuBuilder($ddMenuBuilder_params);

//Prepare provider params
parse_str($providerParams, $providerParams);

//Генерируем меню
$result = $ddMenuBuilder->generate($ddMenuBuilder->prepareProviderParams([
	//Parent by default
	'provider' => isset($provider) ? $provider : 'parent',
	'providerParams' => $providerParams
]));

//Данные, которые необоходимо передать в шаблон
if (isset($placeholders)){
	//Parse a query string
	parse_str($placeholders, $placeholders);
	//Unfold for arrays support (e. g. “some[a]=one&some[b]=two” => “[+some.a+]”, “[+some.b+]”; “some[]=one&some[]=two” => “[+some.0+]”, “[some.1]”)
	$placeholders = ddTools::unfoldArray($placeholders);
}
//Корректно инициализируем при необходимости
if (
	!isset($placeholders) ||
	!is_array($placeholders)
){
	$placeholders = [];
}

$placeholders['children'] = $result['outputString'];

return ddTools::parseText([
	'text' => $tpls_outer,
	'data' => $placeholders
]);
?>