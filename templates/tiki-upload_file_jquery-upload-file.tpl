{* $Id: tiki-upload_file.tpl 47507 2013-09-16 13:52:16Z chibaguy $ *}

{if !empty($filegals_manager) and !isset($smarty.request.simpleMode)}
	{assign var=simpleMode value='y'}
{else}
	{assign var=simpleMode value='n'}
{/if}

{title help="File+Galleries" admpage="fgal"}{if $editFileId}{tr}Edit File:{/tr} {$fileInfo.filename}{else}{tr}Upload File{/tr}{/if}{/title}

{if !empty($galleryId) or (isset($galleries) and count($galleries) > 0 and $tiki_p_list_file_galleries eq 'y') or (isset($uploads) and count($uploads) > 0)}
<div class="navbar">
	{if !empty($galleryId)}
		{button galleryId="$galleryId" href="tiki-list_file_gallery.php" _text="{tr}Browse Gallery{/tr}"}
	{/if}

	{if isset($galleries) and count($galleries) > 0 and $tiki_p_list_file_galleries eq 'y'}
		{if !empty($filegals_manager)}
			{assign var=fgmanager value=$filegals_manager|escape}
			{button href="tiki-list_file_gallery.php?filegals_manager=$fgmanager" _text="{tr}List Galleries{/tr}"}
		{else}
			{button href="tiki-list_file_gallery.php" _text="{tr}List Galleries{/tr}"}
		{/if}
	{/if}
	{if isset($uploads) and count($uploads) > 0}
		{button href="#upload" _text="{tr}Upload File{/tr}"}
	{/if}
	{if !empty($filegals_manager)}
		{if $simpleMode eq 'y'}{button simpleMode='n' galleryId=$galleryId href="" _text="{tr}Advanced mode{/tr}" _ajax="n"}{else}{button galleryId=$galleryId href="" _text="{tr}Simple mode{/tr}" _ajax="n"}{/if}
		<span{if $simpleMode eq 'y'} style="display:none;"{/if}>
			<label for="keepOpenCbx">{tr}Keep gallery window open{/tr}</label>
			<input type="checkbox" id="keepOpenCbx" checked="checked">
		</span>
	{/if}
</div>
{/if}


{if $editFileId and isset($fileInfo.lockedby) and $fileInfo.lockedby neq ''}
	{remarksbox type="note" title="{tr}Info{/tr}" icon="lock"}
	{if $user eq $fileInfo.lockedby}
		{tr}You locked the file{/tr}
	{else}
		{tr}The file is locked by {$fileInfo.lockedby}{/tr}
	{/if}
	{/remarksbox}
{/if}

<div>

<div id="form">
{*debut*}

<div class="container">
    <!-- The file upload form used as target for the file upload widget -->
    <form id="fileupload" url="tiki-upload_file.php" action="tiki-upload_file.php" method="POST" enctype="multipart/form-data">
  {*ajout*}
  <input type="hidden" name="upload">
	<input type="hidden" name="simpleMode" value="{$simpleMode}">
	{if !empty($filegals_manager)}
		<input type="hidden" name="filegals_manager" value="{$filegals_manager}">
	{/if}
	{if !empty($insertion_syntax)}
		<input type="hidden" name="insertion_syntax" value="{$insertion_syntax}">
	{/if}
	{if isset($token_id) and $token_id neq ''}
		<input type="hidden" value="{$token_id}" name="TOKEN">
	{/if}
  {*fin ajout*}
        <!-- Redirect browsers with JavaScript disabled to the origin page -->
        <noscript><input type="hidden" name="redirect" value="http://blueimp.github.io/jQuery-File-Upload/"></noscript>
        <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
        <div class="row fileupload-buttonbar">
            <div class="col-lg-7">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button button">
                    <input type="file" name="userfile[]" multiple>
                    <i class="glyphicon glyphicon-plus"></i>
                    <a>Add files...</a>
                </span>
                <span type="submit" class="btn btn-primary start button">
                    <i class="glyphicon glyphicon-upload"></i>
                    <a>Start upload</a>
                </span>
                <span type="reset" class="btn btn-warning cancel button">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <a>Cancel upload</a>
                </span>
{*ajout*}
          {if $editFileId}
            <input type="hidden" name="galleryId" value="{$galleryId}">
            <input type="hidden" name="fileId" value="{$editFileId}">
            <input type="hidden" name="lockedby" value="{$fileInfo.lockedby|escape}">
          {else}
            {if count($galleries) eq 0}
              <input type="hidden" name="galleryId" value="{$treeRootId}">
            {elseif empty($groupforalert)}
                <span class="btn btn-warning cancel button">
                <a>{tr}File gallery:{/tr}</a>
                <select id="galleryId" name="galleryId[]">
                  <option value="{$treeRootId}" {if $treeRootId eq $galleryId}selected="selected"{/if} style="font-style:italic; border-bottom:1px dashed #666;">{tr}Root{/tr}</option>
                  {section name=idx loop=$galleries}
                    {if $galleries[idx].id neq $treeRootId and ($galleries[idx].perms.tiki_p_upload_files eq 'y' or $tiki_p_userfiles eq 'y')}
                      <option value="{$galleries[idx].id|escape}" {if $galleries[idx].id eq $galleryId}selected="selected"{/if}>{$galleries[idx].name|escape}</option>
                    {/if}
                  {/section}
                </select>
                </span>
            {else}
              <input type="hidden" name="galleryId" value="{$galleryId}">
            {/if}
          {/if}
{*fin ajout*}
                <!--
                <button type="button" class="btn btn-danger delete">
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>Delete</span>
                </button>
                <input type="checkbox" class="toggle">
                -->
                <!-- The global file processing state -->
                <span class="fileupload-process"></span>
            </div>
            <!-- The global progress state -->
            <div class="col-lg-5 fileupload-progress fade">
                <!-- The global progress bar -->
                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                </div>
                <!-- The extended global progress state -->
                <div class="progress-extended">&nbsp;</div>
            </div>
        </div>
        <!-- The table listing the files available for upload/download -->
        <table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
    </form>
