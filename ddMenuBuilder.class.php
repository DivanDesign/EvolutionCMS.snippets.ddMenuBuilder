<?php
/**
 * modx ddMenuBuilder class
 * @version 2.5.1 (2019-11-29)
 * 
 * @uses PHP >= 5.6.
 * @uses (MODX)EvolutionCMS >= 1.1 {@link https://github.com/evolution-cms/evolution }
 * @uses (MODX)EvolutionCMS.libraries.ddTools >= 0.24.1 {@link http://code.divandesign.biz/modx/ddtools }
 * 
 * @copyright 2009–2019 DivanDesign {@link http://www.DivanDesign.biz }
 */

class ddMenuBuilder {
	private
		$hereDocId,
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
	 * @version 1.7 (2019-06-08)
	 * 
	 * @param $params {array_associative|stdClass} — The object of params.
	 * @param $params->showPublishedOnly {boolean} — Брать ли только опубликованные документы. Default: true.
	 * @param $params->showInMenuOnly {boolean} — Брать ли только те документы, что надо показывать в меню. Default: true.
	 * @param $params->sortDir {'ASC'|'DESC'} — Направление сортировки. Default: 'ASC'.
	 * @param $params->templates {array} — Шаблоны элементов меню. Default: $this->templates.
	 * @param $params->templates['item'] {array} — Шаблон элемента. Default: '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'.
	 * @param $params->templates['itemHere'] {array} — Шаблон текущего элемента (когда находимся на этой странице). Default: '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a></li>'.
	 * @param $params->templates['itemActive'] {array} — Шаблон элемента, если один из его дочерних документов here, но при этом не отображается в меню (из-за глубины, например). Default: $this->templates['itemHere'].
	 * @param $params->templates['itemParent'] {array} — Шаблон элемента-родителя. Default: '<li><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>'.
	 * @param $params->templates['itemParentHere'] {array} — Шаблон активного элемента-родителя. Default: '<li class="active"><a href="[~[+id+]~]" title="[+pagetitle+]">[+menutitle+]</a><ul>[+children+]</ul></li>'.
	 * @param $params->templates['itemParentActive'] {array} — Шаблон элемента-родителя, когда дочерний является here. Default: $this->templates['itemParentHere'].
	 * @param $params->templates['itemParentUnpub'] {array} — Шаблон элемента-родителя, если он не опубликован. Default: $this->templates['itemParent'].
	 * @param $params->templates['itemParentUnpubActive'] {array} — Шаблон элемента-родителя, если он не опубликован и дочерний является активным. Default: $this->templates['itemParentActive'].
	 * @param $params->hereDocId {integer} — ID текущего документа. Default: \ddTools::$modx->documentIdentifier.
	 */
	public function __construct($params = []){
		global $modx;
		
		//Include (MODX)EvolutionCMS.libraries.ddTools
		require_once($modx->getConfig('base_path') . 'assets/libs/ddTools/modx.ddtools.class.php');
		
		//Defaults
		$params = (object) array_merge(
			[
				'showPublishedOnly' => true,
				'showInMenuOnly' => true,
				'sortDir' => 'ASC',
				'hereDocId' => \ddTools::$modx->documentIdentifier,
				'templates' => []
			],
			(array) $params
		);
		
		//Если шаблоны переданы
		if (!empty($params->templates)){
			//Перебираем шаблоны объекта
			foreach (
				$params->templates as
				$templateName => $templateContent
			){
				//Если шаблон передан — сохраняем
				if (array_key_exists(
					$templateName,
					$this->templates
				)){
					$params->templates[$templateName] = \ddTools::$modx->getTpl($params->templates[$templateName]);
				}else{
					//Remove invalid templates
					unset($params->templates[$templateName]);
				}
			}
		}
		
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
		if (is_null($this->templates['itemActive'])){
			$this->templates['itemActive'] = $this->templates['itemHere'];
		}
		//Шаблон неопубликованного элемента по умолчанию равен шаблону элемента
		if (is_null($this->templates['itemUnpub'])){
			$this->templates['itemUnpub'] = $this->templates['item'];
		}
		
		//Шаблон неопубликованного элемента по умолчанию равен шаблону элемента
		if (is_null($this->templates['itemUnpubActive'])){
			$this->templates['itemUnpubActive'] = $this->templates['itemActive'];
		}
		
		//Шаблон активного элемента-родителя по умолчанию равен шаблону текущего элемента-родителя
		if (is_null($this->templates['itemParentActive'])){
			$this->templates['itemParentActive'] = $this->templates['itemParentHere'];
		}
		
		//Шаблон неопубликованного элемента-родителя по умолчанию равен шаблону элемента-родителя
		if (is_null($this->templates['itemParentUnpub'])){
			$this->templates['itemParentUnpub'] = $this->templates['itemParent'];
		}
		
		//Шаблон неопубликованного активного элемента-родителя по умолчанию равен шаблону активного элемента-родителя
		if (is_null($this->templates['itemParentUnpubActive'])){
			$this->templates['itemParentUnpubActive'] = $this->templates['itemParentActive'];
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
	 * @version 1.3 (2019-06-08)
	 * 
	 * @desc Подбирает необходимый шаблон для вывода документа.
	 * 
	 * @param $params {stdClass|array_associative} — The object of params. @required
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
					$result = $this->templates['itemParentHere'];
				//Если не не активный
				}else{
					//Если один из дочерних был активным
					if ($params->hasActiveChildren){
						//Сообщаем, что что-то активное есть
						//Шаблон родительского пункта меню, когда активный один из дочерних
						$result = $this->templates['itemParentActive'];
					//Если активных дочерних не было
					}else{
						//Шаблон родительского пункта меню
						$result = $this->templates['itemParent'];
					}
				}
			//Если не опубликован
			}else{
				//Если один из дочерних был активным
				if ($params->hasActiveChildren){
					//Сообщаем, что что-то активное есть
					//Шаблон неопубликованного родительского пункта меню, когда активный один из дочерних
					$result = $this->templates['itemParentUnpubActive'];
				//Если активных дочерних не было
				}else{
					//Шаблон неопубликованного родительского пункта меню
					$result = $this->templates['itemParentUnpub'];
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
						$result = $this->templates['itemHere'];
					//Если активен какой-то из дочерних, не участвующих в визуальном отображении
					}else if($params->hasActiveChildren){
						$result = $this->templates['itemActive'];
					//Если не не активный
					}else{
						//Шаблон пункта меню
						$result = $this->templates['item'];
					}
				}else{
					//Если активен какой-то из дочерних, не участвующих в визуальном отображении (он не может быть «here», потому что неопубликован)
					if ($params->hasActiveChildren){
						$result = $this->templates['itemUnpubActive'];
					}else{
						//Шаблон неопубликованного пункта меню
						$result = $this->templates['itemUnpub'];
					}
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * prepareProviderParams
	 * @version 0.1.1 (2019-06-08)
	 * 
	 * @param $params {stdClass|array_associative} — The object of params. @required
	 * @param $params->provider {'parent'|'select'} — Name of the provider that will be used to fetch documents. Default: 'parent'.
	 * @param $params->providerParams {array_associative} — Parameters to be passed to the provider.
	 * 
	 * @return {array_associative}
	 */
	public function prepareProviderParams($params = []){
		//Defaults
		$params = (object) array_merge(
			[
				'provider' => 'parent'
			],
			(array) $params
		);
		
		$result = [
			'where' => [],
			'depth' => 1
		];
		
		switch ($params->provider){
			case 'select':
				//Required paremeter
				if (
					isset($params->providerParams['ids']) &&
					!empty($params->providerParams['ids'])
				){
					if (is_array($params->providerParams['ids'])){
						$params->providerParams['ids'] = implode(
							',',
							$params->providerParams['ids']
						);
					}
					
					$result['where'][] = '`id` IN(' . $params->providerParams['ids'] . ')';
				}else{
					//Never
					$result['where'][] = '0 = 1';
				}
			break;
			
			default:
			case 'parent':
				//Defaults
				$params->providerParams = array_merge(
					[
						'parentIds' => 0,
						'depth' => 1
					],
					$params->providerParams
				);
				
				if (is_array($params->providerParams['parentIds'])){
					$params->providerParams['parentIds'] = implode(
						',',
						$params->providerParams['parentIds']
					);
				}
				
				$result['where'][] = '`parent` IN(' . $params->providerParams['parentIds'] . ')';
				$result['depth'] = $params->providerParams['depth'];
			break;
		}
		
		return $result;
	}
	
	/**
	 * generate
	 * @version 3.2.1 (2019-11-29)
	 * 
	 * @desc Сторит меню.
	 * 
	 * @param $params {stdClass|array_associative} — The object of params. @required
	 * @param $params->where {array} — Условия выборки. @required
	 * @param $params->where[i] {string} — Условие. @required
	 * @param $params->depth {integer} — Глубина поиска. Default: 1.
	 * 
	 * @return $result {array_associative}
	 * @return $result['hasActive'] {boolean}
	 * @return $result['outputString'] {string}
	 */
	public function generate($params){
		//Defaults
		$params = (object) array_merge(
			[
				'depth' => 1
			],
			(array) $params
		);
		
		$result = [
			//Считаем, что активных пунктов по дефолту нет
			'hasActive' => false,
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
				' . ddTools::$tables['site_content'] . '
			WHERE
				' . $params->where . '
			ORDER BY
				`menuindex` ' . $this->sortDir . '
		');
		
		//Если что-то есть
		if (\ddTools::$modx->db->getRecordCount($dbRes) > 0){
			//Проходимся по всем пунктам текущего уровня
			while ($doc = \ddTools::$modx->db->getRow($dbRes)){
				//Пустые дети
				$children = [
					'hasActive' => false,
					'outputString' => ''
				];
				//И для вывода тоже пустые
				$doc['children'] = $children;
				
				//Если это папка (т.е., могут быть дочерние)
				if ($doc['isfolder']){
					//Получаем детей (вне зависимости от того, нужно ли их выводить)
					$children = $this->generate([
						'where' => [
							'parent' => '`parent` = ' . $doc['id'],
							//Any hidemenu
							'hidemenu' => '`hidemenu` != 2'
						],
						'depth' => $params->depth - 1
					]);
					
					//Если надо выводить глубже
					if ($params->depth > 1){
						//Выводим детей
						$doc['children'] = $children;
					}
				}
				
				//Если вывод вообще нужен (если «$params->depth» <= 0, значит этот вызов был только для выяснения активности)
				if ($params->depth > 0){
					//Получаем правильный шаблон для вывода текущеёго пункта
					$tpl = $this->getOutputTemplate([
						'docId' => $doc['id'],
						'docPublished' => !!$doc['published'],
						//Требуется для определения, надо ли выводить текущий документ, т. к. выше в запросе получаются документы вне зависимости от отображения в меню
						'docShowedInMenu' => !$doc['hidemenu'],
						'hasActiveChildren' => $children['hasActive'],
						'hasChildrenOutput' => $doc['children']['outputString'] != ''
					]);
					
					//Если шаблон определён (документ надо выводить)
					if ($tpl != ''){
						//Если вдруг меню у документа не задано, выставим заголовок вместо него
						if (trim($doc['menutitle']) == ''){$doc['menutitle'] = $doc['pagetitle'];}
						
						//Подготовим к парсингу
						$doc['children'] = $doc['children']['outputString'];
						//Парсим
						$result['outputString'] .= ddTools::parseText([
							'text' => $tpl,
							'data' => $doc
						]);
					}
				}
				
				//Если мы находимся на странице текущего документа или на странице одного из дочерних (не важно отображаются они или нет, т.е., не зависимо от глубины)
				if (
					$doc['id'] == $this->hereDocId ||
					$children['hasActive']
				){
					$result['hasActive'] = true;
				}
			}
		}
		
		return $result;
	}
}
?>