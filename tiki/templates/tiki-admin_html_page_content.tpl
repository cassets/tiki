<a class="pagetitle" href="tiki-admin_html_page_content.php?pageName={$pageName}">{tr}Admin HTML page dynamic zones{/tr}</a><br/>
<h2>{tr}Page{/tr}: {$pageName}</h2><br/><br/>
[<a class="link" href="tiki-admin_html_pages.php">{tr}Admin HTML pages{/tr}</a>
|<a class="link" href="tiki-admin_html_pages.php?pageName={$pageName}">{tr}Edit this HTML page{/tr}</a>]
|<a class="link" href="tiki-page.php?pageName={$pageName}">{tr}View page{/tr}</a>]<br/><br/>
{if $zone}
<h2>{tr}Edit zone{/tr}</h2>
<form action="tiki-admin_html_page_content.php" method="post">
<input type="hidden" name="pageName" value="{$pageName}" />
<input type="hidden" name="zone" value="{$zone}" />
<table class="normal">
<tr><td class="formcolor">{tr}Zone{/tr}:</td><td class="formcolor">{$zone}</td></tr>
<tr><td class="formcolor">{tr}Content{/tr}:</td><td class="formcolor">
{if $type eq 'ta'}
<textarea rows="5" cols="60" name="content">{$content}</textarea>
{else}
<input type="text" name="content" value="{$content}" />
{/if}
</td></tr>
<tr><td  class="formcolor">&nbsp;</td><td class="formcolor"><input type="submit" name="save" value="{tr}Save{/tr}" /></td></tr>
</table>
</form>
{/if

<h2>{tr}Dynamic zones{/tr}</h2>
<div  align="center">
<table class="findtable">
<tr><td class="findtable">{tr}Find{/tr}</td>
   <td class="findtable">
   <form method="get" action="tiki-admin_html_page_content.php">
     <input type="text" name="find" value="{$find}" />
     <input type="submit" value="{tr}find{/tr}" name="search" />
     <input type="hidden" name="sort_mode" value="{$sort_mode}" />
     <input type="hidden" name="pageName" value="{$pageName}" />
   </form>
   </td>
</tr>
</table>
<form action="tiki-admin_html_page_content.php" method="post">
<input type="hidden" name="pageName" value="{$pageName}" />
<input type="hidden" name="zone" value="{$zone}" />
<table class="normal">
<tr>
<td class="heading"><a class="tableheading" href="tiki-admin_html_page_content.php?pageName={$pageName}&amp;offset={$offset}&amp;sort_mode={if $sort_mode eq 'zone_desc'}zone_asc{else}zone_desc{/if}">{tr}zone{/tr}</a></td>
<td class="heading"><a class="tableheading" href="tiki-admin_html_page_content.php?pageName={$pageName}&amp;offset={$offset}&amp;sort_mode={if $sort_mode eq 'content_desc'}content_asc{else}content_desc{/if}">{tr}content{/tr}</a></td>
<td class="heading">{tr}action{/tr}</td>
</tr>
{section name=user loop=$channels}
{if $smarty.section.user.index % 2}
<tr>
<td class="odd">{$channels[user].zone}</td>
<!--<td class="odd">{$channels[user].content|truncate:250:"(...)":true}</td>-->
<td class="odd">
{if $channels[user].type eq 'ta'}
<textarea name="{$channels[user].zone}" cols="20" rows="4">{$channels[user].content}</textarea>
{else}
<input type="text" name="{$channels[user].zone}" value="{$channels[user].content}" />
{/if}
</td>
<td class="odd">
   <a class="link" href="tiki-admin_html_page_content.php?pageName={$pageName}&amp;offset={$offset}&amp;sort_mode={$sort_mode}&amp;zone={$channels[user].zone}">{tr}edit{/tr}</a>
</td>
</tr>
{else}
<tr>
<td class="even">{$channels[user].zone}</td>
<!--<td class="even">{$channels[user].content|truncate:250:"(...)":true}</td>-->
<td class="even">
{if $channels[user].type eq 'ta'}
<textarea name="{$channels[user].zone}" cols="20" rows="4">{$channels[user].content}</textarea>
{else}
<input type="text" name="{$channels[user].zone}" value="{$channels[user].content}" />
{/if}
</td>
<td class="even">
   <a class="link" href="tiki-admin_html_page_content.php?pageName={$pageName}&amp;offset={$offset}&amp;sort_mode={$sort_mode}&amp;zone={$channels[user].zone}">{tr}edit{/tr}</a>
</td>
</tr>
{/if}
{/section}
</table>
<div align="center">
<input type="submit" name="editmany" value="{tr}Mass update{/tr}" />
</div>
</form>
<div class="mini">
{if $prev_offset >= 0}
[<a class="prevnext" href="tiki-admin_html_page_content.php?find={$find}&amp;offset={$prev_offset}&amp;sort_mode={$sort_mode}">{tr}prev{/tr}</a>]&nbsp;
{/if}
{tr}Page{/tr}: {$actual_page}/{$cant_pages}
{if $next_offset >= 0}
&nbsp;[<a class="prevnext" href="tiki-admin_html_page_content.php?find={$find}&amp;offset={$next_offset}&amp;sort_mode={$sort_mode}">{tr}next{/tr}</a>]
{/if}
</div>
</div>

