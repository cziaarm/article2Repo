{**
 * plugins/generic/ejme/depositPointForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * EJME plugin settings
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.ejme.depositForm"}
{include file="common/header.tpl"}
{/strip}
<div id="depositPointSettings">

<form method="post" action="{plugin_url path="settings"}">
{include file="common/formErrors.tpl"}

<table width="100%" class="data">
	<tr valign="top">
		<td class="label" width="20%"><label for="swordUrl">{translate key="plugins.generic.ejme.depositName"}</label></td>
		<td class="value" width="45%"><input type="text" name="depositPoint[name]" id="swordUrl" value="{$depositPoint.name|escape}" size="20" maxlength="20" /></td>
		<td width="35%">{translate key="plugins.generic.ejme.depositName.description"}</td></tr>
	<tr valign="top">
		<td class="label" width="20%"><label for="swordUrl">{translate key="plugins.generic.ejme.depositUrl"}</label></td>
		<td class="value" width="45%"><input type="text" name="depositPoint[url]" id="swordUrl" value="{$depositPoint.url|escape}" size="50" maxlength="90" /></td>
		<td width="35%">{translate key="plugins.generic.ejme.depositUrl.description"}</td></tr>
	<tr valign="top">
		<td class="label" width="20%"><label for="swordUsername">{translate key="user.username"}</label></td>
		<td class="value" width="45%"><input type="text" name="depositPoint[username]" id="swordUsername" value="{$depositPoint.username|escape}" size="20" maxlength="90" /></td>
		<td width="35%"></td>
	</tr>
	<tr valign="top">
		<td class="label" width="20%"><label for="swordPassword">{translate key="user.password"}</label></td>
		<td class="value" width="45%"><input type="password" name="depositPoint[password]" id="swordPassword" value="{$depositPoint.password|escape}" size="20" maxlength="90" /></td>
		<td width="35%"></td>
	</tr>
<!--
	<tr valign="top">
		<td class="label" width="20%"><label for="easyDiscipline">{translate key="plugins.generic.ejme.ejme_discipline"}</label></td>
		<td class="value" width="45%">
			<select name="depositPoint[audience]" id="easyDiscipline">
				<option value="">--select--</option>
		</td>
		<td width="35%">{translate key="plugins.generic.ejme.ejme_discipline.description"}</td></tr>
-->
	<tr valign="top">
		<td class="label" width="20%"><label for="swordUrl">{translate key="plugins.generic.ejme.terms"}</label></td>
		<td class="value" width="45%"><input type="text" name="depositPoint[terms]" id="swordUrl" value="{$depositPoint.terms|escape}" size="50" maxlength="90" /></td>
		<td width="35%">{translate key="plugins.generic.ejme.terms.description"}</td></tr>
</table>
<br/>
<input type="submit" name="save" class="button defaultButton" value="{translate key="common.save"}"/> 
<input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.back();"/>
</form>
</div><!-- depositPointSettings -->

{include file="common/footer.tpl"}
