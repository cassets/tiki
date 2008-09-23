{* $Id$ *}
{popup_init src="lib/overlib.js"}

{title help="Users+Management" admpage="login"}{tr}Admin Users{/tr}{/title}

<div class="navbar">
{if $tiki_p_admin eq 'y'} {* only full admins can manage groups, not tiki_p_admin_users *}
<span class="button2"><a href="tiki-admingroups.php">{tr}Admin Groups{/tr}</a></span>
{/if}
<span class="button2"><a href="tiki-adminusers.php">{tr}Admin Users{/tr}</a></span>
{if $userinfo.userId}
<span class="button2"><a href="tiki-adminusers.php?add=1">{tr}Add a New User{/tr}</a></span>
{/if}
</div>

{if $prefs.feature_intertiki eq 'y' and !empty($prefs.feature_intertiki_mymaster)}
  <b>{tr}Warning: since this tiki site is in slave mode, all user information you enter manually will be automatically overriden by other site's data, including users permissions{/tr}</b>
{/if}
  
{if $tikifeedback}
{remarksbox type="feedback" title="Feedback"}{section name=n loop=$tikifeedback}{$tikifeedback[n].mes}<br />{/section}{/remarksbox}
{/if}

{if $added != "" or $discarded != "" or $discardlist != ''}
{remarksbox type="feedback" title="Batch Upload Results"}
{tr}Updated users{/tr}: {$added}
{if $discarded != ""}- {tr}Rejected users{/tr}: {$discarded}{/if}
<br /><br />
{if $discardlist != ''}
<table class="normal">
<tr><td class="heading">{tr}Username{/tr}</td><td class="heading">{tr}Reason{/tr}</td></tr>
{section name=reject loop=$discardlist}
<tr><td class="odd">{$discardlist[reject].login}</td><td class="odd">{$discardlist[reject].reason}</td></tr>
{/section}
</table>
{/if}
{if $errors}
<br />
{section name=ix loop=$errors}
{$errors[ix]}<br />
{/section}
{/if}
{/remarksbox}
{/if}

{if $prefs.feature_tabs eq 'y'}
{cycle name=tabs values="1,2,3,4" print=false advance=false reset=true}
<div class="tabs">
<span id="tab{cycle name=tabs advance=false assign=tabi}{$tabi}" class="tabmark" style="border-color:{if $cookietab eq $tabi}black{else}white{/if};"><a href="javascript:tikitabs({cycle name=tabs},4);">{tr}Users{/tr}</a></span>
{if $userinfo.userId}
<span id="tab{cycle name=tabs advance=false assign=tabi}{$tabi}" class="tabmark" style="border-color:{if $cookietab eq $tabi}black{else}white{/if};"><a href="javascript:tikitabs({cycle name=tabs},4);">{tr}Edit user{/tr} <i>{$userinfo.login}</i></a></span>
{else}
<span id="tab{cycle name=tabs advance=false assign=tabi}{$tabi}" class="tabmark" style="border-color:{if $cookietab eq $tabi}black{else}white{/if};"><a href="javascript:tikitabs({cycle name=tabs},4);">{tr}Add a New User{/tr}</a></span>
{/if}
<span id="tab{cycle name=tabs advance=false assign=tabi}{$tabi}" class="tabmark" style="border-color:{if $cookietab eq $tabi}black{else}white{/if};"><a href="javascript:tikitabs({cycle name=tabs},4);">{tr}Import/Export{/tr}</a></span>
</div>
{/if}

{cycle name=content values="1,2,3,4" print=false advance=false reset=true}
{* ---------------------- tab with list -------------------- *}
<div id="content{cycle name=content assign=focustab}{$focustab}" class="tabcontent"{if $prefs.feature_tabs eq 'y'} style="display:{if $focustab eq $cookietab}block{else}none{/if};"{/if}>
<h2>{tr}Users{/tr}</h2>

