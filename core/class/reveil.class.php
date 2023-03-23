<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
class reveil extends eqLogic {
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'reveil';
		$return['launchable'] = 'ok';
		$return['state'] = 'nok';
		foreach(eqLogic::byType('reveil') as $Reveil){
			if($Reveil->getIsEnable() && $Reveil->getCmd(null,'isArmed')->execCmd()){
				$cron = cron::byClassAndFunction('reveil', 'CheckReveil', array('reveil_id' => $Reveil->getId()));
				if (!is_object($cron))	
					return $return;
			}
		}
		$return['state'] = 'ok';
		return $return;
	}
	public static function deamon_start($_debug = false) {
		log::remove('reveil');
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') 
			return;
		if ($deamon_info['state'] == 'ok') 
			return;
		foreach(eqLogic::byType('reveil') as $Reveil){
			if($Reveil->getIsEnable() && $Reveil->getCmd(null,'isArmed')->execCmd())
				$Reveil->createDeamon();
		}
	}
	public static function deamon_stop() {	
		foreach(eqLogic::byType('reveil') as $Reveil){
			$cron = cron::byClassAndFunction('reveil', 'CheckReveil', array('reveil_id' => $Reveil->getId()));
			if(is_object($cron))	
				$cron->remove();
		}
	}
	public static function CheckReveil($_option) {	
		$Reveil = eqLogic::byId($_option['reveil_id']);
		if (is_object($Reveil) && $Reveil->getIsEnable()){
			while(true){
				$NextStart =  $Reveil->getCmd(null,'NextStart');
				if(is_object($NextStart) && $Reveil->getCmd(null,'isArmed')->execCmd()){
					$NextStart = DateTime::createFromFormat("d/m/Y H:i", $NextStart->execCmd())->getTimestamp();
					list($NextTime, $NextCmds) = $Reveil->getNextDelaisAction($NextStart);
					if(time() >= $NextTime){
						if($Reveil->EvaluateCondition('on')){
							while($NextTime != 0){
								if(time() >= $NextTime)
									$Reveil->EvaluateAction('on');
								list($NextTime, $NextCmds) = $Reveil->getNextDelaisAction($NextStart);
								if($Reveil->EvaluateCondition('snooze'))
									$Reveil->Snooze();
								if($Reveil->EvaluateCondition('off'))
									$Reveil->StopReveil();
								sleep(1);
							}
						}
					}
				}
				sleep(1);
			}
		}
	}
	private function getNextDelaisAction($NextStart){
		$NextTime = 0;
		$NextCmds = null;
		foreach($this->getConfiguration('Equipements') as $cmd){
			if (isset($cmd['enable']) && $cmd['enable'] == 0)
				continue;
			if (isset($cmd['declencheur']) && $cmd['declencheur'] != 'on')
				continue;          
			if($NextStart + $this->getTime($cmd) == $NextTime)
				$NextCmds[] = $cmd;
			if($NextStart + $this->getTime($cmd) >= time()){
				if($NextTime == 0 || $NextStart + $this->getTime($cmd) < $NextTime){
					$NextTime = $NextStart + $this->getTime($cmd);
					$NextCmds = array($cmd);
				}
			}
		}
		return array($NextTime,$NextCmds);
	}
	private function getTime($cmd) {
		$delais = jeedom::evaluateExpression(intval($cmd['delais']));
		$base = intval($cmd['base']);
		if($base == 0)
			$base = 60;
		return $delais * $base;
	}
	public function createDeamon() {
		$cron = cron::byClassAndFunction('reveil', 'CheckReveil', array('reveil_id' => $this->getId()));
		if (!is_object($cron)) {
			$cron = new cron();
			$cron->setClass('reveil');
			$cron->setFunction('CheckReveil');
			$cron->setOption(array('reveil_id' => $this->getId()));			
			$cron->setDeamon(1);
			$cron->setEnable(1);
			$cron->setTimeout('1');
			$cron->setSchedule('* * * * * *');
			$cron->save();
		}
		$cron->start();
		$cron->run();
	}
	public function preSave() {
		$Programation=$this->getConfiguration('Programation');
		foreach($Programation as $key => $ConigSchedule){
			if($ConigSchedule["id"] == ''){
				$id=rand(0,32767);
				//while(array_search($id, array_column($this->getConfiguration('Programation'), 'id')) !== FALSE)
				//	$id=rand(0,32767);
				$ConigSchedule["id"]=$id;
			}
			$ConigSchedule["url"] = network::getNetworkAccess('external') . '/plugins/reveil/core/api/jeeReveil.php?apikey=' . jeedom::getApiKey('reveil') . '&id=' . $this->getId() . '&prog=' . $ConigSchedule["id"] . '&day=%DAY&heure=%H&minute=%M';
			$Programation[$key]=$ConigSchedule;
		}
		$this->setConfiguration('Programation', $Programation);
	}
	public function postSave() {
		$this->AddCommande("Date de début","NextStart","info",'string',1);
		$this->AddCommande("Arrêt","stop","action","other",1);
		$this->AddCommande("Snooze ","snooze","action","other",1);
		$isArmed=$this->AddCommande("Etat activation","isArmed","info","binary",0,'core::lock','LOCK_STATE');
		$isArmed->execCmd(true);
		$Armed=$this->AddCommande("Activer","armed","action","other",1,'core::lock','LOCK_CLOSE');
		$Armed->setValue($isArmed->getId());
		$Armed->setConfiguration('state', '1');
		$Armed->setConfiguration('armed', '1');
		$Armed->save();
		$Released=$this->AddCommande("Désactiver","released","action","other",1,'core::lock','LOCK_OPEN');
		$Released->setValue($isArmed->getId());
		$Released->save();
		$Released->setConfiguration('state', '0');
		$Released->setConfiguration('armed', '1');
		if($this->getIsEnable() && $this->getCmd(null,'isArmed')->execCmd()){
			$this->NextStart();
			$cron = cron::byClassAndFunction('reveil', 'CheckReveil', array('reveil_id' => $this->getId()));
			if(is_object($cron)){	
				$cron->stop();	
				$cron->start();	
				$cron->run();
			}
		}
	}
	public function UpdateDynamic($id,$days,$heure,$minute){
		$Programation=$this->getConfiguration('Programation');
		$key=array_search($id, array_column($Programation, 'id'));
		if($key !== FALSE){		
			for($day=0;$day<7;$day++)
				$Programation[$key][$day]=false;
			foreach(str_split($days) as $day)
				$Programation[$key][$day]=true;
			$Programation[$key]["Heure"]=$heure;
			$Programation[$key]["Minute"]=$minute;
			$this->setConfiguration('Programation',$Programation);
			$this->save();
			if($this->getIsEnable() && $this->getCmd(null,'isArmed')->execCmd()){
				$this->NextStart();
			}
		}
	}
	public function AddCommande($Name,$_logicalId,$Type="info", $SubType='binary',$Visible=true,$Template='',$GenericType='') {
		$Commande = $this->getCmd(null,$_logicalId);
		if (!is_object($Commande)){
			$Commande = new reveilCmd();
			$Commande->setId(null);
			$Commande->setName($Name);
			$Commande->setIsVisible($Visible);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($this->getId());
			$Commande->setType($Type);
			$Commande->setSubType($SubType);
			$Commande->setTemplate('dashboard',$Template );
			$Commande->setTemplate('mobile', $Template);
			$Commande->setGeneric_type($GenericType);
			$Commande->save();
		}
		return $Commande;
	}
	
	public function EvaluateAction($Autorisation){
		foreach($this->getConfiguration('Equipements') as $cmd){
			if (isset($cmd['enable']) && $cmd['enable'] == 0)
				continue;
			if (isset($cmd['declencheur']) && array_search($Autorisation, $cmd['declencheur']) === false)

				continue;
			$this->ExecuteAction($cmd);
		}
	}
	public function ExecuteAction($cmd) {
		try {
			$options = array();
			if (isset($cmd['options'])) 
				$options = $cmd['options'];
			scenarioExpression::createAndExec('action', $cmd['cmd'], $options);
			log::add('reveil','debug',$this->getHumanName().' Exécution de '.jeedom::toHumanReadable($cmd['cmd']).' : '.json_encode($options));
		} catch (Exception $e) {
			log::add('reveil', 'error', __($this->getHumanName().' Erreur lors de l\'éxecution de ', __FILE__) . $cmd['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
		}	
	}
	public function EvaluateCondition($Autorisation){
		foreach($this->getConfiguration('Conditions') as $Condition){	
			if (isset($Condition['enable']) && $Condition['enable'] == 0)
				continue;
			if (isset($Condition['declencheur']) && array_search($Autorisation, $Condition['declencheur']) === false)
				continue;
			$_scenario = null;
			$expression = scenarioExpression::setTags($Condition['expression'], $_scenario, true);
			$message = __('Evaluation de la condition : ['.jeedom::toHumanReadable($Condition['expression']).'][', __FILE__) . trim($expression) . '] = ';
			$result = evaluate($expression);
			$message .=$this->boolToText($result);
			log::add('reveil','info',$this->getHumanName().' '.$message);
			if(!$result){
				log::add('reveil','debug',$this->getHumanName().' Les conditions ne sont pas remplies');
				return false;	
			}
		}
		return true;
	}
	public function boolToText($value){
		if (is_bool($value)) {
			if ($value) 
				return __('Vrai', __FILE__);
			else 
				return __('Faux', __FILE__);
		} else 
			return $value;
	}
	public function NextStart(){
		$nextTime=null;
		foreach($this->getConfiguration('Programation') as $ConigSchedule){
			$offset=0;
			$timestamp=null;
			if(date('H') > $ConigSchedule["Heure"])
				$offset++;
			if(date('H') == $ConigSchedule["Heure"] && date('i') >= $ConigSchedule["Minute"])	
				$offset++;
			for($day=0;$day<7;$day++){
				$jour=date('w')+$day+$offset;
				if($jour > 6)
					$jour= $jour-7;
				if($ConigSchedule[$jour]){
					$offset+=$day;
					$timestamp=mktime ($ConigSchedule["Heure"], $ConigSchedule["Minute"], 0, date("n") , date("j") , date("Y"))+ (3600 * 24) * $offset;
					break;
				}
			}
			if($timestamp == null)
				continue;
			if($nextTime == null || $nextTime > $timestamp)
				$nextTime = $timestamp;
		}
		if($nextTime == null)
			return false;
		//log::add('reveil','debug',$this->getHumanName().' Prochain reveil sera : '.date('d/m/Y H:i',$nextTime));
		if(cache::byKey('reveil::addSnooze::'.$this->getId())->getValue(false)){
			$nextTime = time() + jeedom::evaluateExpression($this->getConfiguration('snooze'))*60;
			log::add('reveil','info',$this->getHumanName().' Le snooze a été activé, le reveil sera relancé a '.date('d/m/Y H:i',$nextTime));
		}
		log::add('reveil','info',$this->getHumanName().' Le prochain reveil sera relancé a '.date('d/m/Y H:i',$nextTime));
		$this->checkAndUpdateCmd('NextStart',date('d/m/Y H:i',$nextTime));
	}
	public function Snooze(){
		$this->EvaluateAction('snooze');
		cache::set('reveil::addSnooze::'.$this->getId(),true, 0);
		$this->NextStart();
	}
	public function StopReveil(){
		cache::set('reveil::Snooze::'.$this->getId(),false, 0);
		cache::set('reveil::addSnooze::'.$this->getId(),false, 0);
		$this->EvaluateAction('off');
		$this->NextStart();
	}
}
class reveilCmd extends cmd {
    	public function execute($_options = null) {		
		switch($this->getLogicalId()){
			case 'stop':	
				$this->getEqLogic()->StopReveil();
			break;
			case 'snooze':	
				if(cache::byKey('reveil::Snooze::'.$this->getEqLogic()->getId())->getValue(false))
					$this->getEqLogic()->Snooze();
			break;
					case 'armed':
				$Listener=cmd::byId(str_replace('#','',$this->getValue()));
				if (is_object($Listener)){
					$Listener->event(true);
					$Listener->getEqLogic()->NextStart();
				}
			break;
			case 'released':
				$Listener=cmd::byId(str_replace('#','',$this->getValue()));
				if (is_object($Listener)) 
					$Listener->event(false);
			break;
		}
	}
}
?>
