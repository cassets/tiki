{* $Header: /cvsroot/tikiwiki/tiki/templates/modules/mod-directory_top_sites.tpl,v 1.10 2007-02-18 11:21:16 mose Exp $ *}

{if $feature_directory eq 'y'}
{if !isset($tpl_module_title)}
{if $nonums eq 'y'}
{eval var="{tr}Top $module_rows Sites{/tr}" assign="tpl_module_title"}
{else}
{eval var="{tr}Top Sites{/tr}" assign="tpl_module_title"}
{/if}
{/if}
{tikimodule title=$tpl_module_title name="directory_top_sites" flip=$module_params.flip decorations=$module_params.decorations}
  <table  border="0" cellpadding="0" cellspacing="0">
    {section name=ix loop=$modTopdirSites}
      <tr>
        {if $nonums != 'y'}<td valign="top" class="module">{$smarty.section.ix.index_next})</td>{/if}
        <td class="module">
          <a class="linkmodule" href="tiki-directory_redirect.php?siteId={$modTopdirSites[ix].siteId}" {if $directory_open_links eq 'n'}target="_new"{/if}>
            {$modTopdirSites[ix].name}
          </a>
        </td>
      </tr>
    {/section}
  </table>
{/tikimodule}
{/if}
