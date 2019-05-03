<?php
	if (!isConnect('admin')) {
		throw new Exception('{{401 - Accès non autorisé}}');
	}
	$plugin = plugin::byId('reveil');
	sendVarToJS('eqType', $plugin->getId());
	$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">    
   	<div class="col-xs-12 eqLogicThumbnailDisplay">
  		<legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br>
				<span>{{Ajouter}}</span>
			</div>
      			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
      				<i class="fas fa-wrench"></i>
    				<br>
    				<span>{{Configuration}}</span>
  			</div>
      			<div class="cursor bt_showExpressionTest logoSecondary" data-action="gotoPluginConf">
      				<i class="fas fa-check"></i>
    				<br>
    				<span>{{Testeur d'expression}}</span>
  			</div>
  		</div>
  		<legend><i class="fas fa-table"></i> {{Mes reveils}}</legend>
	   	<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
		<div class="eqLogicThumbnailContainer">
    		<?php
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
		?>
		</div>
	</div>
	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure">
					<i class="fa fa-cogs"></i>
					 {{Configuration avancée}}
				</a>
				<a class="btn btn-default btn-sm eqLogicAction" data-action="copy">
					<i class="fas fa-copy"></i>
					 {{Dupliquer}}
				</a>
				<a class="btn btn-sm btn-success eqLogicAction" data-action="save">
					<i class="fas fa-check-circle"></i>
					 {{Sauvegarder}}
				</a>
				<a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove">
					<i class="fas fa-minus-circle"></i>
					 {{Supprimer}}
				</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
    			<li role="presentation">
				<a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay">
					<i class="fa fa-arrow-circle-left"></i>
				</a>
			</li>
    			<li role="presentation" class="active">
				<a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab">
				<i class="fa fa-tachometer"></i> 
					{{Equipement}}
				</a>
			</li>
    			<li role="presentation">
				<a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab">
					<i class="fa fa-list-alt"></i> 
					{{Commandes}}
				</a>
			</li>
    			<li role="presentation">
				<a href="#programationtab" aria-controls="profile" role="tab" data-toggle="tab">
					<i class="fa fa-calendar"></i> 
					{{Programation}}
				</a>
			</li>
    			<li role="presentation">
				<a href="#conditiontab" aria-controls="profile" role="tab" data-toggle="tab">
					<i class="fa fa-asterisk"></i> 
					{{Conditions}}
				</a>
			</li>
    			<li role="presentation">
				<a href="#actiontab" aria-controls="profile" role="tab" data-toggle="tab">
					<i class="fa fa-list-alt"></i> 
					{{Actions}}
				</a>
			</li>
  		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
      				<br/>
    				<form class="form-horizontal">
					<fieldset>
						<div class="form-group ">
							<label class="col-sm-3 control-label">{{Nom du réveil}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="Indiquer le nom de votre réveil" style="font-size : 1em;color:grey;"></i>
								</sup>
							</label>
							<div class="col-sm-5">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du reveil}}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" >{{Objet parent}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="Indiquer l'objet dans lequel le widget de ce réveil apparaîtra sur le dashboard" style="font-size : 1em;color:grey;"></i>
								</sup>
							</label>
							<div class="col-sm-5">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>
									<?php
										foreach (jeeObject::all() as $object) 
											echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">
								{{Catégorie}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="Choisissez une catégorie
								Cette information n'est pas obligatoire mais peut être utile pour filtrer les widgets" style="font-size : 1em;color:grey;"></i>
								</sup>
							</label>
							<div class="col-sm-9">
								<?php
									foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
										echo '</label>';
									}
								?>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label"></label>
							<div class="col-sm-9">
								<label class="checkbox-inline">
									<input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>
									{{Activer}}
								</label>
								<label class="checkbox-inline">
									<input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>
									{{Visible}}
								</label>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" >
								{{Temps après un snooze}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="Saisir le temps pour un snooze" style="font-size : 1em;color:grey;"></i>
								</sup>
							</label>
							<div class="col-sm-5">
								<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="snooze" placeholder="{{Temps snooze (min)}}"/>
							</div>
						</div>
					</fieldset>
				</form>
			</div>		
			<div role="tabpanel" class="tab-pane" id="programationtab">
				<form class="form-horizontal">
					<fieldset>
						<legend>{{Les programmations de la zone :}}
							<sup>
								<i class="fa fa-question-circle tooltips" title="Saisir toutes les programmations pour la zone"></i>
							</sup>
							<a class="btn btn-success btn-xs ProgramationAttr" data-action="add" style="margin-left: 5px;">
								<i class="fa fa-plus-circle"></i>
								{{Ajouter une programmation}}
							</a>
						</legend>
					</fieldset>
				</form>
				<table id="table_programation" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th style="width:30px;"></th>
							<th style="width:600px;">{{Jour actif}}</th>
							<th style="width:100px;">{{Heure}}</th>
							<th>{{Reprogrammation}}</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			<div role="tabpanel" class="tab-pane" id="conditiontab">
				<form class="form-horizontal">
					<fieldset>
						<legend>{{Les conditions d'exécution :}}
							<sup>
								<i class="fa fa-question-circle tooltips" title="Saisir toutes les conditions d'exécution de la gestion"></i>
							</sup>
							<a class="btn btn-success btn-xs conditionAttr" data-action="add" style="margin-left: 5px;">
								<i class="fa fa-plus-circle"></i>
								{{Ajouter une Condition}}
							</a>
						</legend>
					</fieldset>
				</form>			
				<table id="table_condition" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th></th>
							<th>{{Condition}}</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>				
			<div role="tabpanel" class="tab-pane" id="actiontab">
				<form class="form-horizontal">
					<fieldset>
						<legend>{{Les actions:}}
							<sup>
								<i class="fa fa-question-circle tooltips" title="Saisir toutes les actions à mener à l'ouverture"></i>
							</sup>
							<a class="btn btn-success btn-xs ActionAttr" data-action="add" style="margin-left: 5px;">
								<i class="fa fa-plus-circle"></i>
								{{Ajouter une Action}}
							</a>
						</legend>
					</fieldset>
				</form>					
				<table id="table_action" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th></th>
							<th>{{Action}}</th>
							<th>{{Délais}}</th>
							<th>{{Déclencheur}}</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>		
			<div role="tabpanel" class="tab-pane" id="commandtab">	
				<table id="table_cmd" class="table table-bordered table-condensed">
				    <thead>
					<tr>
					    <th>{{Nom}}</th>
					    <th>{{Paramètre}}</th>
					</tr>
				    </thead>
				    <tbody></tbody>
				</table>
			</div>	
		</div>
	</div>
</div>

<?php include_file('desktop', 'reveil', 'js', 'reveil'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
