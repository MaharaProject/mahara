@javascript @core @blocktype @blocktype_peerassessment @blocktype_signoff
Feature: Interacting with the peer assessment and signoff blocks
    As a user
    I want to add a peer assessment and signoff block to my page
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
    | Page UserA_01 | Page 01 | user | UserA |

    And the following "permissions" exist:
    | title | accesstype | accessname | role | multiplepermissions |
    | Page UserA_01 | user | userB | peer | 0 |
    | Page UserA_01 | user | userC | manager | 1 |
    | Page UserA_01 | user | userD | | 1 |

Scenario: Setup and interact with a peer assessment / signoff combo
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_01"
    And I click on "Edit"
    And I expand "General" node in the "blocktype sidebar" property
    And I follow "Peer assessment"
    And I press "Add"
    And I set the field "Instructions" to "This is the custom peer assessment instructions"
    And I press "Save"
    And I follow "Sign-off"
    And I press "Add"
    And I enable the switch "Verify"
    And I press "Save"
    Then I should see "This block's content is displayed below the page heading rather than in a block itself on the page"
    And I log out

    # Add peer assessment
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_01"
    And I follow "Add peer assessment"
    And I expand "Instructions" node
    Then I should see "This is the custom peer assessment instructions"
    And I set the field "Assessment" to "UserB first assessment - draft"
    And I press "Save draft"
    And I follow "Add peer assessment"
    And I set the field "Assessment" to "UserB second assessment - published"
    And I press "Publish"
    And I log out

    # Verify owner can only see published assessment
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_01"
    And I should not see "UserB first assessment - draft"
    And I should see "UserB second assessment - published"
    And I log out

    # Verify manager can't see any assessment when page is not signed off
    Given I log in as "UserC" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_01"
    And I should not see "UserB first assessment - draft"
    And I should see "UserB second assessment - published"
    And I log out

    # Owner signs off the page
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_01"
    And I click on "Update page sign-off"
    And I click on "Yes" in the "Signoff page" property
    Then I should see "Sign-off status updated"
    And I log out

    # Verify peer can't add any assessment after page is signed off
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_01"
    Then I should not see "Add peer assessment"
    And I log out

    # Verify manager can see published assessment when page is signed off
    Given I log in as "UserC" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_01"
    And I should see "UserB second assessment - published"
    And I click on "Update page verification"
    And I click on "Yes" in the "Verify page" property
    Then I should see "Verification status updated"
    And I log out
