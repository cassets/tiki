{extends 'layout_view.tpl'}

{block name="title"}
	{title}{$title|escape}{/title}
{/block}

{block name="content"}
<form method="post" action="{service controller=workspace action=create}">
	<label>
		{tr}Template{/tr}
		<select name="template">
			{foreach from=$templates item=template}
				<option value="{$template.templateId|escape}">{$template.name|escape}</option>
			{/foreach}
		</select>
	</label>
	<label>
		{tr}Workspace name{/tr}
		<input type="text" name="name" value=""/>
	</label>
	<div class="submit">
		<input type="submit" class="btn btn-default btn-sm" value="{tr}Create{/tr}"/>
	</div>
</form>
{/block}
