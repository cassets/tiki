{*Smarty template*}
<a class="pagetitle" href="tiki-g-admin_activities.php?pid={$pid}">{tr}Admin process activities{/tr}</a><br/><br/>
{include file=tiki-g-proc_bar.tpl}

{if count($errors) > 0}
<div class="wikitext">
Errors:<br/>
{section name=ix loop=$errors}
<small>{$errors[ix]}</small><br/>
{/section}
</div>
{/if}

<h3>{tr}Add or edit an activity{/tr} <a class="link" href="tiki-g-admin_activities.php?where2={$where2}&amp;sort_mode2={$sort_mode2}&amp;pid={$pid}&amp;find={$find}&amp;where={$where}&amp;sort_mode={$sort_mode}&amp;activityId=0">{tr}new{/tr}</a></h3>
<form action="tiki-g-admin_activities.php" method="post">
<input type="hidden" name="pid" value="{$pid}" />
<input type="hidden" name="activityId" value="{$info.activityId}" />
<input type="hidden" name="where2" value="{$where2}" />
<input type="hidden" name="sort_mode2" value="{$sort_mode2}" />
<input type="hidden" name="find" value="{$find}" />
<input type="hidden" name="where" value="{$where}" />
<input type="hidden" name="sort_mode" value="{$sort_mode}" />
<table class="normal">
<tr>
  <td class="formcolor">{tr}name{/tr}</td>
  <td class="formcolor"><input type="text" name="name" value="{$info.name}" /></td>
</tr>
<tr>
  <td class="formcolor">{tr}description{/tr}</td>
  <td class="formcolor"><textarea name="description" rows="4" cols="60">{$info.description}</textarea></td>
</tr>
<tr>  
  <td class="formcolor">{tr}type{/tr}</td>
  <td class="formcolor">
  <select name="type">
  <option value="start" {if $info.type eq 'start'}selected="selected"{/if}>{tr}start{/tr}</option>
  <option value="end" {if $info.type eq 'end'}selected="selected"{/if}>{tr}end{/tr}</option>		  
  <option value="activity" {if $info.type eq 'activity'}selected="selected"{/if}>{tr}activity{/tr}</option>		  
  <option value="switch" {if $info.type eq 'switch'}selected="selected"{/if}>{tr}switch{/tr}</option>		  
  <option value="split" {if $info.type eq 'split'}selected="selected"{/if}>{tr}split{/tr}</option>		  
  <option value="join" {if $info.type eq 'join'}selected="selected"{/if}>{tr}join{/tr}</option>		  
  <option value="standalone" {if $info.type eq 'standalone'}selected="selected"{/if}>{tr}standalone{/tr}</option>		  
  </select>
  {tr}interactive{/tr}:<input type="checkbox" name="isInteractive" {if $info.isInteractive eq 'y'}checked="checked"{/if} />
  {tr}auto routed{/tr}:<input type="checkbox" name="isAutoRouted" {if $info.isAutoRouted eq 'y'}checked="checked"{/if} />
  </td>
</tr>

<tr>
  <td class="formcolor">{tr}Add transitions{/tr}</td>
  <td class="formcolor">
    <table class="normal">
		<tr>
			<td class="formcolor">
				{tr}Add transition from:{/tr}<br/>
				<select name="add_tran_from[]" multiple="multiple" size="5">
				{section name=ix loop=$items}
				<option value="{$items[ix].activityId}" {if $items[ix].from eq 'y'}selected="selected"{/if}>{$items[ix].name|adjust:30}</option>
				{/section}			
				</select>
			</td>
			<td class="formcolor">
				{tr}Add transition to:{/tr}<br/>
				<select name="add_tran_to[]" multiple="multiple" size="5">
				{section name=ix loop=$items}
				<option value="{$items[ix].activityId}" {if $items[ix].to eq 'y'}selected="selected"{/if}>{$items[ix].name|adjust:30}</option>
				{/section}			
				</select>
			</td>
		</tr>    
    </table>
  </td>
</tr>

<tr>
  <td class="formcolor">{tr}roles{/tr}</td>
  <td class="formcolor">
  {section name=ix loop=$roles}
  {$roles[ix].name}[<a class="link" href="tiki-g-admin_activities.php?where2={$where2}&amp;sort_mode2={$sort_mode2}&amp;sort_mode={$sort_mode}&amp;find={$find}&amp;where={$where}&amp;activityId={$info.activityId}&amp;pid={$pid}&amp;remove_role={$roles[ix].roleId}">x</a>]<br/>
  {sectionelse}
  {tr}No roles associated to this activity{/tr}
  {/section}
  </td>
