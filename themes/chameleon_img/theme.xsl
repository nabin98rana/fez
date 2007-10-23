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

<xsl:stylesheet xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
	xmlns:dri="http://di.tamu.edu/DRI/1.0/" xmlns:mets="http://www.loc.gov/METS/"
	xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dim="http://www.dspace.org/xmlns/dspace/dim"
	xmlns:xlink="http://www.w3.org/TR/xlink/" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	version="1.0">
	<xsl:import href="../anu-common.xsl"/>
	<!-- show the "ORIGINAL" bitstream -->

	<xsl:template match="mets:fileGrp" mode="detailView">
		<xsl:if test="@USE='CONTENT'">
			<img alt="Preview" class="preview">
				<xsl:attribute name="src">
					<xsl:value-of select="mets:file/mets:FLocat[@LOCTYPE='URL']/@xlink:href"/>
				</xsl:attribute>
			</img>
			<div class="spacer">&#x00A0;</div>
		</xsl:if>
	</xsl:template>

	<!-- Custom metadata list -->
	<xsl:template match="dim:dim" mode="detailView">
		<table>
			<tr valign="top">
				<td>
					<span class="bold">Title:</span>
				</td>
				<td>
					<xsl:value-of select="dim:field[@element='title' and not(@qualifier)]"/>
				</td>
			</tr>
			<xsl:apply-templates select="dim:field[@element='subject' and @qualifier='other']"
				mode="detailView"/>
			<tr valign="top">
				<td>
					<span class="bold">Description:</span>
				</td>
				<td>
					<xsl:value-of select="dim:field[@element='description' and not(@qualifier)]"/>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<span class="bold">Creator:</span>
				</td>
				<td>
					<xsl:value-of select="dim:field[@element='creator']"/>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<span class="bold">Publisher:</span>
				</td>
				<td>
					<xsl:value-of select="dim:field[@element='publisher']"/>
				</td>
			</tr>
			<xsl:apply-templates select="dim:field[@element='contributor']" mode="detailView"/>
			<tr valign="top">
				<td>
					<span class="bold">Type:</span>
				</td>
				<td>
					<xsl:value-of select="dim:field[@element='type']"/>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<span class="bold">Format:</span>
				</td>
				<td>
					<xsl:value-of select="dim:field[@element='format' and not(@qualifier)]"/>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<span class="bold">File size (kb):</span>
				</td>
				<td>
					<xsl:value-of
						select="round(substring-before(dim:field[@element='format' and @qualifier='extent'], ' ') div 1000)"
					/>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<span class="bold">Identifier:</span>
				</td>
				<td>
					<xsl:value-of select="dim:field[@element='identifier' and @qualifier='other']"/>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<span class="bold">Source:</span>
				</td>
				<td>
					<xsl:value-of select="dim:field[@element = 'source']"/>
				</td>
			</tr>
			<tr>
				<td>
					<span class="bold">Coverage:</span>
				</td>
				<td/>
			</tr>
			<tr>
				<td>
					<span class="bold">Language:</span>
				</td>
				<td>
					<xsl:value-of select="dim:field[@element='language' and not(@qualifier)]"/>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<span class="bold">Archive:</span>
				</td>
				<td>
					<a>
						<xsl:attribute name="href">
							<xsl:value-of
								select="dim:field[@element='identifier' and @qualifier='uri']"/>
						</xsl:attribute>
						<xsl:text>View Demetrius Record</xsl:text>
					</a>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<span class="bold">Rights:</span>
				</td>
				<td>
					<a>
						<xsl:attribute name="href">
							<xsl:value-of select="dim:field[@element='rights']"/>
						</xsl:attribute>
						<xsl:text>View Copyright Statement</xsl:text>
					</a>
				</td>
			</tr>
		</table>
	</xsl:template>


	<xsl:template match="dim:field[@element='subject' and @qualifier='other']" mode="detailView">
		<tr>
			<td class="bold" valign="top">
				<xsl:if
					test="not(preceding-sibling::dim:field[@element = 'subject' and @qualifier = 'other'])">
					<span class="bold">Subject:</span>
				</xsl:if>
			</td>
			<td>
				<xsl:variable name="string">
					<!-- remove Lucene special chars -->
					<xsl:value-of select="translate(.,')(','')"/>
				</xsl:variable>
				<a href="{$siteContext}/handle/{$handle}/simple-search?query={$string}">
					<xsl:value-of select="."/>
				</a>
			</td>
		</tr>
	</xsl:template>


	<xsl:template match="dim:field[@element='contributor']" mode="detailView">
		<tr valign="top">
			<td class="nowrap">
				<xsl:if test="not(preceding-sibling::dim:field[@element = 'contributor'])">
					<span class="bold">Contributors:</span>
				</xsl:if>
			</td>
			<td>
				<xsl:value-of select="."/>
			</td>
		</tr>
	</xsl:template>



	<xsl:template name="doMenu">
		<xsl:param name="handle" select="$context"/>
		<div class="menu-options">
			<table class="nav">
				<tr>
					<td>
						<a class="banner"> </a>
					</td>
					<td>
						<a href="{$siteContext}/handle/{$handle}" class="menu-home"/>
					</td>
					<td>
						<a href="{$siteContext}/handle/{$handle}/browse-title" class="menu-browse"/>
					</td>
					<td>
						<a href="{$siteContext}/handle/{$handle}/advanced-search"
							class="menu-search"/>
					</td>
				</tr>
			</table>
		</div>
	</xsl:template>

</xsl:stylesheet>
