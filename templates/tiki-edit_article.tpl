{* $Id$ *}
{* Note: if you edit this file, make sure to make corresponding edits on tiki-edit_submission.tpl*}

{include file='tiki-articles-js.tpl'}

{title help="Articles" admpage="articles"}
	{if $articleId}
		{tr}Edit:{/tr} {$arttitle}
	{else}
		{tr}Edit article{/tr}
	{/if}
{/title}

<div class="t_navbar btn-group form-group">
	{button href="tiki-list_articles.php" class="btn btn-default" _text="{tr}List Articles{/tr}"}
	{button href="tiki-view_articles.php" class="btn btn-default" _text="{tr}View Articles{/tr}"}
</div>

{if $preview}
	<h2>{tr}Preview{/tr}</h2>
	
	{include file='article.tpl'}
{/if}

{if !empty($errors)}
	<div class="alert-warning">
		{tr}One of the email addresses you typed is invalid{/tr}
		<br>
		{foreach from=$errors item=m name=errors}
			{$m}
			{if !$smarty.foreach.errors.last}<br>{/if}
		{/foreach}
	</div>
{/if}

<form enctype="multipart/form-data" method="post" action="tiki-edit_article.php" id='editpageform' role="form">
	<input type="hidden" name="articleId" value="{$articleId|escape}">
	<input type="hidden" name="previewId" value="{$previewId|escape}">
	<input type="hidden" name="imageIsChanged" value="{$imageIsChanged|escape}">
	<input type="hidden" name="image_data" value="{$image_data|escape}">
	<input type="hidden" name="useImage" value="{$useImage|escape}">
	<input type="hidden" name="image_type" value="{$image_type|escape}">
	<input type="hidden" name="image_name" value="{$image_name|escape}">
	<input type="hidden" name="image_size" value="{$image_size|escape}">
	{if !empty($translationOf)}
		<input type="hidden" name="translationOf" value="{$translationOf|escape}">
	{/if}
	{tabset}
		{tab name="{tr}General{/tr}"}
            <h2>{tr}General{/tr}</h2>
			<div class="form-group">
				<label for="title">{tr}Title{/tr}</label>
				<input type="text" name="title" value="{$arttitle|escape}" maxlength="255" class="form-control">
			</div>
			<div class="form-group">
				<label for="heading">{tr}Heading{/tr}</label>
				{if $types.$type.heading_only eq 'y'}
					{textarea name="heading" rows="5" class="form-control" Height="200px" id="subheading"}{$heading}{/textarea}
				{else}
					{textarea _simple="y" name="heading" class="form-control" rows="5" Height="200px" id="subheading" comments="y"}{$heading}{/textarea}
				{/if}
			</div>
			<div class="form-group {if $types.$type.heading_only eq 'y'}hidden{/if}">
				<label for="body">{tr}Body{/tr}</label>
				{textarea name="body" id="body"}{$body}{/textarea}
			</div>
			{if $tiki_p_use_HTML eq 'y'}
				{if $smarty.session.wysiwyg neq 'y'}
					<div class="checkbox">
						<label>
							<input type="checkbox" name="allowhtml" {if $allowhtml eq 'y'}checked="checked"{/if}>
							{tr}Allow full HTML{/tr} <em>({tr}Keep any HTML tag.{/tr})</em>
						</label>
						<div class="help-block">{tr}If not enabled, Tiki will retain some HTML tags (a, p, pre, img, hr, b, i){/tr}.</div>
					</div>
				{else}
					<input type="hidden" name="allowhtml" value="{if $allowhtml eq 'y'}on{/if}">
				{/if}
			{/if}
			{if $prefs.feature_multilingual eq 'y'}
				<div class="form-group">
					<label for="lang">{tr}Language{/tr}</label>
					<select name="lang" class="form-control">
						<option value="">{tr}All{/tr}</option>
						{section name=ix loop=$languages}
							<option value="{$languages[ix].value|escape}"{if $lang eq $languages[ix].value} selected="selected"{/if}>{$languages[ix].name}</option>
						{/section}
					</select>
					<div class="help-block">
						{if $articleId != 0}
							{tr _0="tiki-edit_article.php?translationOf=$articleId"}To translate, do not change the language and the content.
							Instead, <a href="%0">create a new translation</a> in the new language.{/tr}
						{/if}
						{if $translations}
							<p>{tr}Edit Translations{/tr}:</p>
							{section loop=$translations name=t}
								{if $articleId != $translations[t].objId}
									{$translations[t].lang|escape}: <a href="tiki-edit_article.php?articleId={$translations[t].objId|escape}">{$translations[t].objName|escape}</a><br>
								{/if}
							{/section}
						{/if}
					</div>
				</div>
			{/if}
			<div class="checkbox">
				<label>
					<input type="checkbox" name="ispublished" {if $ispublished eq 'y'}checked="checked"{/if}>
					{tr}Published{/tr}
				</label>
			</div>
			<div>
				<input type="submit" class="wikiaction btn btn-default" name="preview" value="{tr}Preview{/tr}" onclick="needToConfirm=false;">
				<input type="submit" class="wikiaction btn btn-primary" name="save" value="{tr}Save{/tr}"  onclick="this.form.saving=true;needToConfirm=false;">
				{if $articleId}<input type="submit" class="wikiaction tips btn btn-link" title="{tr}Cancel{/tr}|{tr}Cancel the edit, you will lose your changes.{/tr}" name="cancel_edit" value="{tr}Cancel Edit{/tr}"  onclick="needToConfirm=false;">{/if}
			</div>
		{/tab}
		{tab name="{tr}Publication{/tr}"}
            <h2>{tr}Publication{/tr}</h2>
			<div class="form-group">
				<label for="authorName">{tr}Author Name (as displayed){/tr}</label>
				<input type="text" name="authorName" value="{$authorName|escape}" class="form-control">
			</div>
			<div class="form-group {if $tiki_p_edit_article_user neq 'y'}hidden{/if}">
				<label for="author">{tr}User (article owner){/tr}</label>
				<input id="author" type="text" name="author" value="{$author|escape}"  class="form-control">
				{autocomplete element='#author' type='username'}
			</div>
			<div class="form-group {if $types.$type.show_pubdate neq 'y' and $types.$type.show_pre_publ eq 'y'}hidden{/if}">
				<label>{tr}Publish Date{/tr}</label>
				<div class="form-group">
					{html_select_date prefix="publish_" time=$publishDate start_year="-10" end_year="+10" field_order=$prefs.display_field_order}
					{tr}at{/tr}
					<span dir="ltr">
						{html_select_time prefix="publish_" time=$publishDate display_seconds=false use_24_hours=$use_24hr_clock}
						&nbsp;
						{$siteTimeZone}
					</span>
				</div>
			</div>
			<div class="form-group {if $types.$type.show_expdate neq 'y' and $types.$type.show_post_expire eq 'y'}hidden{/if}">
				<label>{tr}Exiration Date{/tr}</label>
				<div class="form-group">
					{html_select_date prefix="expire_" time=$expireDate start_year="-10" end_year="+10" field_order=$prefs.display_field_order}
					{tr}at{/tr}
					<span dir="ltr">
						{html_select_time prefix="expire_" time=$expireDate display_seconds=false use_24_hours=$use_24hr_clock}
						&nbsp;
						{$siteTimeZone}
					</span>
				</div>
			</div>
		{/tab}
		{tab name="{tr}Classification{/tr}"}
            <h2>{tr}Classification{/tr}</h2>
			<div class="form-group">
				<label for="topicId">{tr}Topic{/tr}</label>
				<div>
					<select name="topicId">
						{foreach $topics as $topic}
							<option value="{$topic.topicId|escape}" {if $topicId eq $topic.topicId}selected="selected"{/if}>{$topic.name|escape}</option>
						{/foreach}
						<option value="" {if $topicId eq 0}selected="selected"{/if}>{tr}None{/tr}</option>
					</select>
					{if $tiki_p_admin_cms eq 'y'}
						<a href="tiki-admin_topics.php" class="btn btn-link">{tr}Admin Topics{/tr}</a>
					{/if}
				</div>
			</div>
			<div class="form-group">
				<label for="articletype">{tr}Type{/tr}</label>
				<div>
					<select name="articletype">
						{foreach $types as $typei => $prop}
							<option value="{$typei|escape}" {if $type eq $typei}selected="selected"{/if}>{tr}{$typei|escape}{/tr}</option>
						{/foreach}
					</select>
					{if $tiki_p_admin_cms eq 'y'}
						<a href="tiki-article_types.php" class="btn btn-link">{tr}Admin Types{/tr}</a>
					{/if}
				</div>
			</div>
			{include file='categorize.tpl'}
			{include file='freetag.tpl'}
		{/tab}
		{tab name="{tr}Image{/tr}"}
            <h2>{tr}Image{/tr}</h2>
			<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
			<div class="form-group {if $types.$type.show_image neq 'y'}hidden{/if}">
				<label for="userfile1">{tr}Own Image{/tr}</label>
				<input name="userfile1" type="file" onchange="document.getElementById('useImage').checked = true;">
				<div class="help-block">
					{tr}If not the topic image{/tr}
				</div>
			</div>
			{if $hasImage eq 'y'}
				<div class="form-group">
					<label>{tr}Current Image{/tr}</label>
					<div class="form-control">
						{if $imageIsChanged eq 'y'}
							<img alt="{tr}Article image{/tr}" src="article_image.php?image_type=preview&amp;id={$previewId}">
						{else}
							<img alt="{tr}Article image{/tr}" src="article_image.php?image_type=article&amp;id={$articleId}">
						{/if}
					</div>
				</div>
			{/if}
			<div class="form-group {if $types.$type.show_image_caption neq 'y'}hidden{/if}">
				<label for="image_caption">{tr}Image caption{/tr}</label>
				<input type="text" class="form-control" name="image_caption" value="{$image_caption|escape}" >
				<div class="help-block">{tr}Default will use the topic name{/tr}</div>
			</div>
			<div class="checkbox {if $types.$type.show_image neq 'y'}hidden{/if}">
				<label>
					<input type="checkbox" name="useImage" id="useImage" {if $useImage eq 'y'}checked='checked'{/if} >
					{tr}Use own image{/tr}
				</label>
			</div>
			<div class="checkbox {if $types.$type.show_image neq 'y'}hidden{/if}">
				<label>
					<input type="checkbox" name="isfloat" {if $isfloat eq 'y'}checked='checked'{/if}>
					{tr}Float text around image{/tr}
				</label>
			</div>
			<fieldset class="{if $types.$type.show_image neq 'y'}hidden{/if} form-horizontal">
				<legend>{tr}Read Article{/tr}</legend>
				<p>{tr}Maximum dimensions of custom image in view mode{/tr}</p>
				<div class="form-group">
					<label for="image_x" class="control-label col-sm-2">{tr}Width{/tr}</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="image_x"{if $image_x > 0} value="{$image_x|escape}"{/if}> 
						<div class="help-block">{tr}pixels{/tr}</div>
					</div>
				</div>
				<div class="form-group">
					<label for="image_y" class="control-label col-sm-2">{tr}Height{/tr}</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="image_y"{if $image_y > 0} value="{$image_y|escape}"{/if}>
						<div class="help-block">{tr}pixels{/tr}</div>
					</div>
				</div>
			</fieldset>
			<fieldset class="{if $types.$type.show_image neq 'y'}hidden{/if} form-horizontal">
				<legend>{tr}View Articles{/tr}</legend>
				<p>{tr}Maximum dimensions of custom image in list mode{/tr}</p>
				<div class="form-group">
					<label for="list_image_x" class="control-label col-sm-2">{tr}Width{/tr}</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="list_image_x"{if $list_image_x > 0} value="{$list_image_x|escape}"{/if}> 
						<div class="help-block">{tr}pixels{/tr}</div>
					</div>
				</div>
				<div class="form-group">
					<label for="list_image_y" class="control-label col-sm-2">{tr}Height{/tr}</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="list_image_y"{if $list_image_y > 0} value="{$list_image_y|escape}"{/if}>
						<div class="help-block">{tr}pixels{/tr}</div>
					</div>
				</div>
			</fieldset>
		{/tab}
		{tab name="{tr}Advanced{/tr}"}
            <h2>{tr}Advanced{/tr}</h2>
			{if $prefs.feature_multilingual eq 'y' and empty($translationOf)}
				<div class="form-group">
					<label for="translationOf">{tr}Attach existing article ID as translation{/tr}</label>
					<input name="translationOf" type="text" size="4" class="form-control">
				</div>
			{/if}
			<div class="form-group {if $types.$type.show_topline neq 'y'}hidden{/if}">
				<label for="topline">{tr}Topline{/tr}</label>
				<input type="text" name="topline" value="{$topline|escape}" size="60" class="form-control">
			</div>
			<div class="form-group {if $types.$type.show_subtitle neq 'y'}hidden{/if}">
				<label for="subtitle">{tr}Subtitle{/tr}</label>
				<input type="text" name="subtitle" value="{$subtitle|escape}" size="60" class="form-control">
			</div>
			<div class="form-group {if $types.$type.show_linkto neq 'y'}hidden{/if}">
				<label for="linkto">{tr}Source{/tr}</label>
				<input type="url" name="linkto" value="{$linkto|escape}" size="60" class="form-control" placeholder="{tr}http://...{/tr}">
				{if $linkto neq ''}
					<div class="help-block">
						{tr}Test your link: {/tr}
						<a href="{$linkto|escape}" target="_blank">{tr}View{/tr}</a>
					</div>
				{/if}
			</div>
			<div class="form-group {if $types.$type.use_ratings neq 'y'}hidden{/if}">
				<label for="rating">{tr}Author Rating{/tr}</label>
				<select name='rating' class="form-control">
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
			</div>
			{if $prefs.geo_locate_article eq 'y'}
				<div class="form-group">
					<label>{tr}Location{/tr}</label>
					<div class="map-container" data-geo-center="{defaultmapcenter}" data-target-field="geolocation" style="height: 250px; width: 250px;"></div>
					<input type="hidden" name="geolocation" value="{$geolocation_string|escape}">
					{$headerlib->add_map()}
				</div>
			{/if}
			{if $prefs.feature_cms_templates eq 'y' and $tiki_p_use_content_templates eq 'y' and $templates|@count ne 0}
				<div class="form-group">
					<label for="templateId">{tr}Apply template{/tr}</label>
					<select name="templateId" onchange="javascript:document.getElementById('editpageform').submit();">
						<option value="0">{tr}none{/tr}</option>
						{foreach $templates as $template}
							<option value="{$template.templateId|escape}">{tr}{$template.name|escape}{/tr}</option>
						{/foreach}
					</select>
				</div>
			{/if}

		
			{if $prefs.feature_cms_emails eq 'y' and $articleId eq 0}
				<div class="form-group">
					<label for="emails">{tr}Emails to be notified (separated with commas){/tr}</label>
					<div>
						<input type="text" name="emails" value="{$emails|escape}" size="60" class="form-control">
						{if !empty($userEmail) and $userEmail ne $prefs.sender_email}
							{tr}From:{/tr} 
							<label>
								<input type="radio" name="from" value="{$userEmail|escape}"{if empty($from) or $from eq $userEmail} checked="checked"{/if}> 
								{$userEmail|escape}
							</label>
							<label>
								<input type="radio" name="from" value="{$prefs.sender_email|escape}"{if $from eq $prefs.sender_email} checked="checked"{/if}>
								{$prefs.sender_email|escape}
							</label>
						{/if}
					</div>
				</div>
			{/if}

			{if ! empty($all_attributes)}
				<fieldset>
					<legend>{tr}Attributes{/tr}</legend>
					{foreach from=$all_attributes item=att key=attname}
						{assign var='attid' value=$att.itemId|replace:'.':'_'}
						{assign var='attfullname' value=$att.itemId}
						<div class="form-group" id={$attid} {if $types.$type.$attid eq 'y'}style="display:;"{else}style="display:none;"{/if}>
							<label for="{$attfullname|escape}">{$attname|escape}</label>
							<input type="text" name="{$attfullname|escape}" value="{$article_attributes.$attfullname|escape}" size="60" maxlength="255" class="form-control">
						</div>
					{/foreach}
				</fieldset>
			{/if}
		{/tab}
	{/tabset}
	
{if $smarty.session.wysiwyg neq 'y'}
	{jq}
$("#editpageform").submit(function(evt) {
	var isHtml = false;
	if (this.saving && !$("input[name=allowhtml]:checked").length) {
		$("textarea", this).each(function(){
			if ($(this).val().match(/<([A-Z][A-Z0-9]*)\b[^>]*>(.*?)<\/\1>/i)) {
				isHtml = true;
			}
		});
		if (isHtml) {
			this.saving = false;
			return confirm(tr('You appear to be using HTML in your article but have not selected "Allow full HTML".\nThis will result in HTML tags being removed.\nDo you want to save your edits anyway?'));
		}
	}
	return true;
}).attr('saving', false);
	{/jq}
{/if}
</form>

<br>
