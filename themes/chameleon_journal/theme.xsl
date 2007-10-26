<?xml version="1.0" encoding="UTF-8"?>

<!--
    theme.xsl
    
    Version: $Revision: 1.6 $
    
    Date: $Date: 2006/01/28 20:39:41 $
    
    Copyright (c) 2002-2005, Hewlett-Packard Company and Massachusetts
    Institute of Technology.  All rights reserved.
    
    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are
    met:
    
    - Redistributions of source code must retain the above copyright
    notice, this list of conditions and the following disclaimer.
    
    - Redistributions in binary form must reproduce the above copyright
    notice, this list of conditions and the following disclaimer in the
    documentation and/or other materials provided with the distribution.
    
    - Neither the name of the Hewlett-Packard Company nor the name of the
    Massachusetts Institute of Technology nor the names of their
    contributors may be used to endorse or promote products derived from
    this software without specific prior written permission.
    
    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
    ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
    LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
    A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
    HOLDERS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
    INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
    BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
    OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
    ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
    TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
    USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
    DAMAGE.
-->

<xsl:stylesheet xmlns="http://www.w3.org/1999/xhtml" xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
	xmlns:dri="http://di.tamu.edu/DRI/1.0/" xmlns:mets="http://www.loc.gov/METS/"
	xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dim="http://www.dspace.org/xmlns/dspace/dim"
	xmlns:xlink="http://www.w3.org/TR/xlink/"
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:mods="http://www.loc.gov/mods/v3"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:import href="../anu-common.xsl"/>
	<!-- show the "ORIGINAL" bitstream -->
	<xsl:variable name="driPath">dri.php?pid=</xsl:variable>
	<!-- variable to hold the context location of this theme -->


	<xsl:variable name="objects" select="/dri:document/dri:meta/dri:objectMeta"/>


	<xsl:variable name="canUserEditTheme">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:userMeta/dri:metadata[@element='resource' and @qualifier='collection_admin']"
		/>
	</xsl:variable>


	<!-- <xsl:variable name="issueCoverImageRef">
		<xsl:value-of
			select="document($manifestUrl)/rdf:RDF/rdf:Description[@rdf:about=$context]/mods:mods[mods:genre='cover']/mods:identifier[@type='filename']"/>
	</xsl:variable> -->
	<!-- hide the alpha browse and other unnecessary UI features -->
	<xsl:template
		match="dri:options|dri:div[@interactive='yes' and not(contains(@n,'search-query'))]"/>

	<xsl:template match="dri:meta">
		<xsl:apply-templates/>
	</xsl:template>

	<!-- Browse templates -->
	<xsl:template match="dri:includeSet[@type='summaryList']">
		<div class="spacer">&#x00A0;</div>
		<div class="articles">&#x00A0; <xsl:apply-templates select="*[not(name()='head')]"
				mode="summaryList"/>
		</div>
		<div class="spacer">&#x00A0;</div>
	</xsl:template>


	<xsl:template match="*[not(name()='head')]" mode="summaryList">
		<xsl:apply-templates
			select="$objects/dri:object[@objectIdentifier = current()/@objectSource]/mets:METS/mets:fileSec/mets:fileGrp[@USE='CONTENT']"
			mode="summaryList"/>
	</xsl:template>

	<xsl:template
		match="dri:pageMeta|dri:repositoryMeta|dri:object/mets:mets/mets:dmdSec|dim:dim|dri:userMeta"> </xsl:template>


	<xsl:template match="mets:fileGrp[@USE='CONTENT']">
		<xsl:if test="mets:file/mets:FLocat/@xlink:href[contains(., 'manifest.php')]">
			<div class="toc">
				<h2 class="toc">Table of Contents</h2>
				<table cellspacing="15" align="left">
					<xsl:variable name="manifestUrl"
						select="concat($serverUrl,mets:file/mets:FLocat/@xlink:href)"/>
					<!-- <xsl:value-of select="$manifestUrl" /> -->
					<xsl:for-each
						select="document($manifestUrl)/rdf:RDF/rdf:Description[@rdf:about='section']">
						<ul>
							<xsl:apply-templates select="mods:mods"/>
							<ul>
								<xsl:apply-templates select="rdf:Seq/rdf:li"/>
							</ul>
						</ul>
					</xsl:for-each>
				</table>
			</div>
		</xsl:if>
	</xsl:template>

	<xsl:template match="dri:includeSet[@type='summaryList']/dri:artifactInclude" priority="2"> </xsl:template>

	<xsl:template match="@pagination"> </xsl:template>

	<xsl:template match="dri:div[@n='search-query']"> </xsl:template>

	<xsl:template match="mods:mods">
		<li>
			<xsl:value-of select="mods:titleInfo[not(@type='abbreviated')]/mods:title"/>
		</li>
	</xsl:template>


	<xsl:template match="rdf:Seq/rdf:li">
		<xsl:variable name="identifier" select="@rdf:resource"/>
		<xsl:variable name="driDoc" select="document(concat($serverUrl,$driPath,$identifier))"/>
		<li>
			<div class="tocEntry">
				<span class="toc-a">
					<!-- <xsl:value-of select="concat($serverUrl,$driPath,$identifier)"/> -->
					<a>
						<xsl:attribute name="href">
							<xsl:value-of select="$driDoc//mets:fileGrp[@USE='CONTENT']//mets:FLocat/@xlink:href"/>
						</xsl:attribute>
						<xsl:value-of select="$driDoc//dim:field[@element='title']"/>
					</a>
				</span>
				<span class="toc-c">
					<xsl:value-of select="$driDoc//dim:field[@element='contributor']"/>
				</span>
			</div>
		</li>
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
			<input type="hidden" name="parent_pid" value="{$handle}"/>
			<input type="hidden" name="action" value="search"/>
			<input type="hidden" name="theme_id" value="{$themeName}"/>
		</form>
		<xsl:choose>
			<xsl:when test="dri:div[@n='search-results' and not(//dri:object)]">
				<p>No search results found</p>
			</xsl:when>
			<xsl:when test="dri:div[@n='search-results'] and //dri:object"> 
				<h3>Result Documents</h3>
				<ul>
				<xsl:apply-templates select="//dri:object" mode="result-set"/>
				</ul>
			</xsl:when>
		</xsl:choose>
		
		
		<div class="spacer">&#x00A0;</div>
	</xsl:template>
	
	<xsl:template match="dri:object" mode="result-set">
		<li>
			<div class="tocEntry">
				<span class="toc-a">
					<a>
						<xsl:attribute name="href">
							<xsl:value-of select="mets:METS//mets:fileGrp[@USE='CONTENT']//mets:FLocat/@xlink:href"/>
						</xsl:attribute>
						<xsl:value-of select="mets:METS/mets:dmdSec/mets:mdWrap/mets:xmlData/dim:dim/dim:field[@element='title']"/>
					</a>
				</span>
				<span class="toc-c">
					<xsl:value-of select="mets:METS/mets:dmdSec/mets:mdWrap/mets:xmlData/dim:dim/dim:field[@element='contributor']"/>
				</span>
			</div>
		</li>
	</xsl:template>
	<!-- HOME PAGE STYLESHEET STARTS HERE-->

	<xsl:template
		match="dri:options|dri:div[@interactive='yes' and not(contains(@n,'search-query'))]"/>

	<!--xsl:template match="dri:document/dri:body/dri:div[@n='collection-home']"-->


	
</xsl:stylesheet>