</tr>
<tr>
  <td class="formcolor">{tr}Add role{/tr}</td>
  <td class="formcolor">
  {if count($all_roles)}
  <select name="userole">
  <option value="">{tr}add new{/tr}</option>
  {section loop=$all_roles name=ix}
  <option value="{$all_roles[ix].roleId}">{$all_roles[ix].name}</option>
  {/section}
  </select>
  {/if}
  <input type="text" name="rolename" /><input type="submit" name="addrole" value="{tr}add role{/tr}" />
  </td>
</tr>
<tr>
  <td class="formcolor">&nbsp;</td>
  <td class="formcolor"><input type="submit" name="save_act" value="{tr}save{/tr}" /> </td>
</tr>

</table>
</form>

<h3>{tr}Process activities{/tr}</h3>
	
<form action="tiki-g-admin_activities.php" method="post">
<input type="hidden" name="sort_mode" value="{$sort_mode}" />
<input type="hidden" name="pid" value="{$pid}" />
<input type="hidden" name="activityId" value="{$info.activityId}" />
<input type="hidden" name="where2" value="{$where2}" />
<input type="hidden" name="sort_mode2" value="{$sort_mode2}" />
<table>
<tr>
	<td>
		{tr}Find{/tr}
	</td>	
	<td>
		{tr}Type{/tr}
	</td>
	<td>
		{tr}Int{/tr}
	</td>
	<td>
		{tr}Routing{/tr}
	</td>
	<td>
		&nbsp;
	</td>
</tr>			
<tr>
	<td>	
		<input size="8" type="text" name="find" value="{$find}" />
	</td>
	<td>
		<select name="filter_type">
		  <option value="">{tr}all{/tr}</option>
		  <option value="start">{tr}start{/tr}</option>
		  <option value="end" >{tr}end{/tr}</option>		  
		  <option value="activity" >{tr}activity{/tr}</option>		  
		  <option value="switch" >{tr}switch{/tr}</option>		  
		  <option value="split" >{tr}split{/tr}</option>		  
		  <option value="join" >{tr}join{/tr}</option>		  
		  <option value="standalone" >{tr}standalone{/tr}</option>		  
		</select>
	</td>
	<td>
		<select name="filter_interactive">
		<option value="">{tr}all{/tr}</option>
		<option value="y">{tr}Interactive{/tr}</option>
		<option value="n">{tr}Automatic{/tr}</option>
		</select>
	</td>
	<td>
		<select name="filter_autoroute">
		<option value="">{tr}all{/tr}</option>
		<option value="y">{tr}Auto routed{/tr}</option>
		<option value="n">{tr}Manual{/tr}</option>
		</select>
	</td>
	<td>
		<input type="submit" name="filter" value="{tr}filter{/tr}" />
	</td>
