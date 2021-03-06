{extends 'layout_view.tpl'}

{block name="title"}
	{title}{$title|escape}{/title}
{/block}

{block name="content"}
{if $threadId}
	<p>{tr}Your comment was posted.{/tr}</p>
	<p>{object_link type=$type objectId=$objectId}</p>
{else}
	<form method="post" action="{service controller=comment action=post}" role="form">
		<div class="panel panel-default">
			<div class="panel-heading">
				{tr}Post New Comment{/tr}
				{if ! $user or $prefs.feature_comments_post_as_anonymous eq 'y'}
						{if $user}
							{remarksbox type=warning title="Anonymous posting"}
								{tr}You are currently registered on this site. This section is optional. By filling it, you will not link this post to your account and preserve your anonymity.{/tr}
							{/remarksbox}
						{/if}
						<div class="form-inline">
							<div class="form-group">
								<label class="clearfix" for="comment-anonymous_name">{tr}Name{/tr}</label>
								<input type="text" name="anonymous_name" id="comment-anonymous_name" value="{$anonymous_name|escape}"/>
							</div>
							<div class="form-group">
								<label class="clearfix" for="comment-anonymous_email">{tr}Email{/tr}</label>
								<input type="email" id="comment-anonymous_email" name="anonymous_email" value="{$anonymous_email|escape}"/>
							</div>
							<div class="form-group">
								<label class="clearfix" for="comment-anonymous_website">{tr}Website{/tr}</label>
								<input type="url" id="comment-anonymous_website" name="anonymous_website" value="{$anonymous_website|escape}"/>
							</div>
						</div>
				{/if}
			</div>
				<div class="panel-body">
					<input type="hidden" name="type" value="{$type|escape}"/>
					<input type="hidden" name="objectId" value="{$objectId|escape}"/>
					<input type="hidden" name="parentId" value="{$parentId|escape}"/>
					<input type="hidden" name="post" value="1"/>
					{if $prefs.comments_notitle neq 'y'}
						<div class="form-group">
							<label for="comment-title" class="clearfix comment-title">{tr}Title{/tr}</label>
							<input type="text" id="comment-title" name="title" value="{$title|escape}" class="form-control" placeholder="Comment title"/>
						</div>
					{/if}
					{capture name=rows}{if $type eq 'forum'}{$prefs.default_rows_textarea_forum}{else}{$prefs.default_rows_textarea_comment}{/if}{/capture}
					{textarea codemirror='true' syntax='tiki' name=data comments="y" _wysiwyg="n" rows=$smarty.capture.rows class="form-control"}{$data|escape}{/textarea}
				</div>
				<div class="panel-footer">
					{if $prefs.feature_antibot eq 'y'}
						{assign var='showmandatory' value='y'}
						{include file='antibot.tpl'}
					{/if}
					<input type="submit" class="comment-post btn btn-primary btn-sm" value="{tr}Post{/tr}"/>
					<div class="btn btn-link btn-sm">
						<a href="#" onclick="$(this).closest('.comment-container, .ui-dialog-content').reload(); return false;">{tr}Cancel{/tr}</a>
					</div>
				</div>
		</div>
	</form>
{/if}
{/block}
