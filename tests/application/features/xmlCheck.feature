# features/xmlCheck.feature
@javascript
Feature: Test that xml is well formed

  @jet
  Scenario: I go to XML pages and check the XML is mostly well formed
  Given I turn off waiting checks
  And I am on "/oai.php?verb=ListRecords&metadataPrefix=rif"
  And I check the current page is valid XML
  And I am on "/oai.php?verb=ListRecords&metadataPrefix=oai_dc"
  And I check the current page is valid XML
  #And I follow "oai_dc" # disable because firefox doesnt want to render xsl anymore
  #And I check the current page is valid XML
  And I am on "/oai.php?verb=ListRecords&metadataPrefix=oai_dc"
  #And I follow "formats"
  #And I check the current page is valid XML
  #And I am on "/oai.php?verb=ListRecords&metadataPrefix=oai_dc"
  #And I follow "Identifiers"
  #And I check the current page is valid XML
  #And I am on "/oai.php?verb=ListRecords&metadataPrefix=oai_dc"
  #And I follow "Records"
  #And I check the current page is valid XML
  And I am on "/oai.php?verb=Identify"
  And I check the current page is valid XML
  And I am on "/oai.php?verb=ListSets"
  And I check the current page is valid XML
  And I am on "/oai.php?verb=ListMetadataFormats"
  And I check the current page is valid XML
  And I am on "/oai.php?verb=ListIdentifiers&metadataPrefix=oai_dc"
  And I check the current page is valid XML
  And I am on "/list/?cat=quick_filter&sort_by=searchKey0&&tpl=3"
  And I check the current page is valid XML
  And I am on "/list/?cat=quick_filter&sort_by=searchKey0&&tpl=2"
  And I check the current page is valid XML
  #this checks direct fedora file access is working ok, fedora xml is possibly not valid
  #And I login as administrator
  #And I go to the test journal article view page
  #And I follow "Preservation metadata"
  #And I should see text "xml" in code
