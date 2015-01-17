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
	
	public static function generate($startId, $depth){
		global $modx;
		
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
			$result = array(
				//Считаем, что активных по дефолту нет
				'act' => false
			);
			
			//Проходимся по всем пунктам текущего уровня
			while ($doc = $modx->db->getRow($dbRes)){
				//Дети
				$children = array();
				
				//Если вдруг меню у документа не задано, выставим заголовок вместо него
				if (trim($doc['menutitle']) == ''){$doc['menutitle'] = $doc['pagetitle'];}
				
				//Если это папка (т.е., если есть дочерние)
				if ($doc['isfolder']){
					//Получаем детей
					$children = self::generate($doc['id'], $depth - 1);
				}
				
				//Если надо идти глубже
				if ($depth > 1){
					//Получаем дочерние пункты
					$doc['wrapper'] = $children;
				}
				
				//Если текущий пункт является активным
				if ($doc['id'] == self::$id){$result['act'] = true;}
				
				//Если есть дочерние, значит надо использовать какой-то родительский шаблон
				if (!empty($doc['wrapper'])){
					//Если опубликован, значит надо использовать какой-то опубликованный шаблон
					if ($doc['published']){
						//Если текущий пункт является активным
						if ($doc['id'] == self::$id){
							//Шаблон активного родительского пункта меню
							$tpl = self::$templates['parentHere'];
						//Если не не активный
						}else{
							//Если один из дочерних был активным
							if ($children['act']){
								//Сообщаем, что что-то активное есть
								//Шаблон родительского пункта меню, когда активный один из дочерних
								$tpl = self::$templates['parentActive'];
							//Если активных дочерних не было
							}else{
								//Шаблон родительского пункта меню
	 							$tpl = self::$templates['parentRow'];
							}
						}
					//Если не опубликован
					}else{
						//Если один из дочерних был активным
						if ($children['act']){
							//Сообщаем, что что-то активное есть
							//Шаблон неопубликованного родительского пункта меню, когда активный один из дочерних
							$tpl = self::$templates['unpubParentActive'];
						//Если активных дочерних не было
						}else{
							//Шаблон неопубликованного родительского пункта меню
 							$tpl = self::$templates['unpubParentRow'];
						}
					}
				//Если дочерних нет (отображаемых дочерних)
				}else{
					//Если опубликован
					if ($doc['published']){
						//Если текущий пункт является активным
						if ($doc['id'] == self::$id){
							//Шаблон активного пункта
							$tpl = self::$templates['here'];
						//Если активен какой-то из дочерних, не участвующих в визуальном отображении
						}else if($children['act']){
							$tpl = self::$templates['active'];
						//Если не не активный
						}else{
							//Шаблон пункта меню
							$tpl = self::$templates['row'];
						}
					}
				}
				
				//Если один из детей (не важно отображаются они или нет, т.е., не зависимо от глубины) активен
				if ($children['act']){$result['act'] = true;}
				
				//Подготовим к парсингу
				$doc['wrapper'] = $doc['wrapper']['str'];
				//Парсим
				$result['str'] .= ddTools::parseText($tpl, $doc);
			}
		}else{
			$result = false;
		}
		
		return $result;
	}
}
}
?>