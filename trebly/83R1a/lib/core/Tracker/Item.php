<?php
// (c) Copyright 2002-2011 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: Item.php 39302 2011-12-26 14:16:51Z arildb $

class Tracker_Item
{
	private $info;
	private $definition;

	private $owner;
	private $ownerGroup;
	private $perms;

	public static function fromInfo($info)
	{
		$obj = new self;
		$obj->info = $info;
		$obj->definition = Tracker_Definition::get($info['trackerId']);
		$obj->initialize();

		return $obj;
	}

	public static function newItem($trackerId)
	{
		$obj = new self;
		$obj->info = array();
		$obj->definition = Tracker_Definition::get($trackerId);
		$obj->initialize();

		return $obj;
	}

	private function __construct()
	{
	}

	function canView()
	{
		if ($this->isNew()) {
			return true;
		}

		if ($this->canModifyFromSpecialPermissions()) {
			return true;
		}

		$status = $this->info['status'];

		if ($status == 'c') {
			return $this->perms->view_trackers_closed;
		} elseif ($status == 'p') {
			return $this->perms->view_trackers_pending;
		} else {
			return $this->perms->view_trackers;
		}
	}

	function canModify()
	{
		if ($this->isNew()) {
			return $this->perms->create_tracker_items;
		}

		if ($this->canModifyFromSpecialPermissions()) {
			return true;
		}

		$status = $this->info['status'];

		if ($status == 'c') {
			return $this->perms->modify_tracker_items_closed;
		} elseif ($status == 'p') {
			return $this->perms->modify_tracker_items_pending;
		} else {
			return $this->perms->modify_tracker_items;
		}
	}

	function canRemove()
	{
		if ($this->isNew()) {
			return false;
		}

		$status = $this->info['status'];

		if ($status == 'c') {
			return $this->perms->remove_tracker_items_closed;
		} elseif ($status == 'p') {
			return $this->perms->remove_tracker_items_pending;
		} else {
			return $this->perms->remove_tracker_items;
		}
	}

	private function canModifyFromSpecialPermissions()
	{
		global $user;
		if ($user && $this->owner && $user === $this->owner) {
			return true;
		}

		if ($this->ownerGroup && in_array($this->ownerGroup, $this->perms->getGroups())) {
			return true;
		}

		return false;
	}

	private function initialize()
	{
		$this->owner = $this->getItemOwner();
		$this->ownerGroup = $this->getItemGroupOwner();

		$this->perms = $this->getItemPermissions();

		if (! $this->perms) {
			$this->perms = $this->getTrackerPermissions();
		}
	}

	private function getTrackerPermissions()
	{
		if($this->definition === false)
			return null;
		$trackerId = $this->definition->getConfiguration('trackerId');
		return Perms::get('tracker', $trackerId);
	}

	private function getItemPermissions()
	{
		if (! $this->isNew()) {
			$itemId = $this->info['itemId'];

			$perms = Perms::get('trackeritem', $itemId);
			$resolver = $perms->getResolver();
			if (method_exists($resolver, 'from') && $resolver->from() != '') {
				// Item permissions are valid if they are assigned directly to the object or category, otherwise
				// tracker permissions are better than global ones.
				return $perms;
			}
		}
	}

	private function getItemOwner()
	{
		global $prefs;

		if ($prefs['userTracker'] != 'y') {
			return null;
		}

		if ($this->definition->getConfiguration('writerCanModify') != 'y') {
			return null;
		}

		$userField = $this->definition->getUserField();
		if ($userField) {
			return $this->getValue($userField);
		}
	}

	private function getItemGroupOwner()
	{
		global $prefs;

		if ($prefs['groupTracker'] != 'y') {
			return null;
		}

		if ($this->definition->getConfiguration('writerGroupCanModify') != 'y') {
			return null;
		}

		$groupField = $this->definition->getWriterGroupField();
		if ($groupField) {
			return $this->getValue($groupField);
		}
	}

	function canViewField($fieldId)
	{
		// Nothing stops the tracker administrator from doing anything
		if ($this->perms->admin_trackers) {
			return true;
		}

		// Viewing the item is required to view the field (safety)
		if (! $this->canView()) {
			return false;
		}

		$field = $this->definition->getField($fieldId);
		
		if (! $field) {
			return false;
		}

		$isHidden = $field['isHidden'];
		$visibleBy = $field['visibleBy'];

		if ($isHidden == 'c' && $this->canModifyFromSpecialPermissions()) {
			// Creator or creator group check when field can be modified by creator only
			return true;
		} elseif ($isHidden == 'y') {
			// Visible by administrator only
			return false;
		} else {
			// Permission based on visibleBy apply
			return $this->isMemberOfGroups($visibleBy);
		}
	}

	function canModifyField($fieldId)
	{
		// Nothing stops the tracker administrator from doing anything
		if ($this->perms->admin_trackers) {
			return true;
		}

		// Modify the item is required to modify the field (safety)
		if (! $this->canModify()) {
			return false;
		}

		// Cannot modify a field you are not supposed to see
		// Modify without view means insert-only
		if (! $this->isNew() && ! $this->canViewField($fieldId)) {
			return false;
		}

		$field = $this->definition->getField($fieldId);
		
		if (! $field) {
			return false;
		}

		$isHidden = $field['isHidden'];
		$editableBy = $field['editableBy'];

		if ($isHidden == 'c') {
			// Creator or creator group check when field can be modified by creator only
			return $this->canModifyFromSpecialPermissions();
		} elseif ($isHidden == 'p') {
			// Editable by administrator only
			return false;
		} else {
			// Permission based on editableBy apply
			return $this->isMemberOfGroups($editableBy);
		}
	}

	private function isMemberOfGroups($groups)
	{
		// Nothing specified means everyone
		if (empty($groups)) {
			return true;
		}

		$commonGroups = array_intersect($groups, $this->perms->getGroups());
		return count($commonGroups) != 0;
	}

	private function getValue($fieldId)
	{
		if (isset($this->info[$fieldId])) {
			return $this->info[$fieldId];
		}
	}

	private function isNew()
	{
		return empty($this->info);
	}

	/**
	 * Getter method for the permissions of this
	 * item.
	 * 
	 * @param string $permName
	 * @return bool|null
	 */
	public function getPerm($permName)
	{
		return isset($this->perms->$permName) ? $this->perms->$permName : null;
	}
}
