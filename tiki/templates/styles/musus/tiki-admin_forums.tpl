<h1><a class="pagetitle" href="tiki-admin_forums.php">{tr}Admin Forums{/tr}</a></h1>
<!-- the help link info -->
      {if $feature_help eq 'y'}
<a href="http://tikiwiki.org/tiki-index.php?page=Forums" target="tikihelp" class="tikihelp" title="{tr}Tikiwiki.org help{/tr}: {tr}Forums{/tr}">{$helpIcon $helpIconDesc}</a>
{/if}
<!-- link to tpl -->
      {if $feature_view_tpl eq 'y'}
<a href="tiki-edit_templates.php?template=templates/tiki-admin_forums.tpl" target="tikihelp" class="tikihelp" title="{tr}View tpl{/tr}: {tr}admin forums tpl{/tr}"><img alt="{tr}Edit template{/tr}" src="img/icons/info.gif" /></a>
{/if}
<!-- beginning of next bit -->
{if $tiki_p_admin eq 'y'}
<a href="tiki-admin.php?page=forums"><img alt="{tr}Configure/Options{/tr}" src="img/icons/config.gif" /></a>
{/if}

{if $forumId > 0}
<h2>{tr}Edit this Forum:{/tr} {$name}</h2>
<a href="tiki-admin_forums.php" class="linkbut">{tr}Create new forum{/tr}</a>
{else}
<h2>{tr}Create New Forum{/tr}</h2>
{/if}
{if $individual eq 'y'}
<a href="tiki-objectpermissions.php?objectName=forum%20{$name}&amp;objectType=forum&amp;permType=forums&amp;objectId={$galleryId}">{tr}There are individual permissions set for this forum{/tr}</a>
{/if}
<form action="tiki-admin_forums.php" method="post">
<input type="hidden" name="forumId" value="{$forumId|escape}" />
<table>
<tr><td><label>{tr}Name{/tr}:</label></td><td><input type="text" name="name" value="{$name|escape}" /></td></tr>
<tr><td><label>{tr}Description{/tr}:</label></td><td><textarea name="description" rows="4" cols="40">{$description|escape}</textarea></td></tr>
<tr><td><label>{tr}Show description{/tr}:</label></td><td><input type="checkbox" name="show_description" {if $show_description eq 'y'}checked="checked"{/if} /></td></tr>
<tr><td><label>{tr}Prevent flooding{/tr}:</label></td><td><input type="checkbox" name="controlFlood" {if $controlFlood eq 'y'}checked="checked"{/if} />
<label>{tr}Minimum time between posts{/tr}: </label>
<select name="floodInterval">
<option value="15" {if $floodInterval eq 15}selected="selected"{/if}>{tr}15 seconds{/tr}</option>
<option value="30" {if $floodInterval eq 30}selected="selected"{/if}>{tr}30 seconds{/tr}</option>
<option value="60" {if $floodInterval eq 60}selected="selected"{/if}>{tr}1 minute{/tr}</option>
<option value="120" {if $floodInterval eq 120}selected="selected"{/if}>{tr}2 minutes{/tr}</option>
</select>
</td></tr>
<tr><td><label>{tr}Topics per page{/tr}:</label></td><td><input type="text" name="topicsPerPage" value="{$topicsPerPage|escape}" /></td></tr>
<tr><td><label>{tr}Section{/tr}:</label></td><td>
<select name="section">
<option value="" {if $section eq ""}selected="selected"{/if}>{tr}None{/tr}</option>
<option value="__new__"}>{tr}Create new{/tr}</option>
{section name=ix loop=$sections}
<option {if $section eq $sections[ix]}selected="selected"{/if} value="{$sections[ix]|escape}">{$sections[ix]}</option>
{/section}
</select>
<input name="new_section" type="text" />
</td></tr>
<tr><td><label>{tr}Moderator user{/tr}:</label></td><td>
<select name="moderator">
{section name=ix loop=$users}
<option value="{$users[ix]|escape}" {if $moderator eq $users[ix]}selected="selected"{/if}>{$users[ix]}</option>
{/section}
</select>
</td></tr>
<tr><td><label>{tr}Moderator group{/tr}:</label></td><td>
<select name="moderator_group">
<option value="" {if $moderator_group eq ""}selected="selected"{/if}>{tr}None{/tr}</option>
{section name=ix loop=$groups}
<option value="{$groups[ix].groupName|escape}" {if $moderator_group eq $groups[ix].groupName}selected="selected"{/if}>{$groups[ix].groupName}</option>
{/section}
</select>
</td></tr>
<tr><td><label>{tr}Password protected{/tr}</label></td><td>
<select name="forum_use_password">
<option value="n" {if $forum_use_password eq 'n'}selected="selected"{/if}>{tr}No{/tr}</option>
<option value="t" {if $forum_use_password eq 't'}selected="selected"{/if}>{tr}Topics only{/tr}</option>
<option value="a" {if $forum_use_password eq 'a'}selected="selected"{/if}>{tr}All posts{/tr}</option>
</select>
</td></tr>
<tr><td><label>{tr}Forum password{/tr}</label></td><td><input type="text" name="forum_password" value="{$forum_password|escape}" /></td></tr>

