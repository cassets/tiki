<h3>{$plugin.name|escape}
<<<<<<< .working
<a href="javascript:void(0)" onclick="needToConfirm=false;popup_plugin_form('{$plugin_name|lower|@addslashes}')">{tr}Insert{/tr}</a>
{if $plugin.documentation}
<a href="{$plugin.documentation|escape}">{tr}Documentation{/tr}</a>
=======
{if $prefs.javascript_enabled eq 'y'}
	<a href="javascript:void(0)" onclick="needToConfirm=false;popup_plugin_form('{$plugin_name|lower|@addslashes}')">{icon _id=page_edit text="{tr}Insert{/tr}"}</a>
>>>>>>> .merge-right.r18200
{/if}
<<<<<<< .working
=======

{if $prefs.feature_help eq 'y'}
	{if $plugin.documentation}
		<a href="{$plugin.documentation|escape}">{icon _id=help}</a>
	{/if}
>>>>>>> .merge-right.r18200
{/if}

</h3>
<div class="plugin-desc">
{$plugin.description}
</div>
<div class="plugin-sample">
{if $plugin.body}
&#123;{$plugin_name}(
{foreach key=name item=param from=$plugin.params}
	<div class="plugin-param">
	{if $param.required}
		{$name}=<em>"{$param.description|escape}"</em>
	{else}
		[ {$name}=<em>"{$param.description|escape}"</em> ]
	{/if}
	</div>
{/foreach}
)&#125;
<div class="plugin-param">
{$plugin.body}
</div>
</div>
&#123;{$plugin_name}&#125;
{else}
&#123;{$plugin_name|@lower}
{foreach key=name item=param from=$plugin.params}
	<div class="plugin-param">
	{if $param.required}
		{$name}=<em>"{$param.description|escape}"</em>
	{else}
		[ {$name}=<em>"{$param.description|escape}"</em> ]
	{/if}
	</div>
{/foreach}
&#125;
</div>
{/if}
