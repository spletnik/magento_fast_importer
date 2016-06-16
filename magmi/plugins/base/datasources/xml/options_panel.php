<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.0.min.js"></script>
<script>
jQuery.noConflict();
var j = jQuery;
</script>
<div class="plugin_description">
This plugin enables magmi import from xml files (using Dataflow format + magmi extended columns)<br/> <b>NOT Magento 1.5 new importexport format!!</b>
</div>
<div>

<div class="xmlmode">
</div>

 <ul class="formline">
	 <li class="label">XML import mode</li>
 	<li class="value">
 	<select name="XML:importmode" id="XML:importmode">
	 	<option value="local" <?php if($this->getParam("XML:importmode","local")=="local"){?>selected="selected"<?php }?>>Local</option>
 		<option value="remote" <?php if($this->getParam("XML:importmode","local")=="remote"){?>selected="selected"<?php }?>>Remote</option>
 	</select>
 </ul>

<input type="checkbox" id="XML:apple" name="XML:apple" <?php  if($this->getParam("XML:apple",false)==true){?>checked="checked"<?php }?>> apple special
<div id="localxml" <?php if($this->getParam("XML:importmode","local")=="remote"){?> style="display:none"<?php }?>>
 <ul class="formline">
 <li class="label">XMLs base directory</li>
 <li class="value">
 <input type="text" name="XML:basedir" id="XML:basedir" value="<?php echo $this->getParam("XML:basedir","var/import")?>"></input>
 <div class="fieldinfo">Relative paths are relative to magento base directory , absolute paths will be used as is</div></li>
 </ul>
 <ul class="formline">
 <li class="label" >File to import:</li>
 <li class="value" id="xmlds_filelist">
 <?php echo $this->getOptionsPanel("xmlds_filelist.php")->getHtml(); ?>
 </li>
 </ul>
</div>

<div id="remotexml" <?php if($this->getParam("XML:importmode","local")=="local"){?> style="display:none"<?php }?>>
 <ul class="formline">
 <li class="label">Remote XML url</li>
 <li class="value">
 <input type="text" name="XML:remoteurl" id="XML:remoteurl" value="<?php echo $this->getParam("XML:remoteurl","")?>" style="width:400px"></input>
 </li>
 </ul>
 <ul class="formline">
 <li class="label">Merge url</li>
 <li class="value">
 <input type="text" name="XML:merge" id="XML:merge" value="<?php echo $this->getParam("XML:merge","")?>" style="width:400px"></input>
 </li>
 </ul>
 <ul class="formline">
 <li class="label">Merge id column</li>
 <li class="value">
 <input type="text" name="XML:id" id="XML:id" value="<?php echo $this->getParam("XML:id","")?>" style="width:400px"></input>
 </li>
 </ul>
 <input type="checkbox" id="XML:remoteauth" name="XML:remoteauth" <?php  if($this->getParam("XML:remoteauth",false)==true){?>checked="checked"<?php }?>>authentication needed
 <div id="remoteauth" <?php  if($this->getParam("XML:remoteauth",false)==false){?>style="display:none"<?php }?>>
 
 <div class="remoteuserpass">
 	<ul class="formline">
 		<li class="label">User</li>
 		<li class="value"><input type="text" name="XML:remoteuser" id="XML:remoteuser" value="<?php echo $this->getParam("XML:remoteuser","")?>"></li>
 		
 	</ul> 
 	<ul class="formline">
 		<li class="label">Password</li>
 		<li class="value"><input type="text" name="XML:remotepass" id="XML:remotepass" value="<?php echo $this->getParam("XML:remotepass","")?>"></li>
 	</ul> 
 	</div>


</div>
</div>


</div>

<div>
<h3>XML options</h3>
<span class="">Root tag:</span><input type="text" name="XML:Root" value="<?php echo $this->getParam("XML:Root")?>"></input><br />
<span class="">Product tag:</span><input type="text" name="XML:Product" value='<?php echo $this->getParam("XML:Product")?>'></input>
</div>
<h1>Deafult values:</h1>
<table id="defv">
</table>
<input type="text" id="XMLdef" name="XML:defaults" value='<?php echo ($this->getParam('XML:defaults')?$this->getParam('XML:defaults'):'{}');?>' />

<script type="text/javascript">
	handle_auth=function()
	{
		if($('XML:remoteauth').checked)
		{
			$('remoteauth').show();	
		}
		else
		{
			$('remoteauth').hide();
		}
	}
	
	$('XML:basedir').observe('blur',function()
			{
			new Ajax.Updater('xmlds_filelist','ajax_pluginconf.php',{
			parameters:{file:'xmlds_filelist.php',
						plugintype:'datasources',
					    pluginclass:'<?php echo get_class($this->_plugin)?>',
					    profile:'<?php echo $this->getConfig()->getProfile()?>',
					    'XML:basedir':$F('XML:basedir')}});
			});
	$('XML:importmode').observe('change',function()
			{
				if($F('XML:importmode')=='local')
				{
					$('localxml').show();
					$('remotexml').hide();
				}
				else
				{
					$('localxml').hide();
					$('remotexml').show();
				}
			});
	$('XML:remoteauth').observe('click',handle_auth);
	$('XML:remoteurl').observe('blur',handle_auth);

	// XML defaults
	function addNewdef(k,v) {
		j('#defv').find('tr.last').removeClass('last');
		j('#defv').append('<tr class="last"><td><input class="key" placeholder="Column" value="'+(k?k:'')+'"/></td><td><input class="value" placeholder="value" value="'+(v?v:'')+'" /></td></tr>');
		j('#defv').find('.last').find('input').blur(defaults_table);
	}
	// Restore
	var x = JSON.parse(j('#XMLdef').val());
	for(var i in x) {
		addNewdef(i, x[i]);
	}
	// Add empty row
	addNewdef();
	function defaults_table() {
		var tr = j(this).closest('tr');
		var key = j(tr).find('.key').val();
		var val = j(tr).find('.value').val();
		if(j(this).val() == '' && j(this).hasClass('key')) {
			if(!j(tr).hasClass('last'))
				tr.remove();
		} else {
			if(tr.hasClass('last'))
				addNewdef();
		}
		var map = {};
		j('#defv').find('tr').each(function() {
			var key = j(this).find('.key').val();
			var val = j(this).find('.value').val();
			if(key != '')
				map[key] = val;
		});
		j('#XMLdef').val(JSON.stringify(map));
	}
	j('#defv').find('input').blur(defaults_table);

</script>
