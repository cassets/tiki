{if $prefs.feature_categories eq 'y' and $tiki_p_modify_object_categories eq 'y' and (count($categories) gt 0 or $tiki_p_admin_categories eq 'y')}
    {if !isset($notable) || $notable neq 'y'}
        <div class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-4 text-right">{tr}Categorize{/tr}</label>
                <div class="col-sm-8">
                {if isset($colsCategorize)} colspan="{$colsCategorize}"{/if}

    {/if}
{if $mandatory_category >= 0 or $prefs.javascript_enabled neq 'y' or (isset($auto) and $auto eq 'y')}
    <div id="categorizator">
{else}
    {if !isset($notable) || $notable neq 'y'}{button href="#" _flip_id='categorizator' _class='link' _text="{tr}Select Categories{/tr}" _flip_default_open='n'}{/if}
<div id="categorizator" style="display:{if isset($smarty.session.tiki_cookie_jar.show_categorizator) and $smarty.session.tiki_cookie_jar.show_categorizator eq 'y' or (isset($notable) && $notable eq 'y')}block{else}none{/if};">
{/if}
    <div class="multiselect">
  {if count($categories) gt 0}
	{$cat_tree}
    <input type="hidden" name="cat_categorize" value="on">
	<div class="clear">
	{if $tiki_p_admin_categories eq 'y'}
    	<div class="pull-right"><a href="tiki-admin_categories.php" class="link">{tr}Admin Categories{/tr} {icon _id='wrench'}</a></div>
	{/if}
	
	{select_all checkbox_names='cat_categories[]' label="{tr}Select/deselect all categories{/tr}"}
  
	{else}
	<div class="clear">
 	{if $tiki_p_admin_categories eq 'y'}
    <div class="pull-right"><a href="tiki-admin_categories.php" class="link">{tr}Admin Categories{/tr} {icon _id='wrench'}</a></div>
 	{/if}
    {tr}No categories defined{/tr}
  {/if}
    </div> {* end .clear *}
   </div> {* end #multiselect *}
  </div> {* end #categorizator *}
	{if !isset($notable) || $notable neq 'y'}

  {/if}
{/if}
