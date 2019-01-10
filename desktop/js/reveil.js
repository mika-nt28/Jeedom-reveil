$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#table_Prorgamationtab").sortable({axis: "y", cursor: "move", items: ".ProgramationGroup", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#table_condition").sortable({axis: "y", cursor: "move", items: ".ConditionGroup", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#table_action").sortable({axis: "y", cursor: "move", items: ".ActionGroup", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$('.bt_showExpressionTest').off('click').on('click', function () {
  $('#md_modal').dialog({title: "{{Testeur d'expression}}"});
  $("#md_modal").load('index.php?v=d&modal=expression.test').dialog('open');
});
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
	_eqLogic.configuration=new Object();
	_eqLogic.configuration.Programation=new Object();
	_eqLogic.configuration.Conditions=new Object();
	_eqLogic.configuration.Equipements=new Object();
	var ConditionArray= new Array();
	var EquipementArray= new Array();
	var ProgramationArray= new Array();
	$('#programationtab .ProgramationGroup').each(function( index ) {
		ProgramationArray.push($(this).getValues('.expressionAttr')[0])
	});
	$('#conditiontab .ConditionGroup').each(function( index ) {
		ConditionArray.push($(this).getValues('.expressionAttr')[0])
	});
	$('#actiontab .ActionGroup').each(function( index ) {
		EquipementArray.push($(this).getValues('.expressionAttr')[0])
	});
	_eqLogic.configuration.Programation=ProgramationArray;
	_eqLogic.configuration.Conditions=ConditionArray;
	_eqLogic.configuration.Equipements=EquipementArray;
   	return _eqLogic;
}
function printEqLogic(_eqLogic) {
	$('.ProgramationGroup').remove();
	$('.ConditionGroup').remove();
	$('.ActionGroup').remove();
	if (typeof(_eqLogic.configuration.Programation) !== 'undefined') {
		for(var index in _eqLogic.configuration.Programation) {
			if( (typeof _eqLogic.configuration.Programation[index] === "object") && (_eqLogic.configuration.Programation[index] !== null) )
				addProgramation(_eqLogic.configuration.Programation[index],$('#programationtab').find('table tbody'));
		}
	}
	if (typeof(_eqLogic.configuration.Conditions) !== 'undefined') {
		for(var index in _eqLogic.configuration.Conditions) { 
			if( (typeof _eqLogic.configuration.Conditions[index] === "object") && (_eqLogic.configuration.Conditions[index] !== null) )
				addCondition(_eqLogic.configuration.Conditions[index],$('#conditiontab').find('table tbody'));
		}
	}
	if (typeof(_eqLogic.configuration.Equipements) !== 'undefined') {
		for(var index in _eqLogic.configuration.Equipements) { 
			if( (typeof _eqLogic.configuration.Equipements[index] === "object") && (_eqLogic.configuration.Equipements[index] !== null) )
				addAction(_eqLogic.configuration.Equipements[index],$('#actiontab').find('table tbody'));
		}
	}	
}
function addCmdToTable(_cmd) {
	var tr =$('<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">');
	tr.append($('<td>')
		.append($('<input type="hidden" class="cmdAttr form-control input-sm" data-l1key="id">'))
		.append($('<input type="hidden" class="cmdAttr form-control input-sm" data-l1key="type">'))
		.append($('<input type="hidden" class="cmdAttr form-control input-sm" data-l1key="subType">'))
		.append($('<input class="cmdAttr form-control input-sm" data-l1key="name" value="' + init(_cmd.name) + '" placeholder="{{Name}}" title="Name">')));
	var parmetre=$('<td>');	
	if (is_numeric(_cmd.id)) {
		parmetre.append($('<a class="btn btn-default btn-xs cmdAction" data-action="test">')
			.append($('<i class="fa fa-rss">')
				.text('{{Tester}}')));
	}
	parmetre.append($('<a class="btn btn-default btn-xs cmdAction tooltips" data-action="configure">')
		.append($('<i class="fa fa-cogs">')));
	tr.append(parmetre);
	$('#table_cmd tbody').append(tr);
	$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
	jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}
