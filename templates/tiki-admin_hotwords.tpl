{* $Id$ *}

{title help="Hotwords"}{tr}Admin Hotwords{/tr}{/title}

<h2>{tr}Add Hotword{/tr}</h2>

<form method="post" action="tiki-admin_hotwords.php">
<table class="normal">
<tr><td class="formcolor">{tr}Word{/tr}</td><td class="formcolor"><input type="text" name="word" /></td></tr>
<tr><td class="formcolor">{tr}URL{/tr}</td><td class="formcolor"><input type="text" name="url" /></td></tr>
<tr><td class="formcolor">&nbsp;</td><td class="formcolor"><input type="submit" name="add" value="{tr}Add{/tr}" /></td></tr>
</table>
</form>

<h2>{tr}Hotwords{/tr}</h2>
{if $words}
  {include file='find.tpl' _sort_mode='y'}
{/if}
<table class="normal">
<tr>
<th><a href="tiki-admin_hotwords.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'word_desc'}word_asc{else}word_desc{/if}">{tr}Word{/tr}</a></th>
<th><a href="tiki-admin_hotwords.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'url_desc'}url_asc{else}url_desc{/if}">{tr}URL{/tr}</a></th>
<th>{tr}Action{/tr}</th>
</tr>
{cycle values="odd,even" print=false}
{section name=user loop=$words}
<tr>
<td class="{cycle advance=false}">{$words[user].word}</td>
<td class="{cycle advance=false}">{$words[user].url}</td>
<td class="{cycle advance=true}">
<a class="link" href="tiki-admin_hotwords.php?remove={$words[user].word|escape:"url"}{if $offset}&amp;offset={$offset}{/if}&amp;sort_mode={$sort_mode}" 
title="{tr}Delete{/tr}">{icon _id='cross' alt='{tr}Delete{/tr}'}</a>
</td>
</tr>
{sectionelse}
<tr><td colspan="3" class="odd">{tr}No records found{/tr}</td></tr>
{/section}
</table>

<div class="mini">
{if $prev_offset >= 0}
[<a class="prevnext" class="link" href="tiki-admin_hotwords.php?find={$find}&amp;offset={$prev_offset}&amp;sort_mode={$sort_mode}">{tr}Prev{/tr}</a>]&nbsp;
{/if}
{tr}Page{/tr}: {$actual_page}/{$cant_pages}
{if $next_offset >= 0}
&nbsp;[<a class="prevnext" class="link" href="tiki-admin_hotwords.php?find={$find}&amp;offset={$next_offset}&amp;sort_mode={$sort_mode}">{tr}Next{/tr}</a>]
{/if}
{if $prefs.direct_pagination eq 'y'}
<br />
{section loop=$cant_pages name=foo}
{assign var=selector_offset value=$smarty.section.foo.index|times:$prefs.maxRecords}
<a class="prevnext" href="tiki-admin_hotwords.php?find={$find}&amp;offset={$selector_offset}&amp;sort_mode={$sort_mode}">
{$smarty.section.foo.index_next}</a>&nbsp;
{/section}
{/if}
</div>
