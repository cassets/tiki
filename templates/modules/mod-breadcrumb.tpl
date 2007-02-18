{* $Header: /cvsroot/tikiwiki/tiki/templates/modules/mod-breadcrumb.tpl,v 1.12 2007-02-18 11:21:16 mose Exp $ *}
{if !isset($tpl_module_title)}{eval assign=tpl_module_title var="{tr}Recently visited pages{/tr}"}{/if}
{tikimodule title=$tpl_module_title name="breadcrumb" flip=$module_params.flip decorations=$module_params.decorations}
  <table  border="0" cellpadding="0" cellspacing="0">
    {section name=ix loop=$breadCrumb}
      <tr><td class="module">
        <a class="linkmodule" href="tiki-index.php?page={$breadCrumb[ix]|escape:'url'}">
          {if ($maxlen > 0 && strlen($breadCrumb[ix]) > $maxlen)}
            {$breadCrumb[ix]|truncate:$maxlen:"...":true}
          {else}
            {$breadCrumb[ix]}
          {/if}
        </a>
      </td></tr>
    {sectionelse}
      <tr><td class="module">&nbsp;</td></tr>
    {/section}
  </table>
{/tikimodule}
