{if $prefs.user_register_prettytracker eq 'y' and $prefs.user_register_prettytracker_tpl and $prefs.socialnetworks_user_firstlogin != 'y'}
	<input type="text" name="name" id="name" />
	&nbsp;<strong class='mandatory_star'>*</strong>
{else}
	<tr>
		<td>
			<label for="name">{if $prefs.login_is_email eq 'y'}{tr}Email:{/tr}{else}{tr}Username:{/tr}{/if}</label>
			{if $trackerEditFormId}&nbsp;<strong class='mandatory_star'>*</strong>&nbsp;{/if}
		</td>
		<td>
			<input type="text" name="name" id="name" {if 0 and $prefs.feature_ajax eq 'y' && !$userTrackerData} onkeyup="return check_name()" onblur="return check_name()"{/if} />
			{if 0 and $prefs.feature_ajax eq 'y'}{* AJAX_TODO *}
				<span id="ajax_msg_name" style="vertical-align: middle;"></span>
			{/if}
			{if $prefs.login_is_email eq 'y'}
				<em>{tr}Use your email as login{/tr}</em>.
			{else}
				{if $prefs.min_username_length > 1}
					<div class="highlight">
						<em>{tr}Minimum {$prefs.min_username_length} characters long{/tr}</em>
					</div>
				{/if}
				{if $prefs.lowercase_username eq 'y'}
					<div class="highlight"><em>{tr}Lowercase only{/tr}</em></div>
				{/if}
			{/if}
		</td>
	</tr>
{/if}