</tr>
</table>	
</form>
<form action="tiki-g-admin_activities.php" method="post">
<input type="hidden" name="find" value="{$find}" />
<input type="hidden" name="where" value="{$where}" />
<input type="hidden" name="sort_mode" value="{$sort_mode}" />
<input type="hidden" name="where2" value="{$where2}" />
<input type="hidden" name="sort_mode2" value="{$sort_mode2}" />
<input type="hidden" name="pid" value="{$pid}" />
<input type="hidden" name="activityId" value="{$info.activityId}" />
<table class="normal">
<tr>
<td style="text-align:center;" width="7%" class="heading"><input type="submit" name="delete_act" value="x " /></td>
<td width="1%" class="heading" ><a class="tableheading" href="tiki-g-admin_activities.php?where2={$where2}&amp;sort_mode2={$sort_mode2}&amp;pid={$pid}&amp;find={$find}&amp;where={$where}&amp;sort_mode={if $sort_mode eq 'flowNum_desc'}flowNum_asc{else}flowNum_desc{/if}">{tr}#{/tr}</a></td>
<td width="47%" class="heading" ><a class="tableheading" href="tiki-g-admin_activities.php?where2={$where2}&amp;sort_mode2={$sort_mode2}&amp;pid={$pid}&amp;find={$find}&amp;where={$where}&amp;sort_mode={if $sort_mode eq 'name_desc'}name_asc{else}name_desc{/if}">{tr}Name{/tr}</a></td>
<td width="5%" class="heading" ><a class="tableheading" href="tiki-g-admin_activities.php?where2={$where2}&amp;sort_mode2={$sort_mode2}&amp;pid={$pid}&amp;find={$find}&amp;where={$where}&amp;sort_mode={if $sort_mode eq 'type_desc'}type_asc{else}type_desc{/if}">{tr}Type{/tr}</a></td>
<td width="5%" class="heading" ><a class="tableheading" href="tiki-g-admin_activities.php?where2={$where2}&amp;sort_mode2={$sort_mode2}&amp;pid={$pid}&amp;find={$find}&amp;where={$where}&amp;sort_mode={if $sort_mode eq 'isInteractive_desc'}isInteractive_asc{else}isInteractive_desc{/if}">{tr}inter{/tr}</a></td>
<td width="5%" class="heading" ><a class="tableheading" href="tiki-g-admin_activities.php?where2={$where2}&amp;sort_mode2={$sort_mode2}&amp;pid={$pid}&amp;find={$find}&amp;where={$where}&amp;sort_mode={if $sort_mode eq 'isInteractive_desc'}isAutoRouted_asc{else}isAutoRouted_desc{/if}">{tr}route{/tr}</a></td>
<td width="30%" class="heading" >{tr}Action{/tr}</td>
</tr>
{cycle values="odd,even" print=false}
{section name=ix loop=$items}
<tr>
	<td style="text-align:center;" class="{cycle advance=false}">
		<input type="checkbox" name="activity[{$items[ix].activityId}]" />
	</td>
	<td style="text-align:right;" class="{cycle advance=false}">
	  {$items[ix].flowNum}
	</td>

	<td class="{cycle advance=false}">
	  <a class="link" href="tiki-g-admin_activities.php?where2={$where2}&amp;sort_mode2={$sort_mode2}&amp;pid={$pid}&amp;find={$find}&amp;where={$where}&amp;sort_mode={$sort_mode}&amp;activityId={$items[ix].activityId}">{$items[ix].name}</a>
	  {if $items[ix].roles < 1}
		<small>{tr}(no roles){/tr}</small>
	  {/if}
	</td>
	<td style="text-align:center;" class="{cycle advance=false}">
	  {$items[ix].type|act_icon:"$items[ix].isInteractive"}
	</td>
	<td style="text-align:center;" class="{cycle advance=false}">
	  <input type="checkbox" name="activity_inter[{$items[ix].activityId}]" {if $items[ix].isInteractive eq 'y'}checked="checked"{/if} />
	</td>
    <td style="text-align:center;" class="{cycle advance=false}">
	  <input type="checkbox" name="activity_route[{$items[ix].activityId}]" {if $items[ix].isAutoRouted eq 'y'}checked="checked"{/if} />
	</td>

	<td class="{cycle}">
	<a class="link" href="tiki-g-admin_shared_source.php?pid={$pid}&amp;activityId={$items[ix].activityId}">{tr}code{/tr}</a>
	{if $items[ix].isInteractive eq 'y'}
	<br/><a class="link" href="tiki-g-admin_shared_source.php?pid={$pid}&amp;activityId={$items[ix].activityId}&amp;template=1">{tr}template{/tr}</a>
	{/if}
	</td>
</tr>
{sectionelse}
<tr>
	<td class="{cycle advance=false}" colspan="6">
	{tr}No activities defined yet{/tr}
	</td>
</tr>	
{/section}
<tr>
<td class="heading" colspan="7" style="text-align:center;">
<input type="submit" name="update_act" value="{tr}update{/tr}" />
</td>
</tr>
</table>
</form>	

<h3>{tr}Process Transitions{/tr}</h3>
<table class="normal">
<tr>
	<td width="50%">
		<h3>{tr}List of transitions{/tr}</h3>
			<form action="tiki-g-admin_activities.php" method="post" id='filtran'>
			<input type="hidden" name="pid" value="{$pid}" />
			<input type="hidden" name="activityId" value="{$info.activityId}" />
			<input type="hidden" name="find" value="{$find2}" />
			<input type="hidden" name="where" value="{$where2}" />
			<input type="hidden" name="sort_mode2" value="{$sort_mode2}" />
			{tr}From:{/tr}<select name="filter_tran_name" onChange="javascript:document.getElementById('filtran').submit();">
			<option value="" {if $filter_tran_name eq ''}selected="selected"{/if}>{tr}all{/tr}</option>
			{section name=ix loop=$items}
			<option value="{$items[ix].activityId}" {if $filter_tran_name eq $items[ix].activityId}selected="selected"{/if}>{$items[ix].name}</option>
			{/section}
			</select>