{include file=categorize.tpl}
<tr><td><label>{tr}Default ordering for topics{/tr}:</label></td><td>
<select name="topicOrdering">
<option value="commentDate_desc" {if $topicOrdering eq 'commentDate_desc'}selected="selected"{/if}>{tr}Date (desc){/tr}</option>
<option value="average_desc" {if $topicOrdering eq 'average_desc'}selected="selected"{/if}>{tr}Score (desc){/tr}</option>
<option value="replies_desc" {if $topicOrdering eq 'replies_desc'}selected="selected"{/if}>{tr}Replies (desc){/tr}</option>
<option value="hits_desc" {if $topicOrdering eq 'hits_desc'}selected="selected"{/if}>{tr}Reads (desc){/tr}</option>
<option value="lastPost_desc" {if $topicOrdering eq 'lastPost_desc'}selected="selected"{/if}>{tr}Last post (desc){/tr}</option>
<option value="title_desc" {if $topicOrdering eq 'title_desc'}selected="selected"{/if}>{tr}Title (desc){/tr}</option>
<option value="title_asc" {if $topicOrdering eq 'title_asc'}selected="selected"{/if}>{tr}Title (asc){/tr}</option>
</select>
</td></tr>
<tr><td><label>{tr}Default ordering for threads{/tr}:</label></td><td>
<select name="threadOrdering">
<option value="commentDate_desc" {if $threadOrdering eq 'commentDate_desc'}selected="selected"{/if}>{tr}Date (desc){/tr}</option>
<option value="commentDate_asc" {if $threadOrdering eq 'commentDate_asc'}selected="selected"{/if}>{tr}Date (asc){/tr}</option>
<option value="average_desc" {if $threadOrdering eq 'average_desc'}selected="selected"{/if}>{tr}Score (desc){/tr}</option>
<option value="title_desc" {if $threadOrdering eq 'title_desc'}selected="selected"{/if}>{tr}Title (desc){/tr}</option>
<option value="title_asc" {if $threadOrdering eq 'title_asc'}selected="selected"{/if}>{tr}Title (asc){/tr}</option>
</select>
</td></tr>
<tr><td><input type="checkbox" name="useMail" {if $useMail eq 'y'}checked="checked"{/if} />{tr}Send this forums posts to this email{/tr}:</td><td><input type="text" name="mail" value="{$mail|escape}" /></td></tr>
<tr><td><input type="checkbox" name="usePruneUnreplied" {if $usePruneUnreplied eq 'y'}checked="checked"{/if} />{tr}Prune unreplied messages after{/tr}:</td><td>
<select name="pruneUnrepliedAge">
<option value="86400" {if $pruneUnrepliedAge eq 86400}selected="selected"{/if}>{tr}24 hours{/tr}</option>
<option value="172800" {if $pruneUnrepliedAge eq 172800}selected="selected"{/if}>{tr}2 days{/tr}</option>
<option value="432000" {if $pruneUnrepliedAge eq 432000}selected="selected"{/if}>{tr}5 days{/tr}</option>
<option value="604800" {if $pruneUnrepliedAge eq 604800}selected="selected"{/if}>{tr}7 days{/tr}</option>
<option value="1296000" {if $pruneUnrepliedAge eq 1296000}selected="selected"{/if}>{tr}15 days{/tr}</option>
<option value="2592000" {if $pruneUnrepliedAge eq 2592000}selected="selected"{/if}>{tr}30 days{/tr}</option>
<option value="5184000" {if $pruneUnrepliedAge eq 5184000}selected="selected"{/if}>{tr}60 days{/tr}</option>
<option value="7776000" {if $pruneUnrepliedAge eq 7776000}selected="selected"{/if}>{tr}90 days{/tr}</option>
</select>
</td></tr>
<tr><td><input type="checkbox" name="usePruneOld" {if $usePruneOld eq 'y'}checked="checked"{/if} /> {tr}Prune old messages after{/tr}:</td><td>
<select name="pruneMaxAge">
<option value="86400" {if $pruneMaxAge eq 86400}selected="selected"{/if}>{tr}24 hours{/tr}</option>
<option value="172800" {if $pruneMaxAge eq 172800}selected="selected"{/if}>{tr}2 days{/tr}</option>
<option value="432000" {if $pruneMaxAge eq 432000}selected="selected"{/if}>{tr}5 days{/tr}</option>
<option value="604800" {if $pruneMaxAge eq 604800}selected="selected"{/if}>{tr}7 days{/tr}</option>
<option value="1296000" {if $pruneMaxAge eq 1296000}selected="selected"{/if}>{tr}15 days{/tr}</option>
<option value="2592000" {if $pruneMaxAge eq 2592000}selected="selected"{/if}>{tr}30 days{/tr}</option>
<option value="5184000" {if $pruneMaxAge eq 5184000}selected="selected"{/if}>{tr}60 days{/tr}</option>
<option value="7776000" {if $pruneMaxAge eq 7776000}selected="selected"{/if}>{tr}90 days{/tr}</option>
</select>
</td></tr>
<tr>
	<td>{tr}Topic list configuration{/tr}</td>
	<td>
		<table>
			<tr>
				<td><label>{tr}Replies{/tr}</label></td>
				<td><label>{tr}Reads{/tr}</label></td>
				<td><label>{tr}Points{/tr}</label></td>
				<td><label>{tr}Last post{/tr}</label></td>
				<td><label>{tr}author{/tr}</label></td>
			</tr>
			<tr>
				<td><input type="checkbox" name="topics_list_replies" {if $topics_list_replies eq 'y'}checked="checked"{/if} /></td>
				<td><input type="checkbox" name="topics_list_reads" {if $topics_list_reads eq 'y'}checked="checked"{/if} /></td>
				<td><input type="checkbox" name="topics_list_pts" {if $topics_list_pts eq 'y'}checked="checked"{/if} /></td>
				<td><input type="checkbox" name="topics_list_lastpost" {if $topics_list_lastpost eq 'y'}checked="checked"{/if} /></td>
				<td><input type="checkbox" name="topics_list_author" {if $topics_list_author eq 'y'}checked="checked"{/if} /></td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td><label>{tr}Threads can be voted{/tr}</label></td>
	<td><input type="checkbox" name="vote_threads" {if $vote_threads eq 'y'}checked="checked"{/if} /></td>