</div>
{literal}
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
            <span class="preview"></span>
        </td>
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">Processing...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td>
            {% if (!i && !o.options.autoUpload) { %}
                <span class="btn btn-primary start button" disabled style="visibility: none">
                    <i class="glyphicon glyphicon-upload"></i>
                    <a>Start</a>
                </span>
            {% } %}
            {% if (!i) { %}
                <span class="btn btn-warning cancel button">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <a>Cancel</a>
                </span>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td>
            <span class="preview">
                {% if (file.thumbnailUrl) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                {% } %}
            </span>
        </td>
        <td>
            <p class="name">
                {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                {% } else { %}
                    <span>{%=file.name%}</span>
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
            {% } %}
        </td>
        <td>
            <span class="size">{%=o.formatFileSize(file.size)%}</span>
        </td>
        <td>
            {% if (file.deleteUrl) { %}
                <span class="btn btn-danger delete button" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i class="glyphicon glyphicon-trash"></i>
                    <a>Delete</a>
                </span>
                <input type="checkbox" name="delete" value="1" class="toggle">
            {% } else { %}
                <span class="btn btn-warning cancel button">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <a>Cancel</a>
                </span>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
<!--[if (gte IE 8)&(lt IE 10)]>
<script src="vendor_extra/jquery-upload-file/js/cors/jquery.xdr-transport.js"></script>
<![endif]-->
{/literal}
{*fin*}

	{if $editFileId}
		{include file='categorize.tpl' notable='y'}<br>
		<hr class="clear">
		<div id="page_bar">
			<input name="upload" type="submit" class="btn btn-default" value="{tr}Save{/tr}">
		</div>
	{/if}

{if !$editFileId}
	<div id="page_bar">
	</div>
{/if}
</div>
{if !empty($fileInfo.lockedby) and $user ne $fileInfo.lockedby}
	{icon _id="lock" class="" alt=""}
	<span class="attention">{tr}The file is locked by {$fileInfo.lockedby}{/tr}</span>
{/if}

<br>
{if !$editFileId}
	{remarksbox type="note"}
		{tr}Maximum file size is around:{/tr}
		{if $tiki_p_admin eq 'y'}<a title="{$max_upload_size_comment}">{/if}
			{$max_upload_size|kbsize:true:0}
		{if $tiki_p_admin eq 'y'}</a>
			{if $is_iis}<br>{tr}Note: You are running IIS{/tr}. {tr}maxAllowedContentLength also limits upload size{/tr}. {tr}Please check web.config in the Tiki root folder{/tr}{/if}
		{/if}
	{/remarksbox}
{/if}
</div>

{if isset($metarray) and $metarray|count gt 0}
	{include file='metadata/meta_view_tabs.tpl'}
{/if}
