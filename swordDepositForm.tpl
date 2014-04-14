{**
 * plugins/generic/article2Repo/swordDepositForm.tpl
 *
 * Deposit a published/scheduled article in repository using SWORD
 *
 *}

{if $publishedArticle->getSubmissionFileId()}

<div class="separator"></div>
<div id="ejme">
<h3>{translate key="plugins.generic.article2Repo.depositArticleTitle"}</h3>
{if $publishedArticle->getData("ejme_urn")}
<p>This article has been deposited in the {$repository} repository.</p>
<ul>
<li>View the <a href="{$repo_base_url}/{$publishedArticle->getData("ejme_urn")}">repository item page</a> (item may not have been published in the repository)</li>
<li>View the <a href="{$publishedArticle->getData("ejme_link")}">article from the repository</a> (login may be required)</li>
</ul>
{elseif !$deposit_point_url}
<p>No repository has been set please visit the Article2Repo settings page or ask your OJS administrator to.</p>
{else}

<table width="100%" class="data">
<form action="{url op="depositFileInRepo" path=$submission->getId()}" method="post">

<!--
<tr valign="top">
	<td colspan=2 class="label">
	  <input type="radio" name="ejme_dans" id="ejme_dans0" value="0"{if $ejme_dans == "" || $ejme_dans == "0"} checked="1"{/if}
	    onchange="evalEjmeFieldSettings();" /> {translate key="plugins.generic.ejme.ejme_dans0"}
	</td>
</tr>

-->
<tr valign="top">
	<td rowspan="2" width="20%" class="label">
	  <input type="checkbox" name="ejme_dans" id="ejme_dans" value="1"{if $ejme_dans == "1"} checked="1"{/if}
	    onclick="evalFieldSettings();" /> {translate key="plugins.generic.ejme.ejme_dans1"} {$repository|escape}
	</td><td width="80%"> </td>
</tr>	

<tr valign="top">
  <td width="80%" class="value">
	  <input type="radio" name="ejme_access" id="ejme_access0" value="0"{if $ejme_access == "" || $ejme_access == "0"} checked="1"{/if} /> {translate key="plugins.generic.ejme.ejme_access.open"} <br />
	  <input type="radio" name="ejme_access" id="ejme_access1" value="1"{if $ejme_access == "1"} checked="1"{/if} /> {translate key="plugins.generic.ejme.ejme_access.restricted"} <br />
		<br />
		{translate key="plugins.generic.ejme.ejme_date_available"}
		&nbsp;
		<input type="text" class="textField" name="ejme_date_available" id="ejme_date_available" value="{$ejme_date_available|escape}" maxlength="10" size="11" />
		{translate key="submission.date.yyyymmdd"} <br />
		<br />

	  <input type="checkbox" id="ejme_agree" name="ejme_agree" value="1"{if $ejme_agree == "1"} checked="1"{/if}
	    onchange="evalFieldSettings();" /> {translate key="plugins.generic.ejme.ejme_agree"} 
	  <a href="{$terms|escape}" target="_blank">{translate key="plugins.generic.ejme.ejme_agreement.title"}</a> 
	  <br />
	  
	  <input type="hidden" id="ejme_fileid" name="ejme_fileid" value="{if $submissionFile}{$submissionFile->getFileId()}{elseif $galley}{$galley->getFileId()}{else}0{/if}">
	  <input type="hidden" id="ejme_status" name="ejme_status" value="{$ejme_status|default:'0'}">
	  <input type="submit" id="ejme_upload_btn" name="ejme_upload_btn" value="{translate key="plugins.generic.ejme.ejme_upload"}" class="button" 
	    onclick="return setEjmeStatus('{translate key="plugins.generic.ejme.ejme_upload.confirm"}');" />
		<br />
		<br />
	</td>
</tr>
</form>
</table>
</div>
<script type="text/javascript" src="{$baseUrl}/plugins/generic/article2Repo/article2Repo.js"></script>
{/if}
{/if}


