<?php

/**
 * @file plugins/generic/ejme/DepositPointForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DepositPointForm
 * @ingroup plugins_generic_ejme
 *
 * @brief Form for journal managers to create/modify EJME sword deposit point
 */

// $Id$


import('lib.pkp.classes.form.Form');

class DepositPointForm extends Form {

	/** @var $journalId int */
	var $journalId;

	/** @var $plugin object */
	var $plugin;

	/**
	 * Constructor
	 * @param $plugin object
	 * @param $journalId int
	 */
	function DepositPointForm(&$plugin, $journalId) {
		$this->journalId = $journalId;
		$this->plugin =& $plugin;

		parent::Form($plugin->getTemplatePath() . 'depositPointForm.tpl');
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$journalId = $this->journalId;
		$plugin =& $this->plugin;
		$depositPoint = $plugin->getSetting($journalId, 'depositPoint');
		$this->setData('depositPoint', $depositPoint);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('depositPoint'));
	}

	function display() {
		$templateMgr =& TemplateManager::getManager();
		parent::display();
	}

	/**
	 * Save settings. 
	 */
	function execute() {
		$plugin =& $this->plugin;
		$journalId = $this->journalId;
		$depositPoint = $this->getData('depositPoint');
		$plugin->updateSetting($journalId, 'depositPoint', $depositPoint);
	}
}

?>
