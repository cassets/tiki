<br/><br/><br/><br/>
<div align="center">
<h1>{tr}Welcome to the Tiki Chat Rooms{/tr}</h1>
<h2>{tr}Please select a chat channel{/tr}</h2>
<form action="tiki-chatroom.php" method="post">
<select name="channelId">
{section name=ix loop=$channels}
<option value="{$channels[ix].channelId}">{$channels[ix].name}</option>
{/section}
</select><br/><br/>
{if !$user}
{tr}Nickname{/tr}: <input type="text" name="nickname" /><br/><br/>
{/if}
<input type="submit" name="enter" value="{tr}enter chat room{/tr}" />
</form>
</div>
