{* $Id$ *}
{* Note: if you edit this file, make sure to make corresponding edits on tiki-edit_submission.tpl*}

{popup_init src="lib/overlib.js"}
{include file='tiki-articles-js.tpl'}

{assign var=area_name value="body"}

{title help="Articles"}
	{if $articleId}
		{tr}Edit:{/tr} {$title|escape}
	{else}
		{tr}Edit article{/tr}
	{/if}
{/title}

<div class="navbar">
	{button href="tiki-list_articles.php" _text="{tr}List Articles{/tr}"}
	{button href="tiki-view_articles.php" _text="{tr}View Articles{/tr}"}
</div>

{remarksbox type="tip" title="{tr}Tip{/tr}"}
	{tr}Use ...page... to separate pages in a multi-page post{/tr}
{/remarksbox}

{if $preview}
	{include file='tiki-preview_article.tpl'}
{/if}

{if !empty($errors)}
	<div class="simplebox highlight">
		{tr}One of the email addresses you typed is invalid{/tr}
		<br />
		{foreach from=$errors item=m name=errors}
			{$m}
			{if !$smarty.foreach.errors.last}<br />{/if}
		{/foreach}
	</div>
{/if}

<form enctype="multipart/form-data" method="post" action="tiki-edit_article.php" id='editpageform'>
	<input type="hidden" name="articleId" value="{$articleId|escape}" />
	<input type="hidden" name="previewId" value="{$previewId|escape}" />
	<input type="hidden" name="imageIsChanged" value="{$imageIsChanged|escape}" />
	<input type="hidden" name="image_data" value="{$image_data|escape}" />
	<input type="hidden" name="useImage" value="{$useImage|escape}" />
	<input type="hidden" name="image_type" value="{$image_type|escape}" />
	<input type="hidden" name="image_name" value="{$image_name|escape}" />
	<input type="hidden" name="image_size" value="{$image_size|escape}" />
	<table class="normal">
		<tr class="formcolor" id='show_topline' {if $types.$type.show_topline eq 'y'}style="display:;"{else}style="display:none;"{/if}>
			<td>{tr}Topline{/tr} *</td>
			<td><input type="text" name="topline" value="{$topline|escape}" size="60" /></td>
		</tr>
		<tr class="formcolor">
			<td>{tr}Title{/tr}</td>
			<td>
				<input type="text" name="title" value="{$title|escape}" maxlength="255" size="80" />
			</td>
		</tr>
		<tr class="formcolor" id='show_subtitle' {if $types.$type.show_subtitle eq 'y'}style="display:;"{else}style="display:none;"{/if}>
			<td>{tr}Subtitle{/tr} *</td>
			<td>
				<input type="text" name="subtitle" value="{$subtitle|escape}" size="60" />
			</td>
		</tr>
		<tr class="formcolor" id='show_linkto' {if $types.$type.show_linkto eq 'y'}style="display:;"{else}style="display:none;"{/if}>
			<td>{tr}Source{/tr} ({tr}URL{/tr}) *</td>
			<td>
				<input type="text" name="linkto" value="{$linkto|escape}" size="60" />
			</td>
		</tr>
		{if $prefs.feature_multilingual eq 'y'}
			<tr class="formcolor" id='show_lang' {if $types.$type.show_lang eq 'y'}style="display:;"{else}style="display:none;"{/if}>
				<td>{tr}Language{/tr}</td>
				<td>
					<select name="lang">
						<option value="">{tr}All{/tr}</option>
						{section name=ix loop=$languages}
							<option value="{$languages[ix].value|escape}"{if $lang eq $languages[ix].value} selected="selected"{/if}>{$languages[ix].name}</option>
						{/section}
					</select>
				</td>
			</tr>
		{/if}
		<tr class="formcolor" id='show_author' {if $types.$type.show_author eq 'y'}style="display:;"{else}style="display:none;"{/if}>
			<td>{tr}Author Name{/tr}</td>
			<td>
				<input type="text" name="authorName" value="{$authorName|escape}" />
			</td>
		</tr>
		<tr class="formcolor">
			<td>{tr}Topic{/tr}</td>
			<td>
				<select name="topicId">
					{section name=t loop=$topics}
						<option value="{$topics[t].topicId|escape}" {if $topicId eq $topics[t].topicId}selected="selected"{/if}>{$topics[t].name|escape}</option>
					{/section}
					<option value="" {if $topicId eq 0}selected="selected"{/if}>{tr}None{/tr}</option>
				</select>
				{if $tiki_p_admin_cms eq 'y'}
					<a href="tiki-admin_topics.php" class="link">{tr}Admin Topics{/tr}</a>
				{/if}
			</td>
		</tr>
		<tr class="formcolor">
			<td>{tr}Type{/tr}</td>
			<td>
				<select id='articletype' name='type' onchange='javascript:chgArtType();'>
					{foreach from=$types key=typei item=prop}
						<option value="{$typei|escape}" {if $type eq $typei}selected="selected"{/if}>{tr}{$typei|escape}{/tr}</option>
					{/foreach}
				</select>
				{if $tiki_p_admin_cms eq 'y'}
					<a href="tiki-article_types.php" class="link">{tr}Admin Types{/tr}</a>
				{/if}
			</td>
		</tr>
		<tr id='use_ratings' {if $types.$type.use_ratings eq 'y'}style="display:;"{else}style="display:none;"{/if} class="formcolor">
			<td>{tr}Rating{/tr}</td>
			<td>
				<select name='rating'>
					<option value="10" {if $rating eq 10}selected="selected"{/if}>10</option>
					<option value="9.5" {if $rating eq "9.5"}selected="selected"{/if}>9.5</option>
					<option value="9" {if $rating eq 9}selected="selected"{/if}>9</option>
					<option value="8.5" {if $rating eq "8.5"}selected="selected"{/if}>8.5</option>
					<option value="8" {if $rating eq 8}selected="selected"{/if}>8</option>
					<option value="7.5" {if $rating eq "7.5"}selected="selected"{/if}>7.5</option>
					<option value="7" {if $rating eq 7}selected="selected"{/if}>7</option>
					<option value="6.5" {if $rating eq "6.5"}selected="selected"{/if}>6.5</option>
					<option value="6" {if $rating eq 6}selected="selected"{/if}>6</option>
					<option value="5.5" {if $rating eq "5.5"}selected="selected"{/if}>5.5</option>
					<option value="5" {if $rating eq 5}selected="selected"{/if}>5</option>
					<option value="4.5" {if $rating eq "4.5"}selected="selected"{/if}>4.5</option>
					<option value="4" {if $rating eq 4}selected="selected"{/if}>4</option>
					<option value="3.5" {if $rating eq "3.5"}selected="selected"{/if}>3.5</option>
					<option value="3" {if $rating eq 3}selected="selected"{/if}>3</option>
					<option value="2.5" {if $rating eq "2.5"}selected="selected"{/if}>2.5</option>
					<option value="2" {if $rating eq 2}selected="selected"{/if}>2</option>
					<option value="1.5" {if $rating eq "1.5"}selected="selected"{/if}>1.5</option>
					<option value="1" {if $rating eq 1}selected="selected"{/if}>1</option>
					<option value="0.5" {if $rating eq "0.5"}selected="selected"{/if}>0.5</option>
				</select>
			</td>
		</tr>
		<tr id='show_image_1' {if $types.$type.show_image eq 'y'}style="display:;"{else}style="display:none;"{/if} class="formcolor">
			<td>{tr}Own Image{/tr}</td>
			<td>
				<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
				<input name="userfile1" type="file" onchange="document.getElementById('useImage').checked = true;"/>
			</td>
		</tr>
		{if $hasImage eq 'y'}
			<tr class="formcolor">
				<td>{tr}Own Image{/tr}</td>
				<td>{$image_name} [{$image_type}] ({$image_size} {tr}bytes{/tr})</td>
			</tr>
			<tr class="formcolor">
				<td>{tr}Own Image{/tr}</td>
				{if $imageIsChanged eq 'y'}
					<td>
						<img alt="{tr}Article image{/tr}" src="article_image.php?image_type=preview&amp;id={$previewId}" />
					</td>
				{else}
					<td>
						<img alt="{tr}Article image{/tr}" src="article_image.php?image_type=article&amp;id={$articleId}" />
					</td>
				{/if}
			</tr>
		{/if}
		<tr id='show_image_2' {if $types.$type.show_image eq 'y'}style="display:;"{else}style="display:none;"{/if} class="formcolor">
			<td>{tr}Use own image{/tr}</td>
			<td>
				<input type="checkbox" name="useImage" id="useImage" {if $useImage eq 'y'}checked='checked'{/if}/>
			</td>
		</tr>
		<tr id='show_image_3' {if $types.$type.show_image eq 'y'}style="display:;"{else}style="display:none;"{/if} class="formcolor">
			<td>{tr}Float text around image{/tr}</td>
			<td>
				<input type="checkbox" name="isfloat" {if $isfloat eq 'y'}checked='checked'{/if}/>
			</td>
		</tr>
		<tr id='show_image_4' {if $types.$type.show_image eq 'y'}style="display:;"{else}style="display:none;"{/if} class="formcolor">
			<td>{tr}Own image size x{/tr}</td>
			<td>
				<input type="text" name="image_x" value="{$image_x|escape}" />
				{tr}pixels{/tr}
			</td>
		</tr>
		<tr id='show_image_5' {if $types.$type.show_image eq 'y'}style="display:;"{else}style="display:none;"{/if} class="formcolor">
			<td>{tr}Own image size y{/tr}</td>
			<td>
				<input type="text" name="image_y" value="{$image_y|escape}" />
				{tr}pixels{/tr}
			</td>
		</tr>
		<tr id='show_image_caption' {if $types.$type.show_image_caption eq 'y'}style="display:;"{else}style="display:none;"{/if} class="formcolor">
			<td>{tr}Image caption{/tr} *</td>
			<td>
				<input type="text" name="image_caption" value="{$image_caption|escape}" size="80" />
			</td>
		</tr>

		{if $prefs.feature_cms_templates eq 'y' and $tiki_p_use_content_templates eq 'y'}
			<tr class="formcolor">
				<td>{tr}Apply template{/tr}</td>
				<td>
					<select name="templateId" onchange="javascript:document.getElementById('editpageform').submit();">
						<option value="0">{tr}none{/tr}</option>
						{section name=ix loop=$templates}
							<option value="{$templates[ix].templateId|escape}">{tr}{$templates[ix].name}{/tr}</option>
						{/section}
					</select>
				</td>
			</tr>
		{/if}

		{include file='categorize.tpl'}


		<tr class="formcolor">
			<td>
				{tr}Heading{/tr}
			</td>
			<td>
				{toolbars area_name='heading'}
				<textarea class="wikiedit" name="heading" rows="5" cols="80" id='subheading' wrap="virtual">{$heading|escape}</textarea>
			</td>
		</tr>


		<tr id='heading_only' {if $types.$type.heading_only ne 'y'}style="display:;"{else}style="display:none;"{/if} class="formcolor">
			<td>
				{tr}Body{/tr}
			</td>
			<td>
				{toolbars area_name='body'}
				<textarea class="wikiedit" id="body" name="body" rows="{$rows}" cols="{$cols}" wrap="virtual">{$body|escape}</textarea>
				<input type="hidden" name="rows" value="{$rows}"/>
				<input type="hidden" name="cols" value="{$cols}"/>
			</td>
		</tr>

		{if $prefs.cms_spellcheck eq 'y'}
			<tr class="formcolor">
				<td>
					{tr}Spellcheck:{/tr} </td>
				<td>
					<input type="checkbox" name="spellcheck" {if $spellcheck eq 'y'}checked="checked"{/if}/>
				</td>
			</tr>
		{/if}

		<tr id='show_pubdate' {if $types.$type.show_pubdate eq 'y' || $types.$type.show_pre_publ ne 'y'}style="display:;"{else}style="display:none;"{/if} class="formcolor">
			<td>{tr}Publish Date{/tr}</td>
			<td>
				{html_select_date prefix="publish_" time=$publishDateSite start_year="-5" end_year="+10" field_order=$prefs.display_field_order} {tr}at{/tr} 
				<span dir="ltr">{html_select_time prefix="publish_" time=$publishDateSite display_seconds=false}
					&nbsp;{$siteTimeZone}
				</span>
			</td>
		</tr>

		<tr id='show_expdate' {if $types.$type.show_expdate eq 'y' || $types.$type.show_post_expire ne 'y'}style="display:;"{else}style="display:none;"{/if} class="formcolor">
			<td>{tr}Expiration Date{/tr}</td>
			<td>
				{html_select_date prefix="expire_" time=$expireDateSite start_year="-5" end_year="+10" field_order=$prefs.display_field_order} {tr}at{/tr} 
				<span dir="ltr">
					{html_select_time prefix="expire_" time=$expireDateSite display_seconds=false}
					&nbsp;
					{$siteTimeZone}
				</span>
			</td>
		</tr>

		{if $tiki_p_use_HTML eq 'y'}
			<tr class="formcolor">
				<td>{tr}Allow HTML{/tr}</td>
				<td>
					<input type="checkbox" name="allowhtml" {if $allowhtml eq 'y'}checked="checked"{/if}/>
				</td>
			</tr>
		{/if}
		
		{if $prefs.feature_cms_emails eq 'y' and $articleId eq 0}
			<tr class="formcolor">
				<td>
					{tr}Emails to be notified (separated with commas){/tr}
				</td>
				<td>
					<input type="text" name="emails" value="{$emails|escape}" size="80" />
					<br />
					{if !empty($userEmail) and $userEmail ne $prefs.sender_email}
						{tr}From:{/tr} {$userEmail|escape}
						<input type="radio" name="from" value="{$userEmail|escape}"{if empty($from) or $from eq $userEmail} checked="checked"{/if} /> 
						{$prefs.sender_email|escape}
						<input type="radio" name="from" value="{$prefs.sender_email|escape}"{if $from eq $prefs.sender_email} checked="checked"{/if} />
					{/if}
				</td>
			</tr>
		{/if}

		{include file='freetag.tpl'}

		<tr class="formcolor">
			<td></td>
			<td>
				<input type="submit" class="wikiaction" name="preview" value="{tr}Preview{/tr}" />
				<input type="submit" class="wikiaction" name="save" value="{tr}Save{/tr}" />
			</td>
		</tr>
	</table>
</form>

<br />
