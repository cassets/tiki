{* $Id$ *}

<div class="integrated-page">
  {$data}
</div>

<hr />
<div id="page-bar">
  <table><tr>
    {if $cached eq 'y'}
    <td><div class="button2">
      <a href="tiki-integrator.php?repID={$repID|escape}{if strlen($file) gt 0}&amp;file={$file}{/if}&amp;clear_cache" title="{tr}Clear cached version and refresh cache{/tr}">
        {tr}Refresh{/tr}
      </a>
    </div></td>
    {/if}

    <td><div class="button2">
      <a href="tiki-list_integrator_repositories.php">{tr}List Repositories{/tr}</a>
    </div></td>

    {* Show config buttons only for admins *}
    {if $tiki_p_admin eq 'y' or $tiki_p_admin_integrator eq 'y'}
    <td><div class="button2">
       <a href="tiki-admin_integrator_rules.php?repID={$repID|escape}&amp;file={$file|escape}">{tr}configure rules{/tr}</a>
     </div></td>
     <td><div class="button2">
       <a href="tiki-admin_integrator.php?action=edit&amp;repID={$repID|escape}">{tr}Edit Repository{/tr}</a>
     </div></td>
    {/if}

  </tr></table>
</div>
<br />
