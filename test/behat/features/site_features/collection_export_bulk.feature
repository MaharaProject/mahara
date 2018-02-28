@javascript @core @core_artefact
Feature: Mahara users can export collections with bulk option
  As a Mahara user
  I want to export collections in bulk
  So that I can have the same options of exporting as I when exporting pages.

Background:
Given the following "users" exist:
  | username | password | email | firstname | lastname | institution | authname | role |
  | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

And the following "pages" exist:
  | title | description | ownertype | ownername |
  | Page UserA_01 | Page 01 | user | UserA |
  | Page UserA_02 | Page 02 | user | UserA |
  | Page UserA_03 | Page 02 | user | UserA |

And the following "collections" exist:
  | title | description| ownertype | ownername | pages |
  | Collection UserA_01 | Collection 01 | user | UserA | Page UserA_01 |
  | Collection UserA_02 | Collection 02 | user | UserA | Page UserA_02 |
  | Collection UserA_03 | Collection 02 | user | UserA | Page UserA_03 |

Scenario: Export collections in bulk as HTML
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I choose "Export" in "Portfolio" from main menu
  # this tests the page default option "Standalone HTML website"
  When I select the radio "Just some of my collections"
  Then I should see "Select all"
  Then I should see "Reverse selection"
  When I follow "selection_all_collections"
  Then the "Collection UserA_01" checkbox should be checked
  And the "Collection UserA_02" checkbox should be checked
  And the "Collection UserA_03" checkbox should be checked
  When I follow "selection_reverse_collections"
  Then the "Collection UserA_01" checkbox should not be checked
  And the "Collection UserA_02" checkbox should not be checked
  And the "Collection UserA_03" checkbox should not be checked
  When I click on "Generate export"
  Then I should see "You must select at least one collection to export"
  And I should see "There was an error with submitting this form. Please check the marked fields and try again."

Scenario: Export collections in bulk as Leap2A
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I choose "Export" in "Portfolio" from main menu
  # this tests the Leap2A export
  When I select the radio "Leap2A"
  And I select the radio "Just some of my collections"
  Then I should see "Select all"
  Then I should see "Reverse selection"
  When I follow "selection_all_collections"
  Then the "Collection UserA_01" checkbox should be checked
  And the "Collection UserA_02" checkbox should be checked
  And the "Collection UserA_03" checkbox should be checked
  When I follow "selection_reverse_collections"
  Then the "Collection UserA_01" checkbox should not be checked
  And the "Collection UserA_02" checkbox should not be checked
  And the "Collection UserA_03" checkbox should not be checked
  When I follow "selection_all_collections"
  Then the "Collection UserA_01" checkbox should be checked
  And the "Collection UserA_02" checkbox should be checked
  And the "Collection UserA_03" checkbox should be checked
  When I click on "Generate export"
  Then I should see "Please wait while your export is being generated..."
