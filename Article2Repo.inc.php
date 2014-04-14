<?php

import('plugins.GenericPlugin');

class Article2Repo extends GenericPlugin {

	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {

				//additional EJME fields to store/retrieve
				$this->additionalFields = array('ejme_dans','ejme_access','ejme_date_available','ejme_agree','ejme_urn','ejme_link','ejme_status');

				//LOAD FORM
				HookRegistry::register('SubmissionEditHandler::depositFileInRepo', array(&$this, 'depositFileInRepo'));
				HookRegistry::register('Templates::sectionEditor::Submission:Scheduling::SwordDepositForm', array($this, 'swordDepositForm'));

				//SAVE FORM
			        HookRegistry::register('articledao::getAdditionalFieldNames', array(&$this, 'getAdditionalFieldNames'));
			        //OTHER FUNCTIONS
			        HookRegistry::register('TemplateManager::display', array(&$this, 'displayTemplate'));
				HookRegistry::register('ArticleDAO::_returnArticleFromRow', array(&$this, 'articleFromRow'));
			        HookRegistry::register('ArticleHandler::viewFile', array(&$this, 'viewFile'));


			}
 
			return true;
		}
		return false;
	}

	function viewFile($hookName, $params){
		$article = $params[0];
		if($article->getData("ejme_link")){
			PKPRequest::redirectUrl($article->getData("ejme_link"));
			exit;
		}
	}
	function articleFromRow($hookName, $params){

		$templateMgr =& TemplateManager::getManager();

		$journal =& Request::getJournal();
		$journalId = $journal->getId();
		$depositPoint = $this->getSetting($journalId, 'depositPoint');
		if(isset($templateMgr->_tpl_vars['publishedArticle']))
			$publishedArticle = $templateMgr->_tpl_vars['publishedArticle'];

		if (isset($depositPoint['name'])){
			$templateMgr->_tpl_vars['repository'] = $depositPoint['name'];
			$templateMgr->_tpl_vars['deposit_point_url'] = $depositPoint['url'];
			$templateMgr->_tpl_vars['repo_base_url'] = preg_replace("#^(http://[^/]+)/.*$#",'$1', $depositPoint['url']);
		}


	}
	function depositFileInRepo($hookName, $params){
		$something = $params[0];
		$article_id = $params[1];
		$request = $params[2];
		$this->current_article = $article_id;

		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$article = $articleDao->getArticle($article_id);
		$articleFileDao =& DAORegistry::getDAO('ArticleFileDAO');
		if ($article->getSubmissionFileId() != null) {
			$articleFile = $articleFileDao->getArticleFile($article->getSubmissionFileId());
#			error_log("##### Article data: ".print_r($article->_data,true));
			## We will make it update... oh yes we will!
			$ejme_urn = $article->getData("ejme_urn");

			//do the upload
			//we are here at last...
			$article->articleFile = $articleFile;
			$response = $this->depositArticleFile($article, $ejme_urn);
			$ejme_urn = '';
			$ejme_link = '';
			if ($response !== FALSE) {
			  // $response can also be the SWORDAppErrorDocument!
			  if (isset($response->sac_id) && is_array($response->sac_links)) {

					$ejme_urn = (string)$response->sac_id;
					$ejme_link = $response->sac_links[0];
#					$article->setData('ejme_dans', EJME_DANS_REMOTE);
#					error_log("Response is ok? $ejme_urn, ".print_r($ejme_link, true));
				}else{
					error_log("Response from repository had no sac_id and sac_links was not an array");
				}
			}else{
				error_log("Response from repository was FALSE");
			}
			$article->setData('ejme_urn', $ejme_urn);
			$article->setData('ejme_link', $ejme_link);
#			$article->setData('ejme_status', EJME_STATUS_NOACTION);  //reset upload request
			$articleDao =& DAORegistry::getDAO('ArticleDAO');
			$articleDao->updateArticle($article);
#			error_log("EJME_URN: ".$article->getData('ejme_urn'));


		}
		$request->redirect(null, null, 'submissionEditing', array($article_id), null, 'scheduling');
		return false;
	}

	function swordDepositForm($hookName, $params){
	
#		error_log("***** A2Repo swordDepositForm ******");

		$journal =& Request::getJournal();
		$journalId = $journal->getId();
		$depositPoint = $this->getSetting($journalId, 'depositPoint');
		
		$templateMgr =& $params[1];
		$publishedArticle = $templateMgr->_tpl_vars['publishedArticle'];
		
		$articleId = $publishedArticle->getData("id");
		$ejeme_urn = $publishedArticle->getData("ejme_urn");
		$ejme_link = $publishedArticle->getData("ejme_link");

		$templateMgr->_tpl_vars['repository'] = '(?)';
		$templateMgr->_tpl_vars['terms'] = 'http://';
		if (isset($depositPoint['name'])){
			 $templateMgr->_tpl_vars['repository'] = $depositPoint['name'];
			 $templateMgr->_tpl_vars['repo_base_url'] = preg_replace("#^(http://[^/]+)/.*$#",'$1', $depositPoint['url']);
		}
		if (isset($depositPoint['terms'])) $templateMgr->_tpl_vars['terms'] = $depositPoint['terms'];

		$output =& $params[2];
		$output .= $templateMgr->fetch($this->getTemplatePath() . 'swordDepositForm.tpl');

		return false;

	}
	function truncate_files($journalId, $articleId, $files_dir=false){

		if(!$files_dir)
			$files_dir = Config::getVar('files', 'files_dir') . '/journals/' . $journalId .'/articles/' . $articleId;
		if (is_dir($files_dir)) {
		    if ($dh = opendir($files_dir)) {
		        while (($file = readdir($dh)) !== false) {
				if($file == "." || $file =="..") continue;
				if(filetype($files_dir .'/'. $file) == "dir"){
					$this->truncate_files(false,false,$files_dir.'/'.$file);
				}else{
					$fh = fopen($files_dir.'/'.$file,"w");
					ftruncate($fh, 0);
					error_log("Truncated $files_dir/$file after sword deposit");
					fclose($fh);
				}
					
		        }
		        closedir($dh);
		    }	
		}
	}
	function getAdditionalFieldNames($hookName, $params) {
#		error_log("***** A2Repo getAdditionalFieldNames ******");

		$fields =& $params[1];
		foreach ($this->additionalFields as $a) {
			$fields[] = $a;
		}
	
		return false;
	}

	function depositArticleFile ($article,$ejme_urn) {
		
		$journal =& Request::getJournal();
		$journalId = $journal?$journal->getId():0;
		$dp = $this->getSetting($journalId, 'depositPoint');
#		error_log("depositPoint: ".join(",", $dp));
		//$dp is array with hashkeys 'name', 'url', 'username', 'password', 'audience', 'terms'
		
		if (!$this->checkDepositPoint($dp)) return false;
    
#		$this->import('EjmePackager');
		$this->import('EPrintsPackager');
		$this->import('A2RepoSwordDeposit');
		#error_log("~~~~~~~~~ in depositArticleFile - eprintid:".$ejme_urn);
		$deposit = new A2RepoSwordDeposit($journalId, $article, $dp);
		$deposit->setMetadata($ejme_urn);
		$deposit->addArticleFile();
		$deposit->createPackage();

		$response = $deposit->deposit($dp['url'], $dp['username'], $dp['password']);
		if ($response !== false) {
			$deposit->cleanup();
			$this->truncate_files($journalId, $article->getId());
			return $response;
		}
		return false;
	}

	function checkDepositPoint ($depositPoint) {
	  $a =& $depositPoint;
	  if (!is_array($a)) return false;
		foreach ($a as $v) {
			if (!strlen(trim($v))) return false;
		}
		return true;
	}


  	/**
   	* TemplateManager hook to replace regular templates
   	*/
  	function displayTemplate($hookName, $params) {

#		error_log("############### DISPLAY TEMPLATE #################");
#		error_log("params[1]: ".$params[1]);
		if ($params[1] == 'article/article.tpl')	{
			$params[1] = $this->getTemplatePath() . 'article.tpl';
		}

		if ($params[1] == 'sectionEditor/submission/scheduling.tpl'){
			$params[1] = $this->getTemplatePath() . 'scheduling.tpl';
		}
#		if ($params[1] == 'issue/viewPage.tpl')	{
#			$params[1] = $this->getTemplatePath() . 'viewPage.tpl';
#		}

		return false;
	}

	function getName() {
		return 'Article2Repo';
	}

	function getDisplayName() {
		return 'Article 2 Repo Plugin';
	}

	function getDescription() {
		return 'A simple SWORD deposit plugin (based on EJME) that allows editors to deposit an article to a repository once it has been scheduled for publication in OJS. A patch must be applied after installation in order for this plugin to work. Details of this can be found in [ojs-install-dir]/plugins/generic/article2Repo/README';
	}

  /**
   * Display verbs for the management interface.
   */
  function getManagementVerbs() {
    $verbs = array();
    if ($this->getEnabled()) {
      $verbs[] = array(
        'disable',
        Locale::translate('manager.plugins.disable')
      );
      $verbs[] = array(
        'settings',
        Locale::translate('plugins.generic.ejme.settings')
      );
    } else {
      $verbs[] = array(
        'enable',
        Locale::translate('manager.plugins.enable')
      );
    }
    return $verbs;
  }


  /*
   * Execute a management verb on this plugin
   * @param $verb string
   * @param $args array
   * @param $message string Location for the plugin to put a result msg
   * @return boolean
   */
  function manage($verb, $args, &$message) {
    $returner = true;
    $journal =& Request::getJournal();
    $this->addLocaleData();

    switch ($verb) {
      case 'settings':  //establish sword deposit point
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));

				$this->import('DepositPointForm');
				$form = new DepositPointForm($this, $journal->getId());

				if (Request::getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						Request::redirect(null, null, null, array('generic'));
					} else {
						$form->display();
					}
				} else {
					$form->initData();
					$form->display();
				}
				break;
      case 'enable':
        $this->updateSetting($journal->getId(), 'enabled', true);
        $returner = false;
        break;
      case 'disable':
        $this->updateSetting($journal->getId(), 'enabled', false);
        $returner = false;
        break;
    }
    return $returner;
  }

}

?>
