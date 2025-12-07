// See themes-options.js for better method. Need to convert the code below to work the same. The code below does not 
// initialize the color wheel properly with the input field color value. 
(function ($) {
	jQuery(document).ready(function () {
		
		/* controls the show/hide of the Row Name/Avatar form fields. Save some space. */
		jQuery('select#chat_row_name_avatar').on('change', function(){
			var option_selected = jQuery(this).val();

			if (option_selected == "avatar") {
				jQuery('tr#chat_row_name_color_tr').hide();
				jQuery('tr#chat_row_moderator_name_color_tr').hide();
				jQuery('tr#chat_row_avatar_width_tr').show();
				
			} else if (option_selected == "name") {
				jQuery('tr#chat_row_name_color_tr').show();
				jQuery('tr#chat_row_moderator_name_color_tr').show();
				jQuery('tr#chat_row_avatar_width_tr').hide();

			} else if (option_selected == "name-avatar") {
				jQuery('tr#chat_row_avatar_width_tr').show();
				jQuery('tr#chat_row_name_color_tr').show();
				jQuery('tr#chat_row_moderator_name_color_tr').show();
				
			} else if (option_selected == "disabled") {
				jQuery('tr#chat_row_name_color_tr').hide();
				jQuery('tr#chat_row_moderator_name_color_tr').hide();
				jQuery('tr#chat_row_avatar_width_tr').hide();
			}
		});
		
		jQuery('select#chat_users_list_show').on('change', function(){
			var option_selected = jQuery(this).val();

			if (option_selected == "avatar") {
				jQuery('tr#chat_users_list_avatar_width_tr').show();
				jQuery('tr#chat_users_list_moderator_avatar_border_color_tr').show();
				jQuery('tr#chat_users_list_user_avatar_border_color_tr').show();
				jQuery('tr#chat_users_list_avatar_border_width_tr').show();

				jQuery('tr#chat_users_list_name_color_tr').hide();
				jQuery('tr#chat_users_list_moderator_color_tr').hide();
				jQuery('tr#chat_users_list_font_family_tr').hide();
				jQuery('tr#chat_users_list_font_size_tr').hide();
				
			} else if (option_selected == "name") {
				jQuery('tr#chat_users_list_avatar_width_tr').hide();
				jQuery('tr#chat_users_list_moderator_avatar_border_color_tr').hide();
				jQuery('tr#chat_users_list_user_avatar_border_color_tr').hide();
				jQuery('tr#chat_users_list_avatar_border_width_tr').hide();


				jQuery('tr#chat_users_list_name_color_tr').show();
				jQuery('tr#chat_users_list_moderator_color_tr').show();
				jQuery('tr#chat_users_list_font_family_tr').show();
				jQuery('tr#chat_users_list_font_size_tr').show();
			} 
		});

		jQuery('select#chat_load_jscss_all').on('change', function(){
			var option_selected = jQuery(this).val();

			if (option_selected == "enabled") {
				jQuery('tr#chat_front_urls_actions_tr').show();
				jQuery('tr#chat_front_urls_list_tr').show();
				
			} else if (option_selected == "disabled") {
				jQuery('tr#chat_front_urls_actions_tr').hide();
				jQuery('tr#chat_front_urls_list_tr').hide();
			} 
		});

		jQuery('select#psource_chat_wp_admin').on('change', function(){
			var option_selected = jQuery(this).val();

			if (option_selected == "enabled") {
				jQuery('tr.psource_chat_wp_admin_display').show();
				
			} else if (option_selected == "disabled") {
				jQuery('tr.psource_chat_wp_admin_display').hide();
			} 
		});

		jQuery('select#psource_chat_wp_toolbar').on('change', function(){
			var option_selected = jQuery(this).val();

			if (option_selected == "enabled") {
				jQuery('div#psource_chat_wp_toolbar_options').show();
				
			} else if (option_selected == "disabled") {
				jQuery('div#psource_chat_wp_toolbar_options').hide();
			} 
		});

		jQuery('select#psource_chat_dashboard_friends_widget').on('change', function(){
			var option_selected = jQuery(this).val();

			if (option_selected == "enabled") {
				jQuery('div#psource_chat_dashboard_friends_widget_options').show();
				
			} else if (option_selected == "disabled") {
				jQuery('div#psource_chat_dashboard_friends_widget_options').hide();
			} 
		});

		jQuery('select#psource_chat_dashboard_widget').on('change', function(){
			var option_selected = jQuery(this).val();

			if (option_selected == "enabled") {
				jQuery('div#psource_chat_dashboard_widget_options').show();
				
			} else if (option_selected == "disabled") {
				jQuery('div#psource_chat_dashboard_widget_options').hide();
			} 
		});

		if (jQuery('#chat_tab_pane').length) {
			var $pane = jQuery('#chat_tab_pane');
			var $tabs = $pane.find('ul li');
			var $panels = $pane.children('div');

			function activateTab(idx) {
				$tabs.removeClass('ui-tabs-active ui-state-active');
				$panels.hide();
				var $tab = $tabs.eq(idx);
				var target = $tab.find('a').attr('href');
				$tab.addClass('ui-tabs-active ui-state-active');
				if (target && target.charAt(0) === '#') {
					$panels.filter(target).show();
				}
				jQuery.cookie('selected-tab', idx, { path: '/' });
			}

			var target_tab_idx = 0;
			var url_hash = window.location.hash;
			if (url_hash) {
				url_hash = url_hash.replace('_panel', '_tab');
				var $targetLi = $pane.find('ul li' + url_hash);
				if ($targetLi.length) {
					target_tab_idx = $tabs.index($targetLi);
				}
			} else {
				var cookieIdx = jQuery.cookie('selected-tab');
				if (cookieIdx !== undefined && cookieIdx !== null && cookieIdx !== '') {
					target_tab_idx = parseInt(cookieIdx, 10) || 0;
				}
			}

			$tabs.find('a').on('click', function(e){
				e.preventDefault();
				var idx = $tabs.index(jQuery(this).closest('li'));
				activateTab(idx);
			});

			activateTab(target_tab_idx);
		}
	});
})(jQuery);
