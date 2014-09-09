# This is for CDU specific document types.

Feature: creating laal records

  # Laal collection (Anindilyakwa):
  # http://cdu.local/collection/cdu:29713
  # 
  # Create laal item:
  # /workflow/new.php?collection_pid=cdu:29713&xdis_id=360&xdis_id_top=360&wft_id=346

  @create-laal-cdu
  Scenario: Creating a laal type document for CDU
    Given I'm an editor
    Given 'laal.collection' has group 'editor_group' assigned to role 'editor'
    When starting a new record workflow for collection in: laal
    Then using the available required fields in the xml
    Then get uri for the action 'Save'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '202'


