<?php
/**
 * modx ddMenuBuilder class
 * @version 1.2 (2015-02-05)
 * 
 * @uses modx 1.0.6 (Evo)
 * @uses modx ddTools lib 0.13.
 * 
 * @copyright 2015, DivanDesign
 * http://www.DivanDesign.biz
 */

if (!class_exists('ddMenuBuilder')){
//Подключаем modx.ddTools
require_once $modx->config['base_path'].'assets/snippets/ddTools/modx.ddtools.class.php';

class ddMenuBuilder {
	private $id;
	private $templates = array(
		'item' => '',
		'itemHere' => '',
		'itemActive' => '',
		'itemParent' => '',
		'itemParentHere' => '',
		'itemParentActive' => '',
		'itemParentUnpub' => '',
		'itemParentUnpubActive' => ''
	);
	private $sortDir = 'ASC';
	private $where = '';
	
	/**
	 * __construct
	 * @version 1.1.1 (2015-12-26)
	 * 
	 * @param $params {stdClass} — The object of params. @required
	 * @param $params->templates {array} — Шаблоны элементов меню. @required
	 * @param $params->templates['item'] {array} — Шаблон элемента. @required
	 * @param $params->templates['itemHere'] {array} — Шаблон текущего элемента (когда находимся на этой странице). @required
	 * @param $params->templates['itemActive'] {array} — Шаблон элемента, если один из его дочерних документов here, но при этом не отображается в меню (из-за глубины, например). @required
	 * @param $params->templates['itemParent'] {array} — Шаблон элемента-родителя. @required
	 * @param $params->templates['itemParentHere'] {array} — Шаблон активного элемента-родителя. @required
	 * @param $params->templates['itemParentActive'] {array} — Шаблон элемента-родителя, когда дочерний является here. @required
	 * @param $params->templates['itemParentUnpub'] {array} — Шаблон элемента-родителя, если он не опубликован. @required
	 * @param $params->templates['itemParentUnpubActive'] {array} — Шаблон элемента-родителя, если он не опубликован и дочерний является активным. @required
	 * @param $params->sortDir {'ASC'|'DESC'} — Направление сортировки. Default: 'ASC'.
	 * @param $params->showPublishedOnly {boolean} — Брать ли только опубликованные документы. Default: true.
	 * @param $params->showInMenuOnly {boolean} — Брать ли только те документы, что надо показывать в меню. Default: true.
	 * @param $params->id {integer} — ID текущего документа. Default: $modx->documentIdentifier.
	 */
	public function __construct(stdClass $params){
		global $modx;
		
		//ID текущего документа
		if (isset($params->id)){
			$this->id = $params->id;
		}else{
			$this->id = $modx->documentIdentifier;
		}
		
		$this->templates = $params->templates;
		
		//Направление сортировки
		if (isset($params->sortDir)){
			$this->sortDir = strtoupper($params->sortDir);
		}
		
		//По умолчанию берем только опубликованные документы
		if (!isset($params->showPublishedOnly) || $params->showPublishedOnly){
			$this->where .= 'AND `published` = 1 ';
		}
		
		//По умолчанию смотрим только документы, у которых стоит галочка «показывать в меню»
		if (!isset($params->showInMenuOnly) || $params->showInMenuOnly){
			$this->where .= 'AND `hidemenu` = 0';
		}
	}
	
	/**
	 * getOutputTemplate
	 * @version 1.0.4 (2015-12-26)
	 * 
	 * @desc Подбирает необходимый шаблон для вывода документа.
	 * 
	 * @param $params['docId'] {integer} — ID документа. @required
	 * @param $params['docPublished'] {0; 1} — Признак публикации документа. @required
	 * @param $params['hasActiveChildren'] {boolean} — Есть ли у документа активные дочерние документы. @required
	 * @param $params['hasChildrenOutput'] {boolean} — Будут ли у документа выводиться дочерние. @required
	 * 
	 * @return {string} — Шаблон для вывода.
	 */
	private function getOutputTemplate($params){
		$result = '';
		
		//Если у документа будут выводиться дочерние, значит надо использовать какой-то родительский шаблон
		if ($params['hasChildrenOutput']){
			//Если опубликован, значит надо использовать какой-то опубликованный шаблон
			if ($params['docPublished']){
				//Если текущий пункт является активным
				if ($params['docId'] == $this->id){
					//Шаблон активного родительского пункта меню
					$result = $this->templates['itemParentHere'];
				//Если не не активный
				}else{
					//Если один из дочерних был активным
					if ($params['hasActiveChildren']){
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
				if ($params['hasActiveChildren']){
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
			//Если опубликован
			if ($params['docPublished']){
				//Если текущий пункт является активным
				if ($params['docId'] == $this->id){
					//Шаблон активного пункта
					$result = $this->templates['itemHere'];
				//Если активен какой-то из дочерних, не участвующих в визуальном отображении
				}else if($params['hasActiveChildren']){
					$result = $this->templates['itemActive'];
				//Если не не активный
				}else{
					//Шаблон пункта меню
					$result = $this->templates['item'];
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * generate
	 * @version 1.0.1 (2015-12-26)
	 * 
	 * @desc Сторит меню.
	 * 
	 * @param $startId {integer} — Откуда брать. @required
	 * @param $depth {integer} — Глубина поиска. Default: 1.
	 * 
	 * @return {array}
	 */
	public function generate($startId, $depth = 1){
		global $modx;
		
		$result = array(
			//Считаем, что активных пунктов по дефолту нет
			'hasActive' => false,
			//Результирующая строка
			'outputString' => ''
		);
		
		//Получаем все пункты одного уровня
		$dbRes = $modx->db->query('
			SELECT `id`, `menutitle`, `pagetitle`, `published`, `isfolder`
			FROM '.ddTools::$tables['site_content'].'
			WHERE `parent` = '.$startId.' AND `deleted` = 0 '.$this->where.'
			ORDER BY `menuindex` '.$this->sortDir.'
		');
		
		//Если что-то есть
		if ($modx->db->getRecordCount($dbRes) > 0){
			//Проходимся по всем пунктам текущего уровня
			while ($doc = $modx->db->getRow($dbRes)){
				//Пустые дети
				$children = array(
					'hasActive' => false,
					'outputString' => ''
				);
				//И для вывода тоже пустые
				$doc['children'] = $children;
				
				//Если это папка (т.е., могут быть дочерние)
				if ($doc['isfolder']){
					//Получаем детей (вне зависимости от того, нужно ли их выводить)
					$children = self::generate($doc['id'], $depth - 1);
					
					//Если надо выводить глубже
					if ($depth > 1){
						//Выводим детей
						$doc['children'] = $children;
					}
				}
				
				//Если вывод вообще нужен (если «$depth» <= 0, значит этот вызов был только для выяснения активности)
				if ($depth > 0){
					//Получаем правильный шаблон для вывода текущего пункта
					$tpl = $this->getOutputTemplate(array(
						'docId' => $doc['id'],
						'docPublished' => $doc['published'],
						'hasActiveChildren' => $children['hasActive'],
						'hasChildrenOutput' => $doc['children']['outputString'] != ''
					));
					
					//Если шаблон определён (документ надо выводить)
					if ($tpl != ''){
						//Если вдруг меню у документа не задано, выставим заголовок вместо него
						if (trim($doc['menutitle']) == ''){$doc['menutitle'] = $doc['pagetitle'];}
						
						//Подготовим к парсингу
						$doc['children'] = $doc['children']['outputString'];
						//Парсим
						$result['outputString'] .= ddTools::parseText($tpl, $doc);
					}
				}
				
				//Если мы находимся на странице текущего документа или на странице одного из дочерних (не важно отображаются они или нет, т.е., не зависимо от глубины)
				if ($doc['id'] == $this->id || $children['hasActive']){$result['hasActive'] = true;}
			}
		}
		
		return $result;
	}
}
}
?>