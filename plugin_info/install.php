<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function reveil_install(){
}
function reveil_update(){
	log::add('reveil','debug','Lancement du script de mise a jours'); 
	foreach(eqLogic::byType('reveil') as $eqLogic){
		$eqLogic->save();
	}
	log::add('reveil','debug','Fin du script de mise a jours');
}
function reveil_remove(){
}
?>
