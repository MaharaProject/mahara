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
    | Page UserA_00 | Page 01 | user | UserA |
    | Page UserA_01 | Page 01 | user | UserA |
    | Page UserA_02 | Page 02 | user | UserA |

    And the following "permissions" exist:
    | title | accesstype | accessname | role | multiplepermissions |
    | Page UserA_00 | user | userB | peer | 0 |
    | Page UserA_00 | user | userC | manager | 1 |
    | Page UserA_00 | user | userD | | 1 |
    | Page UserA_01 | user | userB | peer | 0 |
    | Page UserA_01 | user | userC | manager | 1 |
    | Page UserA_01 | user | userD | | 1 |
    | Page UserA_02 | user | userB | peer | 0 |
    | Page UserA_02 | user | userC | manager | 1 |
    | Page UserA_02 | user | userD | | 1 |

# Create Page UserA_00 with Image, text, peer assessment and signoff blocks
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_00"
    And I click on "Edit"

    # User adds image block to page
   And I follow "Image"
   And I press "Add"
   Then I should see "Image: Configure"
   And I set the field "Block title" to "Image Block 1"
   And I follow "Image"
   And I attach the file "Image1.jpg" to "File"
   Then I should see "Image - Image1.jpg"
   And I enable the switch "Show description"
   And I press "Save"
   And I scroll to the top

   # User adds a text block to page
   And I follow "Text"
   And I press "Add"
   And I set the field "Block title" to "Text Block 1"
   And I set the field "Block content" to "Here is a new block."
   And I press "Save"

   # user adds peer assessment block
   And I expand "General" node in the "blocktype sidebar" property
    And I follow "Peer assessment"
    And I press "Add"
    And I set the field "Instructions" to "This is the custom peer assessment instructions"
    And I press "Save"

    # user adds sign off block
    And I follow "Sign-off"
    And I press "Add"
    And I enable the switch "Verify"
    And I press "Save"
    Then I should see "This block's content is displayed below the page heading rather than in a block itself on the page"
   And I follow "Return to pages and collections"

# Create Page UserA_01 with Image, text, peer assessment and signoff blocks
    And I go to portfolio page "Page UserA_01"
    And I click on "Edit"

    # User adds image block to page
   And I follow "Image"
   And I press "Add"
   Then I should see "Image: Configure"
   And I set the field "Block title" to "Image Block 1"
   And I follow "Image"
   And I attach the file "Image1.jpg" to "File"
   Then I should see "Image - Image1.jpg"
   And I enable the switch "Show description"
   And I press "Save"
   And I scroll to the top

   # User adds a text block to page
   And I follow "Text"
   And I press "Add"
   And I set the field "Block title" to "Text Block 1"
   And I set the field "Block content" to "Here is a new block."
   And I press "Save"

   # user adds peer assessment block
   And I expand "General" node in the "blocktype sidebar" property
    And I follow "Peer assessment"
    And I press "Add"
    And I set the field "Instructions" to "This is the custom peer assessment instructions"
    And I press "Save"

    # user adds sign off block
    And I follow "Sign-off"
    And I press "Add"
    And I enable the switch "Verify"
    And I press "Save"
    Then I should see "This block's content is displayed below the page heading rather than in a block itself on the page"
   And I follow "Return to pages and collections"

    # Create Page UserA_02 with Image and text, blocks
    And I go to portfolio page "Page UserA_02"
    And I click on "Edit"

    # User adds image block to page
    And I follow "Image"
    And I press "Add"
    Then I should see "Image: Configure"
    And I set the field "Block title" to "Image Block 1"
    And I follow "Image"
    And I attach the file "Image1.jpg" to "File"
    Then I should see "Image - Image1.jpg"
    And I enable the switch "Show description"
    And I press "Save"
    And I scroll to the top

    # User adds a text block to page
    And I follow "Text"
    And I press "Add"
    And I set the field "Block title" to "Text Block 1"
    And I set the field "Block content" to "Here is a new block."
    And I press "Save"
    And I log out

    Given the following "collections" exist:
    | title | description| ownertype | ownername | pages |
    | Collection UserA_01 | Collection 01 | user | UserA | Page UserA_01, Page UserA_02 |

Scenario: Interact with a peer assessment / signoff combo for a single page
    # Add peer assessment
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_00"
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
    And I click on "Yes" in the "Signoff page" property
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
    And I click on "Yes" in the "Verify page" property
    Then I should see "Verification status updated"
    And I log out

Scenario: Interact with a peer assessment / signoff combo on a collection
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I wait "1" seconds
    And I follow "Collection UserA_01"
    And I should see "Add peer assessment"
    When I press "Next page"
    Then I should see "You cannot see the content on this page because it does not require a peer assessment."
    And I log out
