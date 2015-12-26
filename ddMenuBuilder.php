<?php
/**
 * ddMenuBuilder.php
 * @version 1.8 (2015-02-05)
 * 
 * @desc Строит меню. Идея сниппета в совмещении преимуществ Wayfinder и Ditto при значительном упрощении кода.
 * 
 * @uses The library modx.ddTools 0.15.
 * 
 * Основные параметры:
 * @param $startId {integer} - Откуда брать. Default: 0.
 * @param $depth {integer} - Глубина поиска. Default: 1.
 * @param $sortDir {'ASC', 'DESC'} - Направление сортировки. Default: 'ASC'.
 * @param $showPublishedOnly {0; 1} - Брать ли только опубликованные документы. Default: 1.
 * @param $showInMenuOnly {0; 1} - Брать ли только те документы, что надо показывать в меню. Default: 1.
 * 
 * Шаблоны:
 * Доступные плэйсхолдеры во всех шаблонах: [+id+], [+menutitle+], [+pagetitle+], [+published+], [+isfolder+].
 * @param $tpls_item {string: chunkName} - Шаблон пункта меню. Default: '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'.
 * @param $tpls_itemHere {string: chunkName} - Шаблон активного пункта меню. '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'.
 * @param $tpls_itemActive {string: chunkName} - Шаблон пункта меню, если один из его дочерних документов here, но при этом не отображается в меню (из-за глубины, например). Default: $tpls_itemHere.
 * 
 * @param $tpls_itemParent {string: chunkName} - Шаблон пункта меню родителя. Default: '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>';.
 * @param $tpls_itemParentHere {string: chunkName} - Шаблон активного пункта меню родителя. Default: $tpls_itemParent || '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>'.
 * @param $tpls_itemParentActive {string: chunkName} - Шаблон пункта меню родителя, когда дочерний является here. Default: $tpls_itemParentHere.
 * 
 * @param $tpls_itemParentUnpub {string: chunkName} - Шаблон пункта меню родителя, если он не опубликован. Default: $tpls_itemParent.
 * @param $tpls_itemParentUnpubActive {string: chunkName} - Шаблон пункта меню родителя, если он не опубликован и дочерний является активным. Default: $tpls_itemParentActive.
 * 
 * @param $tpls_outer {string: chunkName} - Шаблон внешней обёртки. Доступные плэйсхолдеры: [+children+]. Default: '<ul>[+children+]</ul>'.
 * 
 * @copyright 2015, DivanDesign
 * http://www.DivanDesign.biz
 */

//Подключаем класс (ddTools подключится там)
require_once $modx->config['base_path'].'assets/snippets/ddMenuBuilder/ddmenubuilder.class.php';

//Для обратной совместимости
extract(ddTools::verifyRenamedParams($params, array(
	'tpls_item' => 'tplRow',
	'tpls_itemHere' => 'tplHere',
	'tpls_itemActive' => 'tplActive',
	'tpls_itemParent' => 'tplParentRow',
	'tpls_itemParentHere' => 'tplParentHere',
	'tpls_itemParentActive' => 'tplParentActive',
	'tpls_itemParentUnpub' => 'tplUnpubParentRow',
	'tpls_itemParentUnpubActive' => 'tplUnpubParentActive',
	'tpls_outer' => 'tplWrap'
)));

//Откуда брать
$startId = is_numeric($startId) ? $startId : 0;
//По умолчанию на 1 уровня
$depth = (is_numeric($depth)) ? $depth : 1;

//Задаём шаблоны
$tpls_item = isset($tpls_item) ? $modx->getChunk($tpls_item) : false;
$tpls_itemHere = isset($tpls_itemHere) ? $modx->getChunk($tpls_itemHere) : false;
$tpls_itemActive = isset($tpls_itemActive) ? $modx->getChunk($tpls_itemActive) : false;

$tpls_itemParent = isset($tpls_itemParent) ? $modx->getChunk($tpls_itemParent) : false;
$tpls_itemParentHere = isset($tpls_itemParentHere) ? $modx->getChunk($tpls_itemParentHere) : false;
$tpls_itemParentActive = isset($tpls_itemParentActive) ? $modx->getChunk($tpls_itemParentActive) : false;

$tpls_itemParentUnpub = isset($tpls_itemParentUnpub) ? $modx->getChunk($tpls_itemParentUnpub) : false;
$tpls_itemParentUnpubActive = isset($tpls_itemParentUnpubActive) ? $modx->getChunk($tpls_itemParentUnpubActive) : false;

$ddMenuBuilder_params = new stdClass();

$ddMenuBuilder_params->templates = array();

$ddMenuBuilder_params->templates['item'] = $tpls_item ? $tpls_item : '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>';
$ddMenuBuilder_params->templates['itemHere'] = $tpls_itemHere ? $tpls_itemHere : '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>';
$ddMenuBuilder_params->templates['itemActive'] = $tpls_itemActive ? $tpls_itemActive : $ddMenuBuilder_params->$templates['itemHere'];

$ddMenuBuilder_params->templates['itemParent'] = $tpls_itemParent ? $tpls_itemParent : '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>';
//Если не задан шаблон текущего родителя
if (!$tpls_itemParentHere){
	//Если шаблон родительского пункта был задан, берём его, в противном случае — по умолчанию
	$ddMenuBuilder_params->templates['itemParentHere'] = $tpls_itemParent ? $ddMenuBuilder_params->templates['itemParent'] : '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>';
}else{
	$ddMenuBuilder_params->templates['itemParentHere'] = $tpls_itemParentHere;
}
$ddMenuBuilder_params->templates['itemParentActive'] = $tpls_itemParentActive ? $tpls_itemParentActive : $ddMenuBuilder_params->templates['itemParentHere'];

$ddMenuBuilder_params->templates['itemParentUnpub'] = $tpls_itemParentUnpub ? $tpls_itemParentUnpub : $ddMenuBuilder_params->templates['itemParent'];
$ddMenuBuilder_params->templates['itemParentUnpubActive'] = $tpls_itemParentUnpubActive ? $tpls_itemParentUnpubActive : $ddMenuBuilder_params->templates['itemParentActive'];

$tpls_outer = (isset($tpls_outer)) ? $modx->getChunk($tpls_outer) : '<ul>[+children+]</ul>';

//Направление сортировки
if (isset($sortDir)){$ddMenuBuilder_params->sortDir = $sortDir;}
//По умолчанию будут только опубликованные документы
if (isset($showPublishedOnly)){$ddMenuBuilder_params->showPublishedOnly = $showPublishedOnly;}
//По умолчанию будут только документы, у которых стоит галочка «показывать в меню»
if (isset($showInMenuOnly)){$ddMenuBuilder_params->showInMenuOnly = $showInMenuOnly;}

$ddMenuBuilder = new ddMenuBuilder($ddMenuBuilder_params);

//Генерируем меню
$result = $ddMenuBuilder->generate($startId, $depth);

return ddTools::parseText($tpls_outer, array('children' => $result['outputString']), '[+', '+]');
?>