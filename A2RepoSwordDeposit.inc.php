<?php

/**
 * @file plugins/generic/ejme/EjmeSwordDeposit.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EjmeSwordDeposit
 * @ingroup sword
 *
 * @brief Class providing a SWORD deposit wrapper for a supplemental file
 */

//loosely derived from the OJSSwordDeposit class (./classes/sword/OJSSwordDeposit.inc.php)

require_once('./lib/pkp/lib/swordapp/swordappclient.php');
require_once('./lib/pkp/lib/swordapp/swordappentry.php');

class A2RepoSwordDeposit {
	/** @var $package SWORD deposit package */
	var $package;

	/** @var $outPath Complete path and directory name to use for package creation files */
	var $outPath;

	/** @var $journal */
	var $journal;

	 /** @var $article */
	 var $article;
  
  	/** @var $depositPoint */
  	var $depositPoint;
  
	/**
	 * Constructor.
	 * Create a SWORD deposit object for a supplemental file.
	 */
	function A2RepoSwordDeposit($journalId, &$article, &$depositPoint) {
		// Create a directory for deposit contents
		$this->outPath = tempnam('/tmp', 'sword');
		unlink($this->outPath);
		mkdir($this->outPath);
		mkdir($this->outPath . '/files');

		// Create a package
		$this->package = new EPrintsPackager(
			$this->outPath,
			'files',
			$this->outPath,
			'deposit.zip'
		);

		$journalDao =& DAORegistry::getDAO('JournalDAO');
		$this->journal =& $journalDao->getJournal($journalId);
		$this->depositPoint = $depositPoint;

		$this->article =& $article;
	}

	/**
	 * Register the SuppFile's metadata with the SWORD deposit.
	 */
	function setMetadata($ejme_urn=false) {

	    	//PackagerMetsSwap class
	    	$locale = $this->journal->getPrimaryLocale();
	    	$this->package->setCustodian($this->journal->getSetting('contactName'));
	    	$a = $this->article->getArticleTitle($locale);
		$this->package->setTitle(html_entity_decode($a, ENT_QUOTES, 'UTF-8'));
		$a = $this->article->getArticleAbstract($locale);
		
		$issueDao =& DAORegistry::getDAO('IssueDAO');
  		$issuesResultSet =& $issueDao->getIssues($this->journal->getId());
		#I was unaware we could have more than one.
                while (!$issuesResultSet->eof())
                {
                	$issue = $issuesResultSet->next();
		}
		if(isset($issue)){
		#	error_log("Volume: ".$issue->getVolume());
		#	error_log("Number: ".$issue->getNumber());
		#	error_log("Date: ".$issue->getYear());
			$this->package->setVolume(html_entity_decode($issue->getVolume(), ENT_QUOTES, 'UTF-8'));  
			$this->package->setNumber(html_entity_decode($issue->getNumber(), ENT_QUOTES, 'UTF-8'));  
			$this->package->setDate(html_entity_decode($issue->getYear(), ENT_QUOTES, 'UTF-8'));  
		}else{
			error_log("No issue set");
		}

		$this->package->setPublication(html_entity_decode($this->journal->getLocalizedTitle()));

		$this->package->setAbstract(html_entity_decode(strip_tags($a), ENT_QUOTES, 'UTF-8'));
		$this->package->setType($this->article->getType($locale));
		foreach($this->article->getAuthors($locale) as $author){
			$this->package->addEPCreator(array(
					"given" => $author->getFirstName().' '.$author->getMiddleName() . '', // make non-null
					"family" => $author->getLastName(),
#UCL discovery don't have creator-id	"id" => $author->getEmail()
					));
		}	
		if($ejme_urn){
#			error_log("~~~~~ I'm going to send this eprints id!!! : ".$ejme_urn);
			$this->package->setURN(html_entity_decode($ejme_urn, ENT_QUOTES, 'UTF-8'));
		}

		//EjmePackager extensions
		$a = $this->article->getSubject($locale);
		$this->package->setSubject(html_entity_decode($a, ENT_QUOTES, 'UTF-8'));  
		$this->package->setDateCreated($this->article->getDateSubmitted());                 
#		$a = $this->article->getSource(null);
#		$this->package->setSource(html_entity_decode($a[$locale], ENT_QUOTES, 'UTF-8'));   
		$a = $this->article->getSponsor($locale);
		$this->package->setSponsor(html_entity_decode($a, ENT_QUOTES, 'UTF-8'));  
#		$a = $this->article->getPublisher(null);
#		$this->package->setPublisher(html_entity_decode($a[$locale], ENT_QUOTES, 'UTF-8'));
		$a = $this->article->getLanguage();
		switch ($a) {
		  case 'eng':
			case 'en': $this->package->setLanguage('eng'); break;
			case 'fre':
			case 'fra':
			case 'fr': $this->package->setLanguage('fre/fra'); break;
			case 'dut':
			case 'nld':
			case 'nl': $this->package->setLanguage('dut/nld'); break;
			case 'ger':
			case 'deu':
			case 'de': $this->package->setLanguage('ger/deu'); break;
			case 'ita':
			case 'it': $this->package->setLanguage('ita'); break;
			case 'spa':
			case 'es':
			case 'sp': $this->package->setLanguage('spa'); break;
			default:   $this->package->setLanguage(''); break;
		}

	    //additional EJME metadata
	    switch ($this->article->getData('ejme_access')) {
		case '1': $this->package->setAccess('REQUEST_PERMISSION'); break;
		case '2': $this->package->setAccess('NO_ACCESS'); break;
		default : $this->package->setAccess('OPEN_ACCESS');
	    }
		$this->package->setDateAvailable($this->article->getData('ejme_date_available')); 
	if (isset($this->depositPoint['audience']) && $this->depositPoint['audience'] != '') {
		$this->package->setDiscipline('easy-discipline:' . $this->depositPoint['audience']);
	}
   }

