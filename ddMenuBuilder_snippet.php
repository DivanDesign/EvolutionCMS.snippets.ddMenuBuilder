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

//# Include
//Подключаем класс (ddTools подключится там)
require_once(
	$modx->getConfig('base_path') .
	'assets/snippets/ddMenuBuilder/ddMenuBuilder.class.php'
);


//# Prepare params
$params = \DDTools\ObjectTools::extend([
	'objects' => [
		//Defaults
		(object) [
			//Provider
			'provider' => 'parent',
			'providerParams' => null,
			
			//General
			'sortDir' => null,
			'showPublishedOnly' => null,
			'showInMenuOnly' => null,
			
			//Templates
			'templates' => null,
			'placeholders' => null,
		],
		$params
	]
]);

//Prepare template params
$params->templates = \DDTools\ObjectTools::extend([
	'objects' => [
		//Defaults
		(object) [
			'outer' => '@CODE:<ul>[+children+]</ul>'
		],
		\DDTools\ObjectTools::convertType([
			'object' => $params->templates,
			'type' => 'objectStdClass'
		])
	]
]);

$params->templates->outer = $modx->getTpl($params->templates->outer);

//Prepare provider params
$params->providerParams = \DDTools\ObjectTools::convertType([
	'object' => $params->providerParams,
	'type' => 'objectStdClass'
]);

//Данные, которые необоходимо передать в шаблон
$params->placeholders = \DDTools\ObjectTools::convertType([
	'object' => $params->placeholders,
	'type' => 'objectStdClass'
]);


//# Run
$ddMenuBuilder_params = [
	'templates' => $params->templates
];

//Направление сортировки
if (!empty($params->sortDir)){
	$ddMenuBuilder_params['sortDir'] = $params->sortDir;
}
//По умолчанию будут только опубликованные документы
if (!empty($params->showPublishedOnly)){
	$ddMenuBuilder_params['showPublishedOnly'] = $params->showPublishedOnly;
}
//По умолчанию будут только документы, у которых стоит галочка «показывать в меню»
if (!empty($params->showInMenuOnly)){
	$ddMenuBuilder_params['showInMenuOnly'] = $params->showInMenuOnly;
}

$ddMenuBuilder = new ddMenuBuilder($ddMenuBuilder_params);

//Генерируем меню
$result = $ddMenuBuilder->generate($ddMenuBuilder->prepareProviderParams([
	//Parent by default
	'provider' => $params->provider,
	'providerParams' => $params->providerParams
]));

return \ddTools::parseText([
	'text' => $params->templates->outer,
	'data' => \DDTools\ObjectTools::extend([
		'objects' => [
			$params->placeholders,
			[
				'children' => $result->outputString,
				'totalAllChildren' => $result->totalAll,
				'totalThisLevelChildren' => $result->totalThisLevel,
			]
		]
	])
]);
?>