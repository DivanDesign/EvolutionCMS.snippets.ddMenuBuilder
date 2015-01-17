<?php
/**
 * ddMenuBuilder.php
 * @version 1.7 (2012-10-17)
 * 
 * @desc Строит меню. Идея сниппета в совмещении преимуществ Wayfinder и Ditto при значительном упрощении кода.
 * 
 * @uses modx ddTools lib 0.3.
 * 
 * Основные параметры:
 * @param $startId {integer} - Откуда брать. Default: 0.
 * @param $depth {integer} - Глубина поиска. Default: 1.
 * @param $sortDir {'ASC', 'DESC'} - Направление сортировки. Default: 'ASC'.
 * @param $showPublishedOnly {0; 1} - Брать ли только опубликованные документы. Default: 1.
 * @param $showInMenuOnly {0; 1} - Брать ли только те документы, что надо показывать в меню. Default: 1.
 * 
 * Шаблоны:
 * @param $tplRow {string: chunkName} - Шаблон пункта меню. @required
 * @param $tplHere {string: chunkName} - Шаблон активного пункта меню. @required
 * @param $tplActive {string: chunkName} - Шаблон пункта меню, если один из его дочерних документов here, но при этом не отображается в меню (из-за глубины, например). Default: $tplHere.
 * 
 * @param $tplParentRow {string: chunkName} - Шаблон пункта меню родителя. Default: $tplRow.
 * @param $tplParentHere {string: chunkName} - Шаблон активного пункта меню родителя. Default: $tplParentRow.
 * @param $tplParentActive {string: chunkName} - Шаблон пункта меню родителя, когда дочерний является here. Default: $tplParentHere.
 * 
 * @param $tplUnpubParentRow {string: chunkName} - Шаблон пункта меню родителя, если он не опубликован. Default: $tplParentRow.
 * @param $tplUnpubParentActive {string: chunkName} - Шаблон пункта меню родителя, если он не опубликован и дочерний является активным. Default: $tplParentActive.
 * 
 * @param $tplWrap {string: chunkName} - Шаблон внешней обёртки. Доступные плэйсхолдеры: [+children+]. Default: '<ul>[+children+]</ul>'.
 * 
 * @copyright 2012, DivanDesign
 * http://www.DivanDesign.biz
 */

//Подключаем класс
require_once $modx->config['base_path'].'assets/snippets/ddMenuBuilder/ddmenubuilder.class.php';

//Откуда брать
$startId = is_numeric($startId) ? $startId : 0;
//По умолчанию на 3 уровня
$depth = (is_numeric($depth)) ? $depth : 1;

//Задаём шаблоны
$tplRow = isset($tplRow) ? $modx->getChunk($tplRow) : false;
$tplHere = isset($tplHere) ? $modx->getChunk($tplHere) : false;
$tplActive = isset($tplActive) ? $modx->getChunk($tplActive) : false;

$tplParentRow = isset($tplParentRow) ? $modx->getChunk($tplParentRow) : false;
$tplParentHere = isset($tplParentHere) ? $modx->getChunk($tplParentHere) : false;
$tplParentActive = isset($tplParentActive) ? $modx->getChunk($tplParentActive) : false;

$tplUnpubParentRow = isset($tplUnpubParentRow) ? $modx->getChunk($tplUnpubParentRow) : false;
$tplUnpubParentActive = isset($tplUnpubParentActive) ? $modx->getChunk($tplUnpubParentActive) : false;


ddMenuBuilder::$templates['row'] = $tplRow ? $tplRow : '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>';
ddMenuBuilder::$templates['here'] = $tplHere ? $tplHere : '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>';
ddMenuBuilder::$templates['active'] = $tplActive ? $tplActive : ddMenuBuilder::$templates['here'];

ddMenuBuilder::$templates['parentRow'] = $tplParentRow ? $tplParentRow : '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>';
//Если не задан шаблон текущего родителя
if (!$tplParentHere){
	//Если шаблон родительского пункта был задан, берём его, в противном случае — по умолчанию
	ddMenuBuilder::$templates['parentHere'] = $tplParentRow ? ddMenuBuilder::$templates['parentRow'] : '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>';
}else{
	ddMenuBuilder::$templates['parentHere'] = $tplParentHere;
}
ddMenuBuilder::$templates['parentActive'] = $tplParentActive ? $tplParentActive : ddMenuBuilder::$templates['parentHere'];

ddMenuBuilder::$templates['unpubParentRow'] = $tplUnpubParentRow ? $tplUnpubParentRow : ddMenuBuilder::$templates['parentRow'];
ddMenuBuilder::$templates['unpubParentActive'] = $tplUnpubParentActive ? $tplUnpubParentActive : ddMenuBuilder::$templates['parentActive'];

$tplWrap = (isset($tplWrap)) ? $modx->getChunk($tplWrap) : '<ul>[+children+]</ul>';

//Получаем id текущего документа
ddMenuBuilder::$id = $modx->documentIdentifier;
//Таблицу, в которой лежат страницы
ddMenuBuilder::$table = $modx->getFullTableName('site_content');
//Направление сортировки
ddMenuBuilder::$sortDir = isset($sortDir) ? strtoupper($sortDir) : 'ASC';
//Условие where для sql
ddMenuBuilder::$where = '';

//По умолчанию берем только опубликованные документы
if (!is_numeric($showPublishedOnly) || $showPublishedOnly == 1){
	ddMenuBuilder::$where .= 'AND `published` = 1 ';
}

//По умолчанию смотрим только документы, у которых стоит галочка «показывать в меню»
if (!is_numeric($showInMenuOnly) || $showInMenuOnly == 1){
	ddMenuBuilder::$where .= 'AND `hidemenu` = 0';
}

//Генерируем меню
$result = ddMenuBuilder::generate($startId, $depth);

return ddTools::parseText($tplWrap, array('children' => $result['outputString']), '[+', '+]');
?>