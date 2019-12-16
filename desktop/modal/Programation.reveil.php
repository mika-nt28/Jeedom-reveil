<table class="table_programation">
		<thead>
			<tr>
				<th>Jour actif</th>
				<th>Heure</th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>
	<script type="text/javascript">
      	var reveilId=init('id');
		$.ajax({// fonction permettant de faire de l'ajax
			type: "POST", // methode de transmission des données au fichier php
			url: "plugins/reveil/core/ajax/reveil.ajax.php", // url du fichier php
			data: {
				action: "getProgramation",
				id: reveilId
			},
			dataType: 'json',
			global: false,
			error: function(request, status, error) {
				handleAjaxError(request, status, error);
			},
			success: function(data) { // si l'appel a bien fonctionné
				if (data.state != 'ok') {
					$('#div_alert').showAlert({message: data.result, level: 'danger'});
					return;
				}
				for(var index in data.result) {
					if( (typeof data.result[index] === "object") && (data.result[index] !== null) )
						addProgramation(data.result[index],$('.eqLogic[data-eqLogic_id'+reveilId+' .table_programation'));
				}
			}
		});
		function sendProgramation(_el){	
			var prog=_el.closest('.ProgramationGroup').attr('data-id');
				var day='';
				if(_el.closest('.ProgramationGroup').find('.expressionAttr[data-l1key=1]').is(':checked'))
					day=day+'1';
				if(_el.closest('.ProgramationGroup').find('.expressionAttr[data-l1key=2]').is(':checked'))
					day=day+'2';
				if(_el.closest('.ProgramationGroup').find('.expressionAttr[data-l1key=3]').is(':checked'))
					day=day+'.';
				if(_el.closest('.ProgramationGroup').find('.expressionAttr[data-l1key=4]').is(':checked'))
					day=day+'4';
				if(_el.closest('.ProgramationGroup').find('.expressionAttr[data-l1key=5]').is(':checked'))
					day=day+'5';
				if(_el.closest('.ProgramationGroup').find('.expressionAttr[data-l1key=6]').is(':checked'))
					day=day+'6';
				if(_el.closest('.ProgramationGroup').find('.expressionAttr[data-l1key=0]').is(':checked'))
					day=day+'0';
				var heure=_el.closest('.ProgramationGroup').find('.expressionAttr[data-l1key=Heure]').val();
				var minute=_el.closest('.ProgramationGroup').find('.expressionAttr[data-l1key=Minute]').val();			
			$.ajax({// fonction permettant de faire de l'ajax
				type: "POST", // methode de transmission des données au fichier php
				url: "plugins/reveil/core/ajax/reveil.ajax.php", // url du fichier php
				data: {
					action: "setProgramation",
					id: reveilId,
					prog: prog,
					day: day,
					heure: heure,
					minute: minute
				},
				dataType: 'json',
				global: false,
				error: function(request, status, error) {
					handleAjaxError(request, status, error);
				},
				success: function(data) { // si l'appel a bien fonctionné
					if (data.state != 'ok') {
						$('#div_alert').showAlert({message: data.result, level: 'danger'});
						return;
					}
				}
			});
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
			var tr = $('<tr class="ProgramationGroup" data-id="'+_programation.id+'">')
				.append($('<td>')
					.append($('<div class="col-sm-5">')
						.append($('<label class="checkbox-inline">')
							.append($('<input type="checkbox" class="expressionAttr" data-l1key="1">'))
							.append('Lundi'))
						.append($('<label class="checkbox-inline">')
							.append($('<input type="checkbox" class="expressionAttr" data-l1key="2">'))
							.append('Mardi'))
						.append($('<label class="checkbox-inline">')
							.append($('<input type="checkbox" class="expressionAttr" data-l1key="3">'))
							.append('Mercredi'))
						.append($('<label class="checkbox-inline">')
							.append($('<input type="checkbox" class="expressionAttr" data-l1key="4">'))
							.append('Jeudi'))
						.append($('<label class="checkbox-inline">')
							.append($('<input type="checkbox" class="expressionAttr" data-l1key="5">'))
							.append('Vendredi'))
						.append($('<label class="checkbox-inline">')
							.append($('<input type="checkbox" class="expressionAttr" data-l1key="6">'))
							.append('Samedi'))
						.append($('<label class="checkbox-inline">')
							.append($('<input type="checkbox" class="expressionAttr" data-l1key="0" />'))
							.append('Dimanche'))))
				.append($('<td>')
					.append(Heure)
					.append(Minute));
			_el.append(tr);
			_el.find('tr:last').setValues(_programation, '.expressionAttr');
			$('.eqLogic[data-eqLogic_id='+reveilId+'] .expressionAttr[data-l1key=Heure]').off().on('change', function () {	
				sendProgramation($(this));
			});	
			$('.eqLogic[data-eqLogic_id'+reveilId+' .expressionAttr[data-l1key=Minute]').off().on('change', function () {	
				sendProgramation($(this));
			});	
			$('.eqLogic[data-eqLogic_id'+reveilId+' .expressionAttr[data-l1key=1]').off().on('change', function () {	
				sendProgramation($(this));
			});	
			$('.eqLogic[data-eqLogic_id'+reveilId+' .expressionAttr[data-l1key=2]').off().on('change', function () {	
				sendProgramation($(this));
			});	
			$('.eqLogic[data-eqLogic_id'+reveilId+' .expressionAttr[data-l1key=3]').off().on('change', function () {	
				sendProgramation($(this));
			});	
			$('.eqLogic[data-eqLogic_id'+reveilId+' .expressionAttr[data-l1key=4]').off().on('change', function () {
				sendProgramation($(this));
			});
			$('.eqLogic[data-eqLogic_id'+reveilId+' .expressionAttr[data-l1key=5]').off().on('change', function () {	
				sendProgramation($(this));
			});
			$('.eqLogic[data-eqLogic_id'+reveilId+' .expressionAttr[data-l1key=6]').off().on('change', function () {	
				sendProgramation($(this));
			});
			$('.eqLogic[data-eqLogic_id'+reveilId+' .expressionAttr[data-l1key=0]').off().on('change', function () {	
				sendProgramation($(this));
			});	
		}
	</script>
