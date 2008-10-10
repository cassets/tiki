{* $Id$ *}

{popup_init src="lib/overlib.js"}

{title help="File+Galleries" admpage="fgal"}
  {strip}
    {if $edit_mode eq 'y'}
      {if $galleryId eq 0}
        {tr}Create a file gallery{/tr}
      {else}
        {tr}Edit Gallery{/tr}: {$name}
      {/if}
    {else}
      {if $galleryId eq 0}
        {tr}File Galleries{/tr}
      {else}
        {tr}Gallery{/tr}: {$name}
      {/if}
    {/if}
  {/strip}
{/title}

{if $edit_mode neq 'y' and $description neq ''}
  <div class="simplebox">
    {$description|escape}
  </div>
{/if}

<div class="navbar">
{if $galleryId gt 0}

  {if $user and $prefs.feature_user_watches eq 'y'}
    {if $user_watching_file_gallery eq 'n'}
      <a href="tiki-list_file_gallery.php?galleryId={$galleryId|escape:"url"}&amp;galleryName={$name|escape:"url"}&amp;watch_event=file_gallery_changed&amp;watch_object={$galleryId|escape:"url"}&amp;watch_action=add"{if $filegals_manager neq ''}&amp;filegals_manager={$filegals_manager|escape}{/if} class="icon">{icon _id='eye' align='right' alt="{tr}Monitor this Gallery{/tr}"}</a>
    {else}
      <a href="tiki-list_file_gallery.php?galleryId={$galleryId|escape:"url"}&amp;galleryName={$name|escape:"url"}&amp;watch_event=file_gallery_changed&amp;watch_object={$galleryId|escape:"url"}&amp;watch_action=remove{if $filegals_manager neq ''}&amp;filegals_manager={$filegals_manager|escape}{/if}" class="icon">{icon _id='no_eye' align='right' alt="{tr}Stop Monitoring this Gallery{/tr}"}</a>
    {/if}
  {/if}  
  {if $prefs.rss_file_gallery eq 'y'}
    {if $gal_info.type eq "podcast" or $gal_info.type eq "vidcast"}
      <a href="tiki-file_gallery_rss.php?galleryId={$galleryId}&amp;ver=PODCAST">
      <img src='img/rss_podcast_80_15.png' border='0' alt="{tr}RSS feed{/tr}" title="{tr}RSS feed{/tr}"  align='right' /></a>
    {else}
      <a href="tiki-file_gallery_rss.php?galleryId={$galleryId}">
      {icon _id='feed' alt="{tr}RSS feed{/tr}" title="{tr}RSS feed{/tr}" align='right'}</a>
    {/if}
  {/if}
  {if $view eq 'browse'}
    {if $show_details eq 'y'}
      {self_link show_details='n'}{icon _id='no_information' align='right' onload='adjustThumbnails()'}{/self_link}
    {else}
      {self_link show_details='y'}{icon _id='information' align='right' onload='adjustThumbnails()'}{/self_link}
    {/if}
  {/if}

  {if $tiki_p_list_file_galleries eq 'y' or (!isset($tiki_p_list_file_galleries) and $tiki_p_view_file_gallery eq 'y')}<a href="tiki-list_file_gallery.php{if $filegals_manager neq ''}?filegals_manager={$filegals_manager|escape}{/if}" title="{tr}List Galleries{/tr}">{tr}List Galleries{/tr}</a>{/if}

  {if $tiki_p_create_file_galleries eq 'y' and $edit_mode ne 'y'}
	{button _text="{tr}Create a file gallery{/tr}" href="?edit_mode=1&amp;parentId=`$galleryId`"}
  {/if}
  
  {if $tiki_p_admin_file_galleries eq 'y' or $user eq $gal_info.user}
    {if $edit_mode eq 'y' or $dup_mode eq 'y'}
      <a href="tiki-list_file_gallery.php?galleryId={$galleryId}{if $filegals_manager neq ''}&amp;filegals_manager={$filegals_manager|escape}{/if}">{tr}Browse Gallery{/tr}</a>
    {else}
      <a href="tiki-list_file_gallery.php?edit_mode=1&amp;galleryId={$galleryId}{if $filegals_manager neq ''}&amp;filegals_manager={$filegals_manager|escape}{/if}" title="{tr}Edit Gallery{/tr}">{tr}Edit Gallery{/tr}</a>
      {if $view eq 'browse'}
        <a href="tiki-list_file_gallery.php?view=list&amp;galleryId={$galleryId}{if $filegals_manager neq ''}&amp;filegals_manager={$filegals_manager|escape}{/if}" title="{tr}List Gallery{/tr}">{tr}List Gallery{/tr}</a>
      {else}
        <a href="tiki-list_file_gallery.php?view=browse&amp;galleryId={$galleryId}{if $filegals_manager neq ''}&amp;filegals_manager={$filegals_manager|escape}{/if}" title="{tr}Browse Images{/tr}">{tr}Browse Images{/tr}</a>
      {/if}
    {/if}
  {/if}

  {if $tiki_p_assign_perm_file_gallery eq 'y'}
		<a href="tiki-objectpermissions.php?objectName={$name|escape:"url"}&amp;objectType=file+gallery&amp;permType=file+galleries&amp;objectId={$galleryId}{if $filegals_manager neq ''}&amp;filegals_manager={$filegals_manager|escape}{/if}">{tr}Permissions{/tr}</a>
	{/if}

  
  {if $tiki_p_admin_file_galleries eq 'y' or $user eq $gal_info.user or $gal_info.public eq 'y'}
    {if $tiki_p_upload_files eq 'y'}
      <a href="tiki-upload_file.php?galleryId={$galleryId}{if $filegals_manager neq ''}&amp;filegals_manager={$filegals_manager|escape}{/if}">{tr}Upload File{/tr}</a>
    {/if}
    {if $prefs.feature_file_galleries_batch eq "y" and $tiki_p_batch_upload_file_dir eq 'y'}
      <a href="tiki-batch_upload_files.php?galleryId={$galleryId}{if $filegals_manager neq ''}&amp;filegals_manager={$filegals_manager|escape}{/if}">{tr}Directory batch{/tr}</a>
    {/if}
  {/if}

