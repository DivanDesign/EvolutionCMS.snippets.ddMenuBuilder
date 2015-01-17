<?php
/**
 * modx ddMenuBuilder class
 * @version 1.1 (2012-10-17)
 * 
 * @uses modx 1.0.6 (Evo)
 * @uses modx ddTools lib 0.3.
 * 
 * @copyright Copyright 2012, DivanDesign
 * http://www.DivanDesign.ru
 */

if (!class_exists('ddMenuBuilder')){
//Подключаем modx.ddTools
require_once $modx->config['base_path'].'assets/snippets/ddTools/modx.ddtools.class.php';

class ddMenuBuilder {
	public static $id;
	public static $table;
	public static $templates;
	public static $sortDir;
	public static $where;
// 	public $lim;
	
	/**
	 * getOutputTemplate
	 * @version 1.0.1 (2015-01-17)
	 * 
	 * @desc Подбирает необходимый шаблон для вывода документа.
	 * 
	 * @param $params['doc'] {array: associative} - Массив данных документа. @required
	 * @param $params['doc']['id'] {integer} - ID документа. @required
	 * @param $params['doc']['published'] {0; 1} - Признак публикации документа. @required
	 * @param $params['doc']['children'] {array: associative} - Массив для вывода дочерних документов (используется только для проверки на «!empty»). @required
	 * @param $params['hasActiveChildren'] - Есть ли у документа активные дочерние документы. @required
	 * 
	 * @return {string} - Шаблон для вывода.
	 */
	public static function getOutputTemplate($params){
		$result = '';
		
		//Если есть дочерние, значит надо использовать какой-то родительский шаблон
		if (!empty($params['doc']['children'])){
			//Если опубликован, значит надо использовать какой-то опубликованный шаблон
			if ($params['doc']['published']){
				//Если текущий пункт является активным
				if ($params['doc']['id'] == self::$id){
					//Шаблон активного родительского пункта меню
					$result = self::$templates['parentHere'];
				//Если не не активный
				}else{
					//Если один из дочерних был активным
					if ($params['hasActiveChildren']){
						//Сообщаем, что что-то активное есть
						//Шаблон родительского пункта меню, когда активный один из дочерних
						$result = self::$templates['parentActive'];
					//Если активных дочерних не было
					}else{
						//Шаблон родительского пункта меню
						$result = self::$templates['parentRow'];
					}
				}
			//Если не опубликован
			}else{
				//Если один из дочерних был активным
				if ($params['hasActiveChildren']){
					//Сообщаем, что что-то активное есть
					//Шаблон неопубликованного родительского пункта меню, когда активный один из дочерних
					$result = self::$templates['unpubParentActive'];
				//Если активных дочерних не было
				}else{
					//Шаблон неопубликованного родительского пункта меню
					$result = self::$templates['unpubParentRow'];
				}
			}
		//Если дочерних нет (отображаемых дочерних)
		}else{
			//Если опубликован
			if ($params['doc']['published']){
				//Если текущий пункт является активным
				if ($params['doc']['id'] == self::$id){
					//Шаблон активного пункта
					$result = self::$templates['here'];
				//Если активен какой-то из дочерних, не участвующих в визуальном отображении
				}else if($params['hasActiveChildren']){
					$result = self::$templates['active'];
				//Если не не активный
				}else{
					//Шаблон пункта меню
					$result = self::$templates['row'];
				}
			}
		}
		
		return $result;
	}
	
	public static function generate($startId, $depth){
		global $modx;
		
		$result = array();
		
// 		$limit = ($depth == self::$depth) ? "LIMIT {self::$lim}" : '';
		
		//Получаем все пункты одного уровня
		$sql = '
			SELECT `id`, `menutitle`, `pagetitle`, `published`, `isfolder`
			FROM '.self::$table.'
			WHERE `parent` = '.$startId.' AND `deleted` = 0 '.self::$where.'
			ORDER BY `menuindex` '.self::$sortDir.'
		';
		
		$dbRes = $modx->db->query($sql);
		
		//Если что-то есть
		if ($modx->db->getRecordCount($dbRes) > 0){
			//Считаем, что активных по дефолту нет
			$result['hasActive'] = false;
			//Строка
			$result['outputString'] = '';
			
			//Проходимся по всем пунктам текущего уровня
			while ($doc = $modx->db->getRow($dbRes)){
				//Дети
				$children = array();
				
				//Если это папка (т.е., если есть дочерние)
				if ($doc['isfolder']){
					//Получаем детей
					$children = self::generate($doc['id'], $depth - 1);
				}
				
				//Если надо идти глубже
				if ($depth > 1){
					//Получаем дочерние пункты
					$doc['children'] = $children;
				}
				
				//Получаем правильный шаблон для вывода текущего пункта
				$tpl = self::getOutputTemplate(array(
					'doc' => $doc,
					'hasActiveChildren' => $children['hasActive']
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
				
				//Если мы находимся на странице текущего документа или на странице одного из дочерних (не важно отображаются они или нет, т.е., не зависимо от глубины)
				if ($doc['id'] == self::$id || $children['hasActive']){$result['hasActive'] = true;}
			}
		}
		
		return $result;
	}
}
}
?>