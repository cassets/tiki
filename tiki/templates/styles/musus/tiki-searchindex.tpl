<h1>{tr}Search results{/tr}:</h1>
{tr}Search in{/tr}:<br />
 <a class="linkbut" href="tiki-searchindex.php?highlight={$words}&amp;where=pages">{tr}All{/tr}</a>
{if $feature_wiki eq 'y'}
 <a class="linkbut" href="tiki-searchindex.php?highlight={$words}&amp;where=wikis">{tr}Wiki{/tr}</a>
{/if}
{if $feature_galleries eq 'y'}
 <a class="linkbut" href="tiki-searchindex.php?highlight={$words}&amp;where=galleries">{tr}galleries{/tr}</a>
 <a class="linkbut" href="tiki-searchindex.php?highlight={$words}&amp;where=images">{tr}images{/tr}</a>
{/if}
{if $feature_file_galleries eq 'y'}
 <a class="linkbut" href="tiki-searchindex.php?highlight={$words}&amp;where=files">{tr}files{/tr}</a>
{/if}
{if $feature_forums eq 'y'}
 <a class="linkbut" href="tiki-searchindex.php?highlight={$words}&amp;where=forums">{tr}forums{/tr}</a>
{/if}
{if $feature_faqs eq 'y'}
 <a class="linkbut" href="tiki-searchindex.php?highlight={$words}&amp;where=faqs">{tr}faqs{/tr}</a>
{/if}
{if $feature_blogs eq 'y'}
 <a class="linkbut" href="tiki-searchindex.php?highlight={$words}&amp;where=blogs">{tr}blogs{/tr}</a>
 <a class="linkbut" href="tiki-searchindex.php?highlight={$words}&amp;where=posts">{tr}blog posts{/tr}</a>
{/if}
{if $feature_directory eq 'y'}
 <a class="linkbut" href="tiki-searchindex.php?highlight={$words}&amp;where=directory">{tr}directory{/tr}</a>
{/if}

{if $feature_articles eq 'y'}
 <a class="linkbut" href="tiki-searchindex.php?highlight={$words}&amp;where=articles">{tr}articles{/tr}</a>
{/if}

<br /><br />
{tr}Found{/tr} "{$words}" {tr}in{/tr} {$cant_results} {$where2}
<form class="forms" method="get" action="tiki-searchindex.php">
    {tr}Find{/tr}: <input name="highlight" size="14" type="text" accesskey="s" value="{$words}"/>
    <input type="hidden" name="where" value="{$where|escape}" />
    <input type="submit" class="wikiaction" name="search" value="{tr}go{/tr}"/>
</form>
<br /><br />
{section  name=search loop=$results}
{tr}{$results[search].location}{/tr}:&nbsp;<a href="{$results[search].href}&amp;highlight={$words}" class="wiki">{$results[search].pageName|strip_tags|escape}</a> ({tr}Hits{/tr}: {$results[search].hits})
{if $feature_search_fulltext eq 'y'}
	{if $results[search].relevance <= 0}
		&nbsp;({tr}Simple search{/tr})
	{else}
		&nbsp;({tr}Relevance{/tr}: {$results[search].relevance})
	{/if}
{/if}
{if $results[search].type > ''}
&nbsp;({$results[search].type})
{/if}

<br />
<div class="searchdesc">{$results[search].data|strip_tags|escape}</div>
<div class="searchdate">{tr}Last modification date{/tr}: {$results[search].lastModif|tiki_long_datetime}</div><br />
{sectionelse}
{tr}No pages matched the search criteria{/tr}
{/section}

<div align="center">
<div class="mini">
{if $prev_offset >= 0}
[<a href="tiki-searchindex.php?where={$where}&amp;highlight={$words}&amp;offset={$prev_offset}">{tr}prev{/tr}</a>]&nbsp;
{/if}
{tr}Page{/tr}: {$actual_page}/{$cant_pages}
{if $next_offset >= 0}
&nbsp;[<a href="tiki-searchindex.php?where={$where}&amp;highlight={$words}&amp;offset={$next_offset}">{tr}next{/tr}</a>]
{/if}
</div>
</div>

