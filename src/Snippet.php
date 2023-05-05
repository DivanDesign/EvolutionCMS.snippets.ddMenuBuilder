<?php
namespace ddMenuBuilder;

class Snippet extends \DDTools\Snippet {
	protected
		$version = '2.2.0',
		
		$params = [
			//Defaults
			'provider' => 'parent',
			'providerParams' => [],
			
			'sortDir' => null,
			'showPublishedOnly' => null,
			'showInMenuOnly' => null,
			
			'templates' => [
				'outer' => '@CODE:<ul>[+children+]</ul>'
			],
			'placeholders' => [],
		],
		
		$paramsTypes = [
			'providerParams' => 'objectStdClass',
			'templates' => 'objectStdClass',
			'placeholders' => 'objectStdClass',
		]
	;
	
	/**
	 * prepareParams
	 * @version 1.0 (2023-03-10)
	 *
	 * @param $this->params {stdClass|arrayAssociative|stringJsonObject|stringHjsonObject|stringQueryFormatted}
	 *
	 * @return {void}
	 */
	protected function prepareParams($params = []){
		//Call base method
		parent::prepareParams($params);
		
		$this->params->templates->outer = \ddTools::$modx->getTpl($this->params->templates->outer);
	}
	
	/**
	 * run
	 * @version 1.0 (2023-05-05)
	 * 
	 * @return {string}
	 */
	public function run(){
		$ddMenuBuilder_params = (object) [
			'templates' => $this->params->templates
		];
		
		//Направление сортировки
		if (!empty($this->params->sortDir)){
			$ddMenuBuilder_params->sortDir = $this->params->sortDir;
		}
		//По умолчанию будут только опубликованные документы
		if (!empty($this->params->showPublishedOnly)){
			$ddMenuBuilder_params->showPublishedOnly = $this->params->showPublishedOnly;
		}
		//По умолчанию будут только документы, у которых стоит галочка «показывать в меню»
		if (!empty($this->params->showInMenuOnly)){
			$ddMenuBuilder_params->showInMenuOnly = $this->params->showInMenuOnly;
		}
		
		$ddMenuBuilder = new \ddMenuBuilder\Main($ddMenuBuilder_params);
		
		//Генерируем меню
		$resultObject = $ddMenuBuilder->generate(
			$ddMenuBuilder->prepareProviderParams([
				//Parent by default
				'provider' => $this->params->provider,
				'providerParams' => $this->params->providerParams
			])
		);
		
		return \ddTools::parseText([
			'text' => $this->params->templates->outer,
			'data' => \DDTools\ObjectTools::extend([
				'objects' => [
					$this->params->placeholders,
					[
						'children' => $resultObject->outputString,
						'totalAllChildren' => $resultObject->totalAll,
						'totalThisLevelChildren' => $resultObject->totalThisLevel,
					]
				]
			])
		]);
	}
}