	/**
	 *  $article->getFilePath() contains a bug caused by the fact that
	 *  ArticleFile::getType() is overridden by SuppFile::getType()
	 *
	 */
	function getFilePath (&$file) {
    		$journalId = $this->journal->getJournalId();
		return Config::getVar('files', 'files_dir') . '/journals/' . $journalId .
			'/articles/' . $file->getArticleId() . '/submission/original/' . $file->getFileName();
	}
  
	/**
	 * Add a file to a package. Used internally.
	 */
	function _addFile(&$file) {
		$targetFilename = $this->outPath . '/files/' . $file->getFilename();
		copy($this->getFilePath($file), $targetFilename);
		$this->package->addFile($file->getFilename(), $file->getFileType());
	}

	/**
	 * Add suppl.file to the deposit package.
	 */
	function addArticleFile() {
	  $this->_addFile($this->article->articleFile);
	}

	/**
	 * Build the package.
	 */
	function createPackage() {
		return $this->package->create();
	}

	/**
	 * Deposit the package.
	 * @param $url string SWORD deposit URL
	 * @param $username string SWORD deposit username (i.e. email address for DSPACE)
	 * @param $password string SWORD deposit password
	 */
	function deposit($url, $username, $password) {
	  try {
			$client = new SWORDAPPClient();
#			error_log("Trying to deposit: ".$this->outPath."/deposit.zip");
			$response = $client->deposit(  //url, un, pw, obo, fname, packaging, contenttype, noop, verbose
				$url, $username, $password,
				'',
				$this->outPath . '/deposit.zip',
#				'http://purl.org/net/sword-types/bagit',  //or: 'http://purl.org/net/sword-types/METSDSpaceSIP',
#				'application/zip', false, true
				'http://purl.org/net/sword/package/SimpleZip',
				'application/zip', false, true

			);

		} catch (Exception $e) {
		 	return FALSE;
		}
		return $response;  //return deposit object or errordocument
	}

	/**
	 * Clean up after a deposit, i.e. removing all created files.
	 */
	function cleanup() {
		import('lib.pkp.classes.file.FileManager');
		$fileManager = new FileManager();
		$fileManager->rmtree($this->outPath);
	}
}

?>
