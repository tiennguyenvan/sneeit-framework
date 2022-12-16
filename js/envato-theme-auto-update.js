jQuery( function ( $ ) {
	var sneeit_envato_theme_auto_update_div = $( '#' + sneeit_envato_theme_auto_update.app_name + ' .content' );
	function sneeit_envato_theme_auto_update_show_message( text ) {
		
		sneeit_envato_theme_auto_update_finish_messages();
		
		sneeit_envato_theme_auto_update_div.append(
			'<p><i class="fa fa-spinner fa-spin"></i> '+ text+'</p>'
		);
	}
	function sneeit_envato_theme_auto_update_finish_messages() {
		sneeit_envato_theme_auto_update_div.
			find('.fa-spinner').
			removeClass('fa-spinner').
			removeClass('fa-spin').
			addClass('fa-check');
	}
	function sneeit_envato_theme_auto_update_show_error( text ) {
		sneeit_envato_theme_auto_update_div.append(
			'<div class="error settings-error notice is-dismissible"><p><strong>'+ text+'</strong></p></div>'
		);		
	}
	function sneeit_envato_theme_auto_update_core_queue( url ) {
		
		sneeit_envato_theme_auto_update_show_message( sneeit_envato_theme_auto_update.text.core_queue );		
		
		$.post(ajaxurl, {
			action: 'sneeit_envato_theme_auto_update',
			sub_action: 'core_queue',
			theme: sneeit_envato_theme_auto_update.theme,
			item_id: sneeit_envato_theme_auto_update.item_id,
			version: sneeit_envato_theme_auto_update.version,
			url: url
		}).done(function( data ) {
			if ( data.indexOf( '*** Error: ' ) != -1 ) {
				sneeit_envato_theme_auto_update_show_error( data );
				return;
			}
			
			sneeit_envato_theme_auto_update_show_message( sneeit_envato_theme_auto_update.text.redirect_link );
			window.location.href = data.replace( /&amp;/gi, '&' );
		});
	}
	
	function sneeit_envato_theme_auto_update_download_file( url ) {
		
		sneeit_envato_theme_auto_update_show_message( sneeit_envato_theme_auto_update.text.download_file );		
		
		$.ajax({
			url: ajaxurl, 
			method: 'POST',
			data: {
				action: 'sneeit_envato_theme_auto_update',
				sub_action: 'download_file',
				url: url
			},
			timeout: 300000,
			success : function( data ) {
				if ( data.indexOf( '*** Error: ' ) != -1 ) {
					sneeit_envato_theme_auto_update_show_error( data );
					return;
				}
			
				sneeit_envato_theme_auto_update_core_queue( data );
			}
		});
	}
	
	
	function sneeit_envato_theme_auto_update_get_download_link() {
		
		sneeit_envato_theme_auto_update_show_message( sneeit_envato_theme_auto_update.text.get_download_link );
		
		$.post(ajaxurl, {
			action: 'sneeit_envato_theme_auto_update',
			sub_action: 'get_download_link',
			theme: sneeit_envato_theme_auto_update.theme,
			item_id: sneeit_envato_theme_auto_update.item_id
		}).done(function( data ) {
			if ( data.indexOf( '*** Error: ' ) != -1 ) {
				sneeit_envato_theme_auto_update_show_error( data );
				return;
			}
			
			sneeit_envato_theme_auto_update_download_file( data );
			
		});
	}
	
	sneeit_envato_theme_auto_update_get_download_link();
});
