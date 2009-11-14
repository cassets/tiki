{* $Id$ *}

<form action="tiki-admin.php?page=look"  id="look" name="look" onreset="return(confirm('{tr}Cancel Edit{/tr}'))"  class="admin" method="post">
	<div class="heading input_submit_container" style="text-align: right">
		<input type="submit" name="looksetup" value="{tr}Apply{/tr}" />
		<input type="reset" name="looksetupreset" value="{tr}Reset{/tr}" />
	</div>

	{tabset name="admin_look"}
		{tab name="{tr}Theme{/tr}"}

			{preference name=site_style}
			<div class="adminoptionbox">
				{if $prefs.javascript_enabled eq 'n' or $prefs.feature_jquery eq 'n'}
					<input type="submit" name="changestyle" value="{tr}Go{/tr}" />
				{/if}
			</div>

			<div class="adminoptionbox">
				<div class="adminoptionlabel">
					<label for="general-theme-options">{tr}Theme options{/tr}:</label>
					<select name="site_style_option" id="general-theme-options" {if !$style_options}disabled="disabled"{/if}>
						{if !$style_options}
							<option value="">{tr}None{/tr}</option>
						{/if}
						{section name=ix loop=$style_options}
							<option value="{$style_options[ix]|escape}"{if $prefs.style_option eq $style_options[ix]} selected="selected"{/if}>{$style_options[ix]}</option>
						{/section}
					</select>	
				</div>
				{if $prefs.change_theme eq 'y' and ($user_prefs.theme neq '' and $prefs.site_style neq $user_prefs.theme) or ($prefs.style neq '' and $prefs.site_style neq $prefs.style)}
					{remarksbox type="warning" title="{tr}Admin{/tr}"}{tr}The "users can change theme" feature will override the theme displayed.{/tr}{/remarksbox}
				{/if}
				
				{if $prefs.site_style != $a_style}
					{remarksbox type="note" title="{tr}Note{/tr}}{tr}Theme not saved yet - click "Apply"{/tr}{/remarksbox}
				{/if}	
			</div>
							
			{if isset($thumbfile)}
				<div class="adminoptionboxchild">
					<div id="style_thumb_div">
						<img src="{$thumbfile}" alt="{tr}Theme Screenshot{/tr}" id="style_thumb" />
					</div>
				</div>
			{/if}							

			{preference name=change_theme}
			<div class="adminoptionboxchild" id="change_theme_childcontainer">
				{tr}Restrict available themes{/tr}
				<br />
				{tr}Available styles:{/tr}
				<select name="available_styles[]" multiple="multiple" size="5">
					<option value=''>{tr}All{/tr}</option>
					{section name=ix loop=$styles}
						<option value="{$styles[ix]|escape}"{if $prefs.available_styles|count gt 0 and in_array($styles[ix], $prefs.available_styles)} selected="selected"{/if}>
							{$styles[ix]}
						</option>
					{/section}
				</select>
			</div>

			{preference name=useGroupTheme}
			
			<div class="adminoptionbox">
				<div class="adminoptionlabel">
					<label for="general-slideshows">{tr}Slideshow theme{/tr}:</label> 
					<select name="slide_style" id="general-slideshows">
						{section name=ix loop=$slide_styles}
							<option value="{$slide_styles[ix]|escape}"{if $prefs.slide_style eq $slide_styles[ix]} selected="selected"{/if}>{$slide_styles[ix]}</option>
						{/section}
					</select>
				</div>
			</div>

			{preference name=feature_editcss}
			<div class="adminoptionboxchild" id="feature_editcss_childcontainer">
				{if $tiki_p_create_css eq 'y'}
					{button _text="{tr}Edit CSS{/tr}" href="tiki-edit_css.php"}
				{/if}
			</div>

			{preference name=feature_theme_control}
			<div class="adminoptionboxchild" id="feature_theme_control_childcontainer">
				{button _text="{tr}Theme Control{/tr}" href="tiki-theme_control.php"}

			</div>

			{preference name=feature_view_tpl}
			<div class="adminoptionboxchild" id="feature_view_tpl_childcontainer">
				{button href="tiki-edit_templates.php" _text="{tr}View Templates{/tr}" }
			</div>

			{preference name=feature_edit_templates}
			<div class="adminoptionboxchild" id="feature_edit_templates_childcontainer">
				{button href="tiki-edit_templates.php" _text="{tr}Edit Templates{/tr}" }
			</div>
			{preference name=log_tpl}
		{/tab}
		
		{tab name="{tr}General Layout options{/tr}"}
			{preference name=feature_custom_html_head_content}
			
			{preference name=feature_sitemycode}
			<div class="adminoptionboxchild" id="feature_sitemycode_childcontainer">
				{icon _id=information}
				<em>{tr}The Custom Site Header will display for the Admin only. Select <strong>Publish</strong> to display the content for </em>all<em> users.{/tr}</em>
				{preference name=sitemycode}
				{preference name=sitemycode_publish}
			</div>

		{preference name=feature_sitelogo}
		<div class="adminoptionboxchild" id="feature_sitelogo_childcontainer">
			<fieldset>
				<legend>{tr}Logo{/tr}</legend>
				{preference name=sitelogo_src}
				{preference name=sitelogo_bgcolor}
				{preference name=sitelogo_bgstyle}
				{preference name=sitelogo_align}
				{preference name=sitelogo_title}
				{preference name=sitelogo_alt}
			</fieldset>
					
			<fieldset>
				<legend>{tr}Title{/tr}</legend>
				{preference name=sitetitle}
				{preference name=sitesubtitle}
			</fieldset>
		</div>
		{preference name=feature_sitesearch}
		{preference name=feature_site_login}
		{preference name=feature_top_bar}
		<div class="adminoptionboxchild" id="feature_top_bar_childcontainer">
			{preference name=feature_sitemenu}
			<div class="adminoptionboxchild" id="feature_sitemenu_childcontainer">
				{preference name=feature_topbar_id_menu}
			</div>
			{preference name=feature_topbar_version}
			{preference name=feature_topbar_debug}
			{preference name=feature_topbar_custom_code}
		</div>
		
		{preference name=feature_custom_center_column_header}
		{preference name=feature_left_column}
		{preference name=feature_Right_column}
		
		{preference name=feature_breadcrumbs}
		<div class="adminoptionboxchild" id="feature_breadcrumbs_childcontainer">
			{preference name=feature_siteloclabel}
			{preference name=feature_siteloc}
			{preference name=feature_sitetitle}
			{preference name=feature_sitedesc}
		</div>
		
		{preference name=feature_bot_logo}
		<div class="adminoptionboxchild" id="feature_bot_logo_childcontainer">
			{preference name=bot_logo_code}
		</div>

		{preference name=feature_endbody_code}
		
		{preference name=feature_bot_bar}
		<div class="adminoptionboxchild" id="feature_bot_bar_childcontainer">
			{preference name=feature_bot_bar_icons}
			{preference name=feature_bot_bar_debug}
			{preference name=feature_bot_bar_rss}
			{preference name=feature_bot_bar_power_by_tw}
		</div>

		<div class="adminoptionbox">
			<fieldset>
				<legend>{tr}Site Report Bar{/tr}</legend>
				{preference name=feature_site_report}
				{preference name=feature_site_report_email}
				{preference name=feature_site_send_link}
			</fieldset>
		</div>
	{/tab}

	{tab name="{tr}Shadow layer{/tr}"}
		{preference name=feature_layoutshadows}
		<div class="adminoptionboxchild" id="feature_layoutshadows_childcontainer">
			{preference name=main_shadow_start}
			{preference name=main_shadow_end}

			{preference name=header_shadow_start}
			{preference name=header_shadow_end}

			{preference name=middle_shadow_start}
			{preference name=middle_shadow_end}

			{preference name=center_shadow_start}
			{preference name=center_shadow_end}

			{preference name=footer_shadow_start}
			{preference name=footer_shadow_end}

			{preference name=box_shadow_start}
			{preference name=box_shadow_end}
		</div>
	{/tab}

	{tab name="{tr}Pagination links{/tr}"}

		{preference name=maxRecords}
		{preference name=nextprev_pagination}
		{preference name=direct_pagination}
		<div class="adminoptionboxchild" id="direct_pagination_childcontainer">
			{preference name=direct_pagination_max_middle_links}
			{preference name=direct_pagination_max_ending_links}
		</div>

		{preference name=pagination_firstlast}
		{preference name=pagination_fastmove_links}
		{preference name=pagination_hide_if_one_page}
		{preference name=pagination_icons}
	{/tab}
		
	{tab name="{tr}UI Effects{/tr}"}
		<div class="adminoptionbox">	
			<fieldset class="admin">
				<legend>{tr}JQuery plugins and add-ons{/tr}</legend>	
				{if $prefs.feature_jquery eq 'n'}
					{remarksbox type="warning" title="{tr}Warning{/tr}"}
						{tr}Requires jquery feature{/tr}</em>{icon _id="arrow_right" href="tiki-admin.php?page=features"}{/remarksbox}
				{/if}

				{preference name=feature_jquery_tooltips}
				{preference name=feature_jquery_autocomplete}
				{preference name=feature_jquery_superfish}
				{preference name=feature_jquery_reflection}
				{preference name=feature_jquery_ui}
				{preference name=feature_jquery_ui_theme}

				<div class="adminoptionbox">
					<div class="adminoptionlabel">
						{icon _id=information} <em>{tr}For future use{/tr}:</em>
						<div class="adminoptionboxchild">	
							{preference name=feature_jquery_cycle}
							{preference name=feature_jquery_sheet}
							{preference name=feature_jquery_tablesorter}
						</div>
					</div>
				</div>
			</fieldset>
		</div>

		<div class="adminoptionbox">
			<fieldset class="admin">
				<legend>{tr}Standard UI effects{/tr}</legend>
				{preference name=jquery_effect}
				{preference name=jquery_effect_speed}
				{preference name=jquery_effect_direction}
			</fieldset>
		</div>
			
			<div class="adminoptionbox">			
				<fieldset class="admin">
					<legend>{tr}Tab UI effects{/tr}</legend>
					{preference name=jquery_effect_tabs}
					{preference name=jquery_effect_speed}
					{preference name=jquery_effect_tabs_direction}
			</fieldset>
		</div>

		<fieldset>
			<legend>{tr}Other{/tr}</legend>
			<div class="admin featurelist">
				{preference name=feature_shadowbox}
				{preference name=feature_jscalendar}
			</div>
		</fieldset>		
	{/tab}

	{tab name="{tr}Miscellaneous{/tr}"}
		{preference name=feature_tabs}
		{preference name=layout_section}
		{if $prefs.layout_section eq 'y'}
			{button _text="{tr}Admin layout per section{/tr}" href="tiki-admin_layout.php"}
		{/if}

		{preference name=feature_iepngfix}
		<div class="adminoptionboxchild" id="feature_iepngfix_childcontainer">
			{preference name=iepngfix_selectors}
			{preference name=iepngfix_elements}
		</div>

		<div class="adminoptionbox">
			<fieldset>
				<legend>{tr}Favicon{/tr}</legend>
				{preference name=site_favicon}
				{preference name=site_favicon_type}
			</fieldset>
		</div>

		<div class="adminoptionbox">
			<fieldset class="admin">
				<legend>{tr}Context Menus{/tr} (<em>{tr}Currently used in File Galleries only{/tr}.</em>)</legend>
				{preference name=use_context_menu_icon}
				{preference name=use_context_menu_text}
			</fieldset>
		</div>

		<fieldset>
			<legend>{tr}Separators{/tr}</legend>
			{preference name=site_crumb_seper}
			<div class="adminoptionboxchild">
				<em>{tr}Examples{/tr}: &nbsp; &raquo; &nbsp; / &nbsp; &gt; &nbsp; : &nbsp; -> &nbsp; &#8594;</em>
			</div>

			{preference name=site_nav_seper}
			<div class="adminoptionboxchild">
				<em>{tr}Examples{/tr}: &nbsp; | &nbsp; / &nbsp; &brvbar; &nbsp; :</em>
			</div>
		</fieldset>
	{/tab}
{/tabset}

<div class="input_submit_container clear" style="text-align: center">
	<input type="submit" name="looksetup" value="{tr}Apply{/tr}" />
</div>
</form>
