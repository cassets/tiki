{* $Header: /cvsroot/tikiwiki/tiki/templates/styles/musus/modules/mod-eph.tpl,v 1.3 2004-01-16 19:37:25 musus Exp $ *}

{tikimodule title="<a class=\"tikitlink\" href=\"tiki-eph.php\">{tr}Ephemerides{/tr}</a>" name="eph"}
{if $modephdata}
  <table>
  {if $modephdata.filesize}
    <tr class="module">
      <td text-align="center"><img alt="image" src="tiki-view_eph.php?ephId={$modephdata.ephId}" /></td>
    </tr>
  {/if}
  <tr>
    <td>{$modephdata.textdata}</td>
  </tr>
  </table>
{/if}
{/tikimodule}
