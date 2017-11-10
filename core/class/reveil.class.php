<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
class reveil extends eqLogic {
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'reveil';
		$return['launchable'] = 'ok';
		$return['state'] = 'ok';
		foreach(eqLogic::byType('reveil') as $reveil){
			if($reveil->getIsEnable() && $reveil->getCmd(null,'isArmed')->execCmd()){
				$cron = cron::byClassAndFunction('reveil', 'pull',array('id' => $reveil->getId()));
				if (!is_object($cron)) 	{	
					$return['state'] = 'nok';
					return $return;
				}
			}
		}
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
		foreach(eqLogic::byType('reveil') as $reveil){
			if($reveil->getIsEnable() && $reveil->getCmd(null,'isArmed')->execCmd()){
				$Schedule=$reveil->NextStart();
			}
		}
	}
	public static function deamon_stop() {	
		foreach(eqLogic::byType('reveil') as $reveil){
			$cron = cron::byClassAndFunction('reveil', 'pull',array('id' => $reveil->getId()));
			if (is_object($cron)) 	
				$cron->remove();
		}
	}
	public function postSave() {
		$isArmed=self::AddCommande($this,"Etat activation","isArmed","info","binary",false,'lock');
		$isArmed->event(true);
		$Armed=self::AddCommande($this,"Activer","armed","action","other",true,'lock');
		$Armed->setValue($isArmed->getId());
		$Armed->setConfiguration('state', '1');
		$Armed->setConfiguration('armed', '1');
		$Armed->save();
		$Released=self::AddCommande($this,"Desactiver","released","action","other",true,'lock');
		$Released->setValue($isArmed->getId());
		$Released->save();
		$Released->setConfiguration('state', '0');
		$Released->setConfiguration('armed', '1');
		if($this->getIsEnable() && $this->getCmd(null,'isArmed')->execCmd()){
			$this->NextStart();
		}
		else {
			$cron = cron::byClassAndFunction('reveil', 'pull',array('id' => $this->getId()));
			if (is_object($cron)) 	
				$cron->remove();
		}
	}
	public function preRemove() {
		$cron = cron::byClassAndFunction('reveil', 'pull',array('id' => $this->getId()));
		if (is_object($cron)) 	
			$cron->remove();
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
		
		$shedule='';
		$cron = cron::byClassAndFunction('reveil', 'pull',array('id' => $this->getId()));
		if (is_object($cron)) 	
			$shedule=$cron->getNextRunDate();
		$replace['#shedule#'] = $shedule;
		foreach ($this->getCmd() as $cmd) {
			if ($cmd->getIsVisible() == 1) {
				if ($cmd->getDisplay('hideOn' . $version) == 1) 
					continue;
				if ($cmd->getDisplay('forceReturnLineBefore', 0) == 1) 
					$action .= '<br/>';
				$action .= $cmd->toHtml($_version, $cmdColor);
				if ($cmd->getDisplay('forceReturnLineAfter', 0) == 1) 
					$action .= '<br/>';
			}
		}
		$replace['#action#'] = $action;
      		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'eqLogic', 'reveil')));
  	}
	public static $_widgetPossibility = array('custom' => array(
	        'visibility' => true,
	        'displayName' => false,
	        'optionalParameters' => false,
	));
	public static function AddCommande($eqLogic,$Name,$_logicalId,$Type="info", $SubType='binary',$visible,$Template='') {
		$Commande = $eqLogic->getCmd(null,$_logicalId);
		if (!is_object($Commande))
		{
			$Commande = new reveilCmd();
			$Commande->setId(null);
			$Commande->setName($Name);
			$Commande->setIsVisible($visible);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($eqLogic->getId());
			$Commande->setType($Type);
			$Commande->setSubType($SubType);
		}
     		$Commande->setTemplate('dashboard',$Template );
		$Commande->setTemplate('mobile', $Template);
		$Commande->save();
		return $Commande;
	}
	public static function pull($_option){
		$reveil=eqLogic::byId($_option['id']);
		if(is_object($reveil)){
			if(!$reveil->getCmd(null,'isArmed')->execCmd())
				return;
      			//On verifie que l'on a toujours le cron associé
      			$cron = cron::byClassAndFunction('reveil', 'pull',array('id' => $reveil->getId()));
     		 	if (!is_object($cron)) 	{
        			log::add('reveil','debug','Cron manquant on sort');
				return;
			} else  {
				log::add('reveil','debug','Cron OK on continue');				
			}
			if($reveil->EvaluateCondition()){
				foreach($reveil->getConfiguration('Equipements') as $cmd){
					$reveil->ExecuteAction($cmd);
				}
			}
			$reveil->NextStart();
		}
	}
	
	public function ExecuteAction($cmd) {
		if (isset($cmd['enable']) && $cmd['enable'] == 0)
			return;
		try {
			$options = array();
			if (isset($cmd['options'])) 
				$options = $cmd['options'];
			scenarioExpression::createAndExec('action', $cmd['cmd'], $options);
		} catch (Exception $e) {
			log::add('Volets', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
		}
		$Commande=cmd::byId(str_replace('#','',$cmd['cmd']));
		if(is_object($Commande)){
			log::add('reveil','debug','Exécution de '.$Commande->getHumanName());
			$Commande->execute($options);
		}		
	}
	public function CreateCron($Schedule, $logicalId, $demon=false) {
		log::add('reveil','debug','Création du cron "'.$logicalId.'" ID = '.$this->getId().' --> '.$Schedule);
		$cron = cron::byClassAndFunction('reveil', $logicalId,array('id' => $this->getId()));
		if (!is_object($cron)) 
			$cron = new cron();
		$cron->setClass('reveil');
		$cron->setFunction($logicalId);
		$options['id']= $this->getId();
		if($demon!= false){
			$options['cmd']= $demon->getId();
			$cron->setDeamon(1);
		}
		$cron->setOption($options);
		$cron->setEnable(1);
		$cron->setSchedule($Schedule);
		$cron->save();
		return $cron;
	}
	public function EvaluateCondition(){
		foreach($this->getConfiguration('Conditions') as $condition){	
			if (isset($condition['enable']) && $condition['enable'] == 0)
				return;
			$_scenario = null;
			$expression = scenarioExpression::setTags($condition['expression'], $_scenario, true);
			$message = __('Evaluation de la condition : [', __FILE__) . trim($expression) . '] = ';
			$result = evaluate($expression);
			$message .=$this->boolToText($result);
			log::add('reveil','info',$this->getHumanName().' '.$message);
			if(!$result)
				return false;	
			log::add('reveil','debug','Les conditions ne sont pas remplies');
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
		foreach($this->getConfiguration('programation') as $ConigSchedule){
			$offset=0;
			if(date('H') > $ConigSchedule["Heure"])
				$offset++;
			if(date('H') == $ConigSchedule["Heure"] && date('i') >= $ConigSchedule["Minute"])	
				$offset++;
			for($day=0;$day<7;$day++){
				if($ConigSchedule[date('w')+$day+$offset]){
					$offset+=$day;
					$timestamp=mktime ($ConigSchedule["Heure"], $ConigSchedule["Minute"], 0, date("n") , date("j") , date("Y"))+ (3600 * 24) * $offset;
					break;
				}
			}
			$this->CreateCron(date('i H d m w Y',$timestamp), 'pull');
		}
	}
}
class reveilCmd extends cmd {
    	public function execute($_options = null) {	
		$Listener=cmd::byId(str_replace('#','',$this->getValue()));
		if (is_object($Listener)) {	
			switch($this->getLogicalId()){
				case 'armed':
					$Listener->event(true);
					$this->getEqLogic()->NextStart();
				break;
				case 'released':
					$Listener->event(false);
					$cron = cron::byClassAndFunction('reveil', 'pull',array('id' => $this->getEqLogic()->getId()));
					if (is_object($cron)) 	
						$cron->remove();
					$cron = cron::byClassAndFunction('reveil', 'SimulAubeDemon');
					while(is_object($cron)) {
						$cron->stop();
						$cron->remove();						
						$cron = cron::byClassAndFunction('reveil', 'SimulAubeDemon');
					}
				break;
			}
			$Listener->setCollectDate(date('Y-m-d H:i:s'));
			$Listener->save();
		}
	}
}
?>
