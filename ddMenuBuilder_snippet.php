<?php
/**
 * ddMenuBuilder
 * @version 2.1 (2020-03-07)
 * 
 * @see README.md
 * 
 * @link http://code.divandesign.biz/modx/ddmenubuilder
 * 
 * @copyright 2009–2020 DivanDesign {@link http://www.DivanDesign.biz }
 */

//Подключаем класс (ddTools подключится там)
require_once(
	$modx->getConfig('base_path') .
	'assets/snippets/ddMenuBuilder/ddMenuBuilder.class.php'
);

//Prepare template params
$templates = \ddTools::encodedStringToArray($templates);

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
$providerParams = \ddTools::encodedStringToArray($providerParams);

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
	$placeholders = \ddTools::encodedStringToArray($placeholders);
}else{
	$placeholders = [];
}

$placeholders['children'] = $result->outputString;
$placeholders['totalAllChildren'] = $result->totalAll;
$placeholders['totalThisLevelChildren'] = $result->totalThisLevel;

return \ddTools::parseText([
	'text' => $templates['outer'],
	'data' => $placeholders
]);
?>