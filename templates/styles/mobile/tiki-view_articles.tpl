{if !isset($actions) or $actions eq "y"}
	{if $prefs.art_home_title ne ''}
		{title help="Articles" admpage="articles"}
			{if $prefs.art_home_title eq 'topic' and !empty($topic)}{tr}{$topic|escape}{/tr}
			{elseif $prefs.art_home_title eq 'type' and !empty($type)}{tr}{$type|escape}{/tr}
			{else}{tr}Articles{/tr}{/if}
		{/title}
	{/if}
	<div class="clearfix" style="clear: both;">
		<div style="float: right; padding-left:10px; white-space: nowrap" data-role="controlgroup" data-type="horizontal"> {* mobile *}
		{if $user and $prefs.feature_user_watches eq 'y'}
			{if $user_watching_articles eq 'n'}
					<a {if $prefs.mobile_mode eq "y"}data-role="button" {/if}href="{query _type='relative' watch_event='article_*' watch_object='*' watch_action='add'}" title="{tr}Monitor Articles{/tr}">{icon _id=eye alt="{tr}Monitor Articles{/tr}"}</a> {* mobile *}
			{else}
					<a {if $prefs.mobile_mode eq "y"}data-role="button" {/if}href="{query _type='relative' watch_event='article_*' watch_object='*' watch_action='remove'}" title="{tr}Stop Monitoring Articles{/tr}">{icon _id=no_eye alt="{tr}Stop Monitoring Articles{/tr}"}</a> {* mobile *}
			{/if}
		{/if}
		{if $prefs.feature_group_watches eq 'y' and $tiki_p_admin_users eq 'y'}
			<a {if $prefs.mobile_mode eq "y"}data-role="button" {/if}href="tiki-object_watches.php?watch_event=article_*&amp;objectId=*" class="icon">{icon _id='eye_group' alt="{tr}Group Monitor{/tr}"}</a> {* mobile *}
		{/if}
		</div>
	</div>
{/if}
{section name=ix loop=$listpages}
	{capture name=href}{if empty($urlparam)}{$listpages[ix].articleId|sefurl:article}{else}{$listpages[ix].articleId|sefurl:article:with_next}{$urlparam}{/if}{/capture}
	{if $listpages[ix].disp_article eq 'y'}
		{if $prefs.feature_freetags eq 'y' and $tiki_p_view_freetags eq 'y' and $listpages[ix].freetags.data|@count >0}
			<div class="freetaglist">
				{foreach from=$listpages[ix].freetags.data item=taginfo}
				{capture name=tagurl}{if (strstr($taginfo.tag, ' '))}"{$taginfo.tag}"{else}{$taginfo.tag}{/if}{/capture}
				<a {if $prefs.mobile_mode eq "y"}data-role="button" data-inline="true" data-mini="true" data-theme="a" {/if}class="freetag" href="tiki-browse_freetags.php?tag={$smarty.capture.tagurl|escape:'url'}">{$taginfo.tag}</a> {* mobile *}
				{/foreach}
			</div>
		{/if} 
		<article class="article{if !empty($container_class)} {$container_class}{/if} article{$smarty.section.ix.index}">
			{if ($listpages[ix].show_avatar eq 'y')}
				<div class="avatar">
					{$listpages[ix].author|avatarize}
				</div>
			{/if}
			{if $listpages[ix].show_topline eq 'y' and $listpages[ix].topline}<div class="articletopline">{$listpages[ix].topline|escape}</div>{/if}
			<header class="articletitle clearfix">
				<h2>{object_link type=article id=$listpages[ix].articleId url=$smarty.capture.href title=$listpages[ix].title}</h2>
				{if $listpages[ix].show_subtitle eq 'y' and $listpages[ix].subtitle}<div class="articlesubtitle">{$listpages[ix].subtitle|escape}</div>{/if}
				{if ($listpages[ix].show_author eq 'y')
				 or ($listpages[ix].show_pubdate eq 'y')
				 or ($listpages[ix].show_expdate eq 'y')
				 or ($listpages[ix].show_reads eq 'y')}	
					<span class="titleb">
						{if $listpages[ix].show_author eq 'y'}
							{if $listpages[ix].authorName}
								<span class="author">{tr}Author:{/tr} {$listpages[ix].authorName|escape}&nbsp;- </span>
							{else}
								<span class="author">{tr}Author:{/tr} {$listpages[ix].author|username}&nbsp;- </span>
							{/if}
						{/if}
						{if $listpages[ix].show_pubdate eq 'y'}
							<span class="pubdate">{tr}Published At:{/tr} {$listpages[ix].publishDate|tiki_short_datetime}&nbsp;- </span>
						{/if}
						{if $listpages[ix].show_expdate eq 'y'}
							<span class="expdate">{tr}Expires At:{/tr} {$listpages[ix].expireDate|tiki_short_datetime}&nbsp;- </span>
						{/if}
						{if $listpages[ix].show_reads eq 'y'}
							<span class="reads">({$listpages[ix].nbreads} {tr}Reads{/tr})</span>
						{/if}
					</span><br>
				{/if}
			</header>
			{if $listpages[ix].use_ratings eq 'y'}
				<div class="articleheading">
					{tr}Rating:{/tr} 
					{repeat count=$listpages[ix].rating}
						{icon _id='star' alt="{tr}star{/tr}"}
					{/repeat}
					{if $listpages[ix].rating > $listpages[ix].entrating}
						{icon _id='star_half' alt="{tr}half star{/tr}"}
					{/if}
					({$listpages[ix].rating}/10)
				</div>
			{/if}
			<div class="articleheading">
				<table  cellpadding="0" cellspacing="0">
					<tr>
						<td valign="top">
							{if $listpages[ix].show_image eq 'y'}
								{if $listpages[ix].useImage eq 'y'}
									{if $listpages[ix].hasImage eq 'y'}
										<a href="{$smarty.capture.href}"
												title="{if $listpages[ix].show_image_caption and $listpages[ix].image_caption}{$listpages[ix].image_caption|escape}{elseif $listpages[ix].topicName}{tr}{$listpages[ix].topicName}{/tr}{else}{tr}Read More{/tr}{/if}">
											{$style=''}
											<img {if $listpages[ix].isfloat eq 'y'}{$style="margin-right:4px;float:left;"}{else}class="articleimage"{/if} 
													alt="{if $listpages[ix].show_image_caption and $listpages[ix].image_caption}{$listpages[ix].image_caption|escape}{elseif $listpages[ix].topicName}{tr}{$listpages[ix].topicName}{/tr}{/if}"
													{strip}src="article_image.php?image_type=article&amp;id={$listpages[ix].articleId}
													{if $listpages[ix].list_image_x > 0 and ($largefirstimage neq 'y' or not $smarty.section.ix.first)}
														&amp;width={$listpages[ix].list_image_x}
													{elseif $listpages[ix].image_x > 0}
														&amp;width={$listpages[ix].image_x}
													{/if}
													&amp;cache=y"
													{if $listpages[ix].list_image_y > 0 and ($largefirstimage neq 'y' or not $smarty.section.ix.first)}
														{$style=$style|cat:"max-height:"|cat:$listpages[ix].list_image_y|cat:"px;"}
													{elseif $listpages[ix].image_y > 0}
														{$style=$style|cat:"max-height:"|cat:$listpages[ix].image_y|cat:"px;"}
													{/if}
													style="{$style}"
											>{/strip}
										</a>
									{else}
										{* Intentionally left blank to allow user add an image from somewhere else through the img tag and no other extra image *}
									{/if}
								{else}
									{if isset($topics[$listpages[ix].topicId].image_size) and $topics[$listpages[ix].topicId].image_size > 0}
										<a href="{$smarty.capture.href}"
												title="{if $listpages[ix].show_image_caption and $listpages[ix].image_caption}{$listpages[ix].image_caption|escape}{else}{tr}{$listpages[ix].topicName}{/tr}{/if}">
											<img {if $listpages[ix].isfloat eq 'y'}style="margin-right:4px;float:left;"{else}class="articleimage"{/if} 
													alt="{if $listpages[ix].show_image_caption and $listpages[ix].image_caption}{$listpages[ix].image_caption|escape}{else}{tr}{$listpages[ix].topicName}{/tr}{/if}"
													src="article_image.php?image_type=topic&amp;id={$listpages[ix].topicId}">
										</a>
									{/if}
								{/if}
							{/if}
							{if $listpages[ix].isfloat eq 'n'}
								</td><td  valign="top">
							{/if}
							<div class="articleheadingtext">{$listpages[ix].parsed_heading}</div>
							{if isset($fullbody) and $fullbody eq "y"}
								<div class="articlebody">{$listpages[ix].parsed_body}</div>
							{/if}
						</td>
					</tr>
				</table>
			</div>
			<div class="articletrailer">
				{if ($listpages[ix].size > 0) or (($prefs.feature_article_comments eq 'y') and ($tiki_p_read_comments eq 'y'))}
					{if ($tiki_p_read_article eq 'y' and $listpages[ix].heading_only ne 'y' and (!isset($fullbody) or $fullbody ne "y"))}
						{if ($listpages[ix].size > 0)}
							<div class="status"> {* named to be similar to forum/blog item *}
								<a {if $prefs.mobile_mode eq "y"}data-role="button" data-inline="true" {/if}href="{$smarty.capture.href}" class="more">{tr}Read More{/tr}</a> {* mobile *}
							</div>
							{if ($listpages[ix].show_size eq 'y')}
								<span>
									({$listpages[ix].size} {tr}bytes{/tr})
								</span>
							{/if}
						{/if}
					{/if}
					{if ($prefs.feature_article_comments eq 'y') and ($tiki_p_read_comments eq 'y') and ($listpages[ix].allow_comments eq 'y')}
						<span>
							<a {if $prefs.mobile_mode eq "y"}data-role="button" data-inline="true" {/if}href="{$listpages[ix].articleId|sefurl:article:with_next}show_comzone=y{if !empty($urlparam)}&amp;{$urlparam}{/if}#comments"{if $listpages[ix].comments_cant > 0} class="highlight"{/if}> {* mobile *}
								{if $listpages[ix].comments_cant == 0 and $tiki_p_post_comments == 'y'}
									{if !isset($actions) or $actions eq "y"}
										{tr}Add Comment{/tr}
									{/if}
								{elseif $tiki_p_read_comments eq 'y'}
									{if $listpages[ix].comments_cant == 1}
										{tr}1 comment{/tr}
									{else}
										{$listpages[ix].comments_cant}&nbsp;{tr}comments{/tr}
									{/if}
								{/if}
							</a>
						</span>
					{/if}
				{/if}
				{if !isset($actions) or $actions eq "y"}
					<div class="actions" data-role="controlgroup" data-type="horizontal"> {* mobile *}
						{if $tiki_p_edit_article eq 'y' or ($listpages[ix].author eq $user and $listpages[ix].creator_edit eq 'y')}
							<a {if $prefs.mobile_mode eq "y"}data-role="button" data-inline="true" {/if}class="icon" href="tiki-edit_article.php?articleId={$listpages[ix].articleId}">{icon _id='page_edit'}</a> {* mobile *}
						{/if}
						{if $prefs.feature_cms_print eq 'y'}
							<a {if $prefs.mobile_mode eq "y"}data-role="button" data-inline="true" {/if}class="icon" href="tiki-print_article.php?articleId={$listpages[ix].articleId}">{icon _id='printer' alt="{tr}Print{/tr}"}</a> {* mobile *}
						{/if}
						{if $tiki_p_remove_article eq 'y'}
							<a {if $prefs.mobile_mode eq "y"}data-role="button" data-inline="true" {/if}class="icon" href="tiki-list_articles.php?remove={$listpages[ix].articleId}">{icon _id='cross' alt="{tr}Remove{/tr}"}</a> {* mobile *}
						{/if}
					</div>
					{if $prefs.feature_multilingual eq 'y' and $tiki_p_edit_article eq 'y'}{* mobile - moved out of the previous controlgroup since it was breaking its display*}
						<div class="lang_select">
							{include file='translated-lang.tpl' object_type='article' trads=$listpages[ix].translations articleId=$listpages[ix].articleId}
						</div>
					{/if}{* mobile *}
				{/if}
			</div>
		</article>
	{/if}
{sectionelse}
	{if $quiet ne 'y'}{tr}No articles yet.{/tr}
		{if $tiki_p_edit_article eq 'y'}<a {if $prefs.mobile_mode eq "y"}data-role="button" data-inline="true" {/if}href="tiki-edit_article.php">{tr}Add an article{/tr}</a>{/if} {* mobile *}
	{/if}
{/section}
{if !empty($listpages) && (!isset($usePagination) or $usePagination ne 'n')}
	{pagination_links cant=$cant step=$maxArticles offset=$offset}{if isset($urlnext)}{$urlnext}{/if}{/pagination_links}
{/if}

