<?xml version="1.0" encoding="UTF-8"?>

<!--
  DS-METS-1.0-DIM.xsl

  Version: $Revision: 1.2 $
 
  Date: $Date: 2006/07/27 22:54:52 $
 
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

<!--    
    This Stylesheet is the metadata handler for DIM based objects. This file determines
    how these objects are rendered into HTML, deciding what fields are displayed and in
    what structure are they arranged. There are four 'modes' in which objects can be
    rendered:
    
    
    Summary List:
    This mode is used by the browse-by, search results, and community-collection list - 
    basicaly anywhere objects are agrigated together. In this mode the object should be 
    displayed showing only the most important highlighted information such as a title, 
    author, etc. Then it will provide a link to a page where more information can be 
    found about the object, weather that object is an item, collection, or community.
    
    
    Detailed List:
    This mode is rarely used in Manakin, the one place where it is used is when an item's
    detailed view is being displayed (i.e when the user has selected show full item record),
    the collections that the item are apart of are listed using the detail list mode. 
    
    
    Summary View:
    This mode is used on the item view page (before the user has selected to see the full
    item record). In this case only important highlighted information shuold be displayed
    along with the item's bitstreams and licenses.
    
    
    Detailed View:
    This mode is used by the item view page (when the user has selected to see the full
    item record) and on the community / collection home page. In these cases all the metadata
    about the object should be displayed on the page. For items, where more metadata tends 
    to be available, this should be a technical view of the item's metadata.
    
    Author: Alexey Maslov
-->    

