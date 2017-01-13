$('body').on('change','.expressionAttr[data-l1key=configuration][data-l2key=ReveilType]',function(){
	switch($(this).val()){
		case 'DawnSimulatorEngine':
			$(this).closest('.ActionGroup').find('.DawnSimulatorEngine').show();
			$(this).closest('.ActionGroup').find('.actionOptions').hide();
		break;
		default:
			$(this).closest('.ActionGroup').find('.DawnSimulatorEngine').hide();
		break;
	}
});
function saveEqLogic(_eqLogic) {
	var state_order = '';
    if (!isset(_eqLogic.configuration)) {
        _eqLogic.configuration = {};
    }	
	if (typeof( _eqLogic.cmd) !== 'undefined') {
			_eqLogic.configuration.Conditions=new Object();
			_eqLogic.configuration.Equipements=new Object();
			var ConditionArray= new Array();
			var EquipementArray= new Array();
			$('#tab_condition .ConditionGroup').each(function( index ) {
				ConditionArray.push($(this).getValues('.expressionAttr')[0])
			});
			$('#tab_equipement .ActionGroup').each(function( index ) {
				EquipementArray.push($(this).getValues('.expressionAttr')[0])
			});
			_eqLogic.configuration.Conditions=ConditionArray;
			_eqLogic.configuration.Equipements=EquipementArray;
	}
   	return _eqLogic;
}
function printEqLogic(_eqLogic) {
	$('.ConditionGroup').remove();
	$('.ActionGroup').remove();
	if (typeof(_eqLogic.configuration.Conditions) !== 'undefined') {
		for(var index in _eqLogic.configuration.Conditions) { 
			if( (typeof _eqLogic.configuration.Conditions[index] === "object") && (_eqLogic.configuration.Conditions[index] !== null) )
				addCondition(_eqLogic.configuration.Conditions[index],  '{{Condition}}',$('#tab_condition').find('.div_Condition'));
		}
	}
	if (typeof(_eqLogic.configuration.Equipements) !== 'undefined') {
		for(var index in _eqLogic.configuration.Equipements) { 
			if( (typeof _eqLogic.configuration.Equipements[index] === "object") && (_eqLogic.configuration.Equipements[index] !== null) )
				addAction(_eqLogic.configuration.Equipements[index],  '{{Action}}',$('#tab_equipement').find('.div_action'));
		}
	}	
}
function addCondition(_action, _name, _el) {
	if (!isset(_action)) {
		_action = {};
	}
	if (!isset(_action.options)) {
		_action.options = {};
	}
    	var div = $('<div class="form-group ConditionGroup">')
  		.append($('<label class="col-lg-1 control-label">')
			.text(_name))
   		.append($('<div class="col-lg-1">')
    			.append($('<a class="btn btn-warning btn-sm listCmdCondition" >')
				.append($('<i class="fa fa-list-alt">'))))
		.append($('<div class="col-lg-3">')
			.append($('<input class="expressionAttr form-control input-sm cmdCondition" data-l1key="expression" />')))
 		.append($('<div class="col-lg-1">')
  			.append($('<i class="fa fa-minus-circle pull-left cursor conditionAttr" data-action="remove">')));
        _el.append(div);
        _el.find('.ConditionGroup:last').setValues(_action, '.expressionAttr');
  
}
function addAction(_action, _name, _el) {
	if (!isset(_action)) {
		_action = {};
	}
	if (!isset(_action.options)) {
		_action.options = {};
	}
    	var div = $('<div class="form-group ActionGroup">')
			.append($('<div class="form-group">')
				.append($('<label class="col-lg-1 control-label">')
					.text(_name))
				.append($('<div class="col-lg-1">')
						.append($('<a class="btn btn-warning btn-sm listCmdAction" >')
						.append($('<i class="fa fa-list-alt">'))))
				.append($('<div class="col-lg-3">')
					.append($('<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" />')))
				.append($('<div class="col-lg-6 actionOptions">')
						.append($(jeedom.cmd.displayActionOption(init(_action.cmd, ''), _action.options)))))
			.append($('<div class="form-group">')
				.append($('<label class="col-sm-2 control-label">')
					.text('{{Choisir le type de reveil}}')
					.append($('<sup>')
						.append($('<i class="fa fa-question-circle tooltips" title="Séléctioner le type de réveil">'))))
				.append($('<div class="col-sm-5">')
					.append($('<select class="expressionAttr" data-l1key="configuration" data-l2key="ReveilType">')
						.append($('<option value="default">')
							.text('{{Libre}}'))
						.append($('<option value="DawnSimulatorEngine">')
							.text('{{Simulateur d\'aube}}')))))						
			.append($('<div class="form-group DawnSimulatorEngine">')
				.append($('<label class="col-sm-2 control-label">')
					.text('{{Type de simulateur d\'aube}}')
					.append($('<sup>')
						.append($('<i class="fa fa-question-circle tooltips" title="Choisissez le type de simulation qui vous correspond">'))))
				.append($('<div class="col-sm-5">')
					.append($('<select class="expressionAttr" data-l1key="configuration" data-l2key="DawnSimulatorEngineType">')
							.append($('<option value="Linear">')
								.text('{{Linear}}'))
							.append($('<option value="InQuad">')
								.text('{{InQuad}}'))
							.append($('<option value="InOutQuad">')
								.text('{{InOutQuad}}'))
							.append($('<option value="InOutExpo">')
								.text('{{InOutExpo}}'))
							.append($('<option value="OutInExpo">')
								.text('{{OutInExpo}}'))
							.append($('<option value="InExpo">')
								.text('{{InExpo}}'))
							.append($('<option value="OutExpo">')
								.text('{{OutExpo}}')))))
				.append($('<div class="form-group DawnSimulatorEngine">')
					.append($('<label class="col-sm-2 control-label">')
						.text('{{Valeur de démarrage de la simulation}}')
						.append($('<sup>')
							.append($('<i class="fa fa-question-circle tooltips" title="Saisir la valeur de départ de la simulation (0 par defaut)">'))))
					.append($('<div class="col-sm-5">')
						.append($('<input type="text" class="expressionAttr form-control" data-l1key="configuration" data-l2key="DawnSimulatorEngineStartValue" placeholder="{{Valeur de départ de la simulation (0 par defaut)}}"/>'))))
				.append($('<div class="form-group DawnSimulatorEngine">')
					.append($('<label class="col-sm-2 control-label">')
						.text('{{Valeur d\'arret de la simulation}}')
						.append($('<sup>')
							.append($('<i class="fa fa-question-circle tooltips" title="Saisir la valeur d\'arret de la simulation (100 par defaut)">'))))
					.append($('<div class="col-sm-5">')
						.append($('<input type="text" class="expressionAttr form-control" data-l1key="configuration" data-l2key="DawnSimulatorEngineEndValue" placeholder="{{Valeur d\'arret de la simulation (100 par defaut)}}"/>'))))
				.append($('<div class="form-group DawnSimulatorEngine">')
					.append($('<label class="col-sm-2 control-label">')
						.text('{{Durée de la simulation}}')
						.append($('<sup>')
							.append($('<i class="fa fa-question-circle tooltips" title="Saisir la durée de la simulation">'))))
					.append($('<div class="col-sm-5">')
						.append($('<input type="text" class="expressionAttr form-control" data-l1key="configuration" data-l2key="DawnSimulatorEngineDuration" placeholder="{{Durée de la simulation}}"/>'))))
				.append($('<div class="form-group">')
					.append($('<div class="col-lg-1">')
						.append($('<i class="fa fa-minus-circle pull-left cursor ActionAttr" data-action="remove">'))));
        _el.append(div);
        _el.find('.ActionGroup:last').setValues(_action, '.expressionAttr');
  
}
$('#tab_zones a').click(function(e) {
    e.preventDefault();
    $(this).tab('show');
});
$('body').on('focusout','.expressionAttr[data-l1key=cmd]', function (event) {
    var expression = $(this).closest('.ActionGroup').getValues('.expressionAttr');
    var el = $(this);
    jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
        el.closest('.ActionGroup').find('.actionOptions').html(html);
    })
});
$('body').on('click','.conditionAttr[data-action=add]',function(){
	addCondition({},  '{{Condition}}',$(this).closest('.form-horizontal').find('.div_Condition'));
});
$('body').on('click','.conditionAttr[data-action=remove]',function(){
	$(this).closest('.ConditionGroup').remove();
});
$('body').on('click','.listCmdCondition',function(){
	var el = $(this).closest('.form-group').find('.expressionAttr[data-l1key=expression]');	
	jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
		var message = 'Aucun choix possible';
		if(result.cmd.subType == 'numeric'){
			message = '<div class="row">  ' +
			'<div class="col-md-12"> ' +
			'<form class="form-horizontal" onsubmit="return false;"> ' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >'+result.human+' {{est}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="operator">' +
			'                    <option value="==">{{égal}}</option>' +
			'                  <option value=">">{{supérieur}}</option>' +
			'                  <option value="<">{{inférieur}}</option>' +
			'                 <option value="!=">{{différent}}</option>' +
			'            </select>' +
			'       </div>' +
			'      <div class="col-xs-4">' +
			'         <input type="number" class="conditionAttr form-control" data-l1key="operande" />' +
			'    </div>' +
			'</div>' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="next">' +
			'                    <option value="">rien</option>' +
			'                  <option value="OU">{{ou}}</option>' +
			'            </select>' +
			'       </div>' +
			'</div>' +
			'</div> </div>' +
			'</form> </div>  </div>';
		}
		if(result.cmd.subType == 'string'){
			message = '<div class="row">  ' +
			'<div class="col-md-12"> ' +
			'<form class="form-horizontal" onsubmit="return false;"> ' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >'+result.human+' {{est}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="operator">' +
			'                    <option value="==">{{égale}}</option>' +
			'                  <option value="matches">{{contient}}</option>' +
			'                 <option value="!=">{{différent}}</option>' +
			'            </select>' +
			'       </div>' +
			'      <div class="col-xs-4">' +
			'         <input class="conditionAttr form-control" data-l1key="operande" />' +
			'    </div>' +
			'</div>' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="next">' +
			'                    <option value="">{{rien}}</option>' +
			'                  <option value="OU">{{ou}}</option>' +
			'            </select>' +
			'       </div>' +
			'</div>' +
			'</div> </div>' +
			'</form> </div>  </div>';
		}
		if(result.cmd.subType == 'binary'){
			message = '<div class="row">  ' +
			'<div class="col-md-12"> ' +
			'<form class="form-horizontal" onsubmit="return false;"> ' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >'+result.human+' {{est}}</label>' +
			'            <div class="col-xs-7">' +
			'                 <input class="conditionAttr" data-l1key="operator" value="==" style="display : none;" />' +
			'                  <select class="conditionAttr form-control" data-l1key="operande">' +
			'                       <option value="1">{{Ouvert}}</option>' +
			'                       <option value="0">{{Fermé}}</option>' +
			'                       <option value="1">{{Allumé}}</option>' +
			'                       <option value="0">{{Éteint}}</option>' +
			'                       <option value="1">{{Déclenché}}</option>' +
			'                       <option value="0">{{Au repos}}</option>' +
			'                       </select>' +
			'                    </div>' +
			'                 </div>' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="next">' +
			'                  <option value="">{{rien}}</option>' +
			'                  <option value="OU">{{ou}}</option>' +
			'            </select>' +
			'       </div>' +
			'</div>' +
			'</div> </div>' +
			'</form> </div>  </div>';
		}

		bootbox.dialog({
			title: "{{Ajout d'une nouvelle condition}}",
			message: message,
			buttons: {
				"Ne rien mettre": {
					className: "btn-default",
					callback: function () {
						el.atCaret('insert', result.human);
					}
				},
				success: {
					label: "Valider",
					className: "btn-primary",
					callback: function () {
    						var condition = result.human;
						condition += ' ' + $('.conditionAttr[data-l1key=operator]').value();
						if(result.cmd.subType == 'string'){
							if($('.conditionAttr[data-l1key=operator]').value() == 'matches'){
								condition += ' "/' + $('.conditionAttr[data-l1key=operande]').value()+'/"';
							}else{
								condition += ' "' + $('.conditionAttr[data-l1key=operande]').value()+'"';
							}
						}else{
							condition += ' ' + $('.conditionAttr[data-l1key=operande]').value();
						}
						condition += ' ' + $('.conditionAttr[data-l1key=next]').value()+' ';
						el.atCaret('insert', condition);
						if($('.conditionAttr[data-l1key=next]').value() != ''){
							el.click();
						}
					}
				},
			}
		});
	});
});
$('body').on('click','.ActionAttr[data-action=add]',function(){
	addAction({},  '{{Action}}',$(this).closest('.form-horizontal').find('.div_action'));
});
$('body').on('click','.ActionAttr[data-action=remove]', function () {
	$(this).closest('.ActionGroup').remove();
});
$("body").on('click', ".listCmdAction", function() {
    	var el = $(this).closest('.form-group').find('.expressionAttr[data-l1key=cmd]');
    	jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function(result) {
		el.value(result.human);
        	jeedom.cmd.displayActionOption(el.value(), '', function(html) {
			el.closest('.form-group').find('.actionOptions').html(html);
        	});
    	});
});
$('body').on( 'click','.bt_selectCmdExpression', function() {
	var _this=this;
	jeedom.cmd.getSelectModal({cmd: {type: 'info'},eqLogic: {eqType_name : ''}}, function (result) {
		$(_this).closest('.input-group').find('.cmdAttr').val(result.human);
	});
});  
$('body').on('click','.ScheduleCron',function(){
  var el = $(this).closest('.input-group').find('.eqLogicAttr');
  jeedom.getCronSelectModal({},function (result) {
    el.value(result.value);
  });
});
