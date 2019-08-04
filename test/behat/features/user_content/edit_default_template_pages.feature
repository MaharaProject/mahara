@javascript @core @core_administration
Feature: Site admin includes special blocks for the "Profile" site template and the "Dashboard" site template Bug (1805766)
    1. Site admin set up "Dashboard template" should include the following:
    --- a. My friends block
    --- b. My Groups block
    --- c. My Portfolios block (already on template by default)
    --- d. Watched pages block (already on template by default)
    2. Site admin verify "Profile template" includes "Wall" block which is already on template by default
    3. Verify that only one block of a certin type can be added to a template page

  Scenario: Site admin Site admin set up "Dashboard template" to include the following:
    --- a. My friends block
    --- b. My Groups block
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Configure site" from administration menu
    # Confirm 4 default templates are displayed
    Then I should see "Dashboard template"
    And I should see "Group homepage template"
    And I should see "Profile template"
    And I should see "Page template"
    When I click on "Edit" in "Dashboard template" card menu
    # add "My friends" block and verify it is displayed on the page
    When I follow "Add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on "Show more"
    And I click on "Show more"
    And I click on "Show more"
    And I should see "My portfolios" in the "Content types" property
    And I should see "Watched pages" in the "Content types" property
    And I click on "My friends" in the "Content types" property
    Then I should see "My friends" in the "#column-container" "css_element"
    # add "My groups" block and verify it is displayed on the page
    When I follow "Add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on "Show more"
    And I click on "Show more"
    And I click on "Show more"
    And I click on "My groups" in the "Content types" property
    And I press "Save"

    # Site admin Site admin set up "Profile template" to include the following:
    Given I follow "Return to site pages and collections"
    And I click on "Edit" in "Profile template" card menu
    # Verify that only one block of a certin type can be added to a template page  (ie only 1 Wall block)
    When I follow "Add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on "Show more"
    And I click on "Show more"
    And I should see "Wall" in the "Content types" property
    And I click on "Wall" in the "Content types" property
    And I press "Save"
    Then I should see "Cannot put more than one wall block type into a page."
