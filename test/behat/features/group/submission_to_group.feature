@javascript @core @group
Feature: Mahara users submit pages / colelctions to a group
  As a mahara user
  I need to submit content to the group
  As a group admin user
  I need to see who has and has not submitted content

  Background:
    Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | groupAdmin | Kupuhipa1 | grpadmin@example.com | Group | Admin | mahara | internal | admin |
      | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
      | userB | Kupuhipa1 | test02@example.com | Son | Nguyen | mahara | internal | member |
      | userC | Kupuhipa1 | test03@example.com | Jack | Smith | mahara | internal | member |

    And the following "groups" exist:
      | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
      | Group one | groupAdmin | This is group 01 | standard | ON | ON | all | ON | OFF | userA, userB, userC | |

    And the following "pages" exist:
      | title | description| ownertype | ownername |
      | Page userA_01 | This is the page 01 | user | userA |
      | Page userA_02 | This is the page 02 | user | userA |
      | Page userA_03 | This is the page 03 | user | userA |
      | Page userB_01 | This is the page 04 | user | userB |
      | Page userB_02 | This is the page 05 | user | userB |
      | Page userC_01 | This is the page 06 | user | userC |
      | Page userC_02 | This is the page 07 | user | userC |

    And the following "collections" exist:
      | title | description| ownertype | ownername | pages |
      | Collection userA_01 | This is the collection 01 | user | userA | Page userA_01, Page userA_02 |
      | Collection userA_02 | This is the collection 02 | user | userA | Page userA_03 |
      | Collection userB_01 | This is the collection 03 | user | userB | Page userB_01 |
      | Collection userC_01 | This is the collection 05 | user | userC | Page userC_01 |

  Scenario: Group users submit content to the group and group admin checks who is still to submit content
    # UserA submits a collection to the group
    Given I log in as "userA" with password "Kupuhipa1"
    And I choose "Find groups" in "Groups" from main menu
    When I click on "Group one"
    And I scroll to the base of id "groupviewlist"
    And I select "Collection userA_01" from "Submit for assessment"
    And I press "Submit"
    And I press "Yes"
    Then I should see "Collection submitted"
    And I log out

    # UserB submits a page to the group
    Given I log in as "userB" with password "Kupuhipa1"
    And I choose "Find groups" in "Groups" from main menu
    When I click on "Group one"
    And I scroll to the base of id "groupviewlist"
    And I select "Page userB_02" from "Submit for assessment"
    And I press "Submit"
    And I press "Yes"
    Then I should see "Page submitted"
    And I log out

    # UserC didn't submit anything so should appear on the need to do submissions list
    Given I log in as "groupAdmin" with password "Kupuhipa1"
    And I choose "Find groups" in "Groups" from main menu
    When I click on "Group one"
    And I scroll to the base of id "groupviewlist"
    Then I should see "Jack Smith" in the "ul#nosubmissionslist" "css_element"