<!--			<input type="submit" name="filter_tran" value="{tr}filter{/tr}" /> -->
			</form>
			
			<form action="tiki-g-admin_activities.php" method="post">
			<input type="hidden" name="pid" value="{$pid}" />
			<input type="hidden" name="activityId" value="{$info.activityId}" />
			<input type="hidden" name="find" value="{$find2}" />
			<input type="hidden" name="where" value="{$where2}" />
			<input type="hidden" name="sort_mode" value="{$sort_mode}" />
			<input type="hidden" name="where2" value="{$where2}" />
			<input type="hidden" name="sort_mode2" value="{$sort_mode2}" />
			<table class="normal">
			<tr>
			<td class="heading" width="5%"><input type="submit" name="delete_tran" value="{tr}x{/tr} " /></td>
			<td class="heading" width="95%"><a class="tableheading" href="tiki-g-admin_activities.php?where2={$where2}&amp;sort_mode2={$sort_mode2}&amp;pid={$pid}&amp;find={$find}&amp;where={$where}&amp;sort_mode={if $sort_mode eq 'actFromName_desc'}actFromName_asc{else}actFromName_desc{/if}">{tr}Origin{/tr}</a></td>
			<!--<td class="heading" width="45%"><a class="tableheading" href="tiki-g-admin_activities.php?where2={$where2}&amp;sort_mode2={$sort_mode2}&amp;pid={$pid}&amp;find={$find}&amp;where={$where}&amp;sort_mode={if $sort_mode eq 'actToName_desc'}actToName_asc{else}actToName_desc{/if}">{tr}To{/tr}</a></td>-->
			</tr>
			{cycle values="odd,even" print=false}
			{section name=ix loop=$transitions}
			<tr>
				<td class="{cycle advance=false}">
					<input type="checkbox" name="transition[{$transitions[ix].actFromId}_{$transitions[ix].actToId}]" />
				</td>
				<td class="{cycle advance=false}">
					<a class="link" href="tiki-g-admin_activities.php?where2={$where2}&amp;sort_mode2={$sort_mode2}&amp;pid={$pid}&amp;find={$find}&amp;where={$where}&amp;sort_mode={$sort_mode}&amp;activityId={$transitions[ix].actFromId}">{$transitions[ix].actFromName}</a>
					<img src='lib/Galaxia/img/icons/next.gif' alt='to' />
					<a class="link" href="tiki-g-admin_activities.php?where2={$where2}&amp;sort_mode2={$sort_mode2}&amp;pid={$pid}&amp;find={$find}&amp;where={$where}&amp;sort_mode={$sort_mode}&amp;activityId={$transitions[ix].actToId}">{$transitions[ix].actToName}</a>
				</td>
				<!--
				<td class="{cycle advance=false}">
					{$transitions[ix].actToName}
				</td>
				-->
			</tr>
			{sectionelse}
			<tr>
				<td class="{cycle advance=false}" colspan="3">
				{tr}No transitions defined yet{/tr}
				</td>
			</tr>
			{/section}
			</table>
			</form>		
	</td>
	<td class="formcolor" width="50%">
		<h3>{tr}Add a transition{/tr}</h3>
		<form action="tiki-g-admin_activities.php" method="post">
		<input type="hidden" name="pid" value="{$pid}" />
		<input type="hidden" name="activityId" value="{$info.activityId}" />
		<input type="hidden" name="find" value="{$find2}" />
		<input type="hidden" name="where" value="{$where2}" />
		<input type="hidden" name="sort_mode" value="{$sort_mode}" />
		<input type="hidden" name="where2" value="{$where2}" />
		<input type="hidden" name="sort_mode2" value="{$sort_mode2}" />
		<table class="normal">
		<tr>
		  <td class="formcolor">
		  From:
		  </td>
		  <td>
		  <select name="actFromId">
		  {section name=ix loop=$items}
		  <option value="{$items[ix].activityId}">{$items[ix].name}</option>
		  {/section}
		  </select>
		  </td>
		</tr>
		<tr>
		  <td class="formcolor">
		  To: 
		  </td>
		  <td>
		   <select name="actToId">
		  {section name=ix loop=$items}
		  <option value="{$items[ix].activityId}">{$items[ix].name}</option>
		  {/section}
		  </select>
		  </td>
		</tr>
		<tr>
		  <td class="formcolor">&nbsp;</td>
		  <td class="formcolor">
		    <input type="submit" name="add_trans" value="{tr}add{/tr}" />
		  </td>
		</tr>
		</table>	
		</form>
	</td>
</tr>
</table>	
	