<xsl:stylesheet 
    xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
    xmlns:dri="http://di.tamu.edu/DRI/1.0/"
    xmlns:mets="http://www.loc.gov/METS/"
    xmlns:dim="http://www.dspace.org/xmlns/dspace/dim" 
    xmlns:xlink="http://www.w3.org/TR/xlink/"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
    xmlns="http://www.w3.org/1999/xhtml"
    exclude-result-prefixes="i18n dri mets dim  xlink xsl">
    
    <xsl:output indent="yes"/>
    
    
    <!-- Creates an index across the object store using the METS profile as a distinguishing characteristic. 
        At the time of this writing the current profile for DSpace and DRI is METS SIP Profile 1.0, with DRI
        making use of an extended profile for communities and collections. Since the change is unofficial, we
        have to tag the two profiles differently, but treat them the same in the code (since at some point in 
        the future the two profiles should merge). 
        
        The index allows two modes of access to the store: by means of the 'all' constant to grab all objects
        that user METS 1.0 profile, or by means of specifying an objectIdentifier to grab a specific object. 
        In the near future I will add a third mode to grab an object by both its objectIdentifier and 
        repositoryIdentifier attributes. -->
    
    <xsl:key name="DSMets1.0-DIM-all" match="dri:object[substring-before(mets:METS/@PROFILE,';')='DSPACE METS SIP Profile 1.0 (DRI extensions for DIM)'] |
        dri:object[mets:METS/@PROFILE='DSPACE METS SIP Profile 1.0 (DRI extensions for DIM)'] |
        dri:object[substring-before(mets:METS/@PROFILE,';')='DSPACE METS SIP Profile 1.0 (DRI extensions for DIM and communities/collections)'] |
        dri:object[mets:METS/@PROFILE='DSPACE METS SIP Profile 1.0 (DRI extensions for DIM and communities/collections)']"
        use="'all'"/>
    <xsl:key name="DSMets1.0-DIM" match="dri:object[substring-before(mets:METS/@PROFILE,';')='DSPACE METS SIP Profile 1.0 (DRI extensions for DIM)'] |
        dri:object[mets:METS/@PROFILE='DSPACE METS SIP Profile 1.0 (DRI extensions for DIM)'] |
        dri:object[substring-before(mets:METS/@PROFILE,';')='DSPACE METS SIP Profile 1.0 (DRI extensions for DIM and communities/collections)'] |
        dri:object[mets:METS/@PROFILE='DSPACE METS SIP Profile 1.0 (DRI extensions for DIM and communities/collections)']"
        use="@objectIdentifier"/>
    
       
    <!-- 
        **************************************** 
        summaryList                             
        
        The next set of templates all render objects: items, communities, and 
        collections in a summaryList context. Summary List is used by the 
        browse by pages where multiple items are listed and each one is expected 
        to display a summary of its self.
        **************************************** 
     -->
    
    <!-- 
        What: Matches all objects refrenced as a summaryList.
        The fallback cases for collections/communities as well as items that for whatever reason did not fit
        the table case, but are still using the DSpace METS 1.0 profile. They are broken up into separate 
        templates for simpler overriding. 
        -->
    <xsl:template match="dri:objectInclude[key('DSMets1.0-DIM', @objectSource)]" mode="summaryList">
        <li>
            <xsl:apply-templates select="key('DSMets1.0-DIM', @objectSource)" mode="summaryList">
                <xsl:with-param name="position" select="position()"/>
            </xsl:apply-templates>
            <xsl:apply-templates />
        </li>
    </xsl:template>
    
    <!--
        Selects the named template to display an object based upon it's type: item, collection
        or community.
    -->
    <xsl:template match="key('DSMets1.0-DIM-all', 'all')" mode="summaryList">
        <xsl:param name="position"/>
        <xsl:choose>
            <xsl:when test="mets:METS/mets:structMap/mets:div[@TYPE='DSpace Item']">
                <xsl:call-template name="itemSummaryList_DS-METS-1.0-DIM">
                    <xsl:with-param name="position" select="$position"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="mets:METS/mets:structMap/mets:div[@TYPE='DSpace Collection']">
                <xsl:call-template name="collectionSummaryList_DS-METS-1.0-DIM"/>
            </xsl:when>
            <xsl:when test="mets:METS/mets:structMap/mets:div[@TYPE='DSpace Community']">
                <xsl:call-template name="communitySummaryList_DS-METS-1.0-DIM"/>
            </xsl:when>                
            <xsl:otherwise>
                <i18n:text>xmlui.dri2xhtml.METS-1.0.non-conformant</i18n:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
        
    <!-- 
        Render an Item's summary in a list context. This is normaly used on the browse by pages.
    --> 
    <xsl:template name="itemSummaryList_DS-METS-1.0-DIM">
        <xsl:param name="position"/>
        <xsl:variable name="data" select="./mets:METS/mets:dmdSec/mets:mdWrap/mets:xmlData/dim:dim"/>
        <xsl:attribute name="class">
            <xsl:if test="($position mod 2 = 0)">even </xsl:if>
            <xsl:if test="($position mod 2 = 1)">odd </xsl:if>
            <xsl:text>ds-artifact-item </xsl:text>
        </xsl:attribute>
               
        <div class="artifact-description">
            <div class="artifact-title">
                <a>
                    <xsl:attribute name="href"><xsl:value-of select="@url"/></xsl:attribute>
                    <xsl:choose>
                        <xsl:when test="$data/dim:field[@element='title']">
                            <xsl:value-of select="$data/dim:field[@element='title'][1]/child::node()"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <i18n:text>xmlui.dri2xhtml.METS-1.0.no-title</i18n:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </a>
            </div>
            
            <div class="artifact-info">
                <span class="author">
                    <xsl:choose>
                        <xsl:when test="$data/dim:field[@element='contributor'][@qualifier='author']">
                            <xsl:for-each select="$data/dim:field[@element='contributor'][@qualifier='author']">
                                <xsl:copy-of select="."/>
                                <xsl:if test="count(following-sibling::dim:field[@element='contributor'][@qualifier='author']) != 0">
                                    <xsl:text>; </xsl:text>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <xsl:when test="$data/dim:field[@element='creator']">
                            <xsl:for-each select="$data/dim:field[@element='creator']">
                                <xsl:copy-of select="."/>
                                <xsl:if test="count(following-sibling::dim:field[@element='creator']) != 0">
                                    <xsl:text>; </xsl:text>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <xsl:when test="$data/dim:field[@element='contributor']">
                            <xsl:for-each select="$data/dim:field[@element='contributor']">
                                <xsl:copy-of select="."/>
                                <xsl:if test="count(following-sibling::dim:field[@element='contributor']) != 0">
                                    <xsl:text>; </xsl:text>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <xsl:otherwise>
                            <i18n:text>xmlui.dri2xhtml.METS-1.0.no-author</i18n:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </span>
                <xsl:text> </xsl:text>
                <span class="publisher-date">
                    <xsl:text>(</xsl:text>
                    <xsl:if test="$data/dim:field[@element='publisher']">
                        <span class="publisher">
                            <xsl:copy-of select="$data/dim:field[@element='publisher']/node()"/>
                        </span>
                        <xsl:text>, </xsl:text>
                    </xsl:if>
                    <span class="date">
                        <xsl:value-of select="substring($data/dim:field[@element='date' and @qualifier='issued']/child::node(),1,10)"/>
                    </span>
                    <xsl:text>)</xsl:text>
                </span>
            </div>
        </div>
        
        <xsl:choose>
            <xsl:when
                test="mets:METS/mets:fileSec/mets:fileGrp[@USE='THUMBNAIL']">
                <div class="artifact-preview">
                    <a>
                        <xsl:attribute name="href">
                            <xsl:value-of select="@url" />
                        </xsl:attribute>
                        <img alt="Thumbnail">
                            <xsl:attribute name="src">
                                <xsl:value-of
                                    select="mets:METS/mets:fileSec/mets:fileGrp[@USE='THUMBNAIL']/
                                    mets:file/mets:FLocat[@LOCTYPE='URL']/@xlink:href" />
                            </xsl:attribute>
                        </img>
                    </a>
                </div>
            </xsl:when>
            <xsl:otherwise>
            </xsl:otherwise>
        </xsl:choose>
        
    </xsl:template>
       
    <!-- 
        Render a collection's summary in a list context. Nomarly used on the community-list page.
    --> 
    <xsl:template name="collectionSummaryList_DS-METS-1.0-DIM">
        <xsl:variable name="data" select="./mets:METS/mets:dmdSec/mets:mdWrap/mets:xmlData/dim:dim"/>
        <a>
            <xsl:attribute name="href">
                <xsl:value-of select="@url"/>
            </xsl:attribute>
            <xsl:value-of select="$data/dim:field[@element='title'][1]"/>
        </a>
    </xsl:template>
    
    <!-- 
        Render a community's summary in a list context. This is normaly used on the community-list page.
    --> 
    <xsl:template name="communitySummaryList_DS-METS-1.0-DIM">
        <xsl:variable name="data" select="./mets:METS/mets:dmdSec/mets:mdWrap/mets:xmlData/dim:dim"/>
        <span class="bold">
            <a>
                <xsl:attribute name="href">
                    <xsl:value-of select="@url"/>
                </xsl:attribute>
                <xsl:value-of select="$data/dim:field[@element='title'][1]"/>
            </a>
        </span>
    </xsl:template>
    

    
    <!-- 
        **************************************** 
        detailList                             
        
        The next set of templates all render objects: items, communities, and 
        collections in a detailList context. Detail lists are rarely used, there
        is only one case in Manakin 1.1 where this display type is used. When
        the user selects to show the full information about an item, detailList
        is used to view the collections that the item is a member of.
        **************************************** 
    --> 
    <!--
        Select all detail list objects
     -->
    <xsl:template match="dri:objectInclude[key('DSMets1.0-DIM', @objectSource)]" mode="detailList">
        <li>
            <xsl:apply-templates select="key('DSMets1.0-DIM', @objectSource)" mode="detailList"/>
            <xsl:apply-templates />
        </li>
    </xsl:template>    
    
    <!-- 
        Select which template will display the item based upon it's type.
     -->
    <xsl:template match="key('DSMets1.0-DIM-all', 'all')" mode="detailList">
        <xsl:choose>
            <xsl:when test="mets:METS/mets:structMap/mets:div[@TYPE='DSpace Item']">
                <xsl:call-template name="itemDetailList_DS-METS-1.0-DIM"/>
            </xsl:when>
            <xsl:when test="mets:METS/mets:structMap/mets:div[@TYPE='DSpace Collection']">
                <xsl:call-template name="collectionDetailList_DS-METS-1.0-DIM"/>
            </xsl:when>
            <xsl:when test="mets:METS/mets:structMap/mets:div[@TYPE='DSpace Community']">
                <xsl:call-template name="communityDetailList_DS-METS-1.0-DIM"/>
            </xsl:when>                
            <xsl:otherwise>
                <i18n:text>xmlui.dri2xhtml.METS-1.0.non-conformant</i18n:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!-- 
        Display a detailed DSpace item in list context. This case *never* occures in Manakin 
        as of version 1.1
    --> 
    <xsl:template name="itemDetailList_DS-METS-1.0-DIM">
        <xsl:variable name="data" select="./mets:METS/mets:dmdSec/mets:mdWrap/mets:xmlData/dim:dim"/>
        <!-- Put down the author -->
        <xsl:choose>
            <xsl:when test="$data/dim:field[@element='contributor']/child::node()">
                <xsl:copy-of select="$data/dim:field[@element='contributor']/child::node()"/>.
            </xsl:when>
            <xsl:otherwise>
                <i18n:text>xmlui.dri2xhtml.METS-1.0.no-author</i18n:text>
                <xsl:text>. </xsl:text>
            </xsl:otherwise>
        </xsl:choose>
        <!-- Put down the title -->
        <a>
            <xsl:attribute name="href"><xsl:value-of select="@url"/></xsl:attribute>
            <xsl:choose>
                <xsl:when test="$data/dim:field[@element='title']">
                    <xsl:copy-of select="$data/dim:field[@element='title'][1]/child::node()"/>.
                </xsl:when>
                <xsl:otherwise>
                    <i18n:text>xmlui.dri2xhtml.METS-1.0.no-title</i18n:text>
                </xsl:otherwise>
            </xsl:choose>
        </a>
        <xsl:text>. </xsl:text>
        <!-- Put down the date -->
        <xsl:value-of select="substring($data/dim:field[@element='date' and @qualifier='issued']/child::node(),1,10)"/>
    </xsl:template>
    
    <!-- 
        Display a detailed DSpace collection in list context. This case is encountered when the user selects 
        'show full item detail' and the collections that the item are apart of are listed there using this method.
    -->
    <xsl:template name="collectionDetailList_DS-METS-1.0-DIM">
        <xsl:variable name="data" select="./mets:METS/mets:dmdSec/mets:mdWrap/mets:xmlData/dim:dim"/>
        <a>
            <xsl:attribute name="href">
                <xsl:value-of select="@url"/>
            </xsl:attribute>
            <xsl:value-of select="$data/dim:field[@element='title'][1]"/>
        </a>
        <br/>
        <xsl:choose>
            <xsl:when test="$data/dim:field[@element='description' and @qualifier='abstract']">
                <xsl:copy-of select="$data/dim:field[@element='description' and @qualifier='abstract']"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy-of select="$data/dim:field[@element='description'][1]"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!--
        Display a detailed DSpace item in list context. This case *never* occures in Manakin 
        as of version 1.1
     -->
    <xsl:template name="communityDetailList_DS-METS-1.0-DIM">
        <xsl:variable name="data" select="./mets:METS/mets:dmdSec/mets:mdWrap/mets:xmlData/dim:dim"/>
        <span class="bold">
            <a>
                <xsl:attribute name="href">
                    <xsl:value-of select="@url"/>
                </xsl:attribute>
                <xsl:value-of select="$data/dim:field[@element='title'][1]"/>
            </a>
            <br/>
            <xsl:choose>
                <xsl:when test="$data/dim:field[@element='description' and @qualifier='abstract']">
                    <xsl:copy-of select="$data/dim:field[@element='description' and @qualifier='abstract']/node()"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:copy-of select="$data/dim:field[@element='description'][1]/node()"/>
                </xsl:otherwise>
            </xsl:choose>
        </span>
    </xsl:template>
    
    
    
    
    <!-- 
        **************************************** 
        summaryView                             
        
        The next set of templates all render objects: items, communities, and 
        collections in a summaryView context. Summary Views are only used on
        the initial item view page where an item's summary information is
        displayed. 
           
        **************************************** 
    -->
    
    <!--
        Select summary view objects
     -->
    <xsl:template match="dri:objectInclude[key('DSMets1.0-DIM', @objectSource)]" mode="summaryView">
       <xsl:apply-templates select="key('DSMets1.0-DIM', @objectSource)" mode="summaryView"/>
       <xsl:apply-templates />
    </xsl:template>    
    
    <!--
        Determine which template will be used to display the object based upon it's type.
     -->
    <xsl:template match="key('DSMets1.0-DIM-all', 'all')" mode="summaryView">
        <xsl:choose>
            <xsl:when test="mets:METS/mets:structMap/mets:div[@TYPE='DSpace Item']">
                <xsl:call-template name="itemSummaryView_DS-METS-1.0-DIM"/>
            </xsl:when>
            <xsl:when test="mets:METS/mets:structMap/mets:div[@TYPE='DSpace Collection']">
                <xsl:call-template name="collectionSummaryView_DS-METS-1.0-DIM"/>
            </xsl:when>
            <xsl:when test="mets:METS/mets:structMap/mets:div[@TYPE='DSpace Community']">
                <xsl:call-template name="communitySummaryView_DS-METS-1.0-DIM"/>
            </xsl:when>                
            <xsl:otherwise>
                <i18n:text>xmlui.dri2xhtml.METS-1.0.non-conformant</i18n:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!--
        Display an overview of an item, this is used on the /handle/xxxx/yyyy pages for items. This
        template will summarize an item's metdata, pulling out important fields such as title,
        author, abstract, description, uri, and date. Then below this the item's files and license
        will be listed.
        
        To change what fields are listed, and how they are displayed, implement this method inside
        the theme's XSL. 
     -->
    <xsl:template name="itemSummaryView_DS-METS-1.0-DIM">
        <xsl:variable name="data" select="./mets:METS/mets:dmdSec/mets:mdWrap/mets:xmlData/dim:dim"/>
        <xsl:variable name="context" select="."/>
        <table class="ds-includeSet-table">
            <tr class="ds-table-row odd">
                <td><span class="bold"><i18n:text>xmlui.dri2xhtml.METS-1.0.item-preview</i18n:text>:</span></td>
                <td>
                    <xsl:choose>
                        <xsl:when test="mets:METS/mets:fileSec/mets:fileGrp[@USE='THUMBNAIL']">
                            <a class="image-link">
                                <xsl:attribute name="href"><xsl:value-of select="@url"/></xsl:attribute>
                                <img alt="Thumbnail">
                                    <xsl:attribute name="src">
                                        <xsl:value-of select="mets:METS/mets:fileSec/mets:fileGrp[@USE='THUMBNAIL']/
                                            mets:file/mets:FLocat[@LOCTYPE='URL']/@xlink:href"/>
                                    </xsl:attribute>
                                </img>
                            </a>
                        </xsl:when>
                        <xsl:otherwise>
                            <i18n:text>xmlui.dri2xhtml.METS-1.0.no-preview</i18n:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </td>
            </tr>
            <tr class="ds-table-row even">
                <td><span class="bold"><i18n:text>xmlui.dri2xhtml.METS-1.0.item-title</i18n:text>:</span></td>
                <td>
                    <xsl:choose>
                        <xsl:when test="$data/dim:field[@element='title']">
                            <xsl:value-of select="$data/dim:field[@element='title'][1]/child::node()"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <i18n:text>xmlui.dri2xhtml.METS-1.0.no-title</i18n:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </td>
            </tr>
            <tr class="ds-table-row odd">
                <td><span class="bold"><i18n:text>xmlui.dri2xhtml.METS-1.0.item-author</i18n:text>:</span></td>
                <td>
                    <xsl:choose>
                        <xsl:when test="$data/dim:field[@element='contributor'][@qualifier='author']">
                            <xsl:for-each select="$data/dim:field[@element='contributor'][@qualifier='author']">
                                <xsl:copy-of select="."/>
                                <xsl:if test="count(following-sibling::dim:field[@element='contributor'][@qualifier='author']) != 0">
                                    <xsl:text>; </xsl:text>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <xsl:when test="$data/dim:field[@element='creator']">
                            <xsl:for-each select="$data/dim:field[@element='creator']">
                                <xsl:copy-of select="."/>
                                <xsl:if test="count(following-sibling::dim:field[@element='creator']) != 0">
                                    <xsl:text>; </xsl:text>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <xsl:when test="$data/dim:field[@element='contributor']">
                            <xsl:for-each select="$data/dim:field[@element='contributor']">
                                <xsl:copy-of select="."/>
                                <xsl:if test="count(following-sibling::dim:field[@element='contributor']) != 0">
                                    <xsl:text>; </xsl:text>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <xsl:otherwise>
                            <i18n:text>xmlui.dri2xhtml.METS-1.0.no-author</i18n:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </td>
            </tr>
            <tr class="ds-table-row even">
                <td><span class="bold"><i18n:text>xmlui.dri2xhtml.METS-1.0.item-abstract</i18n:text>:</span></td>
                <td><xsl:copy-of select="$data/dim:field[@element='description' and @qualifier='abstract']/child::node()"/></td>
            </tr>
            <tr class="ds-table-row odd">
                <td><span class="bold"><i18n:text>xmlui.dri2xhtml.METS-1.0.item-description</i18n:text>:</span></td>
                <td><xsl:copy-of select="$data/dim:field[@element='description' and not(@qualifier)]/child::node()"/></td>
            </tr>
            <tr class="ds-table-row even">
                <td><span class="bold"><i18n:text>xmlui.dri2xhtml.METS-1.0.item-uri</i18n:text>:</span></td>
                <td>
                    <a>
                        <xsl:attribute name="href">
                            <xsl:copy-of select="$data/dim:field[@element='identifier' and @qualifier='uri'][1]/child::node()"/>
                        </xsl:attribute>
                        <xsl:copy-of select="$data/dim:field[@element='identifier' and @qualifier='uri'][1]/child::node()"/>
                    </a>
                </td>
            </tr>
            <tr class="ds-table-row odd">
                <td><span class="bold"><i18n:text>xmlui.dri2xhtml.METS-1.0.item-date</i18n:text>:</span></td>
                <td><xsl:copy-of select="substring($data/dim:field[@element='date' and @qualifier='issued']/child::node(),1,10)"/></td>
            </tr>
        </table>
        <h2><i18n:text>xmlui.dri2xhtml.METS-1.0.item-files-head</i18n:text></h2>
        <table class="ds-table file-list">
            <tr class="ds-table-header-row">
                <th><i18n:text>xmlui.dri2xhtml.METS-1.0.item-files-file</i18n:text></th>
                <th><i18n:text>xmlui.dri2xhtml.METS-1.0.item-files-size</i18n:text></th>
                <th><i18n:text>xmlui.dri2xhtml.METS-1.0.item-files-format</i18n:text></th>
                <th><i18n:text>xmlui.dri2xhtml.METS-1.0.item-files-view</i18n:text></th>
            </tr>
            <!-- First, figure out if there is a primary bitstream -->
            <xsl:variable name="primary" select="mets:METS/mets:structMap[@TYPE='LOGICAL']/mets:div[@TYPE='DSpace Item']/mets:fptr/@FILEID"/>
            <xsl:choose>
                <!-- If one exists and it's of text/html MIME type, only display the primary bitstream -->
                <xsl:when test="mets:METS/mets:fileSec/mets:fileGrp[@USE='CONTENT']/mets:file[@ID=$primary]/@MIMETYPE='text/html'">
                    <xsl:call-template name="buildBitstreamRow">
                        <xsl:with-param name="context" select="$context"/>
                        <xsl:with-param name="file" select="mets:METS/mets:fileSec/mets:fileGrp[@USE='CONTENT']/mets:file[@ID=$primary]"/>
                    </xsl:call-template>
                </xsl:when>
                <!-- Otherwise, iterate over and display all of them -->
                <xsl:otherwise>
                    <xsl:for-each select="mets:METS/mets:fileSec/mets:fileGrp[@USE='CONTENT']/mets:file">
                        <xsl:sort select="./mets:FLocat[@LOCTYPE='URL']/@xlink:title"/> 
                        <xsl:call-template name="buildBitstreamRow">
                            <xsl:with-param name="context" select="$context"/>
                        </xsl:call-template>
                    </xsl:for-each>
                </xsl:otherwise>
            </xsl:choose>
        </table>
        <xsl:if test="mets:METS/mets:fileSec/mets:fileGrp[@USE='CC-LICENSE' or @USE='LICENSE']">
            <div class="license-info">
                <p><i18n:text>xmlui.dri2xhtml.METS-1.0.license-text</i18n:text></p>
                <ul>
                    <xsl:if test="mets:METS/mets:fileSec/mets:fileGrp[@USE='CC-LICENSE']">
                        <li><a href="{mets:METS/mets:fileSec/mets:fileGrp[@USE='CC-LICENSE']/mets:file/
                            mets:FLocat[@xlink:title='license_text']/@xlink:href}">Creative Commons</a></li>
                    </xsl:if>
                    <xsl:if test="mets:METS/mets:fileSec/mets:fileGrp[@USE='LICENSE']">
                        <li><a href="{mets:METS/mets:fileSec/mets:fileGrp[@USE='LICENSE']/mets:file/
                            mets:FLocat[@xlink:title='license.txt']/@xlink:href}">Original License</a></li>
                    </xsl:if>
                </ul>
            </div>
        </xsl:if>
    </xsl:template>
    
    <!-- Utility function used by the item's summary view to list each bitstream -->
    <xsl:template name="buildBitstreamRow">
        <xsl:param name="context" select="."/>
        <xsl:param name="file" select="."/>
        <tr>
            <xsl:attribute name="class">
                <xsl:text>ds-table-row </xsl:text>
                <xsl:if test="(position() mod 2 = 0)">even </xsl:if>
                <xsl:if test="(position() mod 2 = 1)">odd </xsl:if>
            </xsl:attribute>
            <td>
                <a>
                    <xsl:attribute name="href">
                        <xsl:value-of select="$file/mets:FLocat[@LOCTYPE='URL']/@xlink:href"/>
                    </xsl:attribute>
                    <xsl:attribute name="title">
                        <xsl:value-of select="$file/mets:FLocat[@LOCTYPE='URL']/@xlink:title"/>
                    </xsl:attribute>
                    <xsl:choose>
                        <xsl:when test="string-length($file/mets:FLocat[@LOCTYPE='URL']/@xlink:title) > 50">
                            <xsl:variable name="title_length" select="string-length($file/mets:FLocat[@LOCTYPE='URL']/@xlink:title)"/>
                            <xsl:value-of select="substring($file/mets:FLocat[@LOCTYPE='URL']/@xlink:title,1,15)"/>
                            <xsl:text> ... </xsl:text>
                            <xsl:value-of select="substring($file/mets:FLocat[@LOCTYPE='URL']/@xlink:title,$title_length - 25,$title_length)"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$file/mets:FLocat[@LOCTYPE='URL']/@xlink:title"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </a>
            </td>
            <!-- File size always comes in bytes and thus needs conversion --> 
            <td>
                <xsl:choose>
                    <xsl:when test="$file/@SIZE &lt; 1000">
                        <xsl:value-of select="$file/@SIZE"/>
                        <i18n:text>xmlui.dri2xhtml.METS-1.0.size-bytes</i18n:text>
                    </xsl:when>
                    <xsl:when test="$file/@SIZE &lt; 1000000">
                        <xsl:value-of select="substring(string($file/@SIZE div 1000),1,5)"/>
                        <i18n:text>xmlui.dri2xhtml.METS-1.0.size-kilobytes</i18n:text>
                    </xsl:when>
                    <xsl:when test="$file/@SIZE &lt; 1000000000">
                        <xsl:value-of select="substring(string($file/@SIZE div 1000000),1,5)"/>
                        <i18n:text>xmlui.dri2xhtml.METS-1.0.size-megabytes</i18n:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="substring(string($file/@SIZE div 1000000000),1,5)"/>
                        <i18n:text>xmlui.dri2xhtml.METS-1.0.size-gigabytes</i18n:text>
                    </xsl:otherwise>
                </xsl:choose>
            </td>
            <!-- Currently format carries forward the mime type. In the original DSpace, this 
                would get resolved to an application via the Bitstream Registry, but we are
                constrained by the capabilities of METS and can't really pass that info through. -->
            <td><xsl:value-of select="substring-before($file/@MIMETYPE,'/')"/>
                <xsl:text>/</xsl:text>
                <xsl:value-of select="substring-after($file/@MIMETYPE,'/')"/>
            </td>
            <td>
                <xsl:choose>
                    <xsl:when test="$context/mets:METS/mets:fileSec/mets:fileGrp[@USE='THUMBNAIL']/
                        mets:file[@GROUP_ID=current()/@GROUP_ID]">
                        <a class="image-link">
                            <xsl:attribute name="href">
                                <xsl:value-of select="$file/mets:FLocat[@LOCTYPE='URL']/@xlink:href"/>
                            </xsl:attribute>
                            <img alt="Thumbnail">
                                <xsl:attribute name="src">
                                    <xsl:value-of select="$context/mets:METS/mets:fileSec/mets:fileGrp[@USE='THUMBNAIL']/
                                        mets:file[@GROUP_ID=current()/@GROUP_ID]/mets:FLocat[@LOCTYPE='URL']/@xlink:href"/>
                                </xsl:attribute>
                            </img>
                        </a>
                    </xsl:when>
                    <xsl:otherwise>
                        <a>
                            <xsl:attribute name="href">
                                <xsl:value-of select="$file/mets:FLocat[@LOCTYPE='URL']/@xlink:href"/>
                            </xsl:attribute>
                            <i18n:text>xmlui.dri2xhtml.METS-1.0.item-files-viewOpen</i18n:text>
                        </a>
                    </xsl:otherwise>
                </xsl:choose>                        
            </td>
        </tr>
    </xsl:template>
    
    
    
    <!--
        Summary view for communities & collections is never used.
        -->
    <xsl:template name="collectionSummaryView_DS-METS-1.0-DIM">
        <i18n:text>xmlui.dri2xhtml.METS-1.0.collection-not-implemented</i18n:text>
    </xsl:template>
    
    <xsl:template name="communitySummaryView_DS-METS-1.0-DIM">
        <i18n:text>xmlui.dri2xhtml.METS-1.0.community-not-implemented</i18n:text>
    </xsl:template>
    
    
    
    
    
    
    <!-- 
        **************************************** 
        detailView                             
        
        The next set of templates all render objects: items, communities, and 
        collections in a detailView context. Detail view is used when viewing
        one item at a time, thus on the community or collection home page. Or
        when viewing an item and the user has clicked show all information
        about the item.
        
        **************************************** 
    -->
    
    
    <!-- Match detailView objects -->
    <xsl:template match="dri:objectInclude[key('DSMets1.0-DIM', @objectSource)]" mode="detailView">
       <xsl:apply-templates select="key('DSMets1.0-DIM', @objectSource)" mode="detailView"/>
       <xsl:apply-templates />
    </xsl:template>    
    
    <!--
        Determine which implementation is used based upon the object type.
     -->
    <xsl:template match="key('DSMets1.0-DIM-all', 'all')" mode="detailView">
        <xsl:choose>
            <xsl:when test="mets:METS/mets:structMap/mets:div[@TYPE='DSpace Item']">
                <xsl:call-template name="itemDetailView_DS-METS-1.0-DIM"/>
            </xsl:when>
            <xsl:when test="mets:METS/mets:structMap/mets:div[@TYPE='DSpace Collection']">
                <xsl:call-template name="collectionDetailView_DS-METS-1.0-DIM"/>
            </xsl:when>
            <xsl:when test="mets:METS/mets:structMap/mets:div[@TYPE='DSpace Community']">
                <xsl:call-template name="communityDetailView_DS-METS-1.0-DIM"/>
            </xsl:when>                
            <xsl:otherwise>
                <i18n:text>xmlui.dri2xhtml.METS-1.0.non-conformant</i18n:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!--
        The templates that handle the respective cases: item, collection, and community. In the case of items
        current Manakin build does really have a special use for detailList so the logic of summaryList is 
        basically used in its place. 
        
        This view is used when the user selects show full item on the view item page.      
     --> 
    <xsl:template name="itemDetailView_DS-METS-1.0-DIM">
        <xsl:variable name="data" select="./mets:METS/mets:dmdSec/mets:mdWrap/mets:xmlData/dim:dim"/>
        <xsl:variable name="context" select="."/>
        <xsl:apply-templates select="$data" mode="detailView"/>
        <h2><i18n:text>xmlui.dri2xhtml.METS-1.0.item-files-head</i18n:text></h2>
        <table class="ds-table file-list">
            <tr class="ds-table-header-row">
                <th><i18n:text>xmlui.dri2xhtml.METS-1.0.item-files-file</i18n:text></th>
                <th><i18n:text>xmlui.dri2xhtml.METS-1.0.item-files-size</i18n:text></th>
                <th><i18n:text>xmlui.dri2xhtml.METS-1.0.item-files-format</i18n:text></th>
                <th><i18n:text>xmlui.dri2xhtml.METS-1.0.item-files-view</i18n:text></th>
            </tr>
            <!-- First, figure out if there is a primary bitstream -->
            <xsl:variable name="primary" select="mets:METS/mets:structMap[@TYPE='LOGICAL']/mets:div[@TYPE='DSpace Item']/mets:fptr/@FILEID"/>
            <xsl:choose>
                <!-- If one exists and it's of text/html MIME type, only display the primary bitstream -->
                <xsl:when test="mets:METS/mets:fileSec/mets:fileGrp[@USE='CONTENT']/mets:file[@ID=$primary]/@MIMETYPE='text/html'">
                    <xsl:call-template name="buildBitstreamRow">
                        <xsl:with-param name="context" select="$context"/>
                        <xsl:with-param name="file" select="mets:METS/mets:fileSec/mets:fileGrp[@USE='CONTENT']/mets:file[@ID=$primary]"/>
                    </xsl:call-template>
                </xsl:when>
                <!-- Otherwise, iterate over and display all of them -->
                <xsl:otherwise>
                    <xsl:for-each select="mets:METS/mets:fileSec/mets:fileGrp[@USE='CONTENT']/mets:file">
                        <xsl:sort select="./mets:FLocat[@LOCTYPE='URL']/@xlink:title"/> 
                        <xsl:call-template name="buildBitstreamRow">
                            <xsl:with-param name="context" select="$context"/>
                        </xsl:call-template>
                    </xsl:for-each>
                </xsl:otherwise>
            </xsl:choose>
        </table>
        <xsl:if test="mets:METS/mets:fileSec/mets:fileGrp[@USE='CC-LICENSE' or @USE='LICENSE']">
            <div class="license-info">
                <p>xmlui.dri2xhtml.METS-1.0.license-text</p>
                <ul>
                    <xsl:if test="mets:METS/mets:fileSec/mets:fileGrp[@USE='CC-LICENSE']">
                        <li><a href="{mets:METS/mets:fileSec/mets:fileGrp[@USE='CC-LICENSE']/mets:file/
                            mets:FLocat[@xlink:title='license_text']/@xlink:href}">Creative Commons</a></li>
                    </xsl:if>
                    <xsl:if test="mets:METS/mets:fileSec/mets:fileGrp[@USE='LICENSE']">
                        <li><a href="{mets:METS/mets:fileSec/mets:fileGrp[@USE='LICENSE']/mets:file/
                            mets:FLocat[@xlink:title='license.txt']/@xlink:href}">Original License</a></li>
                    </xsl:if>
                </ul>
            </div>
        </xsl:if>
    </xsl:template>
    
    <!-- Render each item's descriptive metadata fields -->
    <xsl:template match="dim:dim" mode="detailView" priority="2">
		<table class="ds-includeSet-table">
		    <xsl:for-each select="dim:field">
                <tr>
                    <xsl:attribute name="class">
                        <xsl:text>ds-table-row </xsl:text>
                        <xsl:if test="(position() mod 2 = 0)">even </xsl:if>
                        <xsl:if test="(position() mod 2 = 1)">odd </xsl:if>
                    </xsl:attribute>
                    <td>
                        <xsl:value-of select="./@element"/>
                        <xsl:if test="./@qualifier">
                            <xsl:text>.</xsl:text>
                            <xsl:value-of select="./@qualifier"/>
                        </xsl:if>
                    </td>
                    <td><xsl:copy-of select="./child::node()"/></td>
                    <td><xsl:value-of select="./@language"/></td>
                </tr>
            </xsl:for-each>
		</table>
	</xsl:template>	
	
	    
    <!-- 
         Show a detailed view of a collection. This is used on the collection home page. It 
         adds in the full collection description. Notice that the collection's news and side bar
         fields are not displayed.
     -->
    <xsl:template name="collectionDetailView_DS-METS-1.0-DIM">
        <xsl:variable name="data" select="./mets:METS/mets:dmdSec/mets:mdWrap/mets:xmlData/dim:dim"/>
        <div class="detail-view">&#160;            
            <xsl:choose>
                <xsl:when test="mets:METS/mets:fileSec/mets:fileGrp[@USE='LOGO']">
                    <div class="ds-logo-wrapper">
                        <img>
                            <xsl:attribute name="src">
                                <xsl:value-of select="mets:METS/mets:fileSec/mets:fileGrp[@USE='LOGO']/mets:file/mets:FLocat[@LOCTYPE='URL']/@xlink:href"/>
                            </xsl:attribute>
                            <xsl:attribute name="alt">xmlui.dri2xhtml.METS-1.0.collectiob-logo-alt</xsl:attribute>
                            <xsl:attribute name="attr" namespace="http://apache.org/cocoon/i18n/2.1">alt</xsl:attribute>
                            <xsl:attribute name="class">logo</xsl:attribute>
                        </img>
                     </div>
                </xsl:when>
            </xsl:choose>
            <xsl:if test="string-length($data/dim:field[@element='description'][not(@qualifier)])&gt;0">
	            <p class="intro-text">
	                <xsl:copy-of select="$data/dim:field[@element='description'][not(@qualifier)]/node()"/>
	            </p>
            </xsl:if>
            <xsl:if test="string-length($data/dim:field[@element='rights'][not(@qualifier)])&gt;0">
	            <p class="copyright-text">
	                <xsl:copy-of select="$data/dim:field[@element='rights'][not(@qualifier)]/node()"/>
	            </p>
            </xsl:if>
            <xsl:if test="string-length($data/dim:field[@element='rights'][@qualifier='license'])&gt;0">
	            <p class="license-text">
	                <xsl:copy-of select="$data/dim:field[@element='rights'][@qualifier='license']/node()"/>
	            </p>
            </xsl:if>
        </div>
    </xsl:template>
    
    <!-- 
        Show a detailed view of a community. This is used on the community homepage. 
     -->
    <xsl:template name="communityDetailView_DS-METS-1.0-DIM">
        <xsl:variable name="data" select="./mets:METS/mets:dmdSec/mets:mdWrap/mets:xmlData/dim:dim"/>
        <div class="detail-view">&#160;
            <xsl:choose>
                <xsl:when test="mets:METS/mets:fileSec/mets:fileGrp[@USE='LOGO']">
                    <div class="ds-logo-wrapper">
                        <img>
                            <xsl:attribute name="src">
                                <xsl:value-of select="mets:METS/mets:fileSec/mets:fileGrp[@USE='LOGO']/mets:file/mets:FLocat[@LOCTYPE='URL']/@xlink:href"/>
                            </xsl:attribute>
                            <xsl:attribute name="alt">xmlui.dri2xhtml.METS-1.0.community-logo-alt</xsl:attribute>
                            <xsl:attribute name="attr" namespace="http://apache.org/cocoon/i18n/2.1">alt</xsl:attribute>
                            <xsl:attribute name="class">logo</xsl:attribute>
                        </img>
                    </div>
                </xsl:when>
            </xsl:choose>
            <xsl:if test="string-length($data/dim:field[@element='description'][not(@qualifier)])&gt;0">
	            <p class="intro-text">
	                <xsl:copy-of select="$data/dim:field[@element='description'][not(@qualifier)]/node()"/>
	            </p>
            </xsl:if>
            <xsl:if test="string-length($data/dim:field[@element='rights'][not(@qualifier)])&gt;0">
	            <p class="copyright-text">
	                <xsl:copy-of select="$data/dim:field[@element='rights'][not(@qualifier)]/node()"/>
	            </p>
            </xsl:if>
        </div>
    </xsl:template>
    
</xsl:stylesheet>
