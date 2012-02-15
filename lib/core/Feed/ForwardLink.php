<?php
// (c) Copyright 2002-2012 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

Class Feed_ForwardLink extends Feed_Abstract
{
	var $type = "Feed_ForwardLink";
	
	function wikiView($args)
	{
		global $prefs, $headerlib, $_REQUEST, $smarty;
		
		if (isset($_REQUEST['protocol'], $_REQUEST['contribution']) && $_REQUEST['protocol'] == 'forwardlink') {
			
			//here we do the confirmation that another wiki is trying to talk with this one
			$response = array(
				"protocol"=>	"forwardlink",
				"response"=>	"failure",
				"date"=>		$args['lastModif'],
			);

			$_REQUEST['contribution'] = json_decode($_REQUEST['contribution']);
			$_REQUEST['contribution']->origin = $_SERVER['REMOTE_ADDR'];
			
			if ( Feed_ForwardLink_Contribution::forwardLink($args['object'])
				->addItem($_REQUEST['contribution']) == true ) {
				$response['response'] = "success";
			}
			
			echo json_encode($response);
			die;
		}
		
		$phrase = (!empty($_REQUEST['phrase']) ? htmlspecialchars($_REQUEST['phrase']) : '');
		
		session_start();
		if (!empty($phrase)) $_SESSION['phrase'] = $phrase; //prep for redirect if it happens;
		
		if (!empty($phrase)) Feed_ForwardLink_Search::goToNewestWikiRevision($args['version'], $phrase, $args['object']);
		
		if (!empty($_SESSION['phrase'])) { //recover from redirect if it happened
			$phrase = $_SESSION['phrase'];
			unset($_SESSION['phrase']);
		}
		
		$_REQUEST['preview'] = (!empty($_REQUEST['preview']) ? $_REQUEST['preview'] : $args['version']);
		$phraseI = 0;
		
		$feedItems = Feed_ForwardLink_Contribution::forwardLink($args['object'])->getItems();
		$phrases = array();
		foreach($feedItems as $item) {
			$phrases[] = $thisText = htmlspecialchars($item->forwardlink->text);
		}
		
		$phraser = new JisonParser_Phraser_Handler();
		
		$parsed = $smarty->getTemplateVars("parsed");
		if (!empty($parsed)) {
			$smarty->assign("parsed", $phraser->findPhrases($parsed, $phrases));
		} else {
			$previewd = $smarty->getTemplateVars("previewd");
			if (!empty($previewd)) {
				$previewd = $phraser->findPhrases($previewd, $phrases);
				$smarty->assign("previewd", $previewd);
			}
		}
		
		foreach($feedItems as $i => $item) {
			$thisText = htmlspecialchars($item->forwardlink->text);
			$thisHref = htmlspecialchars($item->textlink->href);
			$linkedText = htmlspecialchars($item->textlink->text);
			
			if ($thisText == $phrase) {
				$headerlib->add_jq_onready(<<<JQ
					$('#page-data')
						.rangyRestoreSelection('$thisText', function(r) {
							$('<a>*</a>')
								.attr('href', '$thisHref')
								.attr('text', '$linkedText')
								.addClass('forwardlink')
								.insertBefore(r.selection[0]);
									
							r.selection.addClass('ui-state-highlight');
									
							$('body,html').animate({
								scrollTop: r.start.offset().top
							});
						});
JQ
				);
			} else {
			
				$headerlib->add_jq_onready(<<<JQ
					$('<a>*</a>')
						.attr('href', '$thisHref')
						.attr('text', '$linkedText')
						.addClass('forwardlink')
						.insertBefore('.phraseStart$i');
					
					$('.phrase$i').addClass('ui-state-highlight');
JQ
				);
			}
			$phraseI++;
		}

		$headerlib->add_jq_onready(<<<JQ
			$('#page-data').trigger('rangyDone');
			
			$('.forwardlink')
				.click(function() {
					me = $(this);
					var href = me.attr('href');
					var text = me.attr('text');
					
					$('<form action="' + href + '" method="post">' + 
						'<input type="hidden" name="phrase" value="' + text + '" />' +
					'</form>')
						.appendTo('body')
						.submit();
					
					return false;
				});
JQ
		);
		
		$wikiAttributes = TikiLib::lib("trkqry")
			->tracker("Wiki Attributes")
			->byName()
			->excludeDetails()
			->filter(array(
				'field'=> 'Type',
				'value'=> 'Question'
			))
			->filter(array(
				'field'=> 'Page',
				'value'=> $args['object']
			))
			->render(false)
			->query();
		
		//print_r($wikiAttributes);
		$answers = array();
		foreach($wikiAttributes as $wikiAttribute) {
			$answers[] = array(
				"question"=> strip_tags($wikiAttribute['Value']),
				"answer"=> '',
			);
		}
		
		$answers = json_encode($answers);
		
		$headerlib
			->add_jsfile("lib/rangy/uncompressed/rangy-core.js")
			->add_jsfile("lib/rangy/uncompressed/rangy-cssclassapplier.js")
			->add_jsfile("lib/rangy/uncompressed/rangy-selectionsaverestore.js")
			->add_jsfile("lib/rangy_tiki/rangy-phraser.js")
			->add_jsfile("lib/ZeroClipboard.js")
			->add_jsfile("lib/core/JisonParser/Phraser.js")
			->add_jsfile("lib/jquery/md5.js");
			
		$href = TikiLib::tikiUrl() . 'tiki-index.php?page=' . urlencode($args['object']);
		$version = $args['version'];
		$date = $args['lastModif'];
		//print_r( $prefs );
		
		$websiteTitle = htmlspecialchars($prefs['browsertitle']);
		
		$headerlib->add_jq_onready(<<<JQ
			var answers = $answers;
			
			$('<div />')
				.appendTo('body')
				.text(tr('Create ForwardLink'))
				.css('position', 'fixed')
				.css('top', '0px')
				.css('right', '0px')
				.css('font-size', '10px')
				.fadeTo(0, 0.85)
				.button()
				.click(function() {
					$(this).remove();
					$.notify(tr('Highlight text to be linked'));
			
					$(document).bind('mousedown', function() {
						if (me.data('rangyBusy')) return;
						$('div.forwardLinkCreate').remove();
						$('embed[id*="ZeroClipboard"]').parent().remove();
					});
					
					var me = $('#page-data').rangy(function(o) {
						if (me.data('rangyBusy')) return;
						
						var forwardLinkCreate = $('<div>' + tr('Accept TextLink & ForwardLink') + '</div>')
							.button()
							.addClass('forwardLinkCreate')
							.css('position', 'absolute')
							.css('top', o.y + 'px')
							.css('left', o.x + 'px')
							.css('font-size', '10px')
							.fadeTo(0,0.80)
							.mousedown(function() {									
								var suggestion = rangy.expandPhrase(o.text, '\\n', me[0]);
								var buttons = {};
								
								if (suggestion == o.text) {
									getAnswers();
								} else {
									buttons[tr('Ok')] = function() {
										o.text = suggestion;
										me.box.dialog('close');
										getAnswers();
									};
									
									buttons[tr('Cancel')] = function() {
										me.box.dialog('close');
										getAnswers();
									};
									
									me.box = $('<div>' +
										'<table>' +
											'<tr>' +
												'<td>' + tr('You selected:') + '</td>' +
												'<td><b>"</b>' + o.text + '<b>"</b></td>' +
											'</tr>' +
											'<tr>' +
												'<td>' + tr('Suggested selection:') + '</td>' +
												'<td class="ui-state-highlight"><b>"</b>' + suggestion + '<b>"</b></td>' +
											'</tr>' +  
										'</tabl>' + 
									'</div>')
										.dialog({
											title: tr("Suggestion"),
											buttons: buttons,
											width: $(window).width() / 2,
											modal: true
										})
								}
								
								function getAnswers() {
									if (!answers.length) {
										return accept();
									}
									
									var answersDialog = $('<div />');
									
									$.each(answers, function() {
										$('<div style="font-weight: bold;" />')
											.text(this.question)
											.appendTo(answersDialog);
										
										$('<input />')
											.appendTo(answersDialog);
									});
									
									var answersDialogButtons = {};
									answersDialogButtons[tr("Ok")] = function() {
										answersDialog.dialog('close');
										accept();
									};
									
									answersDialog.dialog({
										title: tr("Please fill in the questions below"),
										buttons: answersDialogButtons,
										modal: true
									});
								}
								
								function accept() {
									var data = {
										websiteTitle: '$websiteTitle',
										websiteSubtitle: '',
										moderator: '',
										moderatorInfo: '',
										subtitle: '',
										text: (o.text + '').replace(/[\\n'"]/g,' '),
										hash: '',
										author: '',
										href: '$href',
										answers: answers,
										version: $version,
										date: $date
									};
									
									data.hash = md5(data.websiteTitle, data.text);
									
									me.data('rangyBusy', true);
									
									var forwardLinkCopy = $('<div></div>');
									var forwardLinkCopyButton = $('<div>' + tr('Copy To Clipboard') + '</div>')
										.button()
										.appendTo(forwardLinkCopy);
									var forwardLinkCopyValue = $('<textarea style="width: 100%; height: 80%;"></textarea>')
										.val(encodeURI(JSON.stringify(data)))
										.appendTo(forwardLinkCopy);
									forwardLinkCopy.dialog({
										title: tr("Copy This"),
										modal: true,
										close: function() {
											me.data('rangyBusy', false);
											$(document).mousedown();
										},
										draggable: false
									});
									
									forwardLinkCopyValue.select().focus();
									
									var clip = new ZeroClipboard.Client();
									clip.setHandCursor( true );
									
									clip.addEventListener('complete', function(client, text) {
						                forwardLinkCreate.remove();
										forwardLinkCopy.dialog( "close" );
										clip.hide();
										me.data('rangyBusy', false);
										
										
										$.notify(tr('TextLink & ForwardLink data copied to your clipboard'));
										return false;
						            });
									
									clip.glue( forwardLinkCopyButton[0] );
									
									clip.setText(forwardLinkCopyValue.val());
									
									
									$('embed[id*="ZeroClipboard"]').parent().css('z-index', '9999999999');
									
									if (me.box)
										me.box.dialog('close');
								}
							})
							.appendTo('body');
					});
			});
JQ
);
	}
}