<form method="get" action="tiki-adminusers.php">
<table class="findtable">
<tr>
<td>{tr}Find{/tr}</td>
<td><input type="text" name="find" value="{$find|escape}" /></td>
<td><input type="submit" value="{tr}Find{/tr}" name="search" /></td>
<td>{tr}Number of displayed rows{/tr}</td>
<td><input type="text" size="4" name="numrows" value="{$numrows|escape}" /></td>
</tr>
<tr><td colspan="2"></td><td colspan="3"><a href="javascript:toggleBlock('search')" class="link">{icon _id='add' alt='{tr}more{/tr}'}&nbsp;{tr}More Criteria{/tr}</a></td></tr>
</table>
<div  id="search" {if $filterGroup or $filterEmail}style="display:block;"{else}style="display:none;"{/if}>
<table class="findtable">
<tr>
<td>{tr}Group (direct){/tr}</td>
<td><select name="filterGroup">
	<option value=""></option>
{section name=ix loop=$all_groups}
	<option value="{$all_groups[ix].groupName|escape}" {if $filterGroup eq $all_groups[ix].groupName}selected{/if}>{$all_groups[ix].groupName|escape}</option>
{/section}
</select></td>
</tr><tr>
<td>{tr}Email{/tr}</td>
<td><input type="text" name="filterEmail" value="{$filterEmail}" /></td>
</tr>
</table>
<input type="hidden" name="sort_mode" value="{$sort_mode|escape}" />
</div>
</form>

{if $cant_pages > 1 or !empty($initial)}
<div align="center">
{section name=ini loop=$initials}
{if $initial and $initials[ini] eq $initial}
<span class="button2"><span class="linkbuton">{$initials[ini]|capitalize}</span></span> . 
{else}
<a href="tiki-adminusers.php?initial={$initials[ini]}{if $find}&amp;find={$find|escape:"url"}{/if}{if $numrows}&amp;numrows={$numrows}{/if}{if $sort_mode}&amp;sort_mode={$sort_mode}{/if}" 
class="prevnext">{$initials[ini]}</a> . 
{/if}
{/section}
<a href="tiki-adminusers.php?initial={if $find}&amp;find={$find|escape:"url"}{/if}{if $numrows}&amp;numrows={$numrows}{/if}{if $sort_mode}&amp;sort_mode={$sort_mode}{/if}" 
class="prevnext">{tr}All{/tr}</a>
</div>
{/if}

<form name="checkform" method="post" action="{$smarty.server.PHP_SELF}{if $group_management_mode ne  'y' and $set_default_groups_mode ne 'y'}#multiple{/if}">
<table class="normal">
<tr>
<td class="heading auto">{if $users}
  <script type='text/javascript'>
  <!--
  // check / uncheck all.
  // in the future, we could extend this to happen serverside as well for the convenience of people w/o javascript.
  // for now those people just have to check every single box
  document.write("<input name=\"switcher\" title=\"{tr}Select All{/tr}\" id=\"clickall\" type=\"checkbox\" onclick=\"switchCheckboxes(this.form,'checked[]',this.checked)\"/>");
  //-->                     
  </script>{/if}
</td>
<td class="heading"><a class="tableheading" href="tiki-adminusers.php?offset={$offset}&amp;numrows={$numrows}&amp;sort_mode={if $sort_mode eq 'login_desc'}login_asc{else}login_desc{/if}">{tr}User{/tr}</a></td>
{if $prefs.login_is_email neq 'y'}<td class="heading"><a class="tableheading" href="tiki-adminusers.php?offset={$offset}&amp;numrows={$numrows}&amp;sort_mode={if $sort_mode eq 'email_desc'}email_asc{else}email_desc{/if}">{tr}Email{/tr}</a></td>{/if}
<td class="heading"><a class="tableheading" href="tiki-adminusers.php?offset={$offset}&amp;numrows={$numrows}&amp;sort_mode={if $sort_mode eq 'currentLogin_desc'}currentLogin_asc{else}currentLogin_desc{/if}">{tr}Last login{/tr}</a></td>
<td class="heading" colspan="2">{tr}Groups{/tr}</td>
<td class="heading">{tr}Action{/tr}</td>
</tr>
{cycle print=false values="even,odd"}
{section name=user loop=$users}
<tr class="{cycle}">
<td class="thin">{if $users[user].user ne 'admin'}<input type="checkbox" name="checked[]" value="{$users[user].user}" {if $users[user].checked eq 'y'}checked="checked" {/if}/>{/if}</td>

