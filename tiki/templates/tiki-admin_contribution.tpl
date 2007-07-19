{* $Header: /cvsroot/tikiwiki/tiki/templates/tiki-admin_contribution.tpl,v 1.10 2007-07-19 13:16:16 jyhem Exp $ *}
<h1><a  class="pagetitle" href="tiki-admin_contribution.php">{tr}Admin Contributions{/tr}</a>
{if $feature_help eq 'y'}
<a href="{$helpurl}Contribution" target="tikihelp" class="tikihelp" title="{tr}Contribution{/tr}">
<img src="pics/icons/help.png" border="0" height="16" width="16" alt='{tr}Help{/tr}'></a>{/if}
{if $feature_view_tpl eq 'y'}
<a href="tiki-edit_templates.php?template=tiki-admin_contributions.tpl" target="tikihelp" class="tikihelp" title="{tr}View template{/tr}">
<img src="pics/icons/shape_square_edit.png" border="0" width="16" height="16" alt='{tr}Edit{/tr}'></a>{/if}</h1>

{if $contribution}
<h2>{tr}Edit the contribution:{/tr} {$contribution.name|escape}</h2>
<form enctype="multipart/form-data" action="tiki-admin_contribution.php" method="post">
<input type="hidden" name="contributionId" value="{$contribution.contributionId}" />
 <table class="normal">
<tr><td class="formcolor">{tr}Name{/tr}</td><td class="formcolor"><input type="text" name="name"{if $contribution.name} value="{$contribution.name|escape}"{/if} /></td></tr>
<tr><td class="formcolor">{tr}Description{/tr}</td><td class="formcolor"><input type="text" name="description" size="80" maxlength="250"{if $contribution.description} value="{$contribution.description|escape}"{/if} /></td></tr>
<tr><td class="formcolor">&nbsp;</td><td class="formcolor"><input type="submit" name="replace" value="{tr}Save{/tr}" /></td></tr>
</table>
</form>
{/if}

<h2>{tr}Settings{/tr}</h2>
<form action="tiki-admin_contribution.php?page=features" method="post">
<table class="normal">
<tr><td class="formcolor">{tr}Contributions are mandatory in wiki pages{/tr}</td>
<td class="formcolor"><input type="checkbox" name="feature_contribution_mandatory" {if $feature_contribution_mandatory eq 'y'}checked="checked"{/if}/></td></tr>
<tr><td class="formcolor">{tr}Contributions are mandatory in forums{/tr}</td>
<td class="formcolor"><input type="checkbox" name="feature_contribution_mandatory_forum" {if $feature_contribution_mandatory_forum eq 'y'}checked="checked"{/if}/></td></tr>
<tr><td class="formcolor">{tr}Contributions are mandatory in comments{/tr}</td>
<td class="formcolor"><input type="checkbox" name="feature_contribution_mandatory_comment" {if $feature_contribution_mandatory_comment eq 'y'}checked="checked"{/if}/></td></tr>
<tr><td class="formcolor">{tr}Contributions are mandatory in blogs{/tr}</td>
<td class="formcolor"><input type="checkbox" name="feature_contribution_mandatory_blog" {if $feature_contribution_mandatory_blog eq 'y'}checked="checked"{/if}/></td></tr>
<tr><td class="formcolor">{tr}Contributions are displayed in the comment/post{/tr}</td>
<td class="formcolor"><input type="checkbox" name="feature_contribution_display_in_comment" {if $feature_contribution_display_in_comment eq 'y'}checked="checked"{/if}/></td></tr>
<tr><td class="formcolor">{tr}Contributors{/tr}</td>
<td class="formcolor"><input type="checkbox" name="feature_contributor_wiki" {if $feature_contributor_wiki eq 'y'}checked="checked"{/if}/></td></tr>
<tr><td class="formcolor">&nbsp;</td><td class="formcolor"><input type="submit" name="setting" value="{tr}Save{/tr}" /></td></tr>
</table>
</form>


<h2>{tr}Create a new contribution{/tr}</h2>

<form enctype="multipart/form-data" action="tiki-admin_contribution.php" method="post">
 <table class="normal">
<tr><td class="formcolor">{tr}Name{/tr}</td><td class="formcolor"><input type="text" name="name" /></td></tr>
<tr><td class="formcolor">{tr}Description{/tr}</td><td class="formcolor"><input type="text" name="description" size="80" maxlength="250" /></td></tr>
<tr><td class="formcolor">&nbsp;</td><td class="formcolor"><input type="submit" name="add" value="{tr}Add{/tr}" /></td></tr>
</table>
</form>

<h2>{tr}List of contributions{/tr}</h2>
<table class="normal">
<tr>
<td class="heading">{tr}Name{/tr}</td>
<td class="heading">{tr}Description{/tr}</td>
<td class="heading">{tr}Actions{/tr}</td>
</tr>
{cycle print=false values="even,odd"}
{section name=ix loop=$contributions}
<tr>
<td class="{cycle advance=false}">{$contributions[ix].name}</a></td>
<td class="{cycle advance=false}">{$contributions[ix].description|truncate|escape}</a></td>
<td class="{cycle}">
<a class="link" href="tiki-admin_contribution.php?contributionId={$contributions[ix].contributionId}"><img src="pics/icons/shape_square_edit.png" border="0" width="16" height="16" alt='{tr}Edit{/tr}'></a> &nbsp;
<a class="link" href="tiki-admin_contribution.php?remove={$contributions[ix].contributionId}"><img src="pics/icons/cross.png" border="0" width="16" height="16" alt='{tr}remove{/tr}'></a>
</td>
</tr>
{sectionelse}
<tr>
<td colspan="3" class="odd">{tr}No records found{/tr}</td>
</tr>
{/section}
</table>