</tr>
<tr>
	<td><label>{tr}Display last post titles{/tr}</label></td>
	<td>
		<select name="forum_last_n">
			<option value="0" {if $forum_last_n eq 0}selected="selected"{/if}>{tr}no display{/tr}</option>
			<option value="5" {if $forum_last_n eq 5}selected="selected"{/if}>5</option>
			<option value="10" {if $forum_last_n eq 10}selected="selected"{/if}>10</option>
			<option value="20" {if $forum_last_n eq 20}selected="selected"{/if}>20</option>
		</select>
	</td>
</tr>
<tr>
	<td><label>{tr}Forward messages to this forum to this e-mail address, in a format that can be used for sending back to the inbound forum e-mail address{/tr}</label></td>
	<td><input type="text" name="outbound_address" size=30 value="{$outbound_address|escape}" /></td>
</tr>
<tr>
	<td><label>{tr}Originating email address for email from this forum{/tr}</label></td>
	<td><input type="text" name="outbound_from" size=30 value="{$outbound_from|escape}" /></td>
</tr>
<tr>
	<td>{tr}Add messages from this email to the forum{/tr}</td>
	<td>
		<table>
		<tr>
			<td><label>{tr}POP3 server{/tr}:</label></td>
			<td><input type="text" name="inbound_pop_server" value="{$inbound_pop_server|escape}" /></td>
		</tr>
		<tr>
			<td><label>{tr}User{/tr}:</label></td>
			<td><input type="text" name="inbound_pop_user" value="{$inbound_pop_user|escape}" /></td>
		</tr>
		<tr>
			<td><label>{tr}Password{/tr}:</label></td>
			<td><input type="text" name="inbound_pop_password" value="{$inbound_pop_password|escape}" /></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td><label>{tr}Use topic smileys{/tr}</label></td>
	<td><input type="checkbox" name="topic_smileys" {if $topic_smileys eq 'y'}checked="checked"{/if} /></td>
