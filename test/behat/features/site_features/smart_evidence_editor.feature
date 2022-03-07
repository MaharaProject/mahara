@javascript @core @smartevidence
Feature: SmartEvidence editor
    As a site administrator
    I want to edit or copy a framework matrix

Background:
    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm |
    | instone | Institution One | ON | OFF |

Scenario: Site administrator uploads and edits a SmartEvidence framework matrix
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "SmartEvidence" in "Extensions" from administration menu
    And I click on "Import" in the "Arrow-bar nav" "Nav" property
    And I attach the file "example.matrix" to "Matrix file"
    And I click on "Upload matrix"
    # Check that we have new framework
    Then I should see "Title of your framework"
    When I click on "Edit" in "Title of your framework" row
    And I disable the switch "Active framework"
    And I click on "Save"
    Then I should see "Settings saved"
    When I click on "Editor" in the "Arrow-bar nav" "Nav" property
    When I select "Title of your framework" from "Edit saved framework"
    And I wait "1" seconds
    And the SE field "root.name" should contain "Title of your framework"
    And I set the SE field "root.name" to "Fish"
    And the SE field "root.name" should not contain "Title of your framework"
    And the SE field "root.institution" should contain "all"
    And I set the SE field "root.institution" to "Institution One"
    And the SE field "root.evidencestatuses.begun" should contain "Ready for assessment"
    And the SE field "root.standards.0.name" should contain "Title of the standard"
    And I set the SE field "root.standards.0.name" to "Standard One"
    And the SE field "root.description" should contain "You can write more in the description"
    And I set the SE field "root.description" to "This is my new description"
    And the SE field "root.selfassess" should contain "No"
    And I click on "Add" in the "h3:contains('Standards') + button + span" "css_element"
    And the SE field "root.standards.4.shortname" should contain "Short name"
    And I set the SE field "root.standards.4.shortname" to "New standard"
    # Not working yet
    # And I click on "Delete last standard" delete button
    And I click on "1.2"
    And the SE field "root.standardelements.1.name" should contain "1.2 - Sub level of the standard"
    And I scroll to the top
    And I click on "Save"
    And I click on "Management" in the "Arrow-bar nav" "Nav" property
    Then I should see "Fish"
    Then I should see "Institution One"
