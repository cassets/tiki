<?php
// (c) Copyright 2002-2012 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Services_Workspace_Utilities
{
	function validateCategory(array $path)
	{
		$root = $this->getWorkspaceRoot();

		while ($piece = array_shift($path)) {
			$root = $this->findCategory($root, $piece);

			if ($root && count($path) === 0) {
				throw new Services_Exception(__FUNCTION__, 400);
			}
		}
	}

	function validatePerspective($name)
	{
		$perspectivelib = TikiLib::lib('perspective');

		if ($perspectivelib->get_perspective_with_given_name($name)) {
			throw new Services_Exception(__FUNCTION__, 400);
		}
	}

	function validatePage($name)
	{
		$tikilib = TikiLib::lib('tiki');

		if ($tikilib->page_exists($name)) {
			throw new Services_Exception(__FUNCTION__, 400);
		}
	}

	function validateGroup($name)
	{
		$userlib = TikiLib::lib('user');

		if ($userlib->group_exists($name)) {
			throw new Services_Exception(__FUNCTION__, 400);
		}
	}

	function createCategory(array $path)
	{
		$root = $this->getWorkspaceRoot();
		$category = null;

		$categlib = TikiLib::lib('categ');
		while ($piece = array_shift($path)) {
			$category = $this->findCategory($root, $piece);

			if (! $category) {
				$category = $categlib->add_category($root, $piece, '');
			}

			$root = $category;
		}

		return $category;
	}

	function createPerspective($name)
	{
		$perspectivelib = TikiLib::lib('perspective');
		return $perspectivelib->replace_perspective(0, $name);
	}

	function createPage($name)
	{
		$tikilib = TikiLib::lib('tiki');
		if ($tikilib->create_page($name, 0, '', $tikilib->now, '')) {
			return $name;
		}
	}

	function createGroup($name)
	{
		$userlib = TikiLib::lib('user');

		if ($userlib->add_group($name)) {
			return $name;
		}
	}

	function initialize(array $data)
	{
		$perspectivelib = TikiLib::lib('perspective');
		$perspectivelib->set_preference($data['perspective'], 'category_jail', array($data['category']));
		$perspectivelib->set_preference($data['perspective'], 'namespace_default', array($data['page']));

		$categlib = TikiLib::lib('categ');
		$categlib->categorize_any('wiki page', $data['page'], $data['category']);
		$categlib->categorize_any('perspective', $data['perspective'], $data['category']);
	}

	private function getWorkspaceRoot()
	{
		global $prefs;

		$root = (int) $prefs['workspace_root_category'];

		if (! $root) {
			$categlib = TikiLib::lib('categ');
			$tikilib = TikiLib::lib('tiki');

			$root = $categlib->add_category(0, tr('Workspaces'), '');

			$tikilib->set_preference('workspace_root_category', $root);
		}

		if (! $root) {
			throw new Services_Exception(tr('Could not create workspace root'), 500);
		}

		return $root;
	}

	private function findCategory($parent, $name)
	{
		$table = TikiDb::get()->table('tiki_categories');

		return (int) $table->fetchOne('categId', array(
			'parentId' => (int) $parent,
			'name' => $name,
		));
	}

	function getTemplateList()
	{
		$list = $this->templates()->fetchAll(array('templateId', 'name'), array());
		$list = Perms::simpleFilter('workspacetemplate', 'templateId', 'workspace_instanciate', $list);
		
		return $list;
	}

	function addTemplate(array $data)
	{
		if (empty($data['name'])) {
			throw new Services_Exception;
		}

		return $this->templates()->insert(array(
			'name' => $data['name'],
		));
	}

	private function templates()
	{
		return TikiDb::get()->table('tiki_workspace_templates');
	}
}
