@javascript @core @blocktype @blocktype_pdf
Feature: Adding a pdf to a page
    As a student
    I need to be able to add a pdf block to my portfolio
    and check it is visible on the page

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |

    And the following "pages" exist:
    | title | description| ownertype | ownername |
    | Page 1 | page P1 | user | userA |

Scenario:
    # Logging in as a user
    Given I log in as "userA" with password "Kupuhipa1"
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I click on "Page 1" panel menu
    And I click on "Edit" in "Page 1" panel menu
    # Configuring the block
    And I expand "Media" node
    And I follow "PDF" in the "div#fileimagevideo" "css_element"
    And I press "Add"
    And I fill in the following:
    | Block title | Mahara about PDF |

    And I follow "File"
    And I attach the file "mahara_about.pdf" to "File"
    And I press "Save"
    And I display the page
    Then I should see "Mahara about PDF"
