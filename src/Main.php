<?php
namespace ddMenuBuilder;

class Main {
	private
		$hereDocId,
		
		/**
		 * @var $templates {stdClass}
		 * @var $templates->item {string}
		 * @var $templates->itemHere {string}
		 * @var $templates->itemActive {string}
		 * @var $templates->itemUnpub {string}
		 * @var $templates->itemUnpubActive {string}
		 * @var $templates->itemParent {string}
		 * @var $templates->itemParentHere {string}
		 * @var $templates->itemParentActive {string}
		 * @var $templates->itemParentUnpub {string}
		 * @var $templates->itemParentUnpubActive {string}
		 */
		$templates = [
			'item' => '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>',
			'itemHere' => '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>',
			'itemActive' => NULL,
			'itemUnpub' => NULL,
			'itemUnpubActive' => NULL,
			'itemParent' => '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>',
			'itemParentHere' => '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>',
			'itemParentActive' => NULL,
			'itemParentUnpub' => NULL,
			'itemParentUnpubActive' => NULL
		],
		$sortDir,
		$where = [
			'deleted' => '`deleted` = 0'
		],
		$showPublishedOnly,
		$showInMenuOnly
	;
	
	/**
	 * __construct
	 * @version 1.9.1 (2023-05-05)
	 * 
	 * @param $params {arrayAssociative|stdClass} — The object of params.
	 * @param $params->showPublishedOnly {boolean} — Брать ли только опубликованные документы. Default: true.
	 * @param $params->showInMenuOnly {boolean} — Брать ли только те документы, что надо показывать в меню. Default: true.
	 * @param $params->sortDir {'ASC'|'DESC'} — Направление сортировки. Default: 'ASC'.
	 * @param $params->templates {arrayAssociative|stdClass} — Шаблоны элементов меню. Default: $this->templates.
	 * @param $params->templates->item {string} — Шаблон элемента. Default: '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'.
	 * @param $params->templates->itemHere {string} — Шаблон текущего элемента (когда находимся на этой странице). Default: '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'.
	 * @param $params->templates->itemActive {string} — Шаблон элемента, если один из его дочерних документов here, но при этом не отображается в меню (из-за глубины, например). Default: $this->templates->itemHere.
	 * @param $params->templates->itemParent {string} — Шаблон элемента-родителя. Default: '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>'.
	 * @param $params->templates->itemParentHere {string} — Шаблон активного элемента-родителя. Default: '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>'.
	 * @param $params->templates->itemParentActive {string} — Шаблон элемента-родителя, когда дочерний является here. Default: $this->templates->itemParentHere.
	 * @param $params->templates->itemParentUnpub {string} — Шаблон элемента-родителя, если он не опубликован. Default: $this->templates->itemParent.
	 * @param $params->templates->itemParentUnpubActive {string} — Шаблон элемента-родителя, если он не опубликован и дочерний является активным. Default: $this->templates->itemParentActive.
	 * @param $params->hereDocId {integer} — ID текущего документа. Default: \ddTools::$modx->documentIdentifier.
	 */
	public function __construct($params = []){
		$params = \DDTools\ObjectTools::extend([
			'objects' => [
				//Defaults
				(object) [
					'showPublishedOnly' => true,
					'showInMenuOnly' => true,
					'sortDir' => 'ASC',
					'hereDocId' => \ddTools::$modx->documentIdentifier,
					'templates' => [],
				],
				$params
			]
		]);
		
		$this->templates = (object) $this->templates;
		
		//Если шаблоны переданы
		if (!empty($params->templates)){
			//Перебираем шаблоны объекта
			foreach (
				$params->templates as
				$templateName =>
				$templateContent
			){
				//Если шаблон передан — сохраняем
				if (property_exists(
					$this->templates,
					$templateName
				)){
					$this->templates->{$templateName} = \ddTools::$modx->getTpl($templateContent);
				}
			}
		}
		
		unset($params->templates);
		
		//Все параметры задают свойства объекта
		foreach (
			$params as
			$paramName => $paramValue
		){
			//На всякий случай проверяем
			if (property_exists(
				$this,
				$paramName
			)){
				$this->{$paramName} = $paramValue;
			}
		}
		
		//Шаблон активного элемента по умолчанию равен шаблону текущего элемента
		if (is_null($this->templates->itemActive)){
			$this->templates->itemActive = $this->templates->itemHere;
		}
		//Шаблон неопубликованного элемента по умолчанию равен шаблону элемента
		if (is_null($this->templates->itemUnpub)){
			$this->templates->itemUnpub = $this->templates->item;
		}
		
		//Шаблон неопубликованного элемента по умолчанию равен шаблону элемента
		if (is_null($this->templates->itemUnpubActive)){
			$this->templates->itemUnpubActive = $this->templates->itemActive;
		}
		
		//Шаблон активного элемента-родителя по умолчанию равен шаблону текущего элемента-родителя
		if (is_null($this->templates->itemParentActive)){
			$this->templates->itemParentActive = $this->templates->itemParentHere;
		}
		
		//Шаблон неопубликованного элемента-родителя по умолчанию равен шаблону элемента-родителя
		if (is_null($this->templates->itemParentUnpub)){
			$this->templates->itemParentUnpub = $this->templates->itemParent;
		}
		
		//Шаблон неопубликованного активного элемента-родителя по умолчанию равен шаблону активного элемента-родителя
		if (is_null($this->templates->itemParentUnpubActive)){
			$this->templates->itemParentUnpubActive = $this->templates->itemParentActive;
		}
		
		//Валидация типов
		$this->sortDir = strtoupper($this->sortDir);
		$this->showPublishedOnly = boolval($this->showPublishedOnly);
		$this->showInMenuOnly = boolval($this->showInMenuOnly);
		
		//По умолчанию берем только опубликованные документы
		if ($this->showPublishedOnly){
			$this->where['published'] = '`published` = 1';
		}
		
		//По умолчанию смотрим только документы, у которых стоит галочка «показывать в меню»
		if ($this->showInMenuOnly){
			$this->where['hidemenu'] = '`hidemenu` = 0';
		}
	}
	