function addProgramation(_programation,  _el) {
	var Heure=$('<select class="expressionAttr form-control" data-l1key="Heure" >');
    var Minute=$('<select class="expressionAttr form-control" data-l1key="Minute" >');
	var number = 0;
    while (number < 24) {
		Heure.append($('<option value="'+number+'">')
			.text(number));
    	number++;
	}
  	number = 0;
    while (number < 60) {
		Minute.append($('<option value="'+number+'">')
			.text(number));
    	number++;
	}
	var tr = $('<tr class="ProgramationGroup">')
		.append($('<td>')
			.append($('<span class="input-group-btn">')
				.append($('<a class="btn btn-default ProgramationAttr btn-sm" data-action="remove">')
					.append($('<i class="fa fa-minus-circle">'))))
		       	.append($('<span class="expressionAttr" data-l1key="id">')))
		.append($('<td>')
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="expressionAttr" data-l1key="1">'))
				.append('{{Lundi}}'))
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="expressionAttr" data-l1key="2">'))
				.append('{{Mardi}}'))
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="expressionAttr" data-l1key="3">'))
				.append('{{Mercredi}}'))
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="expressionAttr" data-l1key="4">'))
				.append('{{Jeudi}}'))
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="expressionAttr" data-l1key="5">'))
				.append('{{Vendredi}}'))
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="expressionAttr" data-l1key="6">'))
				.append('{{Samedi}}'))
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="expressionAttr" data-l1key="0" />'))
				.append('{{Dimanche}}')))
		.append($('<td>')
			.append(Heure)
			.append(Minute))	
		.append($('<td>')
		       	.append($('<span class="expressionAttr" data-l1key="url">')));
        _el.append(tr);
        _el.find('tr:last').setValues(_programation, '.expressionAttr');
	$('.ProgramationAttr[data-action=remove]').off().on('click',function(){
		$(this).closest('tr').remove();
	});
}
function addCondition(_condition,_el) {
	var tr = $('<tr class="ConditionGroup">')
		.append($('<td>')
			.append($('<input type="checkbox" class="expressionAttr" data-l1key="enable" checked/>')))
		.append($('<td>')
			.append($('<div class="input-group">')
				.append($('<span class="input-group-btn">')
					.append($('<a class="btn btn-default conditionAttr btn-sm" data-action="remove">')
						.append($('<i class="fa fa-minus-circle">'))))
				.append($('<input class="expressionAttr form-control input-sm cmdCondition" data-l1key="expression"/>'))
				.append($('<span class="input-group-btn">')
					.append($('<a class="btn btn-warning btn-sm listCmdCondition">')
						.append($('<i class="fa fa-list-alt">'))))));

        _el.append(tr);
        _el.find('tr:last').setValues(_condition, '.expressionAttr');
	$('.conditionAttr[data-action=remove]').off().on('click',function(){
		$(this).closest('tr').remove();
	});  
}
function addAction(_action,  _el) {
	var tr = $('<tr class="ActionGroup">');
	tr.append($('<td>')
		.append($('<input type="checkbox" class="expressionAttr" data-l1key="enable" checked/>')));		
	tr.append($('<td>')
		.append($('<div class="input-group">')
			.append($('<span class="input-group-btn">')
				.append($('<a class="btn btn-default ActionAttr btn-sm" data-action="remove">')
					.append($('<i class="fa fa-minus-circle">'))))
			.append($('<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd"/>'))
			.append($('<span class="input-group-btn">')
				.append($('<a class="btn btn-success btn-sm listAction" title="Sélectionner un mot-clé">')
					.append($('<i class="fa fa-tasks">')))
				.append($('<a class="btn btn-success btn-sm listCmdAction data-type="action"">')
					.append($('<i class="fa fa-list-alt">')))))	
		.append($('<div class="actionOptions">')
	       		.append($(jeedom.cmd.displayActionOption(init(_action.cmd, ''), _action.options)))));
	tr.append($('<td>')
		.append($('<select class="expressionAttr form-control input-sm cmdAction" data-l1key="declencheur">')
			.append($('<option value="on">')
				.text('{{Allumage}}'))
			.append($('<option value="off">')
				.text('{{Extinction}}'))));	
	_el.append(tr);
        _el.find('tr:last').setValues(_action, '.expressionAttr');
	_el.find('tr:last .DawnSimulatorEngine').hide();
	$('.ActionAttr[data-action=remove]').off().on('click',function(){
		$(this).closest('tr').remove();
	});
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
$('.ProgramationAttr[data-action=add]').off().on('click',function(){
	addProgramation({},$(this).closest('.tab-pane').find('table'));
});
$('.conditionAttr[data-action=add]').off().on('click',function(){
	addCondition({},$(this).closest('.tab-pane').find('table'));
});
$('body').on('click','.listCmdCondition',function(){
	var el = $(this).closest('tr').find('.expressionAttr[data-l1key=expression]');	
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
$('.ActionAttr[data-action=add]').off().on('click',function(){
	addAction({},$(this).closest('.tab-pane').find('table'));
});
$("body").on('click', ".listAction", function() {
	var el = $(this).closest('tr').find('.expressionAttr[data-l1key=cmd]');
	jeedom.getSelectActionModal({}, function (result) {
		el.value(result.human);
		jeedom.cmd.displayActionOption(el.value(), '', function (html) {
			el.closest('td').find('.actionOptions').html(html);
		});
	});
}); 
$("body").on('click', ".listCmdAction", function() {
	var el = $(this).closest('tr').find('.expressionAttr[data-l1key=cmd]');
	jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
		el.value(result.human);
		jeedom.cmd.displayActionOption(el.value(), '', function (html) {
			el.closest('td').find('.actionOptions').html(html);
		});
	});
});
$('body').on( 'click','.bt_selectCmdExpression', function() {
	var _this=this;
	jeedom.cmd.getSelectModal({cmd: {type: 'info'},eqLogic: {eqType_name : ''}}, function (result) {
		$(_this).closest('.input-group').find('.cmdAttr').val(result.human);
	});
});  
