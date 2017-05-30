<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function reveil_update() {
	log::add('reveil','debug','Lancement du scripte de mise a  jours'); 
	foreach(eqLogic::byType('reveil') as $eqLogic){ 
		if($eqLogic->getConfiguration('ScheduleCron') != ''){
			$Shedule=explode(' ',$eqLogic->getConfiguration('ScheduleCron'));
			$Config['Heure']=$Shedule[1];
			$Config['Minute']=$Shedule[0];
			foreach(explode(',',$Shedule[4]) as $day)
				$Config[$day]=true;
			$eqLogic->setConfiguration('Schedule',$Config);
			$eqLogic->setConfiguration('ScheduleCron','');
			$eqLogic->save();
		}
	}
}
?>
