<?php

function prefs_sitelogo_list() {
	return array(
		'sitelogo_src' => array(
			'name' => tra('Logo source (image path)'),
			'type' => 'text',
		),
		'sitelogo_bgcolor' => array(
			'name' => tra('Logo background color'),
			'hint' => tra('Examples:') . ' ' .  '1) silver - 2) #fff',
			'type' => 'text',
			'size' => '15',
		),
		'sitelogo_bgstyle' => array(
			'name' => tra('Logo background style'),
			'hint' => tra('Examples:') . ' ' . '1) silver url(myStyle/img.gif) repeat - 2) padding: 30px 10px; background: #fff',
			'type' => 'text',
			'size' => '20',
		),
		'sitelogo_align' => array(
			'name' => tra('Logo alignment'),
			'type' => 'list',
			'options' => array(
				'left' => 'Left',
				'center' => 'Center',
				'right' => 'Right',
			),
		),
		'sitelogo_title' => array(
			'name' => tra('Logo title (on mouse over)'),
			'type' => 'text',
			'size' => '50',
		),
		'sitelogo_alt' => array(
			'name' => tra('Alt. description (e.g. for text browsers)'),
			'type' => 'text',
			'size' => '50',
		),
	);	
}
	