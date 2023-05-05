<?php
/**
 * ddMenuBuilder
 * @version 2.1.1 (2020-04-12)
 * 
 * @see README.md
 * 
 * @link http://code.divandesign.ru/modx/ddmenubuilder
 * 
 * @copyright 2009–2020 Ronef {@link https://Ronef.ru }
 */

//Подключаем класс (ddTools подключится там)
require_once(
	$modx->getConfig('base_path') .
	'assets/snippets/ddMenuBuilder/ddMenuBuilder.class.php'
);

//Prepare template params
$templates = \DDTools\ObjectTools::extend([
	'objects' => [
		//Defaults
		(object) [
			'outer' => '@CODE:<ul>[+children+]</ul>'
		],
		\DDTools\ObjectTools::convertType([
			'object' => $templates,
			'type' => 'objectStdClass'
		])
	]
]);

$templates->outer = $modx->getTpl($templates->outer);

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
$providerParams = \DDTools\ObjectTools::convertType([
	'object' => $providerParams,
	'type' => 'objectStdClass'
]);

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
$placeholders = \DDTools\ObjectTools::extend([
	'objects' => [
		\DDTools\ObjectTools::convertType([
			'object' => $placeholders,
			'type' => 'objectStdClass'
		]),
		[
			'children' => $result->outputString,
			'totalAllChildren' => $result->totalAll,
			'totalThisLevelChildren' => $result->totalThisLevel,
		]
	]
]);

return \ddTools::parseText([
	'text' => $templates->outer,
	'data' => $placeholders
]);
?>