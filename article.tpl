{**
 * article.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Article View.
 *}
{include file="article/header.tpl"}

{if $galley}
	{if $galley->isHTMLGalley()}
		{$galley->getHTMLContents()}
	{elseif $galley->isPdfGalley() && $article->getData('ejme_link')}
			{assign var=pdfUrl value=$article->getData('ejme_link')}
{*			{url|assign:"pdfUrl" op="viewFile" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal) escape=false} *}

		{translate|assign:"noPluginText" key='article.pdf.pluginMissing'}
		<script type="text/javascript"><!--{literal}
			$(document).ready(function(){
				if ($.browser.webkit) { // PDFObject does not correctly work with safari's built-in PDF viewer
					var embedCode = "<object id='pdfObject' type='application/pdf' data='{/literal}{$pdfUrl|escape:'javascript'}{literal}' width='99%' height='99%'><div id='pluginMissing'>{/literal}{$noPluginText|escape:'javascript'}{literal}</div></object>";
					$("#articlePdf").html(embedCode);
					if($("#pluginMissing").is(":hidden")) {
						$('#fullscreenShow').show();
						$("#articlePdf").resizable({ containment: 'parent', handles: 'se' });
					} else { // Chrome Mac hides the embed object, obscuring the text.  Reinsert.
						$("#articlePdf").html('{/literal}{$noPluginText|escape:"javascript"}{literal}');
					}
				} else {
					var success = new PDFObject({ url: "{/literal}{$pdfUrl|escape:'javascript'}{literal}" }).embed("articlePdf");
					if (success) {
						// PDF was embedded; enbale fullscreen mode and the resizable widget
						$('#fullscreenShow').show();
						$("#articlePdfResizer").resizable({ containment: 'parent', handles: 'se' });
					}
				}
			});
		{/literal}
		// -->
		</script>
		<div id="articlePdfResizer">
			<div id="articlePdf" class="ui-widget-content">
				{translate key="article.pdf.pluginMissing"}
			</div>
		</div>
		<p>
			{* The target="_parent" is for the sake of iphones, which present scroll problems otherwise. *}
			{if $article->getData('ejme_link')}
				<a class="action" target="_parent" href="{$article->getData("ejme_link")|escape}">{translate key="article.pdf.download"}</a>
			{else}
<!--				<a class="action" target="_parent" href="{url op="download" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal)}">{translate key="article.pdf.download"}</a>  -->

			{/if}

			<a class="action" href="#" id="fullscreenShow">{translate key="common.fullscreen"}</a>
			<a class="action" href="#" id="fullscreenHide">{translate key="common.fullscreenOff"}</a>
		</p>
	{else}
			<!-- no local copies to be accessed -->
			<p>Sorry, no full text available here.</p>
		
	{/if}
{else}

	<div id="topBar">
		{assign var=galleys value=$article->getLocalizedGalleys()}
		{if $galleys && $subscriptionRequired && $showGalleyLinks}
			<div id="accessKey">
				<img src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
				{translate key="reader.openAccess"}&nbsp;
				<img src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />
				{if $purchaseArticleEnabled}
					{translate key="reader.subscriptionOrFeeAccess"}
				{else}
					{translate key="reader.subscriptionAccess"}
				{/if}
			</div>
		{/if}
	</div>
	{if $coverPagePath}
		<div id="articleCoverImage"><img src="{$coverPagePath|escape}{$coverPageFileName|escape}"{if $coverPageAltText != ''} alt="{$coverPageAltText|escape}"{else} alt="{translate key="article.coverPage.altText"}"{/if}{if $width} width="{$width|escape}"{/if}{if $height} height="{$height|escape}"{/if}/>
		</div>
	{/if}
	{call_hook name="Templates::Article::Article::ArticleCoverImage"}
	<div id="articleTitle"><h3>{$article->getLocalizedTitle()|strip_unsafe_html}</h3></div>
	<div id="authorString"><em>{$article->getAuthorString()|escape}</em></div>
	<br />
	{if $article->getLocalizedAbstract()}
		<div id="articleAbstract">
		<h4>{translate key="article.abstract"}</h4>
		<br />
		<div id="articleAbstractBody">{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}</div>
		<br />
		</div>
	{/if}

	{if $citationFactory->getCount()}
		<h4>{translate key="submission.citations"}</h4>
		<br />
		<div>
			{iterate from=citationFactory item=citation}
				<p>{$citation->getRawCitation()|strip_unsafe_html}</p>
			{/iterate}
		</div>
		<br />
	{/if}

	{if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain)}
		{assign var=hasAccess value=1}
	{else}
		{assign var=hasAccess value=0}
	{/if}

	{if $galleys}
		{if $article->getData('ejme_link')}
			{translate key="reader.fullText"}
			{if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
				{foreach from=$article->getLocalizedGalleys() item=galley name=galleyList}
					<a href="{$article->getData('ejme_link')|escape}" class="file" target="_parent">{$galley->getGalleyLabel()|escape}</a>
	<!--
					<a href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" class="file" target="_parent">{$galley->getGalleyLabel()|escape}</a> -->


					{if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}
						{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isPdfGalley()}
							<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
						{else}
							<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />
						{/if}
					{/if}
				{/foreach}
				{if $subscriptionRequired && $showGalleyLinks && !$restrictOnlyPdf}
					{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}
						<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
					{else}
						<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />
					{/if}
				{/if}
			{else}
				&nbsp;<a href="{url page="about" op="subscriptions"}" target="_parent">{translate key="reader.subscribersOnly"}</a>
			{/if}
		{else}
			<!-- no local copies to be accessed -->
			<p>Sorry, no full text available here.</p>

		{/if}
	{/if}
{/if}


{include file="article/comments.tpl"}

<div class="separator"></div>
{if $article->getData('ejme_link')}

<div id="articleTitle"><h3>Downloads in last 12 months for "{$article->getLocalizedTitle()|strip_unsafe_html}" from {$repository}</h3></div>

<img src="{$repo_base_url}/cgi/irstats.cgi?page=get_view_raw&amp;IRS_epchoice=EPrint&amp;eprint={$article->getData('ejme_urn')}&amp;IRS_datechoice=period&amp;period=-12m&amp;start_day=1&amp;start_month=1&amp;start_year=2008&amp;end_day=31&amp;end_month=1&amp;end_year=2008&amp;view=MonthlyDownloadsGraph"/>
{else}
<script type="text/javascript" >
	//lets scrape it out of the abstract....
	var abody_elems = document.getElementById("articleAbstractBody").children;
	var title = "{$article->getLocalizedTitle()|strip_unsafe_html}";
	var repository = "{$repository}";
	var repo_base_url = "{$repo_base_url}";
</script>

<script type="text/javascript" src="{$baseUrl}/plugins/generic/article2Repo/article2Repo_article.js"></script>
{/if}

{include file="article/footer.tpl"}

