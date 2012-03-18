<?php
// (c) Copyright 2002-2012 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_module_info()
{
	global $lang;

	$modlib = TikiLib::lib('mod');
	$cachelib = TikiLib::lib('cache');

	if (! $modules_options = $cachelib->getSerialized('module_list_for_plugin' . $lang)) {
		$all_modules = $modlib->get_all_modules();
		$all_modules_info = array_combine($all_modules, array_map(array( $modlib, 'get_module_info' ), $all_modules));
		uasort($all_modules_info, 'compare_names');
		$modules_options = array();
		foreach ($all_modules_info as $module => $module_info) {
			$modules_options[] = array('text' => $module_info['name'] . ' (' . $module . ')', 'value' => $module);
		}

		$cachelib->cacheItem('module_list_for_plugin' . $lang, serialize($modules_options));
	}

	return array(
		'name' => tra('Insert Module'),
		'documentation' => 'PluginModule',
		'description' => tra('Display a module'),
		'prefs' => array( 'wikiplugin_module' ),
		'validate' => 'all',
		'icon' => 'img/icons/module.png',
		'extraparams' =>true,
		'tags' => array( 'basic' ),
		'params' => array(
			'module' => array(
				'required' => true,
				'name' => tra('Module Name'),
				'description' => tra('Module name as known in Tiki'),
				'default' => '',
				'options' => $modules_options,
			),
			'float' => array(
				'required' => false,
				'name' => tra('Float'),
				'description' => tra('Align the module to the left or right on the page allowing other elements to align against it'),
				'default' => 'nofloat',
				'advanced' => true,
				'options' => array(
					array('text' => 'No Float', 'value' => ''), 
					array('text' => tra('Left'), 'value' => 'left'), 
					array('text' => tra('Right'), 'value' => 'right')
				)
			),
			'decoration' => array(
				'required' => false,
				'name' => tra('Decoration'),
				'description' => tra('Show box decorations (default is to show them)'),
				'advanced' => true,
				'options' => array(
					array('text' => '', 'value' => ''), 
					array('text' => tra('Yes'), 'value' => '1'), 
					array('text' => tra('No'), 'value' => '0'), 
				)
			),
			'flip' => array(
				'required' => false,
				'name' => tra('Flip'),
				'description' => tra('Add ability to show/hide the content of the module (default is the site admin setting for modules)'),
				'options' => array(
					array('text' => '', 'value' => ''), 
					array('text' => tra('Yes'), 'value' => '1'), 
					array('text' => tra('No'), 'value' => '0'), 
				),
				'advanced' => true,
			),
			'max' => array(
				'required' => false,
				'name' => tra('Max'),
				'description' => tra('Number of rows (default: 10)'),
				'default' => 10,
				'advanced' => true,
			),
			'np' => array(
				'required' => false,
				'name' => tra('Parse'),
				'description' => tra('Parse wiki syntax.') . ' ' . tra('Default:') . ' ' . tra('No'),
				'default' => '1',
				'options' => array(
					array('text' => '', 'value' => ''), 
					array('text' => tra('Yes'), 'value' => '0'), 
					array('text' => tra('No'), 'value' => '1'), 
				),
				'advanced' => true,
			),
			'notitle' =>array(
				'required' => false,
				'name' => tra('Title'),
				'description' => tra('Show/hide module title (default is to show the title)'),
				'options' => array(
					array('text' => '', 'value' => ''), 
					array('text' => tra('Show title'), 'value' => 'n'), 
					array('text' => tra('Hide title'), 'value' => 'y')
				)
			),
			'module_style' => array(
				'required' => false,
				'name' => tra('Module Style'),
				'description' => tra('Inline CSS for the containing DIV element, e.g. "max-width:80%"'),
				'default' => '',
				'advanced' => true,
			),
		)
	);
}

function wikiplugin_module($data, $params)
{
	static $instance = 0;

	$out = '';
	
	extract($params, EXTR_SKIP);

	if (!isset($float)) {
		$float = 'nofloat';
	}

    if (!isset($max)) {
        if (!isset($rows)) {
            $max = 10; // default value
        } else $max=$rows; // rows=> used instead of max=> ?
    }

	if (!isset($np)) {
		$np = '1';
	}

	if (!isset($module) or !$module) {
		$out = '<form class="box" id="modulebox">';

		$out .= '<br /><select name="choose">';
		$out .= '<option value="">' . tra('Please choose a module'). '</option>';
		$out .= '<option value="" style="background-color:#bebebe;">' . tra('to be used as argument'). '</option>';
		$out .= '<option value="" style="background-color:#bebebe;">{MODULE(module=>name_of_module)}</option>';
		$handle = opendir('modules');

		while ($file = readdir($handle)) {
			if ((substr($file, 0, 4) == "mod-") and (substr($file, -4, 4) == ".php")) {
				$mod = substr(substr(basename($file), 4), 0, -4);

				$out .= "<option value=\"$mod\">$mod</option>";
			}
		}

		$out .= '</select></form>';
	} else {

		$instance++;
		if (empty($moduleId)) {
			$moduleId = 'wikiplugin_' . $instance;
		}

		$module_reference = array(
			'moduleId' => $moduleId,
			'name' => $module,
			'params' => $params,
			'rows' => $max,
			'position' => null,
			'ord' => null,
			'cache_time'=> 0,
		);

		if (!empty($module_style)) {
			$module_reference['module_style'] = $module_style;
		}

		global $modlib; require_once 'lib/modules/modlib.php';
		$out = $modlib->execute_module($module_reference);
	}

	if ($out) {
		if ($float != 'nofloat') {
			$data = "<div style='float: $float;'>";
		} else {
			$data = "<div>";
		}	
		if ($np) {
  		$data.= "~np~$out~/np~</div>";
		} else {
			$data.= "$out</div>";
		}
	} else {
        // Display error message
		$data = "<div class=\"highlight\">" . tra("Sorry, no such module"). "<br /><b>$module</b></div>" . $data;
	}

	return $data;
}