<td><a class="link" href="tiki-adminusers.php?offset={$offset}&amp;numrows={$numrows}&amp;sort_mode={$sort_mode}&amp;user={$users[user].userId}{if feature_tabs ne 'y'}#2{/if}" title="{tr}Edit Account Settings{/tr}: {$users[user].user}">{$users[user].user}</a></td>

{if $prefs.login_is_email ne 'y'}<td>{$users[user].email}</td>{/if}

<td>{if $users[user].currentLogin eq ''}{tr}Never{/tr} <em>({tr}Registered{/tr} {$users[user].age|duration_short} {tr}ago{/tr})</em>{else}{$users[user].currentLogin|dbg|tiki_long_datetime}{/if}
{if $users[user].waiting eq 'u'}<br />{tr}Need to validate email{/tr}{/if}</td>

<td class="thin"><a class="link" href="tiki-assignuser.php?assign_user={$users[user].user|escape:url}" title="{tr}Assign to group{/tr}">{icon _id='group_key' alt="{tr}Assign{/tr} `$users[user].user` {tr}to groups{/tr} "}</a></td>

<td>
{foreach from=$users[user].groups key=grs item=what}
{if $grs != "Anonymous"}
{if $what eq 'included'}<i>{/if}<a class="link" href="tiki-admingroups.php?group={$grs|escape:"url"}" title={if $what eq 'included'}"{tr}Edit Included Group{/tr}"{else}"{tr}Edit Group{/tr}: {$grs}"{/if}>{$grs}</a>{if $what eq 'included'}</i>{/if}
{if $what ne 'included' and $grs != "Registered"}<a class="link" href="tiki-adminusers.php?offset={$offset}&amp;numrows={$numrows}&amp;sort_mode={$sort_mode}&amp;user={$users[user].user}&amp;action=removegroup&amp;group={$grs|escape:"url"}" title="{tr}Remove{/tr} {tr}from{/tr} {$grs}">{icon _id=delete alt="{tr}Remove{/tr}" style="vertical-align:middle"}</a>{/if}
{if $grs eq $users[user].default_group} {tr}default{/tr}{/if}<br />
{/if}
{/foreach}
</td>

<td>
  {if $prefs.feature_userPreferences eq 'y' || $user eq 'admin'}
    <a class="link" href="tiki-user_preferences.php?userId={$users[user].userId}" title="{tr}Change user preferences{/tr}: {$users[user].user}">{icon _id='wrench' alt="{tr}Change user preferences{/tr}: `$users[user].user`"}</a>
  {/if}

  <a class="link" href="tiki-adminusers.php?offset={$offset}&amp;numrows={$numrows}&amp;sort_mode={$sort_mode}&amp;user={$users[user].userId}{if feature_tabs ne 'y'}#2{/if}" title="{tr}Edit Account Settings{/tr}: {$users[user].user}">{icon _id='page_edit' alt="{tr}Edit Account Settings{/tr}: `$users[user].user`"}</a>

  <a class="link" href="tiki-user_information.php?userId={$users[user].userId}" title="{tr}User Information{/tr}: {$users[user].user}">{icon _id='help' alt="{tr}User Information{/tr}: `$users[user].user`"}</a>

  {if $users[user].user ne 'admin'}
    <a class="link" href="tiki-adminusers.php?offset={$offset}&amp;numrows={$numrows}&amp;sort_mode={$sort_mode}&amp;action=delete&amp;user={$users[user].user|escape:url}" title="{tr}Delete{/tr}: {$users[user].user}">{icon _id='cross' alt="{tr}Delete{/tr}: `$users[user].user`"}</a>
  	{if $users[user].valid && $users[user].waiting eq 'a'}
		<a class="link" href="tiki-login_validate.php?user={$users[user].user|escape:url}&amp;pass={$users[user].valid|escape:url}" title="{tr}Validate user{/tr}: {$users[user].user}">{icon _id='accept' alt="{tr}Validate user{/tr}: `$users[user].user`"}</a>
	{/if}
  {/if}