</tr>
<tr>
	<td><label>{tr}Show topic summary{/tr}</label></td>
	<td><input type="checkbox" name="topic_summary" {if $topic_summary eq 'y'}checked="checked"{/if} /></td>
</tr>
<tr>
	<td>{tr}User information display{/tr}</td>
	<td>
	<table>
	<tr>
		<td><label>{tr}avatar{/tr}</label></td>
		<td><label>{tr}flag{/tr}</label></td>
		<td><label>{tr}posts{/tr}</label></td>
		<td><label>{tr}user level{/tr}</label></td>
		<td><label>{tr}email{/tr}</label></td>
		<td><label>{tr}online{/tr}</label></td>
	</tr>
	<tr>
		<td><input type="checkbox" name="ui_avatar" {if $ui_avatar eq 'y'}checked="checked"{/if} /></td>
		<td><input type="checkbox" name="ui_flag" {if $ui_flag eq 'y'}checked="checked"{/if} /></td>
		<td><input type="checkbox" name="ui_posts" {if $ui_posts eq 'y'}checked="checked"{/if} /></td>
		<td><input type="checkbox" name="ui_level" {if $ui_level eq 'y'}checked="checked"{/if} /></td>
		<td><input type="checkbox" name="ui_email" {if $ui_email eq 'y'}checked="checked"{/if} /></td>
		<td><input type="checkbox" name="ui_online" {if $ui_online eq 'y'}checked="checked"{/if} /></td>
	</tr>		
	</table>
	</td>
</tr>
<tr>
	<td><label>{tr}Approval type{/tr}</label></td>
	<td>
		<select name="approval_type">
			<option value="all_posted" {if $approval_type eq 'all_posted'}selected="selected"{/if}>{tr}All posted{/tr}</option>
			<option value="queue_anon" {if $approval_type eq 'queue_anon'}selected="selected"{/if}>{tr}Queue anonymous posts{/tr}</option>
			<option value="queue_all" {if $approval_type eq 'queue_all'}selected="selected"{/if}>{tr}Queue all posts{/tr}</option>
		</select>
	</td>
</tr>
<tr>
	<td><label>{tr}Attachments{/tr}</label></td>
	<td>
		<select name="att">
			<option value="att_no" {if $att eq 'att_no'}selected="selected"{/if}>{tr}No attachments{/tr}</option>
			<option value="att_all" {if $att eq 'att_all'}selected="selected"{/if}>{tr}Everybody can attach{/tr}</option>
			<option value="att_perm" {if $att eq 'att_perm'}selected="selected"{/if}>{tr}Only users with attach permission{/tr}</option>
			<option value="att_admin" {if $att eq 'att_admin'}selected="selected"{/if}>{tr}Moderators and admin can attach{/tr}</option>
		</select>
		<br />
		<label>{tr}Store attachments in:{/tr}</label>
		<table>
			<tr><td><input type="radio" name="att_store" value="db" {if $att_store eq 'db'}checked="checked"{/if} /> {tr}Database{/tr}</td></tr>
			<tr><td><input type="radio" name="att_store" value="dir" {if $att_store eq 'dir'}checked="checked"{/if} /> {tr}Directory (include trailing slash){/tr}: <input type="text" name="att_store_dir" value="{$att_store_dir|escape}" size="14" /></td></tr>
			<tr><td><label>{tr}Max attachment size (bytes){/tr}: </label><input type="text" name="att_max_size" value="{$att_max_size|escape}" /></td></tr>
		</table>
	</td>
