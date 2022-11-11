@javascript @core @group
Feature: Mahara users submit pages / colelctions to a group
  As a mahara user
  I need to submit content to the group
  As a group admin user
  I need to see who has and has not submitted content

  Background:
    Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | groupAdmin | Kupuh1pa! | groupAdmin@example.org | Group | Admin | mahara | internal | admin |
      | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
      | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
      | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | mahara | internal | member |

    And the following "groups" exist:
      | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
      | GroupA | groupAdmin | GroupA owned by groupAdmin | standard | ON | ON | all | ON | OFF | UserA, UserB, UserC | |

    And the following "pages" exist:
      | title | description | ownertype | ownername |
      | Page UserA_01 | Page 01| user | UserA |
      | Page UserA_02 | Page 02 | user | UserA |
      | Page UserA_03 | Page 03 | user | UserA |
      | Page UserB_01 | Page 04 | user | UserB |
      | Page UserB_02 | Page 05 | user | UserB |
      | Page UserC_01 | Page 06 | user | UserC |
      | Page UserC_02 | Page 07 | user | UserC |

     And the following "blocks" exist:
    | title                | type           | page             |retractable | data |
    | Text                 | text           | Page UserA_01    | yes        | textinput=This is some text;tags=texttag |
    | Image JPG            | image          | Page UserA_01    | no         | attachment=Image1.jpg; width=100;tags=imagetag |

    And the following "collections" exist:
      | title | description | ownertype | ownername | pages |
      | Collection UserA_01 | Collection 01 | user | UserA | Page UserA_01, Page UserA_02 |
      | Collection UserA_02 | Collection 02 | user | UserA | Page UserA_03 |
      | Collection UserB_01 | Collection 03 | user | UserB | Page UserB_01 |
      | Collection UserC_01 | Collection 05 | user | UserC | Page UserC_01 |

  Scenario: Group users submit content to the group (and shouldn't be able to make edits/quick edit) and group admin checks who is still to submit content
    # UserA submits a collection to the group
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    When I click on "GroupA"
    And I scroll to the base of id "groupviewlist"
    And I select "Collection UserA_01" from "Submit for assessment"
    And I click on "Submit"
    And I click on "Yes"
    Then I should see "Portfolio submitted"
    And I click on "view your submission"
    And I click on "Details"
    And I should not see "Quick edit"
    And I log out

    # UserB submits a page to the group
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    When I click on "GroupA"
    And I scroll to the base of id "groupviewlist"
    And I select "Page UserB_02" from "Submit for assessment"
    And I click on "Submit"
    And I click on "Yes"
    Then I should see "Portfolio submitted"
    And I log out

    # UserC didn't submit anything so should appear on the need to do submissions list
    Given I log in as "groupAdmin" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    When I click on "GroupA"
    And I scroll to the base of id "nosubmissionslist"
    Then I should see "Cecilia User" in the "Members without a submission to the group" "Groups" property
