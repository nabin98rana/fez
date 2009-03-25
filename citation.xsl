<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  version="1.0" xmlns:str="http://exslt.org/strings" extension-element-prefixes="str" >
<xsl:import href="str.replace.function.xsl" />

<xsl:output method="text" omit-xml-declaration="yes" indent="no"/>  

<xsl:param name="form"/>
<xsl:param name="baseURL" />

<xsl:strip-space elements="*"/>

<xsl:template match="/xmlfeed">
	<xsl:choose>
		<xsl:when test="$form ='tablecells'">
			<xsl:call-template name="tablecells" />

		</xsl:when>
		<xsl:otherwise>
			<xsl:call-template name="ul" />
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="tablecells">  <!-- use this display in fez  -->
	<xsl:for-each select="item/citation">
		<xsl:text>document.write('&lt;tr&gt;');</xsl:text> 
		<xsl:text>document.write('&lt;td&gt;');</xsl:text>
		
		<xsl:text>document.write('</xsl:text>
		<xsl:call-template name="addBaseURL">
			<xsl:with-param name="string">
			<xsl:call-template name="escapeSingleQuotes">
				<xsl:with-param name="string">
				<xsl:call-template name="unEscapeSingleQuotes">
					<xsl:with-param name="string">
					<xsl:call-template name="cleanData">
						<xsl:with-param name="data">
						<xsl:value-of select="."/>
						</xsl:with-param>
					</xsl:call-template>
					</xsl:with-param>
				</xsl:call-template>
				</xsl:with-param>
			</xsl:call-template>
			</xsl:with-param>
		</xsl:call-template>
		<xsl:text>');</xsl:text>
		<xsl:text>document.write('&lt;/td&gt;');</xsl:text> 
	<xsl:text>document.write('&lt;/tr&gt;');</xsl:text>  		
	</xsl:for-each>	
</xsl:template>

<xsl:template name="ul">  <!-- use this display for all other cases  -->
	<xsl:text>document.write('&lt;ul&gt;');</xsl:text>  
		<xsl:for-each select="item/citation">
		<xsl:text>document.write('&lt;li&gt;');</xsl:text>
		<xsl:text>document.write('</xsl:text>
		<xsl:call-template name="addBaseURL">
			<xsl:with-param name="string">
			<xsl:call-template name="escapeSingleQuotes">
				<xsl:with-param name="string">
				<xsl:call-template name="unEscapeSingleQuotes">
					<xsl:with-param name="string">
					<xsl:call-template name="cleanData">
						<xsl:with-param name="data">
						<xsl:value-of select="."/>
						</xsl:with-param>
					</xsl:call-template>
					</xsl:with-param>
				</xsl:call-template>
				</xsl:with-param>
			</xsl:call-template>
			</xsl:with-param>
		</xsl:call-template>
		<xsl:text>');</xsl:text>
		<xsl:text>document.write('&lt;/li&gt;');</xsl:text>
	</xsl:for-each>	
	<xsl:text>document.write('&lt;/ul&gt;');</xsl:text> 
</xsl:template>

<xsl:template name="unEscapeSingleQuotes">
	<xsl:param name="string" />
	<xsl:variable name="quote">'</xsl:variable>
	<xsl:variable name="escapeQuote">\'</xsl:variable>
	<xsl:value-of select="str:replace($string,$escapeQuote,$quote)"/>
</xsl:template>

<xsl:template name="escapeSingleQuotes">
	<xsl:param name="string" />
	<xsl:variable name="quote">'</xsl:variable>
	<xsl:variable name="escapeQuote">\'</xsl:variable>
	<xsl:value-of select="str:replace($string,$quote,$escapeQuote)"/>
</xsl:template>

<xsl:template name="cleanData">
	<xsl:param name="data" />
	<xsl:value-of select="normalize-space($data)"/>
</xsl:template>

<xsl:template name="addBaseURL">
	<xsl:param name="string" />
	<xsl:variable name="blank">href="/fez/</xsl:variable>
	<xsl:variable name="href">href="</xsl:variable>
	<xsl:value-of select="str:replace($string,$blank,concat($href, $baseURL))"/>
</xsl:template>

</xsl:stylesheet>