</td>
</tr>
{sectionelse}
<tr class="odd"><td colspan="8">{tr}No records found.{/tr}</td></tr>
{/section}
  <tr>
  <td class="form" colspan="18">
  <a name="multiple"></a>{if $users}<p align="left"> {*on the left to have it close to the checkboxes*}
  {if $group_management_mode neq 'y' && $set_default_groups_mode neq 'y'}
  {tr}Perform action with checked:{/tr}
  <select name="submit_mult">
    <option value="" selected="selected">-</option>
    <option value="remove_users" >{tr}Remove{/tr}</option>
    {if $prefs.feature_wiki_userpage == 'y'}<option value="remove_users_with_page">{tr}Remove Users and their Userpages{/tr}</option>{/if}
    <option value="assign_groups" >{tr}Manage Group Assignments{/tr}</option>
    <option value="set_default_groups">{tr}Set Default Groups{/tr}</option>
  </select>
  <input type="submit" value="{tr}OK{/tr}" />
  {elseif $group_management_mode eq 'y'}
  <select name="group_management">
  	<option value="add">{tr}Assign selected to{/tr}</option>
  	<option value="remove">{tr}Remove selected from{/tr}</option>
  </select>
  {tr}the following groups:{/tr}<br />
  <select name="checked_groups[]" multiple="multiple" size="20">
  {section name=ix loop=$groups}
  	<option value="{$groups[ix].groupName}">{$groups[ix].groupName}</option>
  {/section}
  </select><br /><input type="submit" value="{tr}OK{/tr}" /><div class="simplebox">{tr}Tip: Hold down CTRL to select multiple{/tr}</div>
  {elseif $set_default_groups_mode eq 'y'}
  {tr}Set the default group of the selected users to{/tr}:<br />
  <select name="checked_group" size="20">
  {section name=ix loop=$groups}
  	<option value="{$groups[ix].groupName|escape}" />{$groups[ix].groupName}</option>
  {/section}
  </select><br /><input type="submit" value="{tr}OK{/tr}" />
  <input type="hidden" name="set_default_groups" value="{$set_default_groups_mode}" />
  {/if}
  </p>
{/if}
  </td></tr>
  </table>
  
<input type="hidden" name="find" value="{$find|escape}" />
<input type="hidden" name="numrows" value="{$numrows|escape}" />
<input type="hidden" name="sort_mode" value="{$sort_mode|escape}" />
<input type="hidden" name="offset" value="{$offset|escape}" />
</form>

{if $cant_pages > 1}
<br />
<div class="mini">
{if $prev_offset >= 0}
[<a class="prevnext" href="tiki-adminusers.php?{if $find}find={$find|escape:"url"}&amp;{/if}{if $initial}initial={$initial}&amp;{/if}offset={$prev_offset}&amp;numrows={$numrows}&amp;sort_mode={$sort_mode}">{tr}Prev{/tr}</a>]&nbsp;
{/if}
{tr}Page{/tr}: {$actual_page}/{$cant_pages}
{if $next_offset >= 0}
&nbsp;[<a class="prevnext" href="tiki-adminusers.php?{if $find}find={$find|escape:"url"}&amp;{/if}{if $initial}initial={$initial}&amp;{/if}offset={$next_offset}&amp;numrows={$numrows}&amp;sort_mode={$sort_mode}">{tr}Next{/tr}</a>]
{/if}
{if $prefs.direct_pagination eq 'y'}
<br />
{section loop=$cant_pages name=foo}
{assign var=selector_offset value=$smarty.section.foo.index|times:$maxRecords}

