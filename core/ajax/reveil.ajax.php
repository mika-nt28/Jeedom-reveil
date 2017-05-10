<?php
try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');
	if (!isConnect('admin')) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}
	if (init('action') == 'updateShedule') {
		$eqLogic=eqLogic::byId(init('id'));
		if(is_object($eqLogic)){
      $Schedule=init('Schedule');
      $eqLogic->setConfiguration('ScheduleCron',$Schedule);
      $eqLogic->save();
			$cron = $eqLogic->CreateCron($Schedule, 'pull');
      $eqLogic->refreshWidget();
		}
		ajax::success($result);
	}
	throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayExeption($e), $e->getCode());
}
?>
