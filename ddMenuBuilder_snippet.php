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

//Include (MODX)EvolutionCMS.libraries.ddTools
require_once(
	$modx->getConfig('base_path') .
	'assets/libs/ddTools/modx.ddtools.class.php'
);

return \DDTools\Snippet::runSnippet([
	'name' => 'ddMenuBuilder',
	'params' => $params
]);
?>