<a class="prevnext" href="tiki-adminusers.php?find={$find|escape:"url"}&amp;{if $initial}initial={$initial}&amp;{/if}offset={$selector_offset}&amp;numrows={$numrows}&amp;sort_mode={$sort_mode}">
{$smarty.section.foo.index_next}</a>&nbsp;
{/section}
{/if}

</div>
{/if}
</div>

{* ---------------------- tab with form -------------------- *}
<a name="2" ></a>
<div id="content{cycle name=content assign=focustab}{$focustab}" class="tabcontent"{if $prefs.feature_tabs eq 'y'} style="display:{if $focustab eq $cookietab}block{else}none{/if};"{/if}>
{if $userinfo.userId}
<h2>{tr}Edit user{/tr}: {$userinfo.login}</h2>
{if $userinfo.login ne 'admin'}<a class="linkbut" href="tiki-assignuser.php?assign_user={$userinfo.login|escape:url}">{tr}Assign to Groups{/tr}: {$userinfo.login}</a>{/if}
{else}
<h2>{tr}Add a New User{/tr}</h2>
{/if}
<form action="tiki-adminusers.php" method="post" enctype="multipart/form-data" name="RegForm">
<table class="normal">
<tr class="formcolor"><td>{if $prefs.login_is_email eq 'y'}{tr}Email{/tr}{else}{tr}User{/tr}{/if}:</td><td>{if $userinfo.login neq 'admin'}<input type="text" name="name"  value="{$userinfo.login|escape}" /><br /> {if $prefs.login_is_email eq 'y'}<em>{tr}Use the email as username{/tr}.</em>
{elseif $prefs.lowercase_username eq 'y'} <em>{tr}Lowercase only{/tr}</em>.
{/if}<br />
{if $userinfo.userId}
<p>{icon _id=exclamation alt="{tr}Warning{/tr}" style="vertical-align:middle"} <em>{tr}Warning: changing the username could require the user to change his password(for user registered with an old tikiwiki <=1.8){/tr}</em></p>
  {if $prefs.feature_intertiki_server eq 'y'}
    <i>{tr}Warning: it will mess with slave intertiki sites that use this one as master{/tr}</i>
  {/if}
{/if}
{else}
<input type="hidden" name="name" value="{$userinfo.login|escape}" />{$userinfo.login}
{/if}
</td></tr>
{*
  No need to specify user password or to ask him to change it, if :
    Tiki is using the Tiki + PEAR Auth systems
    AND Tiki won't create the user in the Tiki auth system
    AND Tiki won't create the user in the PEAR Auth system 
*}
{if $prefs.auth_method eq 'auth' and ( $prefs.auth_create_user_tiki eq 'n' or $prefs.auth_skip_admin eq 'y' ) and $prefs.auth_create_user_auth eq 'n' and $userinfo.login neq 'admin'}
<tr class="formcolor"><td colspan="2"><b>{tr}No password is required{/tr}</b><br /><i>{tr}Tikiwiki is configured to delegate the password managment to LDAP through PEAR Auth.{/tr}</i></td></tr>
{else}
<tr class="formcolor"><td>{tr}Password{/tr}:</td><td><input type="password" name="pass" id="pass1" onKeyUp="runPassword(this.value, 'mypassword');" />
<div style="float:right;width:150px;margin-left:5px;">
				<div id="mypassword_text"></div>
				<div id="mypassword_bar" style="font-size: 5px; height: 2px; width: 0px;"></div> 
				</div>
