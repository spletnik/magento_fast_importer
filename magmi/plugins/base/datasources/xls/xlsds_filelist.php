<?php 
	$files=$this->getXLSList();
if($files!==false && count($files)>0){?>
<select name="XLS:filename" id="xlsfile">
	<?php foreach($files as $fname){ ?>
		<option <?php if($fname==$this->getParam("XLS:filename")){?>selected=selected<?php }?> value="<?php echo $fname?>"><?php echo basename($fname)?></option>
	<?php }?>
</select>
<a id='xlsdl' href="./download_file.php?file=<?php $this->getParam("XLS:filename")?>">Download xls</a>
<script type="text/javascript">
 $('xlsdl').observe('click',function(el){
	    var fval=$('xlsfile').value;
 		$('xlsdl').href="./download_file.php?file="+fval;}
	);
</script><?php } else {?>
<span> No xls files found in <?php echo $this->getScanDir(false)?></span>
	<?php }?>
