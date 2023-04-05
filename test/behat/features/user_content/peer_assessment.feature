@javascript @core @blocktype @blocktype_peerassessment
Feature: Interacting with the peer assessment and signoff config/functionality
    As an author
    I want to add a peer assessment and signoff switch to my page
    So I can get peer assessment before signing off the page
    As a peer
    I want to add a peer assessment to the page
    As a manager
    I want to verify the page

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela  | User | mahara | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org | Bob     | User | mahara | internal | member |
    | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | mahara | internal | member |
    | UserD | Kupuh1pa! | UserD@example.org | Dmitri  | User | mahara | internal | member |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_00 | Page 01 | user | UserA |
    | Page UserA_01 | Page 01 | user | UserA |
    | Page UserA_02 | Page 02 | user | UserA |
    | Page UserA_03 | Page 03 | user | UserA |

    And the following "blocks" exist:
    | title              | type           | page          | retractable | data                  |
    | Image Block 1      | image          | Page UserA_00 | no | attachemnt=Image1.jpg;showdescription=true |
    | Text Block 1       | text           | Page UserA_00 | no | textinput=Here is a new block. |
    | Peer Assessment    | peerassessment | Page UserA_00 | no | instructions=This is the custom peer assessment instructions |
    | Image Block 2      | image          | Page UserA_01 | no | attachemnt=Image1.jpg;showdescription=true |
    | Text Block 2       | text           | Page UserA_01 | no | textinput=Here is a new block. |
    | Peer Assessment    | peerassessment | Page UserA_01 | no | instructions=This is the custom peer assessment instructions |
    | Image Block 3      | image          | Page UserA_02 | no | attachemnt=Image1.jpg;showdescription=true |
    | Text Block 3       | text           | Page UserA_02 | no | textinput=Here is a new block. |
    | Image Block 4      | image          | Page UserA_03 | no | attachemnt=Image1.jpg;showdescription=true |
    | Text Block 4       | text           | Page UserA_03 | no | textinput=This is some text. |
    | Peer Assessment    | peerassessment | Page UserA_03 | no | instructions=This is the custom peer assessment instructions for Page UserA_03 |

    And the following "permissions" exist:
    | title         | accesstype | accessname | role    | multiplepermissions |
    | Page UserA_00 | user       | userB      | peer    | 0                   |
    | Page UserA_00 | user       | userC      | manager | 1                   |
    | Page UserA_01 | user       | userB      | peer    | 0                   |
    | Page UserA_01 | user       | userC      | manager | 1                   |
    | Page UserA_02 | user       | userB      | peer    | 0                   |
    | Page UserA_02 | user       | userC      | manager | 1                   |

    Given the following "collections" exist:
    | title | description| ownertype | ownername | pages |
    | Collection UserA_01 | Collection 01 | user | UserA | Page UserA_01, Page UserA_02 |

    # Add signoff block to Page UserA_00
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_00"
    Then I click on "Edit"
    And I click on "Configure" in the "Toolbar buttons" "Nav" property
    When I click on "Advanced"
    And I enable the switch "Sign-off"
    And I enable the switch "Verify"
    And I click on "Save"
    And I click on "Return to portfolios"
    And I go to portfolio page "Page UserA_01"
    Then I click on "Edit"
    # Author adds sign off switch
    And I click on "Configure" in the "Toolbar buttons" "Nav" property
    When I click on "Advanced"
    And I enable the switch "Sign-off"
    And I enable the switch "Verify"
    And I click on "Save"
    Then I click on "Display"
    Then I should see "Mark this page as 'Signed off' when you have finished adding all your evidence."
    # Add peer assessment and signoff switch to Page Page UserA_03
    Given I go to portfolio page "Page UserA_03"
    And I click on "Edit"
    # Author adds sign off switch
    And I click on "Configure" in the "Toolbar buttons" "Nav" property
    When I click on "Advanced"
    And I enable the switch "Sign-off"
    And I enable the switch "Verify"
    And I click on "Save"
    When I click on "Display"
    Then I click on "More options"
    # Then I should see "This block's content is displayed aligned to the right hand side. The block is best placed at top right of the page."
    And I click on "Return to portfolios"
    # share the page with people and give a role
    When I choose "Shared by me" in "Share" from main menu
    And I click on "Pages" in the "Share tabs" "Misc" property
    And I click on "Share" in "Page UserA_03" row
    And I select "Person" from "accesslist[0][searchtype]"
    And I select "Dmitri User" from select2 nested search box in row number "1"
    And I select "Peer and manager" from "accesslist[0][role]"
    And I click on "Save"
    And I log out

Scenario: Log in as UserB with role of Peer and Interact with a peer assessment / signoff combo for a single page
    # Add peer assessmentimage jpg
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_00"
    And I click on "Add peer assessment"
    And I expand "Instructions" node
    Then I should see "This is the custom peer assessment instructions"
    And I set the field "Assessment" to "UserB first assessment - draft"
    And I click on "Save draft"
    And I click on "Add peer assessment"
    And I set the field "Assessment" to "UserB second assessment - published"
    And I click on "Publish"
    And I log out

    # Verify owner can only see published assessment
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_00"
    And I should not see "UserB first assessment - draft"
    And I should see "UserB second assessment - published"
    And I log out

    # Verify manager can't see any assessment when page is not signed off
    Given I log in as "UserC" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_00"
    And I should not see "UserB first assessment - draft"
    And I should see "UserB second assessment - published"
    And I log out

    # Owner signs off the page
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_00"
    And I click on "Update page sign-off"
    And I click on "Yes" in the "Signoff page" "Peerassessment" property
    Then I should see "Sign-off status updated"
    And I log out

    # Verify peer can't add any assessment after page is signed off
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_00"
    Then I should not see "Add peer assessment"
    And I log out

    # Verify manager can see published assessment when page is signed off
    Given I log in as "UserC" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_00"
    And I should see "UserB second assessment - published"
    And I click on "Update page verification"
    And I click on "Yes" in the "Verify page" "Peerassessment" property
    Then I should see "Verification status updated"
    And I log out

# Log in as UserD with role of Peer and Manager verify they can see published content and make an assessment
    Given I log in as "UserD" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_03"
    And I should see "This is some text"
    And I click on "Add peer assessment"
    And I expand "Instructions" node
    Then I should see "This is the custom peer assessment instructions"
    And I set the field "Assessment" to "UserB first assessment - draft"
    And I click on "Save draft"
    And I click on "Add peer assessment"
    And I set the field "Assessment" to "UserD second assessment - published"
    And I click on "Publish"
    And I log out

Scenario: Log in as UserB with the role of Peer and Interact with a peer assessment / signoff combo on a collection page that has no peer assessment block
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I wait "1" seconds
    And I click on "Collection UserA_01"
    And I should see "Add peer assessment"
    When I click on "Next page"
    Then I should see "You cannot see the content on this page because it does not require a peer assessment."
    And I log out