<br />
{if $prefs.min_pass_length > 1}<em>{tr}Minimum {$prefs.min_pass_length} characters long{/tr}</em>. {/if}
{if $prefs.pass_chr_num eq 'y'}<em>{tr}Password must contain both letters and numbers{/tr}</em>.{/if}
{if ! ( $prefs.auth_method eq 'auth' and ( $prefs.auth_create_user_tiki eq 'n' or $prefs.auth_skip_admin eq 'y' ) and $prefs.auth_create_user_auth eq 'n' ) }
<p><div><a class="linkbut" href="#" onClick="genPass('genepass','pass1','pass2');runPassword(document.RegForm.genpass.value, 'mypassword');">Generate a password:</a> <input id='genepass' name="genpass" type="text" /></div></p>{/if}
</td></tr>
<tr class="formcolor"><td>{tr}Repeat Password{/tr}:</td><td><input type="password" name="pass2" id="pass2" /></td></tr>
{if $userinfo.login neq 'admin'}
<tr class="formcolor"><td>{tr}Must Change Password{/tr}:</td><td><input type="checkbox" name="pass_first_login" /> 
{tr}User must change password at first login{/tr}.</td></tr>
{/if}
{/if}
{if $prefs.login_is_email neq 'y'}<tr class="formcolor"><td>{tr}Email{/tr}:</td><td><input type="text" name="email" size="30"  value="{$userinfo.email|escape}" /></td></tr>{/if}
{if $userinfo.userId != 0}
<tr class="formcolor"><td>{tr}Created{/tr}:</td><td>{$userinfo.created|tiki_long_datetime}</td></tr>
{if $userinfo.login neq 'admin'}<tr class="formcolor"><td>{tr}Registered{/tr}:</td><td>{if $userinfo.registrationDate}{$userinfo.registrationDate|tiki_long_datetime}{/if}</td></tr>{/if}
<tr class="formcolor"><td>{tr}Last Login{/tr}:</td><td>{if $userinfo.lastLogin}{$userinfo.lastLogin|tiki_long_datetime|default:'Never'}{/if}</td></tr>
{/if}
<tr class="formcolor"><td>&nbsp;</td><td>
{if $userinfo.userId}
<input type="hidden" name="user" value="{$userinfo.userId|escape}" />
<input type="hidden" name="edituser" value="1" />
<input type="submit" name="submit" value="{tr}Save{/tr}" />
{else}
<input type="submit" name="newuser" value="{tr}Add{/tr}" />
{/if}
</td></tr>
</table>
<br /><br />

{if $prefs.userTracker eq 'y'}
{if $userstrackerid and $usersitemid}
{tr}User tracker item : {$usersitemid}{/tr} <span class="button2"><a href="tiki-view_tracker_item.php?trackerId={$userstrackerid}&amp;itemId={$usersitemid}&amp;show=mod" class="linkbut">{tr}Edit Item{/tr}</a></span>
{/if}
<br /><br />
{/if}
</form>
</div>

{* ---------------------- tab with upload -------------------- *}
<div id="content{cycle name=content assign=focustab}{$focustab}" class="tabcontent"{if $prefs.feature_tabs eq 'y'} style="display:{if $focustab eq $cookietab}block{else}none{/if};"{/if}>
<h2>{tr}Batch upload (CSV file):{/tr}</h2>
<form action="tiki-adminusers.php" method="post" enctype="multipart/form-data">
<table class="normal">
<tr class="formcolor">
  <td>{tr}CSV File:{/tr} <a {popup text='login,password,email,groups&lt;br /&gt;user1,password1,email1,&quot;group1,group2&quot;&lt;br /&gt;user2, password2,email2'}>{icon _id='help'}</a></td>
  <td>
    <input type="file" name="csvlist"/><br />
	<input type="radio" name="overwrite" value="y" checked="checked" />&nbsp;{tr}Overwrite{/tr}<br />
	<input type="radio" name="overwrite" value="c"/>&nbsp;{tr}Overwrite but keep the previous login if the login exists in another case{/tr}<br />
	<input type="radio" name="overwrite" value="n" />&nbsp;{tr}Don't overwrite{/tr}<br />
	{tr}Overwrite groups:{/tr} <input type="checkbox" name="overwriteGroup" />
  </td>
</tr>
<tr class="formcolor"><td>&nbsp;</td><td>
<input type="submit" name="batch" value="{tr}Add{/tr}" />
</td></tr>
</table>
</form>
{remarksbox type="tip" title="{tr}Tip{/tr}"}{tr}You can export users of a group in <a href="tiki-admingroups.php">admin->groups->a_group</a>{/tr}{/remarksbox}
</div>
