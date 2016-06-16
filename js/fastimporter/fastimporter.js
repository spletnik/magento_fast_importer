$jImporter = jQuery.noConflict();
$jImporter(document).ready(function(){
    // seconds -> hh:mm::ss
    function get_time(diff) {
	if(!diff) diff=0;
	function n(x) {return x<10?'0'+x:x;}
	var sec = diff%60;
	diff -= sec;
	diff /= 60;
	var min = diff%60;
	diff -= min;
	diff /= 60;
	return n(diff)+":"+n(min)+":"+n(sec);
    }

    var state = 'stopped';
    var started;
    var counter_i;
    $jImporter('#run_fast_importer').click(function() {
        $jImporter('#edit_form').append('<div id="popup"><div id="popupCont">\
<div id="fi_progress"><div class="progress-label center"><span id="fi_curr">0</span>/<span id="fi_all">0</span></div></div>\
<br />\
<span class="big fi_label">Skipped: </span><span id="fi_skipped" class="fi_right"></span><br />\
<span class="big fi_label">Deleted: </span><span id="fi_deleted" class="fi_right"></span><br />\
<span class="big fi_label">Time elapsed: </span><span id="fi_time" class="fi_right">00:00:00</span><br />\
<span class="big fi_label">Time left: </span><span id="time_left" class="fi_right">00:00:00</span><br />\
<br />\
<span class="big">Warnings<span id="warn_n"></span>:</span>\
<ul id="warnings" style="margin-left:10px;height: 200px;overflow: auto;font-size:10px;"></ul>\
<span class="big">Errors<span id="error_n"></span>:</span>\
<ul id="errors" style="margin-left:10px;height: 200px;overflow: auto;font-size:10px;"></ul>\
</div></div>');
        $jImporter('#popup').dialog({
            resizable: false,
            width: 800,
            height: 620,
            modal: true,
            show: "slide",
            showOpt: {direction: 'down'},
            title: 'Importing is in progress... Please be patient. This may take a while',
            
            overlay: {
                backgroundColor: '#000000',
                opacity: 1
            },

            close: function( event, ui ) {
                $jImporter.ajax({
                    url: end_import_url, //end_import_url definiram v block/adminhtml/fastimporter/edit.php
                    type: 'GET',
                });
                location.reload();
            }
        });
	state = 'running';
	started = new Date().getTime();
	$jImporter('#fi_progress').progressbar({value: false});
	counter_i = setInterval(function() {
	    var diff = parseInt((new Date().getTime())/1000, 10) - parseInt(started/1000, 10);
	    $jImporter('#fi_time').html(get_time(diff));
	}, 1000);
        $jImporter.ajax({
            url: run_import_url, //run_import_url definiram v block/adminhtml/fastimporter/edit.php
            type: 'GET',
            data: '',
            success: function(response) {
		clearInterval(counter_i);
                $jImporter('#popup').dialog({
                    title: "Import finished"
                });
		state='finished';
            },
        });
        setTimeout(updateAjax, 500);
        return false;
    });
    function updateAjax(){
        $jImporter.ajax({
            url: status_import_url, //status_import_url definiram v block/adminhtml/fastimporter/edit.php
            type: 'GET',
            data: '',
            success: function(response) {
		response = JSON.parse(response);
		for(var k in response) {
		    $jImporter('#fi_'+k).html(response[k]);
		}
		for(var i=0; i<response['errors'].length; i++)
		    $jImporter('#errors').append('<li>'+response['errors'][i]+'</li>');
		for(var i=0; i<response['warnings'].length; i++)
		    $jImporter('#warnings').append('<li>'+response['warnings'][i]+'</li>');
		var err_n = $jImporter('#errors').children().length;
		var warn_n = $jImporter('#warnings').children().length;
		$jImporter('#error_n').html(' (<span style="color:red">'+err_n+'</span>)');
		$jImporter('#warn_n').html(' (<span style="color:orange">'+warn_n+'</span>)');

		// Time left
		var p_left = response['all'] - response['curr'];
		var t_left = parseInt(p_left / response['speed'], 10);
		$jImporter('#time_left').html(get_time(t_left));

		console.log(response['percent']+'%');
//		$jImporter('#fi_progress .green').css('width', response['percent']+'%');
		$jImporter('#fi_progress').progressbar('value', response['percent']);

		if(state !== 'end') {
		    if(state == 'finished')
			state = 'end';
                    setTimeout(updateAjax, 10);
		}
            }
        });
    }
    $jImporter(window).keypress(function(e) {
	if(e.keyCode == "~".charCodeAt(0))
	    $jImporter('.replace-field').toggle();
    });

    // Disable remove-old
    $jImporter('#mode').change(function() {
	var val = $jImporter(this).val();
	if(['update', 'create'].indexOf(val) == -1) {
	    $jImporter('#old').val('');
	    $jImporter('#old').attr('disabled', 'disabled');
	} else {
	    $jImporter('#old').removeAttr('disabled');
	}
    });
    if(['update', 'create'].indexOf($jImporter("#mode").val()) == -1) {
	$jImporter('#old').val('');
	$jImporter('#old').attr('disabled', 'disabled');
    }
});

