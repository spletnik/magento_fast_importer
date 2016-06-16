<div class="plugin_description">
This plugin remove not imported products.
</div>

<div class="formline">
<?php $action=$this->getParam("ROLD:mode",'')?>
<?php print "ACTION: '$action'\n"; ?>
<span>Action:</span><select name="ROLD:mode">
	<option value="0" <?php if($action===''){?>selected="selected"<?php }?>>-</option>
	<option value="disable" <?php if($action==='disable'){?>selected="selected"<?php }?>>Disable</option>
	<option value="remove" <?php if($action==='remove'){?>selected="selected"<?php }?>>Remove</option>
</select>
</div>

<div class="formline">
<span class="label">Import id:</span>
<span class="value"><input type="text" name="ROLD:import_id" maxlength=3 size=3" value="<?php echo $this->getParam("ROLD:import_id","")?>"></input></span>
</div>
