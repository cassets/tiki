{* $Id$ *}
{tikimodule error=$module_params.error title=$tpl_module_title name="last_blog_posts" flip=$module_params.flip decorations=$module_params.decorations nobox=$module_params.nobox notitle=$module_params.notitle}

{if $nonums neq 'y'}<ol>{else}<ul>{/if}
	{section name=ix loop=$modLastBlogPosts}
		<li>
			<a class="linkmodule" href="{$modLastBlogPosts[ix].postId|sefurl:blogpost}" title="{$modLastBlogPosts[ix].created|tiki_short_datetime}, {tr}by{/tr} {if $modLastBlogPosts[ix].user ne ''}{$modLastBlogPosts[ix].user|username}{else}{tr}Anonymous{/tr}{/if}">
				{if $blogid eq '-1'}{$modLastBlogPosts[ix].blogTitle|escape}: {/if}
				{$modLastBlogPosts[ix].title|escape}
				{if !isset($module_params.nodate) or $module_params.nodate neq 'y'}
					<small class="description">{$modLastBlogPosts[ix].created|tiki_short_date}</small>
				{/if}
			</a>
		</li>
	{/section}
{if $nonums neq 'y'}</ol>{else}</ul>{/if}
{/tikimodule}