</tr>
<tr><td>&nbsp;</td><td><input type="submit" name="save" value="{tr}Save{/tr}" /></td></tr>
</table>
</form>
<h2>{tr}Forums{/tr}</h2>
<div align="center">
<table class="findtable">
<tr><td>{tr}Find{/tr}</td>
   <td>
   <form method="get" action="tiki-admin_forums.php">
     <input type="text" name="find" value="{$find|escape}" />
     <input type="submit" value="{tr}find{/tr}" name="search" />
     <input type="hidden" name="sort_mode" value="{$sort_mode|escape}" />
   </form>
   </td>
</tr>
</table>
<table>
<tr>
<th><a href="tiki-admin_forums.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'name_desc'}name_asc{else}name_desc{/if}">{tr}name{/tr}</a></th>
<th><a href="tiki-admin_forums.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'threads_desc'}threads_asc{else}threads_desc{/if}">{tr}topics{/tr}</a></th>
<th><a href="tiki-admin_forums.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'comments_desc'}comments_asc{else}comments_desc{/if}">{tr}coms{/tr}</a></th>
<th>{tr}users{/tr}</th>
<th>{tr}age{/tr}</th>
<th>{tr}ppd{/tr}</th>
<!--<th><a href="tiki-admin_forums.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'lastPost_desc'}lastPost_asc{else}lastPost_desc{/if}">{tr}last post{/tr}</a></th>-->
<th><a href="tiki-admin_forums.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'hits_desc'}hits_asc{else}hits_desc{/if}">{tr}hits{/tr}</a></th>
<th>{tr}action{/tr}</th>
</tr>
{cycle values="odd,even" print=false}
{section name=user loop=$channels}
<tr class="odd">
<td><a href="tiki-view_forum.php?forumId={$channels[user].forumId}">{$channels[user].name}</a></td>
<td style="text-align:right;" class="{cycle advance=false}">{$channels[user].threads}</td>
<td style="text-align:right;" class="{cycle advance=false}">{$channels[user].comments}</td>
<td style="text-align:right;" class="{cycle advance=false}">{$channels[user].users}</td>
<td style="text-align:right;" class="{cycle advance=false}">{$channels[user].age}</td>
<td style="text-align:right;" class="{cycle advance=false}">{$channels[user].posts_per_day|string_format:"%.2f"}</td>
<!--<td style="text-align:right;" class="{cycle advance=false}">{$channels[user].lastPost|tiki_short_datetime}</td>-->
<td style="text-align:right;" class="{cycle advance=false}">{$channels[user].hits}</td>
<td class="{cycle advance=false}">
{if ($tiki_p_admin eq 'y') or (($channels[user].individual eq 'n') and ($tiki_p_admin_forum eq 'y')) or ($channels[user].individual_tiki_p_admin_forum eq 'y')}
   <a href="tiki-admin_forums.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;forumId={$channels[user].forumId}"><img alt="{tr}Configure/Options{/tr}" src="img/icons/config.gif" /></a>
   <a href="tiki-objectpermissions.php?objectName={tr}Forum{/tr}%20{$channels[user].name}&amp;objectType=forum&amp;permType=forums&amp;objectId={$channels[user].forumId}"><img alt="{tr}Assign Permissions{/tr}" src="img/icons/key.gif" /></a>
     <a href="tiki-admin_forums.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;remove={$channels[user].forumId}" 
onclick="return confirmTheLink(this,'{tr}Are you sure you want to delete this forum?{/tr}')" 
title="{tr}Click here to delete this forum{/tr}"><img alt="{tr}Remove{/tr}" src="img/icons2/delete.gif" /></a>  
{/if}
</td>
</tr>
{/section}
</table>
<div class="mini">
{if $prev_offset >= 0}
[<a class="prevnext" href="tiki-admin_forums.php?find={$find}&amp;offset={$prev_offset}&amp;sort_mode={$sort_mode}">{tr}prev{/tr}</a>] 
{/if}
{tr}Page{/tr}: {$actual_page}/{$cant_pages}
{if $next_offset >= 0}
 [<a class="prevnext" href="tiki-admin_forums.php?find={$find}&amp;offset={$next_offset}&amp;sort_mode={$sort_mode}">{tr}next{/tr}</a>]
{/if}
</div>
</div>
