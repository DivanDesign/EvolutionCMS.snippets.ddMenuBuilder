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
	 * @version 1.0.2 (2015-01-17)
	 * 
	 * @desc Подбирает необходимый шаблон для вывода документа.
	 * 
	 * @param $params['docId'] {integer} - ID документа. @required
	 * @param $params['docPublished'] {0; 1} - Признак публикации документа. @required
	 * @param $params['hasActiveChildren'] {boolean} - Есть ли у документа активные дочерние документы. @required
	 * @param $params['hasChildrenOutput'] {boolean} - Будут ли у документа выводиться дочерние. @required
	 * 
	 * @return {string} - Шаблон для вывода.
	 */
	public static function getOutputTemplate($params){
		$result = '';
		
		//Если у документа будут выводиться дочерние, значит надо использовать какой-то родительский шаблон
		if ($params['hasChildrenOutput']){
			//Если опубликован, значит надо использовать какой-то опубликованный шаблон
			if ($params['docPublished']){
				//Если текущий пункт является активным
				if ($params['docId'] == self::$id){
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
			if ($params['docPublished']){
				//Если текущий пункт является активным
				if ($params['docId'] == self::$id){
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
		
		$result = array(
			//Считаем, что активных пунктов по дефолту нет
			'hasActive' => false,
			//Результирующая строка
			'outputString' => ''
		);
		
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
					$tpl = self::getOutputTemplate(array(
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
				if ($doc['id'] == self::$id || $children['hasActive']){$result['hasActive'] = true;}
			}
		}
		
		return $result;
	}
}
}
?>