<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:dir="http://apache.org/cocoon/directory/2.0">

<xsl:template match="dir:directory">
    <xsl:element name="files">
        
        <xsl:attribute name="path"><xsl:value-of select="@name"/></xsl:attribute>
        <xsl:apply-templates select="dir:directory">
            <xsl:with-param name="parent" select="@name"/>
        </xsl:apply-templates>
        <xsl:apply-templates select="dir:file">
           <xsl:with-param name="parent" select="@name"/>
        </xsl:apply-templates>
    </xsl:element>
</xsl:template>
    
    <xsl:template match="dir:directory/dir:directory">
        <xsl:param name="parent"/>
        <xsl:element name="file">
            <xsl:attribute name="type">dir</xsl:attribute>
            <xsl:value-of select="concat($parent , '/', @name)"/>
        </xsl:element> 
    </xsl:template>
    
    <xsl:template match="dir:directory/dir:file">
        <xsl:param name="parent"/>
        <xsl:variable name="fileExt" select="translate(substring-after(@name,'.'),'jpegbmngift' , 'JPEGBMNGIFT')"/>
        <xsl:if test="$fileExt = 'JPG' or $fileExt = 'JPEG' or $fileExt = 'GIF' or $fileExt = 'BMP' or $fileExt = 'TIF' or $fileExt = 'TIFF' or $fileExt = 'PNG'">
              <xsl:element name="file">
                  <xsl:attribute name="type">img</xsl:attribute>
                  <xsl:value-of select="concat($parent , '/', @name)"/>
              </xsl:element>              
            </xsl:if>
    </xsl:template>

</xsl:stylesheet>
