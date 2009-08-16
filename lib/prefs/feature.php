<?php

function prefs_feature_list() {
	return array(
		'feature_wiki' => array(
			'name' => tra('Wiki'),
			'description' => tra('Collaboratively authored documents with history of changes.'),
			'type' => 'flag',
			'help' => 'Wiki',
		),
		'feature_blogs' => array(
			'name' => tra('Blog'),
			'description' => tra('Online diaries or journals.'),
			'type' => 'flag',
			'help' => 'Blogs',
		),
		'feature_galleries' => array(
			'name' => tra('Image Gallery'),
			'description' => tra('Collections of graphic images for viewing or downloading (photo album)'),
			'type' => 'flag',
			'help' => 'Image+Gallery',
		),
		'feature_machine_translation' => array(
			'name' => tra('Machine Translation (by Google Translate)'),
			'description' => tra('Uses Google Translate to translate the content of wiki pages to other languages.'),
			'help' => 'Translating+Tiki+Content',
			'warning' => tra('Experimental. This feature is still under development.'),
			'type' => 'flag',
		),	
		'feature_trackers' => array(
			'name' => tra('Trackers'),
			'description' => tra('Database & form generator'),
			'help' => 'Trackers',
			'type' => 'flag',
		),
		'feature_forums' => array(
			'name' => tra('Forums'),
			'description' => tra('Online discussions on a variety of topics. Threaded or flat.'),
			'help' => 'Forums',
			'type' => 'flag',
		),
		'feature_file_galleries' => array(
			'name' => tra('File Gallery'),
			'description' => tra('Computer files, videos or software for downloading. With check-in & check-out (lock)'),
			'help' => 'File Gallery',
			'type' => 'flag',
		),
		'feature_articles' => array(
			'name' => tra('Articles'),
			'description' => tra('Articles can be used for date-specific news and announcements. You can configure articles to automatically publish and expire at specific times or to require that submissions be approved before becoming "live."'),
			'help' => 'Article',
			'type' => 'flag',
		),
		'feature_polls' => array(
			'name' => tra('Polls'),
			'description' => tra('Brief list of votable options; appears in module (left or right column)'),
			'help' => 'Poll',
			'type' => 'flag',
		),
		'feature_newsletters' => array(
			'name' => tra('Newletters'),
			'description' => tra('Content mailed to registered users.'),
			'help' => 'Newsletters',
			'type' => 'flag',
		),
		'feature_calendar' => array(
			'name' => tra('Calendar'),
			'description' => tra('Events calendar with public, private and group channels.'),
			'help' => 'Calendar',
			'type' => 'flag',
		),
		'feature_banners' => array(
			'name' => tra('Banners'),
			'description' => tra('Insert, track, and manage advertising banners.'),
			'help' => 'Banners',
			'type' => 'flag',
		),
		'feature_categories' => array(
			'name' => tra('Category'),
			'description' => tra('Global category system. Items of different types (wiki pages, articles, tracker items, etc) can be added to one or many categories. Categories can have permissions.'),
			'help' => 'Category',
			'type' => 'flag',
		),
		'feature_score' => array(
			'name' => tra('Score'),
			'description' => tra('Score is a game to motivate participants to increase their contribution by comparing to other users.'),
			'help' => 'Score',
			'type' => 'flag',
		),
		'feature_search' => array(
			'name' => tra('Search'),
			'description' => tra('Enables searching for content on the website.'),
			'help' => 'Search',
			'type' => 'flag',
		),
		'feature_freetags' => array(
			'name' => tra('Freetags'),
			'description' => tra('Allows to set tags on pages and various objects within the website and generate tag cloud navigation patterns.'),
			'help' => 'Tags',
			'type' => 'flag',
		),
		'feature_actionlog' => array(
			'name' => tra('Action Log'),
			'description' => tra('Allows to keep track of what users are doing and produce reports on a per-user or per-category basis.'),
			'help' => 'Action+Log',
			'type' => 'flag',
		),
		'feature_contribution' => array(
			'name' => tra('Contribution'),
			'description' => tra('Allows users to specify the type of contribution they are making while editing objects. The contributions are then displayed as color-coded in history and other reports.'),
			'help' => 'Contribution',
			'type' => 'flag',
		),
		'feature_multilingual' => array(
			'name' => tra('Multilingual'),
			'description' => tra('Enables internationalization features and multilingual support for then entire site.'),
			'help' => 'Internationalization',
			'type' => 'flag',
		),
		'feature_faqs' => array(
			'name' => tra('FAQ'),
			'description' => tra('Frequently asked questions and answers'),
			'help' => 'FAQ',
			'type' => 'flag',
		),
		'feature_surveys' => array(
			'name' => tra('Surveys'),
			'description' => tra('Questionnaire with multiple choice or open ended question'),
			'help' => 'Surveys',
			'type' => 'flag',
		),
		'feature_directory' => array(
			'name' => tra('Directory'),
			'description' => tra('User-submitted Web links'),
			'help' => 'Directory',
			'type' => 'flag',
		),
		'feature_quizzes' => array(
			'name' => tra('Quizzes '),
			'description' => tra('Timed questionnaire with recorded scores.'),
			'help' => 'Quizzes',
			'type' => 'flag',
		),
		'feature_featuredLinks' => array(
			'name' => tra('Featured links'),
			'description' => tra('Simple menu system which can optionally add an external web page in an iframe'),
			'help' => 'Featured+links',
			'type' => 'flag',
		),
		'feature_copyright' => array(
			'name' => tra('Copyright'),
			'description' => tra('The Copyright Management System (or ©MS) is a way of licensing your content'),
			'help' => 'Copyright',
			'type' => 'flag',
		),
		'feature_multimedia' => array(
			'name' => tra('Multimedia'),
			'description' => tra('The applet is designed to read MP3 or FLV file'),
			'help' => 'Multimedia',
			'type' => 'flag',
			'warning' => tra('Experimental. This feature is not actively maintained.'),
		),
		'feature_shoutbox' => array(
			'name' => tra('Shoutbox'),
			'description' => tra('Quick comment (graffiti) box. Like a group chat, but not in real time.'),
			'help' => 'Shoutbox',
			'type' => 'flag',
		),
		'feature_maps' => array(
			'name' => tra('Maps'),
			'description' => tra('Navigable, interactive maps with user-selectable layers'),
			'help' => 'Maps',
			'warning' => tra('Requires mapserver'),
			'type' => 'flag',
		),
		'feature_gmap' => array(
			'name' => tra('Google Maps'),
			'description' => tra('Use of Google Maps interactively inside Tiki'),
			'help' => 'GMap',
			'type' => 'flag',
		),
		'feature_live_support' => array(
			'name' => tra('Live support system'),
			'description' => tra('One-on-one chatting with customer'),
			'help' => 'Live+Support',
			'type' => 'flag',
		),
		'feature_tell_a_friend' => array(
			'name' => tra('Tell a Friend'),
			'description' => tra('Add a link "Email this page" in all the pages'),
			'help' => 'Tell+a+Friend',
			'type' => 'flag',
		),
		'feature_html_pages' => array(
			'name' => tra('HTML pages'),
			'description' => tra('Static and dynamic HTML content'),
			'help' => 'HTML+Pages',
			'warning' => tra('HTML can be used in wiki pages. This is a separate feature.'),
			'type' => 'flag',
		),
		'feature_contact' => array(
			'name' => tra('Contact Us'),
			'description' => tra('Basic form from visitor to admin'),
			'help' => 'Contact+us',
			'type' => 'flag',
		),
		'feature_minichat' => array(
			'name' => tra('Minichat'),
			'description' => tra('Real-time group text chatting'),
			'help' => 'Minichat',
			'type' => 'flag',
		),
		'feature_comments_moderation' => array(
			'name' => tra('Comments Moderation '),
			'description' => tra('An admin must validate a comment before it is visible'),
			'help' => 'Comments',
			'type' => 'flag',
		),
		'feature_comments_locking' => array(
			'name' => tra('Comments Locking'),
			'description' => tra('Comments can be closed (no new comments)'),
			'help' => 'Comments',
			'type' => 'flag',
		),
	);
}

?>
