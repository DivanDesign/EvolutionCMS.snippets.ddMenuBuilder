<?php
/**
 * ddMenuBuilder
 * @version 2.2 (2023-05-05)
 * 
 * @see README.md
 * 
 * @link http://code.divandesign.ru/modx/ddmenubuilder
 * 
 * @copyright 2009–2023 Ronef {@link https://Ronef.ru }
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