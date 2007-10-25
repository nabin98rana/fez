<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
	xmlns:dir="http://apache.org/cocoon/directory/2.0" xmlns:dri="http://di.tamu.edu/DRI/1.0/"
	xmlns:mets="http://www.loc.gov/METS/" xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:dim="http://www.dspace.org/xmlns/dspace/dim" xmlns:xlink="http://www.w3.org/TR/xlink/"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:mods="http://www.loc.gov/mods/v3"
	version="1.0">

	<xsl:import href="dri2xhtml.xsl"/>
	<xsl:key name="objectbyIdentifier" match="dri:object" use="@objectIdentifier"/>




	<xsl:variable name="theme.cssPath">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='theme'][@qualifier='cssPath']"
		/>
	</xsl:variable>
	<xsl:variable name="canUserEditTheme">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:userMeta/dri:metadata[@element='resource' and @qualifier='collection_admin']"
		/>
	</xsl:variable>

	<xsl:variable name="containerHandle">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='focus'][@qualifier='container']"
		/>
	</xsl:variable>

	<xsl:variable name="themeIdent" select="$containerHandle"/>

	<xsl:variable name="imageLoc">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='theme'][@qualifier='imageLoc']"
		/>
	</xsl:variable>

	<xsl:variable name="staticLoc">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='theme'][@qualifier='staticLoc']"
		/>
	</xsl:variable>

	<!-- variable to hold the context location of this theme -->
	<xsl:variable name="themeLoc">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='contextPath'][not(@qualifier)]"/>
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='theme'][@qualifier='path']"
		/>
	</xsl:variable>

	<xsl:variable name="themeName">
		<xsl:value-of select="substring-after($themeLoc,'themes/')"/>
	</xsl:variable>


	<xsl:variable name="objects" select="/dri:document/dri:meta/dri:objectMeta"/>

	<!-- variable holding url of css server -->
	<xsl:variable name="serverUrl">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='server'][@qualifier='url']"
		/>
	</xsl:variable>

	<!-- variable holding webapp context -->
	<xsl:variable name="siteContext">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='contextPath'][not(@qualifier)]"
		/>
	</xsl:variable>

	<!-- variable holding object context -->
	<xsl:variable name="handle">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='focus' and @qualifier='container']"
		/>
	</xsl:variable>

	<xsl:variable name="context">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='focus' and @qualifier='container']"
		/>
	</xsl:variable>
	<!--xsl:variable name="rnd">
              <xsl:text>?</xsl:text>
             <xsl:value-of select="generate-id(/dri:document/dri:meta/dri:pageMeta/dri:metadata)"/>
             <xsl:text>=</xsl:text>
             <xsl:value-of select="generate-id(/dri:document/dri:meta/dri:userMeta/dri:metadata)"/>    
        </xsl:variable-->

	<xsl:variable name="cssDirDocRef">
		<xsl:value-of select="$serverUrl"/>
		<xsl:text>/themes/css_dir_content.php?path=</xsl:text>
		<xsl:value-of select="$themeLoc"/>
		<xsl:text>/css</xsl:text>
		<!--xsl:value-of select="$rnd"/-->
	</xsl:variable>

	<xsl:variable name="themeSpecCss">
		<xsl:value-of select="translate($themeIdent,':','_')"/>
		<xsl:text>.css</xsl:text>
	</xsl:variable>

	<xsl:variable name="css2Use">
		<xsl:for-each select="document($cssDirDocRef)">
			<xsl:choose>
				<xsl:when test="dir:directory/dir:file[@name = $themeSpecCss]">
					<xsl:apply-templates select="dir:directory/dir:file[@name = $themeSpecCss]"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>default.css</xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
	</xsl:variable>


	<!--
	  General Templates
	-->
	<!-- hide the alpha browse and other unnecessary UI features -->
	<xsl:template
		match="dri:options|dri:div[@interactive='yes' and not(contains(@n,'search-query'))]"/>


	<xsl:template match="dri:document[dri:body/dri:div[@n='collection-home']]">
		<html>
			<xsl:call-template name="doMeta"/>
			<xsl:call-template name="doHomePage"/>
			<!-- xsl:value-of select="$issueCoverImageRef"/ -->
		</html>
	</xsl:template>

	<xsl:template match="dri:document[dri:body/dri:div[@n='community-home']]">
		<html>
			<xsl:call-template name="doMeta"/>
			<xsl:call-template name="doCommunityHomePage"/>
		</html>
	</xsl:template>



	<xsl:template match="dir:directory/dir:file">
		<xsl:value-of select="@name"/>
	</xsl:template>


	<!-- do the basic page layout (header, content, footer, nav) -->
	<xsl:template match="dri:document">

		<html>
			<xsl:call-template name="doMeta"/>
			<body class="mainsite">
				<div class="main">
					<div class="header-container">
						<div class="header-image"/>
						<xsl:choose>
							<xsl:when test="dri:body/dri:div[contains(@n,'search')]">
								<xsl:call-template name="doMenu">
									<xsl:with-param name="handle">
										<xsl:value-of select="$handle"/>
									</xsl:with-param>
								</xsl:call-template>
							</xsl:when>
							<xsl:otherwise>
								<xsl:call-template name="doMenu"/>
							</xsl:otherwise>
						</xsl:choose>
						<hr class="header"/>
					</div>
					<xsl:apply-templates/>
					<xsl:call-template name="doFooter"/>
				</div>
			</body>
		</html>
	</xsl:template>

	<xsl:variable name="issueCover">
		<xsl:variable name="manifestUrl">
			<xsl:choose>
				<xsl:when test="//mets:file/mets:FLocat/@xlink:href[contains(., 'manifest.php')]">
					<xsl:value-of
						select="concat($serverUrl,//mets:file/mets:FLocat/@xlink:href[contains(., 'manifest.php')])"
					/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>null</xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:choose>
			<xsl:when
				test="$manifestUrl != 'null' and document($manifestUrl)//mods:mods[mods:genre='cover']">
				<xsl:value-of
					select="document($manifestUrl)//mods:mods[mods:genre='cover']/mods:identifier[@type='filename']"/>

			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$serverUrl"/>
				<xsl:value-of select="$themeLoc"/>
				<xsl:text>/images/</xsl:text>
				<xsl:value-of select="$themeIdent"/>
				<xsl:text>-cover.jpg</xsl:text>
			</xsl:otherwise>
		</xsl:choose>

	</xsl:variable>


	<xsl:template name="doHomePage">
		<body class="homepage">
			<xsl:call-template name="doMenu"/>
			<div class="homepage-abstract">
				<div class="homepage-img">
					<a name="rolloverLink">
						<xsl:attribute name="href">
							<xsl:value-of select="$serverUrl"/>
							<xsl:value-of select="$siteContext"/>
							<xsl:text>theme.php?parent_pid=</xsl:text>
							<xsl:value-of select="$handle"/>
							<xsl:text>&amp;action=browse-title</xsl:text>
							<xsl:text>&amp;theme_id=</xsl:text>
							<xsl:value-of select="$themeName"/>
						</xsl:attribute>
						<img border="0" name="rollover" alt="The Issue  coverpage">
							<xsl:attribute name="src">
								<xsl:value-of select="$issueCover"/>
							</xsl:attribute>
						</img>
					</a>
				</div>
				<div class="abstract">
					<div class="title">
						<xsl:apply-templates
							select="$objects/dri:object[@objectIdentifier = $handle]/mets:METS/mets:dmdSec[1]//dim:dim/dim:field[@element='description' and @qualifier = 'abstract']"
						/>
					</div>
					<div class="description">
						<xsl:apply-templates
							select="$objects/dri:object[@objectIdentifier = $handle]/mets:METS/mets:dmdSec[1]//dim:dim/dim:field[@element='description' and not(@qualifier)]"
						/>
					</div>

				</div>
			</div>
		</body>
	</xsl:template>

	<xsl:template name="doCommunityHomePage">
		<body class="homepage">
			<div id="collection-list">
				<h3>Issues</h3>
				<xsl:apply-templates
					select="dri:body/dri:div/dri:div[@id='artifactbrowser.CommunityViewer.div.community-view']"
				/>
			</div>
			<div id="community-cover">
				<xsl:variable name="imgsrc" select="//mets:fileGrp[@USE='preview'][1]/mets:file/mets:FLocat/@xlink:href"/>
				<img name="rollover" alt="The Community coverpage" src="{$imgsrc}" />
			</div>
		</body>
	</xsl:template>
	<xsl:template match="dri:div[@id='artifactbrowser.CommunityViewer.div.community-view']">
		<xsl:apply-templates
			select="dri:includeSet/dri:objectInclude/dri:includeSet/dri:objectInclude"/>
	</xsl:template>

	<xsl:template match="dri:objectInclude">
		<xsl:variable name="communityID" select="@objectSource"/>

		
		<xsl:variable name="thumbsrc" select="key('objectbyIdentifier' ,$communityID)/mets:METS/mets:fileSec/mets:fileGrp[@USE='thumbnail']/mets:file/mets:FLocat/@xlink:href"/>
		<xsl:variable name="imgsrc" select="key('objectbyIdentifier' ,$communityID)/mets:METS/mets:fileSec/mets:fileGrp[@USE='preview']/mets:file/mets:FLocat/@xlink:href"/>
		<xsl:variable name="href">
			<xsl:value-of select="$serverUrl"/>
			<xsl:value-of select="$siteContext"/>
			<xsl:text>theme.php?parent_pid=</xsl:text>
			<xsl:value-of select="$communityID"/>
			<xsl:text>&amp;action=collection-home</xsl:text>
			<xsl:text>&amp;theme_id=</xsl:text>
			<xsl:value-of select="$themeName"/>
		</xsl:variable>
		<div class="thumbnail">
			<a class="imageref" href="{$href}">
				<img alt="The Collection coverpage {$communityID}-cover.jpg" 
					 src="{$thumbsrc}" onMouseOver="document.rollover.src='{$imgsrc}'"/>
			</a>
			<p class="thumbtitle">
				<a href="{$href}">
					<xsl:value-of
					select="key('objectbyIdentifier' ,@objectSource)/mets:METS/mets:dmdSec[1]/mets:mdWrap/mets:xmlData/dim:dim/dim:field[@element='title']"
				/>
				</a>
			</p>
		</div>
	</xsl:template>

	<!--
	  Templates for Item View
	-->
	<xsl:template match="*[not(name()='head')]" mode="summaryView">
		<xsl:apply-templates
			select="$objects/dri:object[@objectIdentifier = current()/@objectSource]/mets:METS/mets:fileSec/mets:fileGrp"
			mode="detailView"/>
		<xsl:apply-templates
			select="$objects/dri:object[@objectIdentifier = current()/@objectSource]/mets:METS/mets:dmdSec[@ID='dmd_1']/mets:mdWrap/mets:xmlData/dim:dim"
			mode="detailView"/>
	</xsl:template>


	<!-- by default, show the branded preview image if there is one -->
	<xsl:template match="mets:fileGrp" mode="detailView">
		<xsl:if test="@USE='BRANDED_PREVIEW'">
			<img alt="Preview" class="preview">
				<xsl:attribute name="src">
					<xsl:value-of select="mets:file/mets:FLocat[@LOCTYPE='URL']/@xlink:href"/>
				</xsl:attribute>
			</img>
			<div class="spacer">&#x00A0;</div>
		</xsl:if>
	</xsl:template>


	<xsl:template match="dim:dim" mode="detailView">
		<table>
			<xsl:apply-templates select="dim:field" mode="detailView"/>
		</table>
	</xsl:template>


	<xsl:template match="dim:field" mode="detailView">
		<tr valign="top">
			<td>
				<span class="bold">
					<xsl:value-of select="@element"/>
					<xsl:if test="@qualifier">
						<xsl:text>.</xsl:text>
						<xsl:value-of select="@qualifier"/>
					</xsl:if>
				</span>
			</td>
			<td>
				<xsl:value-of select="."/>
			</td>
		</tr>
	</xsl:template>


	<!--
	  Templates for Browse
	-->
	<xsl:template match="dri:includeSet[@type='summaryList']">
		<div class="spacer">&#x00A0;</div>
		<div class="images">
			<xsl:apply-templates select="*[not(name()='head')]" mode="summaryList"/>
		</div>
		<div class="spacer">&#x00A0;</div>
	</xsl:template>


	<xsl:template match="*[not(name()='head')]" mode="summaryList">
		<xsl:apply-templates
			select="$objects/dri:object[@objectIdentifier = current()/@objectSource]/mets:METS/mets:fileSec/mets:fileGrp[@USE='THUMBNAIL']"
			mode="summaryList"/>
	</xsl:template>


	<xsl:template match="mets:fileGrp[@USE='THUMBNAIL']" mode="summaryList">
		<div class="thumbnail">
			<a class="imageref" target="_blank">
				<xsl:attribute name="href">
					<xsl:value-of select="ancestor::dri:object/@url"/>
				</xsl:attribute>
				<img alt="Thumbnail" class="general">
					<xsl:attribute name="src">
						<xsl:value-of select="mets:file/mets:FLocat[@LOCTYPE='URL']/@xlink:href"/>
					</xsl:attribute>
				</img>
			</a>
			<p class="thumbtitle">
				<a class="thumbtitle" target="_blank">
					<xsl:attribute name="href">
						<xsl:value-of select="ancestor::dri:object/@url"/>
					</xsl:attribute>
					<xsl:attribute name="title">
						<xsl:value-of
							select="ancestor::dri:object//dim:dim/dim:field[@element='title' and not(@qualifier)]"
						/>
					</xsl:attribute>
					<xsl:choose>
						<xsl:when
							test="ancestor::dri:object//dim:dim/dim:field[@element='title' and not(@qualifier)]">
							<xsl:value-of
								select="ancestor::dri:object//dim:dim/dim:field[@element='title' and not(@qualifier)]"
							/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>Untitled</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</a>
			</p>
		</div>
	</xsl:template>


	<!--
	  Templates for Search
	-->
	<xsl:template match="dri:div[@n='advanced-search']">
		<xsl:apply-templates select="//@pagination"/>
		<xsl:apply-templates select=".//dri:includeSet[@type='summaryList']"/>
	</xsl:template>


	<xsl:template
		match="dri:div[@n='advanced-search' and not(dri:div[@n='search-results'])] | dri:div[@n='advanced-search' and dri:div[@n='search-results' and not(dri:includeSet)]]">
		<xsl:variable name="search-context">
			<xsl:value-of
				select="substring-after(../../dri:meta/dri:pageMeta/dri:metadata[@element='focus' and @qualifier='container'], ':')"
			/>
		</xsl:variable>
		<h2 class="advanced-search-heading">Search the Collection</h2>
		<form method="get" class="advanced-search-form" action="{$serverUrl}theme.php">
			<!--xsl:call-template name="doSearchTable"/-->
			<xsl:apply-templates select=".//dri:table[@n='search-query']" mode="search"/>
			<input type="hidden" name="scope">
				<xsl:attribute name="value">
					<xsl:value-of select="$handle"/>
				</xsl:attribute>
			</input>
			<input type="hidden" name="parent_pid" value="{$handle}" />
			<input type="hidden" name="action" value="search" />
			<input type="hidden" name="theme_id" value="{$themeName}" />
		</form>
		<xsl:if test="dri:div[@n='search-results' and not(dri:includeSet)]">
			<p>No search results found</p>
		</xsl:if>
		
		<div class="spacer">&#x00A0;</div>
	</xsl:template>


	<!--
	  Named Templates
	-->

	<!-- output the metadata and stylesheet info -->
	<xsl:template name="doMeta">
		<head>
			<title>
				<xsl:value-of
					select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='title']"/>
			</title>
			<xsl:choose>
				<xsl:when test="$canUserEditTheme = 'yes'">
					<link type="text/css" rel="stylesheet"
						href="{$serverUrl}{$themeLoc}/css/main.css"/>
					<link type="text/css" rel="stylesheet"
						href="{$serverUrl}{$themeLoc}/css/styles.css"/>
					<link type="text/css" rel="stylesheet"
						href="{$serverUrl}{$themeLoc}/css/chameleon_ui.css"/>
					<script type="text/javascript"
						src="{$serverUrl}{$themeLoc}/scripts/css_query.js"> </script>
					<script type="text/javascript" src="{$serverUrl}{$themeLoc}/scripts/sarissa.js"> </script>
					<script type="text/javascript"
						src="{$serverUrl}{$themeLoc}/scripts/chameleon.js"> </script>
					<script type="text/javascript"
						src="{$serverUrl}{$themeLoc}/scripts/javascript-static.js"/>
					<script type="text/javascript" src="{$serverUrl}{$themeLoc}/scripts/overlib.js"/>
					<script type="text/javascript" src="{$serverUrl}{$themeLoc}/scripts/cookies.js"/>
					<script type="text/javascript" src="{$serverUrl}{$themeLoc}/scripts/ufo.js"/>
					<link rel="stylesheet" type="text/css">
						<xsl:attribute name="href">
							<xsl:choose>
								<xsl:when test="$theme.cssPath">
									<xsl:value-of select="$serverUrl"/>
									<xsl:value-of select="$themeLoc"/>
									<xsl:text>/css/</xsl:text>
									<xsl:value-of select="$theme.cssPath"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="$serverUrl"/>
									<xsl:value-of select="$themeLoc"/>
									<xsl:text>/css/</xsl:text>
									<xsl:value-of select="$themeIdent"/>
									<xsl:text>.css</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:attribute>
					</link>
				</xsl:when>
				<xsl:otherwise>
					<link rel="stylesheet" type="text/css">
						<xsl:attribute name="href">
							<xsl:value-of select="$serverUrl"/>
							<xsl:value-of select="$themeLoc"/>
							<xsl:text>/css/</xsl:text>
							<xsl:copy-of select="$css2Use"/>
						</xsl:attribute>
					</link>
				</xsl:otherwise>
			</xsl:choose>
		</head>
		<xsl:call-template name="doConfig"/>
	</xsl:template>

	<!-- output configuration info used by the chemeleon scripts -->
	<xsl:template name="doConfig">
		<xsl:if test="$canUserEditTheme = 'yes'">
			<xml style="display:none" id="config">
				<SERVER_URI id="serverUrl">
					<xsl:value-of select="$serverUrl"/>
				</SERVER_URI>
				<THEME_ROOT id="themeRoot">
					<xsl:value-of select="$serverUrl"/>
					<xsl:value-of select="$themeLoc"/>
				</THEME_ROOT>
				<THEME_NAME id="themeName">
					<xsl:value-of select="substring-after($themeLoc,'themes/')"/>
				</THEME_NAME>
				<CSS_FILE id="cssFile">
					<xsl:value-of select="$themeSpecCss"/>
				</CSS_FILE>
				<REMOTE_URI id="cssLocation">
					<xsl:value-of select="$serverUrl"/>
					<xsl:value-of select="$themeLoc"/>
					<xsl:text>/css/</xsl:text>
					<xsl:value-of select="$themeSpecCss"/>
				</REMOTE_URI>
				<REMOTE_URI id="defaultCssLocation">
					<xsl:value-of select="$serverUrl"/>
					<xsl:value-of select="$themeLoc"/>
					<xsl:text>/css/default.css</xsl:text>
				</REMOTE_URI>
				<REMOTE_URI id="imageLocation">
					<xsl:value-of select="$serverUrl"/>
					<xsl:text>themes/image_dir_content.php?path=</xsl:text>
					<xsl:value-of select="$themeLoc"/>
					<xsl:text>/images</xsl:text>
				</REMOTE_URI>
				<REMOTE_URI id="imageUploadUri">
					<xsl:value-of select="$serverUrl"/>
					<xsl:text>themes/image_uploader.php</xsl:text>
				</REMOTE_URI>
				<REMOTE_URI id="cssUploadUri">
					<xsl:value-of select="$serverUrl"/>
					<xsl:text>themes/css_updater.php</xsl:text>
				</REMOTE_URI>
			</xml>
		</xsl:if>

	</xsl:template>


	<xsl:template name="doMenu">
		<xsl:param name="handle" select="$context"/>
		<div class="menu-options">
			<a href="{$serverUrl}theme.php?parent_pid={$handle}&amp;action=collection-home&amp;theme_id={$themeName}"
				class="menu">HOME</a>&#x00A0;<a
					href="{$serverUrl}theme.php?parent_pid={$handle}&amp;action=browse-title&amp;theme_id={$themeName}"
				class="menu">BROWSE</a>&#x00A0;<a
					href="{$serverUrl}theme.php?parent_pid={$handle}&amp;action=search&amp;theme_id={$themeName}" class="menu">SEARCH</a></div>
	</xsl:template>


	<xsl:template name="doFooter">
		<div class="footer">
			<p id="content" class="footer-text">
				<xsl:text>The&#x00A0;Australian&#x00A0;National&#x00A0;University&#x00A0;-&#x00A0;CRICOS&#x00A0;Provider&#x00A0;Number:&#x00A0;00120C</xsl:text>
			</p>
			<p class="footer-text">
				<a target="_blank" href="http://www.dspace.org/">DSpace Software</a>
				<xsl:text>&#x00A0;Copyright&#x00A0;&#x00A9;&#x00A0;2002-2005&#x00A0;</xsl:text>
				<a target="_blank" href="http://web.mit.edu/">MIT</a>
				<xsl:text>&#x00A0;and&#x00A0;</xsl:text>
				<a target="_blank" href="http://www.hp.com/">Hewlett-Packard</a>
				<xsl:text>&#x00A0;-</xsl:text>
				<a target="_blank"
					href="/feedback?fromPage=http%3A%2F%2Fsts59132.anu.edu.au%2Fhome.jsp"
				>Feedback</a>
			</p>
		</div>
	</xsl:template>


	<!--xsl:template name="doSearchTable"-->
	<xsl:template match="dri:table[@n='search-query']" mode="search">
		<table class="search">
			<tr class="ds-table-header-row">
				<th class="ds-table-header-cell odd"></th>
				<th class="ds-table-header-cell even">Search Type</th>
				<th class="ds-table-header-cell odd">Search Term</th>
			</tr>
			<tr class="ds-table-row even">
				<td class="ds-table-cell odd"/>
				<td class="ds-table-cell even">
					<select type="select" class="ds-select-field" name="searchKey1">
						<option value="ANY" selected="true">All Fields</option>
						<option value="title">Title</option>
						<option value="keyword">Subject</option>
						<option value="abstract">Description</option>
					</select>
				</td>
				<td>
					<input size="60" class="ds-text-field" name="query1" type="text"/>
				</td>
			</tr>
			
			
			<tr>
				<td align="center" colspan="3">
					<input type="hidden" name="num_search_field" value="3"/>
					<input type="submit" name="search" value="Search"/>
				</td>
			</tr>
		</table>
	</xsl:template>

</xsl:stylesheet>