	/**
	 * getOutputTemplate
	 * @version 1.3.2 (2021-03-09)
	 * 
	 * @desc Подбирает необходимый шаблон для вывода документа.
	 * 
	 * @param $params {stdClass|arrayAssociative} — The object of params. @required
	 * @param $params->docId {integer} — ID документа. @required
	 * @param $params->docPublished {boolean} — Признак публикации документа. @required
	 * @param $params->docShowedInMenu {boolean} — Признак отображения документа в меню. @required
	 * @param $params->hasActiveChildren {boolean} — Есть ли у документа активные дочерние документы. @required
	 * @param $params->hasChildrenOutput {boolean} — Будут ли у документа выводиться дочерние. @required
	 * 
	 * @return {string} — Шаблон для вывода.
	 */
	private function getOutputTemplate($params){
		$params = (object) $params;
		
		$result = '';
		
		//Если у документа будут выводиться дочерние, значит надо использовать какой-то родительский шаблон
		if ($params->hasChildrenOutput){
			//Если опубликован, значит надо использовать какой-то опубликованный шаблон
			if ($params->docPublished){
				//Если текущий пункт является активным
				if ($params->docId == $this->hereDocId){
					//Шаблон активного родительского пункта меню
					$result = $this->templates->itemParentHere;
				//Если не не активный
				}else{
					//Если один из дочерних был активным
					if ($params->hasActiveChildren){
						//Сообщаем, что что-то активное есть
						//Шаблон родительского пункта меню, когда активный один из дочерних
						$result = $this->templates->itemParentActive;
					//Если активных дочерних не было
					}else{
						//Шаблон родительского пункта меню
						$result = $this->templates->itemParent;
					}
				}
			//Если не опубликован
			}else{
				//Если один из дочерних был активным
				if ($params->hasActiveChildren){
					//Сообщаем, что что-то активное есть
					//Шаблон неопубликованного родительского пункта меню, когда активный один из дочерних
					$result = $this->templates->itemParentUnpubActive;
				//Если активных дочерних не было
				}else{
					//Шаблон неопубликованного родительского пункта меню
					$result = $this->templates->itemParentUnpub;
				}
			}
		//Если дочерних нет (отображаемых дочерних)
		}else{
			if (
				(
					//Либо документ должен отображаться в меню
					$params->docShowedInMenu ||
					//Либо отображение в меню вообще не важно
					!$this->showInMenuOnly
				) &&
				(
					//Либо документ опубликован
					$params->docPublished ||
					//Либо публикация вообще не важна
					!$this->showPublishedOnly
				)
			){
				//Если опубликован, значит надо использовать какой-то опубликованный шаблон
				if ($params->docPublished){
					//Если текущий пункт является активным
					if ($params->docId == $this->hereDocId){
						//Шаблон активного пункта
						$result = $this->templates->itemHere;
					//Если активен какой-то из дочерних, не участвующих в визуальном отображении
					}elseif($params->hasActiveChildren){
						$result = $this->templates->itemActive;
					//Если не не активный
					}else{
						//Шаблон пункта меню
						$result = $this->templates->item;
					}
				}else{
					//Если активен какой-то из дочерних, не участвующих в визуальном отображении (он не может быть «here», потому что неопубликован)
					if ($params->hasActiveChildren){
						$result = $this->templates->itemUnpubActive;
					}else{
						//Шаблон неопубликованного пункта меню
						$result = $this->templates->itemUnpub;
					}
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * prepareProviderParams
	 * @version 0.3.1 (2023-05-05)
	 * 
	 * @param $params {stdClass|arrayAssociative} — The object of params. @required
	 * @param $params->provider {'parent'|'select'} — Name of the provider that will be used to fetch documents. Default: 'parent'.
	 * @param $params->providerParams {stdClass|arrayAssociative} — Parameters to be passed to the provider.
	 * 
	 * @return {stdClass}
	 */
	public function prepareProviderParams($params = []){
		$params = \DDTools\ObjectTools::extend([
			'objects' => [
				//Defaults
				(object) [
					'provider' => 'parent',
					'providerParams' => new \stdClass(),
				],
				$params
			]
		]);
		
		$result = (object) [
			'where' => [],
			'depth' => 1
		];
		
		switch ($params->provider){
			case 'select':
				//Required paremeter
				if (
					isset($params->providerParams->ids) &&
					!empty($params->providerParams->ids)
				){
					if (is_array($params->providerParams->ids)){
						$params->providerParams->ids = implode(
							',',
							$params->providerParams->ids
						);
					}
					
					$result->where[] =
						'`id` IN(' .
						$params->providerParams->ids .
						')'
					;
				}else{
					//Never
					$result->where[] = '0 = 1';
				}
			break;
			
			case 'parent':
			default:
				$params->providerParams = \DDTools\ObjectTools::extend([
					'objects' => [
						//Defaults
						(object) [
							'parentIds' => 0,
							'depth' => 1,
						],
						$params->providerParams
					]
				]);
				
				if (is_array($params->providerParams->parentIds)){
					$params->providerParams->parentIds = implode(
						',',
						$params->providerParams->parentIds
					);
				}
				
				$result->where[] =
					'`parent` IN(' .
					$params->providerParams->parentIds .
					')'
				;
				$result->depth = $params->providerParams->depth;
			break;
		}
		
		return $result;
	}
	
	/**
	 * generate
	 * @version 4.0.3 (2023-05-05)
	 * 
	 * @desc Сторит меню.
	 * 
	 * @param $params {stdClass|arrayAssociative} — The object of params. @required
	 * @param $params->where {array} — Условия выборки. @required
	 * @param $params->where[i] {string} — Условие. @required
	 * @param $params->depth {integer} — Глубина поиска. Default: 1.
	 * @param $params->level {integer} — For internal using only, not recommended to pass it. Default: 1.
	 * 
	 * @return $result {stdClass}
	 * @return $result->hasActive {boolean}
	 * @return $result->totalAll {integer} — Количество отображаемых пунктов всех уровней.
	 * @return $result->totalThisLevel {integer} — Количество отображаемых пунктов этого уровня.
	 * @return $result->outputString {string}
	 */
	public function generate($params){
		$params = \DDTools\ObjectTools::extend([
			'objects' => [
				//Defaults
				(object) [
					'depth' => 1,
					//For internal using only, not recommended to pass it
					'level' => 1,
				],
				$params
			]
		]);
		
		$result = (object) [
			//Считаем, что активных пунктов по дефолту нет
			'hasActive' => false,
			//Как и вообще пунктов
			'totalAll' => 0,
			'totalThisLevel' => 0,
			//Результирующая строка
			'outputString' => ''
		];
		
		$params->where = implode(
			' AND ',
			array_merge(
				$this->where,
				$params->where
			)
		);
		
		//Получаем все пункты одного уровня
		$dbRes = \ddTools::$modx->db->query('
			SELECT
				`id`,
				`menutitle`,
				`pagetitle`,
				`published`,
				`isfolder`,
				`hidemenu`
			FROM
				' . \ddTools::$tables['site_content'] . '
			WHERE
				' . $params->where . '
			ORDER BY
				`menuindex` ' . $this->sortDir . '
		');
		
		//Если что-то есть
		if (\ddTools::$modx->db->getRecordCount($dbRes) > 0){
			//Проходимся по всем пунктам текущего уровня
			while ($doc = \ddTools::$modx->db->getRow($dbRes)){
				$doc = (object) $doc;
				
				//Пустые дети
				$children = (object) [
					'hasActive' => false,
					'totalAll' => 0,
					'outputString' => ''
				];
				//И для вывода тоже пустые
				$doc->children = $children;
				//Количество отображаемых потомков всех уровней
				$doc->totalAllChildren = 0;
				//Количество отображаемых непосредственных потомков
				$doc->totalThisLevelChildren = 0;
				
				//Если это папка (т.е., могут быть дочерние)
				if ($doc->isfolder){
					//Получаем детей (вне зависимости от того, нужно ли их выводить)
					$children = $this->generate([
						'where' => [
							'parent' =>
								'`parent` = ' .
								$doc->id
							,
							//Any hidemenu
							'hidemenu' => '`hidemenu` != 2'
						],
						'depth' => $params->depth - 1,
						'level' => $params->level + 1
					]);
					
					//Можно смело наращивать без условия, т. к. возвращается количество отображаемых детей
					$result->totalAll += $children->totalAll;
					
					//Если надо выводить глубже
					if ($params->depth > 1){
						//Выводим детей
						$doc->children = $children;
						$doc->totalAllChildren = $children->totalAll;
						$doc->totalThisLevelChildren = $children->totalThisLevel;
					}
				}
				
				//Если вывод вообще нужен (если «$params->depth» <= 0, значит этот вызов был только для выяснения активности)
				if ($params->depth > 0){
					//Получаем правильный шаблон для вывода текущеёго пункта
					$tpl = $this->getOutputTemplate([
						'docId' => $doc->id,
						'docPublished' => !!$doc->published,
						//Требуется для определения, надо ли выводить текущий документ, т. к. выше в запросе получаются документы вне зависимости от отображения в меню
						'docShowedInMenu' => !$doc->hidemenu,
						'hasActiveChildren' => $children->hasActive,
						'hasChildrenOutput' => $doc->children->outputString != ''
					]);
					
					//Если шаблон определён (документ надо выводить)
					if ($tpl != ''){
						//Пунктов меню становится больше
						$result->totalAll++;
						$result->totalThisLevel++;
						
						//Если вдруг меню у документа не задано, выставим заголовок вместо него
						if (trim($doc->menutitle) == ''){
							$doc->menutitle = $doc->pagetitle;
						}
						
						//Подготовим к парсингу
						$doc->children = $doc->children->outputString;
						$doc->level = $params->level;
						
						//Парсим
						$result->outputString .= \ddTools::parseText([
							'text' => $tpl,
							'data' => $doc
						]);
					}
				}
				
				//Если мы находимся на странице текущего документа или на странице одного из дочерних (не важно отображаются они или нет, т.е., не зависимо от глубины)
				if (
					$doc->id == $this->hereDocId ||
					$children->hasActive
				){
					$result->hasActive = true;
				}
			}
		}
		
		return $result;
	}
}
?>