<div class="box">
<div class="box-title">
{tr}Login{/tr}
</div>
<div class="box-data">
    {if $user}
      {tr}logged as{/tr}: {$smarty.session.user}<br/>
      <a class="linkmodule" href="tiki-logout.php">{tr}Logout{/tr}</a>
    {else}
     <form name="loginbox" action="{$login_url}" method="post" {if $feature_challenge eq 'y'}onSubmit="doChallengeResponse()"{/if}> 
     {if $feature_challenge eq 'y'}
     <script language="javascript" src="lib/md5.js"></script>   
     {literal}
     <script language="javascript">
     <!--
     function doChallengeResponse() {
       document.loginbox.zoofoo.value=MD5(document.loginbox.pass.value);
       str = document.loginbox.user.value + 
       MD5(document.loginbox.pass.value) +
       document.loginbox.challenge.value;
       document.loginbox.response.value = MD5(str);
       document.loginbox.pass.value='';
       /*
       document.login.password.value = "";
       document.logintrue.username.value = document.login.username.value;
       document.logintrue.response.value = MD5(str);
       document.logintrue.submit();
       */
       document.loginbox.submit();
       return false;
     }
     // -->
    </script>
    {/literal}
     <input type="hidden" name="challenge" value="{$challenge}" />
     <input type="hidden" name="zoofoo" value="" />
     <input type="hidden" name="response" value="" />
     {/if}
      <table border="0">
      <tr>
        <td>
          <table>
          <tr><td><span class="user-box-text">{tr}user{/tr}:</span></td></tr>
          <tr><td><input type="text" name="user"  size="20" /></td></tr>
          <tr><td><span class="user-box-text">{tr}pass{/tr}:</span></td></tr>
          <tr><td><input type="password" name="pass" size="20" /></td></tr>
          <tr><td><input type="submit" name="login" value="{tr}login{/tr}" /></td></tr>
          <tr>
          {if $allowRegister eq 'y' and $forgotPass eq 'y'}
            <td valign="bottom">[<a class="link" href="tiki-register.php">{tr}register{/tr}</a>|<a class="link" href="tiki-remind_password.php">{tr}I forgot my pass{/tr}</a>]</td>
          {/if}
          {if $forgotPass eq 'y' and $allowRegister ne 'y'}
            <td valign="bottom"><a class="link" href="tiki-remind_password.php">{tr}I forgot my pass{/tr}</a></td>
          {/if}
          {if $forgotPass ne 'y' and $allowRegister eq 'y'}
            <td valign="bottom"><a class="link" href="tiki-register.php">{tr}register{/tr}</a></td>
          {/if}
          {if $forgotPass ne 'y' and $allowRegister ne 'y'}
          <td valign="bottom">&nbsp;</td>
          {/if}
          </tr>
          {if $http_login_url ne '' or $https_login_url ne ''}
          <tr>
          <td valign="bottom">
            <a class="link" href="{$http_login_url}">{tr}standard{/tr}</a> |
            <a class="link" href="{$https_login_url}">{tr}secure{/tr}</a>
          </td>
          </tr>
          {/if}
          {if $show_stay_in_ssl_mode eq 'y'}
          <tr><td><span class="user-box-text">{tr}stay in ssl mode{/tr}:&nbsp;</span>
            <input type="checkbox" name="stay_in_ssl_mode" {if $stay_in_ssl_mode eq 'y'}checked="checked"{/if} />
          </td>
          </tr>
          {else}
          <input type="hidden" name="stay_in_ssl_mode" value="{$stay_in_ssl_mode}" />
          {/if}
          </table>
        </td>
      </tr>
      </table>
      </form>
    {/if}
</div>
</div>
