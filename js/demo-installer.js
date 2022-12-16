jQuery( function ( $ ) {

// fixme: debug
	/* for debuging database
$.post(ajaxurl, {
	action: 'sneeit_demo_installer',
	sub_action: 'install_start',			
	folder: 'debug'	// place this folder inside sneeit_demo with sneeit-demo-data.txt inside
});
$('#sneeit-demo-installer .demo .demo-actions .button-primary').click(function(){
	$.post(ajaxurl, {
		action: 'sneeit_demo_installer',
		sub_action: 'install_start',			
		folder: 'debug'	// place this folder inside sneeit_demo with sneeit-demo-data.txt inside
	});
});
return;
/**/


/* work around to fix the problem with some hostings which have 
 * ajax security to not allow access system folders
 * in this case is: /tmp/ folder 
 * this must be sync with demo-installer php files
 * */
var Sneeit_Safe_Forward_Slash = 'sneeit-safe-forward-slash';
/*
SNEEIT DEMO INSTALLER
*********************
 ---- BUILDER ------
*********************
*/
	var Sneeit_Demo_Installer_Build_Building = false;
	var Sneeit_Demo_Installer_Build_Files = new Array();
	var Sneeit_Demo_Installer_Build_Files_String = '';
	var Sneeit_Demo_Installer_Build_Folder = '';	
	
	function sneeit_demo_installer_data_is_error(data, update_status, action_selector) {
		if (typeof(update_status) == 'undefined') {
			update_status = true;
		}
		try {
		    data = $.parseJSON(data);
		} catch (e) {
		    // not JSON
		    if (update_status) {
		    	sneeit_demo_installer_update_process_status(
		    		100, 
		    		sneeit_demo_installer.text['Unknown error'], 
		    		true, 
		    		action_selector);	
		    }  
		    return true;
		}
		if (data == null) {
			if (update_status) {
				sneeit_demo_installer_update_process_status(
					100, 
					sneeit_demo_installer.text['Unknown error'], 
					true,
					action_selector);
			}
			return true;
		}
		if ('error' in data) {
			if (update_status) {
				sneeit_demo_installer_update_process_status(
					100, 
					data['error'], 
					true, 
					action_selector);
			} else {
				if (data['error']) {
					return data['error'];
				}
			}
			return true;
		}
		return false; 
	}
	function sneeit_demo_installer_update_process_status(percent, status, error, action_selector) {
		if (percent < 0) {
			percent = 0;
		}
		if (percent > 100) {
			percent == 100;
		}
		if (typeof(error) == 'undefined') {
			error = false;
		}
		
		$(action_selector).find('.demo-process-percent').html(percent.toFixed(2)+'%');
		$(action_selector).find('.demo-process-overlay')
			.stop()
			.animate({'height': (percent+'%')}, 500);
		$(action_selector).find('.demo-process-message').html(status);

		if (error) {
			$(action_selector).addClass('error');
		} else if (percent == 100)	{
			$(action_selector).addClass('finished');
		}
	}

/*
SNEEIT DEMO BUILDER
*************************
 ---- B U I L D E R ----
*************************
*/
	/**************
	 * BUILD : 01*/
	function sneeit_demo_installer_build_database() {		
		sneeit_demo_installer_update_process_status(0, sneeit_demo_installer.text['Building database file'], false, '#build-demo-process');
		$.post(ajaxurl, {
			action: 'sneeit_demo_installer',
			sub_action: 'build_database'			
		}).done(function( data ) {					
			if (sneeit_demo_installer_data_is_error(data, true, '#build-demo-process')) {				
				return;
			}			
			data = $.parseJSON(data);			
			Sneeit_Demo_Installer_Build_Folder = data['folder'];
			
			sneeit_demo_installer_build_list_media_files();
		});
	}
	
	/**************
	 * BUILD : 02*/
	function sneeit_demo_installer_build_list_media_files() {
		sneeit_demo_installer_update_process_status(10, sneeit_demo_installer.text['Listing files'], false, '#build-demo-process');		
		$.post(ajaxurl, {
			action: 'sneeit_demo_installer',
			sub_action: 'build_list_files',
			folder: Sneeit_Demo_Installer_Build_Folder			
		}).done(function( data ) {			
			if (sneeit_demo_installer_data_is_error(data, true, '#build-demo-process')) {
				return;
			}
			Sneeit_Demo_Installer_Build_Files_String = data;
			Sneeit_Demo_Installer_Build_Files = $.parseJSON(data);
			sneeit_demo_installer_build_media_files(0);
		});
	}
	
	/**************
	 * BUILD : 03*/
	function sneeit_demo_installer_build_media_files(latest_id) {		
		percent = 20;
		if (latest_id) { // count percent processed file in all files in list
			percent = (Sneeit_Demo_Installer_Build_Files.length - latest_id) / Sneeit_Demo_Installer_Build_Files.length;
			percent = 80 * (1- percent) + 20;
		}
		
		sneeit_demo_installer_update_process_status(percent, sneeit_demo_installer.text['Building Files'], false, '#build-demo-process');
		
		$.post(ajaxurl, {
			action: 'sneeit_demo_installer',
			sub_action: 'build_files',
			files: Sneeit_Demo_Installer_Build_Files_String,
			latest: latest_id,
			folder: Sneeit_Demo_Installer_Build_Folder
		}).done(function( data ) {			
			if (sneeit_demo_installer_data_is_error(data, true, '#build-demo-process')) {
				return;
			}
			data = $.parseJSON(data);
			latest_id = data['latest'];
			if (latest_id == Sneeit_Demo_Installer_Build_Files.length) {
				sneeit_demo_installer_build_finalize();
			} else {
				sneeit_demo_installer_build_media_files(latest_id);
			}
		});
	}
	
	/**************
	 * BUILD : 04*/
	function sneeit_demo_installer_build_finalize() {
		sneeit_demo_installer_update_process_status(100, sneeit_demo_installer.text['Built successfully'], false, '#build-demo-process');
		$('#build-demo-process .demo-process-percent').html(sneeit_demo_installer.text['Done']);
		Sneeit_Demo_Installer_Build_Building = false;
		sneeit_demo_installer_explore(Sneeit_Demo_Installer_Build_Folder);
	}

/*
SNEEIT DEMO SUPPORTER
*****************************
 ---- S U P P O R T E R ----
*****************************
*/
	/******************
	 * SUPPORTER : 01*/
	function sneeit_demo_installer_delete(folder_name) {		
		$.post(ajaxurl, {
			action: 'sneeit_demo_installer',
			sub_action: 'delete',
			folder: folder_name
		}).done(function( data ) {
			var ret = sneeit_demo_installer_data_is_error(data, false);
			if (ret) {
				if (typeof(ret) == 'String') {
					$('#sneeit-demo-installer .explored').html(ret);
				} else {
					$('#sneeit-demo-installer .explored').html(
						sneeit_demo_installer.text['Can not delete the demo data']);
				}
				return;
			}
			sneeit_demo_installer_explore();
		});
	}
	
	/******************
	 * SUPPORTER : 02*/
	function sneeit_demo_installer_explore(folder_name) {
		$('#sneeit-demo-installer .explored').show();
		$('#sneeit-demo-installer .explored').addClass('loading');
		$('#sneeit-demo-installer .explored').html('<i class="fa fa-cog fa-spin explored-loading-icon"></i>');

		if (typeof(folder_name) == 'undefined') {
			folder_name = '';
		}
		$.post(ajaxurl, {
			action: 'sneeit_demo_installer',
			sub_action: 'explore'
		}).done(function( data ) {
			var ret = sneeit_demo_installer_data_is_error(data, false);
			if (ret) {
				if (typeof(ret) == 'String') {
					$('#sneeit-demo-installer .explored').html(ret);
				} else {
					$('#sneeit-demo-installer .explored').html(
						sneeit_demo_installer.text['Can not explore the demo data']);
				}
				
				return;
			}
			data = $.parseJSON(data);			
			if (data.length == 0) {
				$('#sneeit-demo-installer .explored').html(sneeit_demo_installer.text['Not found any built demo files']);
				if (!Sneeit_Demo_Installer_Build_Building) {					
					$('#build-demo-process').css('display', 'none').removeClass('finished');
					$('#build-demo').css('display', 'block');
				}
				return;
			}
			
			var html = '';
			for (var i = data.length - 1; i >= 0; i-=1) {	
				
				html += 
'<div class="item" data-folder="'+data[i]['folder']+'">'+
	'<span class="col name">'+
		'<strong>'+
			data[i]['folder']+
		'</strong>'+
		(data[i]['name'] != data[i]['folder']? ' ('+data[i]['name']+')':'')+
	'</span>'+
	'<a class="col delete" href="javascript:void(0)" data-folder="'+data[i]['folder']+'">'+
		sneeit_demo_installer.text['Delete']+
	'</a>'+
	
	/* COMMING SOON: Allow restore from custom built demo folder */
	/* '<a class="col restore" href="javascript:void(0)">'+sneeit_demo_installer.text['Restore Demo']+'</a>'+ */
	
	'<a class="col get" href="javascript:void(0)">'+sneeit_demo_installer.text['Get Code']+'</a>'+
	'<div class="clear"></div>'+
	'<div class="content">'+
	'<p>'+sneeit_demo_installer.text['Download those below files and upload to some where (example: Google drive, Drop Box).']+'</p>'+			
	'<ul>';				
				for (var j = 0; j < data[i]['files'].length; j++) {
					html += 
		'<li>'+
			'<a href="'+data[i]['files'][j]['link']+'">'+
				data[i]['files'][j]['name']+
			'</a>'+
		'</li>';
				}
				

				html +=		
	'</ul>'+
	
	'<p>'+sneeit_demo_installer.text['copy_code']+'</p>'+
				
	'<textarea class="code">'+
"	'your-demo-id' => array(\n"+
"		'name' => __('"+sneeit_demo_installer.text['Your demo name']+"', 'your-theme-slug'), \n"+
"		'screenshot' => 'link-to-your-screenshot-image',\n"+
"		'files' => array(\n";
				for (var j = 0; j < data[i]['files'].length; j++) {
						html += 
"			'direct-download-link-to-"+data[i]['files'][j]['name']+"',\n";
				}
				html +=					
"		) \n"+
"	)\n"+
		'</textarea>'+
	'</div>'+
'</div>';
			}


			/* finished explored demo folders */
			$('#sneeit-demo-installer .explored').html(html).removeClass('loading');
			if (folder_name) {
				$('#sneeit-demo-installer .explored .item[data-folder="'+folder_name+'"] .content').slideDown();
			}
			
			/* restore demo when click [restore] link*/
			$('#sneeit-demo-installer .explored .item .restore').click(function () {
				if (!confirm(sneeit_demo_installer.text['Please make sure your site is very NEW or just a TEST site, because demo data will ERASE all your database. Are you sure to install demo?'])) {
					return;
				}
				
				// let's go
				$('#sneeit-demo-installer .explored').slideUp();				
			});
			
			/* show declare code when click [get] link*/
			$('#sneeit-demo-installer .explored .item .get').click(function () {
				$('#sneeit-demo-installer .explored .item .content').slideUp();
				$(this).parent().find('.content').each(function () {
					if ($(this).css('display') == 'none') {
						$(this).stop().slideDown();
					} else {
						$(this).stop().slideUp();
					}
				});
			});

			/* delete demo folder and files when click [delete] link */
			$('#sneeit-demo-installer .explored .item .delete').click(function () {
				var folder_name = $(this).attr('data-folder');
				sneeit_demo_installer_delete(folder_name);				
			});
		});
	}


/*
SNEEIT DEMO INSTALLER
*****************************
 ---- I N S T A L L E R ----
*****************************
*/
	var Sneeit_Demo_Installer_Install_Folder = '';	
	var Sneeit_Demo_Installer_Install_Files = new Array();
	var Sneeit_Demo_Installer_Install_Links = new Array();
	var Sneeit_Demo_Installer_Install_Selector = null;

	/****************************************
	 * INSTALLER : 01 - create temp folder */
	function sneeit_demo_installer_install_folder() {		
		sneeit_demo_installer_update_process_status(0, sneeit_demo_installer.text['Creating download folder'], false, Sneeit_Demo_Installer_Install_Selector);
		
		$.post(ajaxurl, {
			action: 'sneeit_demo_installer',
			sub_action: 'install_folder',
			folder: Sneeit_Demo_Installer_Install_Folder
		}).done(function( data ) {
			if (sneeit_demo_installer_data_is_error(data, true, Sneeit_Demo_Installer_Install_Selector)) {
				return;
			}

			sneeit_demo_installer_install_download(0);
		});
	}

	/***************************************************
	 * INSTALLER : 02 - download files to temp folder */
	var start_percent = Math.random() + 4; /* limited at 5% */
	var current_percent = start_percent;
	function sneeit_demo_installer_install_download(latest_id) {		
		latest_id = Number(latest_id);
		if (latest_id) { 
			// count percent processed file in all files in list
			current_percent = (Sneeit_Demo_Installer_Install_Links.length - latest_id) / Sneeit_Demo_Installer_Install_Links.length;
			// limit at 45%
			// percent range for this is 40% from start_percent 
			// because start percent will never higher than 5
			// So this will also never higher than 45%
			current_percent = 40 * (1 - current_percent) + start_percent; 		
		}

		sneeit_demo_installer_update_process_status(current_percent, sneeit_demo_installer.text['Downloading files'], false, Sneeit_Demo_Installer_Install_Selector);

		$.post(ajaxurl, {
			action: 'sneeit_demo_installer',
			sub_action: 'install_download',
			folder: Sneeit_Demo_Installer_Install_Folder,
			latest: latest_id,
			links: Sneeit_Demo_Installer_Install_Links
		}).done(function( data ) {
			if (sneeit_demo_installer_data_is_error(data, true, Sneeit_Demo_Installer_Install_Selector)) {
				return;
			}			
			data = $.parseJSON(data);
			Sneeit_Demo_Installer_Install_Files.push(data['file']);

			latest_id = Number(data['latest']);
			if (latest_id == Sneeit_Demo_Installer_Install_Links.length) {
				// point to next step
				start_percent = current_percent;
				sneeit_demo_installer_install_extract(0);
			} else {
				// continue loop
				sneeit_demo_installer_install_download(latest_id);
			}
		});
	}
	
	/******************************************************
	 * INSTALLER : 03 - extract files in template folder */
	function sneeit_demo_installer_install_extract(latest_id) {
		latest_id = Number(latest_id);
		if (latest_id) { 
			// count percent processed file in all files in list
			current_percent = (Sneeit_Demo_Installer_Install_Files.length - latest_id) / Sneeit_Demo_Installer_Install_Files.length;
			// limit at 75%
			// percent range for this is 30% from start_percent 
			// because previous percent will never higher than 45
			// So this will also never higher than 75%
			current_percent = 30 * (1 - current_percent) + start_percent; 		
		}

		sneeit_demo_installer_update_process_status(current_percent, sneeit_demo_installer.text['Extracting files'], false, Sneeit_Demo_Installer_Install_Selector);
		
		var files = Sneeit_Demo_Installer_Install_Files;
		for (var i = 0; i < files.length; i++) {
			files[i] = files[i].replaceAll('/', Sneeit_Safe_Forward_Slash);
		}
		$.post(ajaxurl, {
			action: 'sneeit_demo_installer',
			sub_action: 'install_extract',
			latest: latest_id,
			files: Sneeit_Demo_Installer_Install_Files,
			folder: Sneeit_Demo_Installer_Install_Folder
		}).done(function( data ) {
			if (sneeit_demo_installer_data_is_error(data, true, Sneeit_Demo_Installer_Install_Selector)) {
				return;
			}
			
			data = $.parseJSON(data);
			latest_id = Number(data['latest']);
			if (latest_id == Sneeit_Demo_Installer_Install_Files.length) {
				// point to next step
				start_percent = current_percent;
				sneeit_demo_installer_install_list();
			} else {
				// continue loop
				sneeit_demo_installer_install_extract(latest_id);
			}
		});
	}
	
	/************************************************************
	 * INSTALLER : 04 - list number of files in media structure */
	var sneeit_demo_installer_num_file = 0;
	function sneeit_demo_installer_install_list() {		
		current_percent = start_percent + Math.random()+4; // prev 75%, so limted at 80%
		sneeit_demo_installer_update_process_status(current_percent, sneeit_demo_installer.text['Listing files'], false, Sneeit_Demo_Installer_Install_Selector);

		$.post(ajaxurl, {
			action: 'sneeit_demo_installer',
			sub_action: 'install_list',			
			folder: Sneeit_Demo_Installer_Install_Folder
		}).done(function( data ) {
			if (sneeit_demo_installer_data_is_error(data, true, Sneeit_Demo_Installer_Install_Selector)) {
				return;
			}
			
			data = $.parseJSON(data);
			sneeit_demo_installer_num_file = Number(data['num']);
			start_percent = current_percent;
			sneeit_demo_installer_install_move(0, 0);			
		});
	}
	
	
	/****************************************************
	 * INSTALLER : 05 - move files to "uploads" folder */	
	function sneeit_demo_installer_install_move(latest_id, fail_num) {		
		latest_id = Number(latest_id);
		if (latest_id) { 
			// count percent processed file in all files in list
			current_percent = (sneeit_demo_installer_num_file - latest_id ) / sneeit_demo_installer_num_file;
			
			// percent range for this is 15% from start_percent 
			// because previous percent will never higher than 80%
			// So this will also never higher than 95%
			if (current_percent < 0) {
				current_percent = 0;
			}
			current_percent = 15 * (1 - current_percent) + start_percent; 		
		}

		sneeit_demo_installer_update_process_status(current_percent, sneeit_demo_installer.text['Moving files'], false, Sneeit_Demo_Installer_Install_Selector);

		$.post(ajaxurl, {
			action: 'sneeit_demo_installer',
			sub_action: 'install_move',
			folder: Sneeit_Demo_Installer_Install_Folder,
			files: Sneeit_Demo_Installer_Install_Files,
			fail: fail_num,
			latest: latest_id
		}).done(function( data ) {
			if (sneeit_demo_installer_data_is_error(data, true, Sneeit_Demo_Installer_Install_Selector)) {
				return;
			}			
			data = $.parseJSON(data);			

			latest_id = data['latest'];
			fail_num = data['fail'];			
			
			if ('done' == latest_id) {
				// point to next step
				start_percent = current_percent;
				sneeit_demo_installer_install_start();
			} else {
				// continue loop
				sneeit_demo_installer_install_move(latest_id, fail_num);
			}
		});
	}

	/*************************************
	 * INSTALLER : 06 - write to database*/
	function sneeit_demo_installer_install_start() {
		current_percent = start_percent + Math.random()+4; // limited at 99%
		sneeit_demo_installer_update_process_status(current_percent, sneeit_demo_installer.text['Installing files'], false, Sneeit_Demo_Installer_Install_Selector);

		$.post(ajaxurl, {
			action: 'sneeit_demo_installer',
			sub_action: 'install_start',			
			folder: Sneeit_Demo_Installer_Install_Folder
		}).done(function( data ) {
			if (sneeit_demo_installer_data_is_error(data, true, Sneeit_Demo_Installer_Install_Selector)) {
				return;
			}

			sneeit_demo_installer_install_finalize();
		});
	}

	/**************************************************
	 * INSTALLER : 07 - delete temp folder and finish */
	function sneeit_demo_installer_install_finalize() {		
		sneeit_demo_installer_update_process_status(100, sneeit_demo_installer.text['Installed successfully'], false, Sneeit_Demo_Installer_Install_Selector);

		$(Sneeit_Demo_Installer_Install_Selector).find('.demo-process-percent').html(sneeit_demo_installer.text['Done']);
		$(Sneeit_Demo_Installer_Install_Selector).addClass('finished');
		Sneeit_Demo_Installer_Build_Building = false;

		sneeit_demo_installer_explore();
	}
	
	
/*
 * 
 * * * * * * * * *
*********************
 ---- ACTIONS --------
*********************
 * * * * * * * * *
 * 
*/	
	// explore when first load page
	sneeit_demo_installer_explore();

	// when click build demo button
	$('#build-demo').click(function () {
		if (Sneeit_Demo_Installer_Build_Building) {
			alert(sneeit_demo_installer.text['A process is running!']);
			return;
		}
		if (!confirm(sneeit_demo_installer.text['This function is for DEVELOPERS only to help them building demo data to integrate to their themes. Are you sure to BUILD yours?'])) {
			return;
		}
		Sneeit_Demo_Installer_Build_Building = true;
		$(this).css('display', 'none');
		$('#build-demo-process').css('display', 'block');
		$('#sneeit-demo-installer .explored').slideUp(500);
		sneeit_demo_installer_build_database();
	});


	// when click install demo
	$('.button-start-demo-install').click(function () {
		if (Sneeit_Demo_Installer_Build_Building) {
			alert(sneeit_demo_installer.text['A process is running!']);
			return;
		}
		if (!confirm(sneeit_demo_installer.text['Please make sure your site is very NEW or just a TEST site, because demo data will ERASE all your database. Are you sure to install demo?'])) {
			return;
		}
		Sneeit_Demo_Installer_Build_Building = true;

		$(this).parents('.demo-main').hide();
		Sneeit_Demo_Installer_Install_Selector = $(this).parents('.demo').find('.demo-process');
		Sneeit_Demo_Installer_Install_Selector.show();

		// get the parameters
		Sneeit_Demo_Installer_Install_Folder = $(this).attr('data-id');
		
		var demo_list = sneeit_demo_installer.demo_list;
		

		Sneeit_Demo_Installer_Install_Links = demo_list[Sneeit_Demo_Installer_Install_Folder]['files'];
		
		sneeit_demo_installer_install_folder();

	});
});