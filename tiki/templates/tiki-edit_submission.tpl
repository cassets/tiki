{if $preview}
{include file="tiki-preview_article.tpl"}
{/if}
<a class="pagetitle" href="tiki-edit_submission.php">{tr}Edit{/tr}: {$title}</a><br/><br/>
[<a class="link" href="tiki-list_submissions.php">list submissions</a>]
<br/><br/>
<form enctype="multipart/form-data" method="post" action="tiki-edit_submission.php">
<input type="hidden" name="subId" value="{$subId}" />
<input type="hidden" name="image_data" value="{$image_data}" />
<input type="hidden" name="useImage" value="{$useImage}" />
<input type="hidden" name="image_type" value="{$image_type}" />
<input type="hidden" name="image_name" value="{$image_name}" />
<input type="hidden" name="image_size" value="{$image_size}" />
<table class="normal">
<tr><td class="formcolor">{tr}Title{/tr}</td><td class="formcolor"><input type="text" name="title" value="{$title}" /></td></tr>
<tr><td class="formcolor">{tr}Author Name{/tr}</td><td class="formcolor"><input type="text" name="authorName" value="{$authorName}" /></td></tr>
<tr><td class="formcolor">{tr}Topic{/tr}</td><td class="formcolor">
<select name="topicId">
{section name=t loop=$topics}
<option value="{$topics[t].topicId}" {if $topicId eq $topics[t].topicId}selected="selected"{/if}>{$topics[t].name}</option>
{/section}
</select></td></tr>
<tr><td class="formcolor">{tr}Own Image{/tr}</td><td class="formcolor"><input type="hidden" name="MAX_FILE_SIZE" value="1000000">
<input name="userfile1" type="file"></td></tr>
{if $hasImage eq 'y'}
<tr><td class="formcolor">Own Image: </td><td class="formcolor">{$image_name} [{$image_type}] ({$image_size} bytes)</td></tr>
{if $tempimg ne 'n'}
<tr><td class="formcolor">Own Image:</td><td class="formcolor">
<img alt="theimage" border="0" src="{$tempimg}" {if $image_x > 0}width="{$image_x}"{/if}{if $image_y > 0 }height="{$image_y}"{/if}/>
</td></tr>
{/if}
{/if}
<tr><td class="formcolor">{tr}Use own image{/tr}</td><td class="formcolor">
<input type="checkbox" name="useImage" {if $useImage eq 'y'}checked='checked'{/if}/>
</td></tr>
<tr><td class="formcolor">{tr}Own image size x{/tr}</td><td class="formcolor"><input type="text" name="image_x" value="{$image_x}" /></td></tr>
<tr><td class="formcolor">{tr}Own image size y{/tr}</td><td class="formcolor"><input type="text" name="image_y" value="{$image_y}" /></td></tr>
<tr><td class="formcolor">{tr}Heading{/tr}</td><td class="formcolor"><textarea class="wikiedit" name="heading" rows="5" cols="80" wrap="virtual">{$heading}</textarea></td></tr>
<tr><td class="formcolor">{tr}Body{/tr}</td><td class="formcolor"><textarea class="wikiedit" name="body" rows="25" cols="80" wrap="virtual">{$body}</textarea></td></tr>
<tr><td class="formcolor">{tr}Publish Date{/tr}</td><td class="formcolor">
{html_select_date time=$publishDate end_year="+1"} at {html_select_time time=$publishDate display_seconds=false}
</td></tr>
</table>
{if $tiki_p_use_HTML eq 'y'}
<div align="center">{tr}Allow HTML{/tr}: <input type="checkbox" name="allowhtml" {if $allowhtml eq 'y'}checked="checked"{/if}/></div>
{/if}
<div align="center">
<input type="submit" class="wikiaction" name="preview" value="{tr}preview{/tr}" />
<input type="submit" class="wikiaction" name="save" value="{tr}save{/tr}" />
</div>
</form>
<br/>
<div class="wiki-edithelp">
<p>
<a class="wiki">{tr}TextFormattingRules{/tr}</a><br />
<strong>{tr}Emphasis{/tr}:</strong> '<strong></strong>' {tr}for{/tr} <em>{tr}italics{/tr}</em>, _<em></em>_ {tr}for{/tr} <strong>{tr}bold{/tr}</strong>, '<strong></strong>'_<em></em>_ {tr}for{/tr} <em><strong>{tr}both{/tr}</strong></em><br />
<strong>{tr}Lists{/tr}:</strong> * {tr}for bullet lists{/tr}, # {tr}for numbered lists{/tr}, ;{tr}term{/tr}:{tr}definition{/tr} {tr}for definiton lists{/tr}<br/> 
<strong>{tr}References{/tr}:</strong> {tr}JoinCapitalizedWords{/tr} {tr}or use square brackets for an{/tr} {tr}external link{/tr}: [URL] or [URL|{tr}link_description{/tr}] or [URL|description|nocache].<br />
<strong>{tr}Misc{/tr}</strong> "!", "!!", "!!!" {tr}make_headings{/tr}, "-<em></em>-<em></em>-<em></em>-" {tr}makes a horizontal rule{/tr}<br />
<strong>{tr}Title_bar{/tr}</strong> "-={tr}title{/tr}=-" {tr}creates a title bar{/tr}.<br/>
<strong>{tr}Images{/tr}</strong> "{literal}{{/literal}img src=http://example.com/foo.jpg width=200 height=100 align=center link=http://www.yahoo.com desc=foo}" {tr}displays an image{/tr} {tr}height width desc link and align are optional{/tr}<br/> 
<strong>{tr}Non cacheable images{/tr}</strong> "{literal}{{/literal}img nocache=1 src=http://example.com/foo.jpg width=200 height=100 align=center link=http://www.yahoo.com desc=foo}" {tr}displays an image{/tr} {tr}height width desc link and align are optional{/tr}<br/> 
<strong>{tr}Tables{/tr}</strong> "||row1-col1|row1-col2|row1-col3||row2-col1|row2-col2|row3-col3||" {tr}creates a table{/tr}<br/>
<strong>{tr}RSS feeds{/tr}</strong> "{literal}{{/literal}rss id=n max=m{literal}}{/literal}" {tr}displays rss feed with id=n maximum=m items{/tr}<br/>
<strong>{tr}Simple box{/tr}</strong> "^{tr}Box content{/tr}^" {tr}Creates a box with the data{/tr}<br/>
<strong>{tr}Dynamic content{/tr}</strong> "{literal}{{/literal}content id=n}" {tr}Will be replaced by the actual value of the dynamic content block with id=n{/tr}<br/>
</p>
</div>
