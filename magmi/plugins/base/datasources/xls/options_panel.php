<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.0.min.js"></script>
<script>
jQuery.noConflict();
var j = jQuery;
</script>
<div class="plugin_description">
This plugin enables magmi import from xls files.
</div>
<div>

<div class="xlsmode">
</div>

 <ul class="formline">
	 <li class="label">XLS import mode</li>
 	<li class="value">
 	<select name="XLS:importmode" id="XLS:importmode">
	 	<option value="local" <?php if($this->getParam("XLS:importmode","local")=="local"){?>selected="selected"<?php }?>>Local</option>
 		<option value="remote" <?php if($this->getParam("XLS:importmode","local")=="remote"){?>selected="selected"<?php }?>>Remote</option>
 	</select>
 </ul>

<input type="checkbox" id="XLS:firstrow" name="XLS:firstrow" <?php  if($this->getParam("XLS:firstrow",false)){?>checked="checked"<?php }?>> First row has column names
<div id="localxls" <?php if($this->getParam("XLS:importmode","local")=="remote"){?> style="display:none"<?php }?>>
 <ul class="formline">
 <li class="label">XLSs base directory</li>
 <li class="value">
 <input type="text" name="XLS:basedir" id="XLS:basedir" value="<?php echo $this->getParam("XLS:basedir","var/import")?>"></input>
 <div class="fieldinfo">Relative paths are relative to magento base directory , absolute paths will be used as is</div></li>
 </ul>
 <ul class="formline">
 <li class="label" >File to import:</li>
 <li class="value" id="xlsds_filelist">
 <?php echo $this->getOptionsPanel("xlsds_filelist.php")->getHtml(); ?>
 </li>
 </ul>
</div>

<div id="remotexls" <?php if($this->getParam("XLS:importmode","local")=="local"){?> style="display:none"<?php }?>>
 <ul class="formline">
 <li class="label">Remote XLS url</li>
 <li class="value">
 <input type="text" name="XLS:remoteurl" id="XLS:remoteurl" value="<?php echo $this->getParam("XLS:remoteurl","")?>" style="width:400px"></input>
 </li>
 </ul>
 <ul class="formline">
 <li class="label">Merge url</li>
 <li class="value">
 <input type="text" name="XLS:merge" id="XLS:merge" value="<?php echo $this->getParam("XLS:merge","")?>" style="width:400px"></input>
 </li>
 </ul>
 <ul class="formline">
 <li class="label">Merge id column</li>
 <li class="value">
 <input type="text" name="XLS:id" id="XLS:id" value="<?php echo $this->getParam("XLS:id","")?>" style="width:400px"></input>
 </li>
 </ul>
	<input type="checkbox" id="XLS:remoteauth" name="XLS:remoteauth" <?php  if($this->getParam("XLS:remoteauth",false)==true){?>checked="checked"<?php }?>>authentication needed
</div>


</div>

<script type="text/javascript">
	$('XLS:basedir').observe('blur',function()
			{
			new Ajax.Updater('xlsds_filelist','ajax_pluginconf.php',{
			parameters:{file:'xlsds_filelist.php',
						plugintype:'datasources',
					    pluginclass:'<?php echo get_class($this->_plugin)?>',
					    profile:'<?php echo $this->getConfig()->getProfile()?>',
					    'XLS:basedir':$F('XLS:basedir')}});
			});
	$('XLS:importmode').observe('change',function()
			{
				if($F('XLS:importmode')=='local')
				{
					$('localxls').show();
					$('remotexls').hide();
				}
				else
				{
					$('localxls').hide();
					$('remotexls').show();
				}
			});
	$('XLS:remoteurl').observe('blur',handle_auth);
</script>
