<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
class reveil extends eqLogic {
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'reveil';
		$return['launchable'] = 'ok';
		$return['state'] = 'ok';
		foreach(eqLogic::byType('reveil') as $reveil){
			$cron = cron::byClassAndFunction('reveil', 'pull',array('id' => $reveil->getId()));
			if (!is_object($cron)) 	{	
				$return['state'] = 'nok';
				return $return;
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
			$reveil->save();
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
		if($this->getIsEnable()){
			$cron = $this->CreateCron($this->getConfiguration('ScheduleCron'), 'pull');
		}
	}
	public function postRemove() {
		$cron = cron::byClassAndFunction('reveil', 'pull',array('id' => $this->getId()));
		if (is_object($cron)) 	
			$cron->remove();
	}
	public static function pull($_option){
		$reveil=eqLogic::byId($_option['id']);
		if(is_object($reveil)){
			if($reveil->EvaluateCondition()){
				foreach($reveil->getConfiguration('Equipements') as $cmd){
					switch($cmd['configuration']['ReveilType']){
						case 'DawnSimulatorEngine';
							$cron=$reveil->CreateCron('* * * * *', 'SimulAubeDemon',$cmd);
							$cron->start();
							$cron->run();
						break;
						default:
							log::add('reveil','debug','Execution de l\'action reveil libre');
							$reveil->ExecuteAction($cmd,'');
						break;
					}
				}
			}
		}
	}
	
	public static function SimulAubeDemon($_option){
		log::add('reveil','debug','Execution de l\'action reveil simulation d\'aube'. json_encode($_option));
		$reveil=eqLogic::byId($_option['id']);
		if(is_object($reveil)){
			log::add('reveil','debug','Simulation d\'aube : '.$reveil->getHumanName());
			$time = 0;
			$cmd=$_option['cmd'];
			while(true){
				$options['slider'] = ceil($reveil->dawnSimulatorEngine(
					$cmd['configuration']['DawnSimulatorEngineType'],
					$time,
					$cmd['configuration']['DawnSimulatorEngineStartValue'], 
					$cmd['configuration']['DawnSimulatorEngineEndValue'], 
					$cmd['configuration']['DawnSimulatorEngineDuration']
				));
				log::add('reveil','debug','Valeur de l\'intensité lumineuse :' .$options['slider']. '%');
				$time++;
				$reveil->ExecuteAction($cmd,$options);
				if($options['slider'] == $cmd['configuration']['DawnSimulatorEngineEndValue']){
					log::add('reveil','debug','Fin de la simulationd d\'aube');
					break;
				}else
					sleep(60);
			}
		}
		$cron = cron::byClassAndFunction('reveil', 'SimulAubeDemon',array('id' => $_option['id']));
		if(is_object($cron))
			$cron->remove();
	}
	private function dawnSimulatorEngine($type, $time, $startValue, $endValue, $duration) {
		switch ($type){
			case 'Linear':
				return $endValue * $time / $duration + $startValue;
			break;
			case 'InQuad':
				$time = $time / $duration;
				return $endValue * pow($time, 2) + $startValue;
			break;
			case 'InOutQuad':
				$time = $time / $duration * 2;
				if ($time < 1)
					return $endValue / 2 * pow($time, 2) + $startValue;
				else
					return -$endValue / 2 * (($time - 1) * ($time - 3) - 1) + $startValue;
			break;
			case 'InOutExpo':
				if ($time == 0)
					return $startValue ;
				if ($time == $duration)
					return $startValue + $endValue;
				$time = $time / $duration * 2;
				if ($time < 1)
					return $endValue / 2 * pow(2, 10 * ($time - 1)) + $startValue - $endValue * 0.0005;
				else{
					$time = $time - 1;
					return $endValue / 2 * 1.0005 * (-pow(2, -10 * $time) + 2) + $startValue;
				}
			break;
			case 'OutInExpo':
				if ($time < $duration / 2)
					return self::equations('OutExpo', $time * 2, $startValue, $endValue / 2, $duration);
				else
					return self::equations('InExpo', ($time * 2) - $duration, $startValue + $endValue / 2, $endValue / 2, $duration);
			break;
			case 'InExpo':
				if($time == 0)
					return $startValue;
				else
					return $endValue * pow(2, 10 * ($time / $duration - 1)) + $startValue - $endValue * 0.001;	
			break;
			case 'OutExpo':
				if($time == $duration)
					return $startValue + $endValue;
				else
					return $endValue * 1.001 * (-pow(2, -10 * $time / $duration) + 1) + $startValue;
			break;
		}
	}
	public function ExecuteAction($cmd,$options='') {	
		$Commande=cmd::byId(str_replace('#','',$cmd['cmd']));
		if($options=='')
			$options=$cmd['options'];
		if(is_object($Commande)){
			log::add('reveil','debug','Execution de '.$Commande->getHumanName());
			$Commande->execute($options);
		}		
	}
	public function CreateCron($Schedule, $logicalId,$demon=false) {
		$cron = cron::byClassAndFunction('reveil', $logicalId,array('id' => $this->getId()));
		if (!is_object($cron)) {
			$cron = new cron();
			$cron->setClass('reveil');
			$cron->setFunction($logicalId);
			$options['id']= $this->getId();
			if($demon!= false){
				$options['cmd']= $demon;
				$cron->setDeamon(1);
			}
			$cron->setOption($options);
			$cron->setEnable(1);
		}
		$cron->setSchedule($Schedule);
		$cron->save();
		return $cron;
	}
	public function EvaluateCondition(){
		foreach($this->getConfiguration('Conditions') as $condition){
			$expression = scenarioExpression::setTags($condition['expression']);
			$message = __('Evaluation de la condition : [', __FILE__) . trim($expression) . '] = ';
			$result = evaluate($expression);
			if (is_bool($result)) {
				if ($result) {
					$message .= __('Vrai', __FILE__);
				} else {
					$message .= __('Faux', __FILE__);
				}
			} else {
				$message .= $result;
			}
			log::add('reveil','info',$message);
			if(!$result){
				log::add('reveil','debug','Les conditions ne sont pas remplies');
				return false;
			}
		}
		return true;
	}
}
class reveilCmd extends cmd {
    	public function execute($_options = null) {	
	}
}
?>
