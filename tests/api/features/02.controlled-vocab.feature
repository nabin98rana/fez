
# This is for the purpose of getting the right cvo id to use when creating a record.

Feature: viewing controlled vocabs

  @contvocab
  Scenario: getting a controlled vocab item
    Given I'm a super administrator
    Given getting a record 'doc-with-cont-vocab.record'
    When viewing the workflow 'Update Selected Record - Generic'
    # Should be: When GETing the uri for display field 'Subjects'
    When GETing the uri for display field 'Subjects'
    Then I should get xml
    # a cv element
    And I should see element 'element'

# For reference the cv_selector url is something like
# http://cdu.local/cv_selector.php?parent_id=1&form=wfl_form2&element=sd&xsdmf_cvo_min_level=0
# The parameter that is important is the parent id. Without it an empty response of <cv_tree></cv_tree> is returned.