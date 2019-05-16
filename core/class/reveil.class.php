<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
class reveil extends eqLogic {
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
		$this->AddCommande("Date de début","NextStart","info",'string',true);
		$this->AddCommande("Arret ","stop","action","other",true);
		$this->AddCommande("Snooze ","snooze","action","other",true);
		$isArmed=$this->AddCommande("Etat activation","isArmed","info","binary",false,'lock','LOCK_STATE');
		$isArmed->execCmd(true);
		$Armed=$this->AddCommande("Activer","armed","action","other",true,'lock','LOCK_CLOSE');
		$Armed->setValue($isArmed->getId());
		$Armed->setConfiguration('state', '1');
		$Armed->setConfiguration('armed', '1');
		$Armed->save();
		$Released=$this->AddCommande("Desactiver","released","action","other",true,'lock','LOCK_OPEN');
		$Released->setValue($isArmed->getId());
		$Released->save();
		$Released->setConfiguration('state', '0');
		$Released->setConfiguration('armed', '1');
		if($this->getIsEnable() && $this->getCmd(null,'isArmed')->execCmd()){
			$this->NextStart();
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
	
	public function toHtml($_version = 'dashboard') {
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) 
			return $replace;
		$version = jeedom::versionAlias($_version);
		if ($this->getDisplay('hideOn' . $version) == 1)
			return '';
		$cmdColor = ($this->getPrimaryCategory() == '') ? '' : jeedom::getConfiguration('eqLogic:category:' . $this->getPrimaryCategory() . ':' . $vcolor);
		$replace['#cmdColor#'] = $cmdColor;
		foreach ($this->getCmd() as $cmd) {
			$action = '';
			if ($cmd->getIsVisible() == 1) {
				if ($cmd->getDisplay('hideOn' . $version) == 1) 
					continue;
				if ($cmd->getDisplay('forceReturnLineBefore', 0) == 1) 
					$action .= '<br/>';
				$action .= $cmd->toHtml($_version, $cmdColor);
				if ($cmd->getDisplay('forceReturnLineAfter', 0) == 1) 
					$action .= '<br/>';
				$replace['#'.$cmd->getLogicalId().'#'] = $action;
			}
		}
      		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'eqLogic', 'reveil')));
  	}

	public static $_widgetPossibility = array('custom' => array(
	        'visibility' => true,
	        'displayName' => true,
	        'displayObjectName' => true,
	        'optionalParameters' => true,
	        'background-color' => true,
	        'text-color' => true,
	        'border' => true,
	        'border-radius' => true
	));
	public function AddCommande($Name,$_logicalId,$Type="info", $SubType='binary',$visible,$Template='',$GenericType='') {
		$Commande = $this->getCmd(null,$_logicalId);
		if (!is_object($Commande))
		{
			$Commande = new reveilCmd();
			$Commande->setId(null);
			$Commande->setName($Name);
			$Commande->setIsVisible($visible);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($this->getId());
			$Commande->setType($Type);
			$Commande->setSubType($SubType);
		}
     		$Commande->setTemplate('dashboard',$Template );
		$Commande->setTemplate('mobile', $Template);
		$Commande->setGeneric_type($GenericType);
		$Commande->save();
		return $Commande;
	}
	public static function cron() {	
		foreach(eqLogic::byType('reveil') as $Reveil){	
			if($Reveil->getIsEnable() && $Reveil->getCmd(null,'isArmed')->execCmd()){
				$NextStart = DateTime::createFromFormat("d/m/Y H:i", $Reveil->getCmd(null,'NextStart')->execCmd())->getTimestamp();
				$allActionIsExecute = true;
				if($Reveil->EvaluateCondition()){
					foreach($Reveil->getConfiguration('Equipements') as $cmd){
						$now = mktime(date("H"),date("i"), 0);
						$StartTimeCmd =$NextStart + jeedom::evaluateExpression($cmd['delais']) * 60;
						if($StartTimeCmd <= $now){
							log::add('reveil','debug',$Reveil->getHumanName().' Nous n\'avons pas executer toute les commandes ont attend avant la prochaine execution');
							$allActionIsExecute = false;
						}
						if($StartTimeCmd >= $now && $StartTimeCmd < $now + 30){
							log::add('reveil','debug',$Reveil->getHumanName().' '.$cmd['cmd'].' le temps est venue => '.$StartTimeCmd.' >= '.time());
							//Créneau de 60s pour l'execution de la commande
							$Reveil->ExecuteAction($cmd,'on');
						}
					}
				}
				if($allActionIsExecute){
					log::add('reveil','debug',$Reveil->getHumanName().' Fin du reveil, Programmation du prochaine');
					$Reveil->NextStart();
                		}
			}
		}
	}
	public function ExecuteAction($cmd,$Declancheur) {
		if (isset($cmd['enable']) && $cmd['enable'] == 0)
			return;
		if (isset($cmd['declencheur']) && $cmd['declencheur'] != $Declancheur)
			return;
		try {
			$options = array();
			if (isset($cmd['options'])) 
				$options = $cmd['options'];
			scenarioExpression::createAndExec('action', $cmd['cmd'], $options);
			log::add('reveil','debug',$this->getHumanName().' Exécution de '.$cmd['cmd'].' : '.json_encode($options));
		} catch (Exception $e) {
			log::add('reveil', 'error', __($this->getHumanName().' Erreur lors de l\'éxecution de ', __FILE__) . $cmd['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
		}	
	}
	public function EvaluateCondition(){
		foreach($this->getConfiguration('Conditions') as $Condition){	
			if (isset($Condition['enable']) && $Condition['enable'] == 0)
				return;
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
			if($nextTime == null || $nextTime > $timestamp)
				$nextTime = $timestamp;
		}
		log::add('reveil','debug',$this->getHumanName().' Prochain reveil sera : '.date('d/m/Y H:i',$nextTime));
		if(cache::byKey('reveil::addSnooze::'.$this->getId())->getValue(false)){
			$nextTime = time() + jeedom::evaluateExpression($this->getConfiguration('snooze'))*60;
			log::add('reveil','info',$this->getHumanName().' Le snooze a été activé, le reveil sera relancé a '.date('d/m/Y H:i',$nextTime));
		}
		$this->checkAndUpdateCmd('NextStart',date('d/m/Y H:i',$nextTime));
	}
	public function Snooze(){
		if($this->EvaluateCondition()){
			foreach($this->getConfiguration('Equipements') as $cmd){
				$this->ExecuteAction($cmd,'off');
			}
		}
		cache::set('reveil::addSnooze::'.$this->getId(),true, 0);
	}
	public function StopReveil(){
		cache::set('reveil::Snooze::'.$this->getId(),false, 0);
		cache::set('reveil::addSnooze::'.$this->getId(),false, 0);
		foreach($this->getConfiguration('Equipements') as $cmd){
			$this->ExecuteAction($cmd,'off');
		}
	}
}
class reveilCmd extends cmd {
    	public function execute($_options = null) {	
		$Listener=cmd::byId(str_replace('#','',$this->getValue()));
		if (is_object($Listener)) {	
			switch($this->getLogicalId()){
				case 'stop':	
					$this->getEqLogic()->StopReveil();
				break;
				case 'snooze':	
					if(cache::byKey('reveil::Snooze::'.$this->getEqLogic()->getId())->getValue(false))
						$this->getEqLogic()->Snooze();
				break;
				case 'armed':
					$Listener->event(true);
					$Listener->getEqLogic()->NextStart();
				break;
				case 'released':
					$Listener->event(false);
				break;
			}
			$Listener->save();
		}
	}
}
?>