{else}

  {if $edit_mode eq 'y' or $dup_mode eq 'y'}
    <a href="tiki-list_file_gallery.php{if $filegals_manager neq ''}?filegals_manager={$filegals_manager|escape}{/if}">{tr}List Galleries{/tr}</a>
  {/if}
  {if $tiki_p_create_file_galleries eq 'y' and $edit_mode ne 'y'}
	{button _text="{tr}Create a file gallery{/tr}" href="?edit_mode=1&amp;parentId=-1&amp;galleryId=0"}
  {/if}
  {if $tiki_p_create_file_galleries eq 'y' and $dup_mode ne 'y'}
    <a href="tiki-list_file_gallery.php?dup_mode=1{if $filegals_manager neq ''}&amp;filegals_manager={$filegals_manager|escape}{/if}">{tr}Duplicate File Gallery{/tr}</a>
  {/if}

{/if}

{if $edit_mode neq 'y'}
<a href="#" onclick="javascript:window.open('tiki-list_file_gallery.php?galleryId={$galleryId}&amp;slideshow','','menubar=no,width=600,height=500');">{tr}SlideShow{/tr}</a>
{/if}

</div>

{if $filegals_manager neq ''}
{remarksbox type="tip" title="{tr}Tip{/tr}"}{tr}Be careful to set the right permissions on the files you link to{/tr}.{/remarksbox}
{/if}
{if isset($fileChangedMessage) and $fileChangedMessage neq ''}
  {remarksbox type="note" title="{tr}Note{/tr}"}
    {$fileChangedMessage}
    <form method="post" action="{$smarty.server.PHP_SELF}{if $filegals_manager neq ''}?filegals_manager={$filegals_manager|escape}{/if}">
      <input type="hidden" name="galleryId" value="{$galleryId|escape}" />
      <input type="hidden" name="fileId" value="{$fileId|escape}" />
      {tr}Your comment{/tr} ({tr}optional{/tr}): <input type="text" name="comment" size="40" />
      {icon _id='accept' _tag='input_image'}
    </form>
  {/remarksbox}
{/if}

<div class="navbar" align="right">
  {if $user and $prefs.feature_user_watches eq 'y'}
    {if $category_watched eq 'y'}
      {tr}Watched by categories{/tr}:
      {section name=i loop=$watching_categories}
        <a href="tiki-browse_categories?parentId={$watching_categories[i].categId}">{$watching_categories[i].name}</a>&nbsp;
      {/section}
    {/if}			
  {/if}
</div>


{foreach from=$fgal_diff item=fgp_prop key=fgp_name name=change}
{if $smarty.foreach.change.first}
<div class="rbox tip">
<div class="rbox-title tip">{tr}Modifications{/tr}</div>
{/if}
<div class="rbox-data warning">{tr}Property <b>{$fgp_name}</b> Changed{/tr}</div>
{if $smarty.foreach.change.last}
</div>
{/if}
{/foreach}

{if $edit_mode eq 'y'}

  {include file='edit_file_gallery.tpl'}

{elseif $dup_mode eq 'y'}

  {include file='duplicate_file_gallery.tpl'}

{else}
{if $files or ($find ne '')}
  {include file='find.tpl' find_show_num_rows = 'y'}
{/if}
  {include file='list_file_gallery.tpl'}

  {if $galleryId gt 0
    && $prefs.feature_file_galleries_comments == 'y'
    && (($tiki_p_read_comments  == 'y'
    && $comments_cant != 0)
    ||  $tiki_p_post_comments  == 'y'
    ||  $tiki_p_edit_comments  == 'y')}

    <span class="button2">
      <a href="#" onclick="javascript:flip('comzone');flip('comzone_close','inline');return false;"{if $comments_cant > 0} class="highlight"{/if}>
        {if $comments_cant == 0 or ($tiki_p_read_comments  == 'n' and $tiki_p_post_comments  == 'y')}
          {tr}Add Comment{/tr}
        {elseif $comments_cant == 1}
          {tr}1 comment{/tr}
        {else}
          {$comments_cant} {tr}comments{/tr}
        {/if}
        <span id="comzone_close" style="display:{if isset($smarty.session.tiki_cookie_jar.show_comzone) and $smarty.session.tiki_cookie_jar.show_comzone eq 'y'}inline{else}none{/if};">({tr}Hide{/tr})</span>
      </a>
    </span>

  {include file=comments.tpl}
  {/if}
{/if}

{if $galleryId>0}
  {if $edited eq 'y'}
  <div class="wikitext">
    {tr}You can access the file gallery using the following URL{/tr}: <a class="fgallink" href="{$url}?galleryId={$galleryId}">{$url}?galleryId={$galleryId}</a>
  </div>
  {/if}
{/if}

