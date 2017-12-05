<?php
header('Content-type: application/json');
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

if (!jeedom::apiAccess(init('apikey'), 'reveil')) {
	echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (reveil)', __FILE__);
	die();
}

$content = file_get_contents('php://input');
$json = json_decode($content, true);
log::add('reveil', 'debug', $content);

$eqlogic = reveil::byId(init('id'));
if (!is_object($eqlogic)) {
	throw new Exception(__('Commande ID reveil inconnu : ', __FILE__) . init('id'));
}
if ($eqlogic->getEqType_name() != 'reveil') {
	throw new Exception(__('Cette commande n\'est pas de type reveil : ', __FILE__) . init('id'));
}
if (is_array($json)) {
	$ConigSchedule=$eqlogic->getConfiguration('Programation');
	$ConigSchedule[0]["Heure"]=$json['heure'];
	$ConigSchedule[0]["Minute"]=$json['minute'];
	$eqlogic->setConfiguration('Programation',$ConigSchedule);
	$eqlogic->save();
	$eqlogic->NextStart();
}
return true;
?>
