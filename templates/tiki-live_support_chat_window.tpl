<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
    <link rel="StyleSheet"  href="styles/{$style}" type="text/css" />
    {include file="bidi.tpl"}
    <title>Live support:{$role} window</title>
    {literal}
	<script type="text/javascript" src="lib/live_support/live-support.js">
	</script>
	{/literal}
	{$trl}
  </head>
  <body onUnload="javascript:chat_close(document.getElementById('role').value,document.getElementById('username').value);">
  	<input type="hidden" id="reqId" value="{$reqId}" />
  	<input type="hidden" id="senderId" value="{$senderId}" />
  	<input type="hidden" id="role" value="{$role}" />
  	<input type="hidden" id="username" value="{$username}" />
	{if $role eq 'user'}
		<table>
			<tr>
				<td  valign="top" style="text-align:center;">{$req_info.operator|avatarize}<br/>
					<b>{$req_info.operator}</b>
				</td>
				<td valign="top" >
					{tr}Chat started{/tr}<br/>
					<i>{$req_info.reason}</i>
				</td>
			</tr>
		</table>
	{elseif $role eq 'operator'}
		{if $req_info.tiki_user}
			<table>
			<tr>
				<td  valign="top" style="text-align:center;">{$req_info.tiki_user|avatarize}<br/>
					<b>{$req_info.tiki_user}</b>
				</td>
				<td valign="top" >
					{tr}Chat started{/tr}<br/>
					<i>{$req_info.reason}</i>
				</td>
			</tr>
			</table>
		{else}
			<table>
			<tr>
				<td valign="top" style="text-align:center;">
					<b>{$req_info.user}</b>
				</td>
				<td valign="top" >
					{tr}Chat started{/tr}<br/>
					<i>{$req_info.reason}</i>
				</td>
			</tr>
			</table>
		{/if}
	{else}
		<table width="100%">
			<tr>
				<td width="2%" style="text-align:center;" valign="top">
					<b>{tr}User:{/tr}</b><br/>
					{if $req_info.tiki_user}
						{$req_info.tiki_user|avatarize}<br/>
						<b>{$req_info.tiki_user}</b>
					{else}
						<b>{$req_info.user}</b>
					{/if}
				</td>
				<td valign="top">
					<i>{$req_info.reason}</i>
				</td>
				<td width="2%" style="text-align:center;" valign="top">
					<b>{tr}Operator:{/tr}</b><br/>
					{$req_info.operator|avatarize}<br/>
					<b>{$req_info.operator}</b>				
				</td>
			</tr>
		</table>
	{/if}
  	<iframe name='chat_data' src='tiki-live_support_chat_frame.php' width="290" height="300" scrolling="yes">
  	</iframe>
  	{literal}
  	<input type="text" id="data" onKeyPress="javascript:if(event.keyCode == 13) {write_msg(document.getElementById('data').value,document.getElementById('role').value,document.getElementById('username').value);}" />
  	<input type="button" value="send" onClick="javascript:write_msg(document.getElementById('data').value,document.getElementById('role').value,document.getElementById('username').value);" />
  	{/literal}
  	<script>
        /* Activate polling of requests */
        var last_event=0;
        event_poll();
    </script>
  </body>
</html>  