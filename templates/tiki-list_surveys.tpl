<a class="pagetitle" href="tiki-list_surveys.php">Surveys</a><br/><br/>
{if $tiki_p_view_survey_stats eq 'y'}
<a class="link" href="tiki-survey_stats.php">{tr}Survey stats{/tr}</a><br/><br/>
{/if}
<table class="normal">
<tr>
<td class="heading"><a class="tableheading" href="tiki-list_surveys.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'name_desc'}name_asc{else}name_desc{/if}">{tr}name{/tr}</a></td>
<td class="heading"><a class="tableheading" href="tiki-list_surveys.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'description_desc'}description_asc{else}description_desc{/if}">{tr}description{/tr}</a></td>
<td style="text-align:right;" width="2%" class="heading">{tr}questions{/tr}</td>
</tr>
{cycle values="odd,even" print=false}
{section name=user loop=$channels}
{if ($tiki_p_admin eq 'y') or ($channels[user].individual eq 'n' and $tiki_p_take_survey eq 'y') or ($channels[user].individual_tiki_p_take_survey eq 'y')}
<tr>
<td class="{cycle advance=false}">
{if ($tiki_p_admin_surveys eq 'y') or ($channels[user].status eq 'o' and $channels[user].taken_survey eq 'n')}
<a class="tablename" href="tiki-take_survey.php?surveyId={$channels[user].surveyId}">
{else}
<a class="link" href="tiki-survey_stats_survey.php?surveyId={$channels[user].surveyId}">
{/if}
{$channels[user].name}</a>
{if ($tiki_p_admin eq 'y') or ($channels[user].individual eq 'n' and $tiki_p_admin_surveys eq 'y') or ($channels[user].individual_tiki_p_admin_surveys eq 'y')} (<a class="link" href="tiki-admin_surveys.php?surveyId={$channels[user].surveyId}"><small>adm</small></a>){/if}
{if ($tiki_p_admin eq 'y') or ($channels[user].individual eq 'n' and $tiki_p_view_survey_stats eq 'y') or ($channels[user].individual_tiki_p_view_survey_stats eq 'y')} (<a class="link" href="tiki-survey_stats_survey.php?surveyId={$channels[user].surveyId}"><small>stats</small></a>){/if}
</td>
<td class="{cycle advance=false}">{$channels[user].description}</td>
<td style="text-align:right;" width="2%" class="{cycle}">{$channels[user].questions}</td>
</tr>
{/if}
{/section}
</table>
<br/>
<div align="center">
<div class="mini">
{if $prev_offset >= 0}
[<a class="prevnext" href="tiki-list_surveys.php?find={$find}&amp;offset={$prev_offset}&amp;sort_mode={$sort_mode}">{tr}prev{/tr}</a>]&nbsp;
{/if}
{tr}Page{/tr}: {$actual_page}/{$cant_pages}
{if $next_offset >= 0}
&nbsp;[<a class="prevnext" href="tiki-list_surveys.php?find={$find}&amp;offset={$next_offset}&amp;sort_mode={$sort_mode}">{tr}next{/tr}</a>]
{/if}
{if $direct_pagination eq 'y'}
<br/>
{section loop=$cant_pages name=foo}
{assign var=selector_offset value=$smarty.section.foo.index|times:$maxRecords}
<a class="prevnext" href="tiki-list_surveys.php?find={$find}&amp;offset={$selector_offset}&amp;sort_mode={$sort_mode}">
{$smarty.section.foo.index_next}</a>&nbsp;
{/section}
{/if}
</div>
</div>

