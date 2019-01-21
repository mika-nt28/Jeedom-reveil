<?php
try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');
	/*if (!isConnect('admin')) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}*/
	if (init('action') == 'getProgramation') {
		$eqLogic=eqLogic::byId(init('id'));
		if(is_object($eqLogic)){
			ajax::success($eqLogic->getConfiguration('Programation'));
		}
		ajax::success(false);
	}
	if (init('action') == 'setProgramation') {
		$eqLogic=eqLogic::byId(init('id'));
		if(is_object($eqLogic))
			$eqLogic->UpdateDynamic(init('prog'),init('day'),init('heure'),init('minute'));
		ajax::success(true);
	}
	throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayExeption($e), $e->getCode());
}
?>
