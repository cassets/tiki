{* $Id$ *}

{title help="Blogs"}{tr}Blogs{/tr}{/title}

<div class="navbar">
  <a href="tiki-edit_blog.php">{tr}Edit Blog{/tr}</a>
  <a href="tiki-blog_post.php">{tr}Post{/tr}</a>
  <a href="tiki-list_blogs.php">{tr}List Blogs{/tr}</a>
</div>

{if $listpages or ($find ne '')}
  {include file='find.tpl' _sort_mode='y'}
{/if}

<table class="normal">
	<tr>
		<td class="heading">
			<a class="tableheading" href="tiki-list_posts.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'postId_desc'}postId_asc{else}postId_desc{/if}">{tr}Id{/tr}</a>
		</td>
		<td class="heading">{tr}Blog Title{/tr}</td>
		<td class="heading">
			<a class="tableheading" href="tiki-list_posts.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'created_desc'}created_asc{else}created_desc{/if}">{tr}Created{/tr}</a>
		</td>
		<td class="heading">{tr}Size{/tr}</td>
		<td class="heading">
			<a class="tableheading" href="tiki-list_posts.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'user_desc'}user_asc{else}user_desc{/if}">{tr}User{/tr}</a>
		</td>
		<td class="heading">{tr}Action{/tr}</td>
	</tr>

	{cycle values="odd,even" print=false}
	{section name=changes loop=$listpages}
		<tr>
			<td class="{cycle advance=false}">&nbsp;{$listpages[changes].postId}&nbsp;</td>
			<td class="{cycle advance=false}">
				&nbsp;
				<a class="blogname" href="tiki-edit_blog.php?blogId={$listpages[changes].blogId}" title="{$listpages[changes].blogTitle}">{$listpages[changes].blogTitle|truncate:$prefs.blog_list_title_len:"...":true}</a>
				&nbsp;
			</td>
			<td class="{cycle advance=false}">&nbsp;{$listpages[changes].created|tiki_short_datetime}&nbsp;</td>
			<td class="{cycle advance=false}">&nbsp;{$listpages[changes].size}&nbsp;</td>
			<td class="{cycle advance=false}">&nbsp;{$listpages[changes].user}&nbsp;</td>
			<td class="{cycle}">
				<a class="link" href="tiki-blog_post.php?postId={$listpages[changes].postId}">{icon _id='page_edit'}</a>
				<a class="link" href="tiki-list_posts.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;remove={$listpages[changes].postId}">{icon _id='cross' alt='{tr}Remove{/tr}'}</a>
			</td>
		</tr>
	{sectionelse}
		<tr>
			<td colspan="6" class="odd">
				<b>{tr}No records found{/tr}</b>
			</td>
		</tr>
	{/section}
</table>

<br />

<div class="mini">
	{if $prev_offset >= 0}
		[<a class="prevnext" href="tiki-list_posts.php?find={$find}&amp;offset={$prev_offset}&amp;sort_mode={$sort_mode}">{tr}Prev{/tr}</a>]
		&nbsp;
	{/if}
	{tr}Page{/tr}: {$actual_page}/{$cant_pages}
	{if $next_offset >= 0}
		&nbsp;
		[<a class="prevnext" href="tiki-list_posts.php?find={$find}&amp;offset={$next_offset}&amp;sort_mode={$sort_mode}">{tr}Next{/tr}</a>]
	{/if}
	{if $prefs.direct_pagination eq 'y'}
		<br />
		{section loop=$cant_pages name=foo}
			{assign var=selector_offset value=$smarty.section.foo.index|times:$prefs.maxRecords}
			<a class="prevnext" href="tiki-list_posts.php?find={$find}&amp;offset={$selector_offset}&amp;sort_mode={$sort_mode}">
				{$smarty.section.foo.index_next}
			</a>
		&nbsp;
		{/section}
	{/if}
</div>
