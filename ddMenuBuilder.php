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
 * @param $tpls_itemParentHere {string: chunkName} - Шаблон активного пункта меню родителя. Default: '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>'.
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
require_once $modx->getConfig('base_path').'assets/snippets/ddMenuBuilder/ddMenuBuilder.class.php';

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

$ddMenuBuilder_params = new stdClass();

//Задаём шаблоны
$ddMenuBuilder_params->templates = array();

if (isset($tpls_item)){$ddMenuBuilder_params->templates['item'] = $modx->getChunk($tpls_item);}
if (isset($tpls_itemHere)){$ddMenuBuilder_params->templates['itemHere'] = $modx->getChunk($tpls_itemHere);}
if (isset($tpls_itemActive)){$ddMenuBuilder_params->templates['itemActive'] = $modx->getChunk($tpls_itemActive);}

if (isset($tpls_itemParent)){$ddMenuBuilder_params->templates['itemParent'] = $modx->getChunk($tpls_itemParent);}
if (isset($tpls_itemParentHere)){$ddMenuBuilder_params->templates['itemParentHere'] = $modx->getChunk($tpls_itemParentHere);}
if (isset($tpls_itemParentActive)){$ddMenuBuilder_params->templates['itemParentActive'] = $modx->getChunk($tpls_itemParentActive);}

if (isset($tpls_itemParentUnpub)){$ddMenuBuilder_params->templates['itemParentUnpub'] = $modx->getChunk($tpls_itemParentUnpub);}
if (isset($tpls_itemParentUnpubActive)){$ddMenuBuilder_params->templates['itemParentUnpubActive'] = $modx->getChunk($tpls_itemParentUnpubActive);}

if (empty($ddMenuBuilder_params->templates)){unset($ddMenuBuilder_params->templates);}

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
