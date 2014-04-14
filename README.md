ojs-article2Repo
================
Article 2 Repo Plugin for PKP-OJS software


Plugin for OJS - The Article2Repo plugin deposits full-text PDFs to a repository from OJS. Based on the EJME plugin which deposits data from OJS to repos

###### OJS bit #####

based on Ejme plugin

Test on ojs 2.3.5 only

Once installed using the OJS plugin installation interface you'll need to apply the patch like so:

Apply the patch article2Repo.patch by going to the directory in which ojs is installed (eg /path/to/docroot/ojs) and running

patch -p0 < plugins/generic/article2Repo.patch

if need be you can reverse the patch like so:

patch -R -p0 < plugins/generic/article2Repo.patch

##### EPrints bit #####

To get the most out of this plug-in (like full-texts being actually deposited into the repository) 
You will also need to patch the repository code (if using EPrints 3.1.x) This patch also contains a 
bit for irstats so that OJS can acces and display stats gaphs on the article pages

From the repository install directory /path/to/eprints3 ON THE EPRINTS SERVER run the following:

patch -R -p0 < EPrints_article2Repo.patch

(This patch file file can be removed from the OJS instance as it doens't really belong there)
