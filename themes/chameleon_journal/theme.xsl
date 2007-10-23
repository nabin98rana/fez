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
	<!--xsl:variable name="themeLoc">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='contextPath'][not(@qualifier)]"/>
		<xsl:text>/themes/</xsl:text>
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='theme'][@qualifier='path']"
		/>
	</xsl:variable-->

	<xsl:variable name="containerHandle">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='focus'][@qualifier='container']"
		/>
	</xsl:variable>

	<xsl:variable name="themeIdent" select="substring-after($containerHandle , '/')"/>
	<!-- variable holding url of css server -->

	<!-- variable holding webapp context -->
	<xsl:variable name="siteContext">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='contextPath'][not(@qualifier)]"
		/>
	</xsl:variable>

	<!-- variable holding object context -->
	<xsl:variable name="handle">
		<xsl:value-of
			select="substring-after(/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='focus' and @qualifier='container'],':')"
		/>
	</xsl:variable>

	<xsl:variable name="objects" select="/dri:document/dri:meta/dri:objectMeta"/>

	<xsl:variable name="context">
		<xsl:value-of
			select="substring-after(/dri:document/dri:meta/dri:pageMeta/dri:metadata[@element='focus' and @qualifier='container'],':')"
		/>
	</xsl:variable>

	<xsl:variable name="canUserEditTheme">
		<xsl:value-of
			select="/dri:document/dri:meta/dri:userMeta/dri:metadata[@element='resource' and @qualifier='collection_admin']"
		/>
	</xsl:variable>



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
		<li>
			<div class="tocEntry">
				<span class="toc-a">
					<a>
						<xsl:attribute name="href">
							<xsl:value-of
								select="document(concat($serverUrl,$driPath,$identifier))//mets:FLocat/@xlink:href"
							/>
						</xsl:attribute>
						<xsl:value-of
							select="document(concat($serverUrl,$driPath,$identifier))//dim:field[@element='title']"
						/>
					</a>
				</span>
				<span class="toc-c">
					<xsl:value-of
						select="document(concat($serverUrl,$driPath,$identifier))//dim:field[@element='contributor']"
					/>
				</span>
			</div>
		</li>
	</xsl:template>


	<!-- HOME PAGE STYLESHEET STARTS HERE-->

	<xsl:template
		match="dri:options|dri:div[@interactive='yes' and not(contains(@n,'search-query'))]"/>

	<!--xsl:template match="dri:document/dri:body/dri:div[@n='collection-home']"-->


	<xsl:template name="doFooter">
		<div class="footer">
			<xsl:attribute name="id">footer</xsl:attribute>
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


</xsl:stylesheet>
