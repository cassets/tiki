{* $Id$ *}

{tikimodule error=$module_params.error title=$tpl_module_title name="quick_edit" flip=$module_params.flip decorations=$module_params.decorations nobox=$module_params.nobox notitle=$module_params.notitle}
<form method="post" action="{$qe_action|escape}">
{if $templateId}<input type="hidden" name="templateId" value="{$templateId}" />{/if}
{if $customTip}<input type="hidden" name="customTip" value="{$customTip}" />{/if}
{if $customTipTitle}<input type="hidden" name="customTipTitle" value="{$customTipTitle}" />{/if}
{if $wikiTplHeader}<input type="hidden" name="wikiTplHeader" value="{$wikiTplHeader}" />{/if}
{if $mod_quickedit_heading}<div class="box-data">{$mod_quickedit_heading}</div>{/if}
{if $enterdescription==1 or $chooseCateg==1 or $pastetext==1}<legend>{tr}Page name{/tr}</legend>{/if}
<input id="{$qefield}" type="text" size="{$size}" name="page" />
{if $enterdescription==1}{if $prefs.feature_wiki_description eq 'y' or $prefs.metatag_pagedesc eq 'y'}
<div>
{if $prefs.metatag_pagedesc eq 'y'}</div><legend>{tr}Description (used for metatags):{/tr}</legend>{else}<legend>{tr}Description:{/tr}</legend>{/if}
<a id="flipperqdescription" href="javascript:flipWithSign('qdescription')">[+]</a>
<input id="qdescription" style="display: none;" type="text" size="{$size}" name="description" />
</div>{/if}{/if}
{if $chooseCateg==1}
<div>
<input type="hidden" name="cat_categorize" value="on" />
<label>{tr}Category{/tr}</label>
<a id="flipperqcat" href="javascript:flipWithSign('qcat')">[+]</a>
<select id="qcat" style="display: none;" name="cat_categories[]">
<option></option>
{foreach from=$qcats item="cat"}<option value="{$cat.categId}"{if $cat.categId=$categId} selected="selected"{/if}>{$cat.name}</option>
{/foreach}
</select>
</div>
{else}
{if $categId}<input type="hidden" name="categId" value="{$categId}" />{/if}
{/if}
{if $addcategId}<input type="hidden" name="cat_categories[]" value="{$addcategId}" />
<input type="hidden" name="cat_categorize" value="on" />{/if}
{if $pastetext==1}<label>{tr}Paste content here{/tr}</label><textarea name="copypaste" cols="{$size}" rows="2"></textarea>{/if}
<input type="submit" name="qedit" value="{$submit}" />
</form>
{if $prefs.javascript_enabled eq 'y' and $prefs.feature_jquery_autocomplete eq 'y'}
{jq}
	$jq("#{{$qefield}}").tiki("autocomplete", "pagename");
{/jq}
{/if}
{/tikimodule}
