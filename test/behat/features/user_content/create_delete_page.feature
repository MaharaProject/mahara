@javascript @core @portfolio
Feature: Creating a page with stuff in it
   In order to have a portfolio
   As a user I need navigate to a portfolio
   So I can create a page and add content to it

Background:
    Given the following "users" exist:
    | username | password  | email             | firstname | lastname | institution | authname | role   |
    | UserA    | Kupuh1pa! | UserA@example.org | Angela    | User     | mahara      | internal | member |

    And the following "blocks" exist:
    | title                     | type     | page                   | retractable | updateonly | data                                                |
    | Portfolios shared with me | newviews | Dashboard page: UserA  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |

Scenario: Creating a page with content in it (Bug 1426983)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Files" in "Create" from main menu
    # Navigating to Portfolio to create a page
    # This is the test for manually creating a page
    And I choose "Portfolios" in "Create" from main menu
    And I scroll to the base of id "addview-button"
    And I should see "Portfolios" in the "H1 heading" "Common" property
    And I click on "Create" in the "Create" "Views" property
    And I click on "Page" in the dialog
    And I fill in the following:
    | Page title       | Test view         |
    | Page description | First description |
    # Open the 'Advanced' accordion and check for the instructions field and 'Prevent removing of blocks' toggle
    # (Bug 1891265)
    When I click on "Advanced"
    Then I should see "Instructions"
    And I should see "Prevent removing of blocks"
    # (Bug 1891265 end)
    And I click on "Save"
    # Editing the pages
    And I click on "Configure" in the "Toolbar buttons" "Nav" property
    #Change the Page title
    And I fill in the following:
    | Page title       | This is the edited page title |
    | Page description | This is the edited description |
    And I click on "Save"
    # Adding media block
    When I click on the add block button
    And I click on "Add" in the "Add new block" "Blocks" property
    And I click on blocktype "Files"
    And I click on "Save" in the "Submission" "Modal" property
    # Adding Journal block
    When I click on the add block button
    And I click on "Add" in the "Add new block" "Blocks" property
    And I click on blocktype "Recent journal entries"
    And I click on "Save" in the "Submission" "Modal" property
    # Adding profile info block
    When I click on the add block button
    And I click on "Add" in the "Add new block" "Blocks" property
    And I click on blocktype "Profile information"
    And I click on "Save" in the "Submission" "Modal" property
    # Adding external media block - but remove it instead
    When I click on the add block button
    And I click on "Add" in the "Add new block" "Blocks" property
    And I click on blocktype "External media"
    And I click on "Remove" in the "Submission" "Modal" property

    And I display the page
    # Show last updated date and time when seeing a portfolio page (Bug 1634591)
    And I should see "Updated on" in the "Last updated" "Views" property
    # actual date format displayed is 31 May 2018, 13:29
    And I should see the date "today" in the "Last updated" "Views" property with the format "d F Y"
    # Verifying the page title and description changed
    Then I should see "This is the edited page title"
    And I should not see "This is the edited description"
    # Create a timeline version
    And I click on "More options"
    And I click on "Save to timeline"
    # Check that the image is displayed on page and ensure the link is correct
    #Then I should see image "Image2.png" on the page
    # The "..." button should only have the option to print and delete the page
    And I should see "More options"
    And I click on "More options"
    Then I should see "Print"
    And I should see "Delete this page"
    # User share page with public and enable copy page functionality
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Manage sharing" in "This is the edited page title" card access menu
    And I click on "Advanced options"
    And I enable the switch "Allow copying"
    And I collapse "Advanced options" node
    And I scroll to the center of id "row-0"
    And I select "Public" from "General" in shared with select2 box
    And I click on "Save"
    And I log out

    # Log in as UserA and copy the page
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I wait "1" seconds
    Then I should see "This is the edited page title"
    When I click on "This is the edited page title"
    And I click on "More options"
    And I click on "Copy"
    And I fill in the following:
    | Page title | This is my page now |
    And I click on "Save"
    And I click on "Display page"
    And I should not see "This is the edited description"
    And I log out

    # check page can be deleted (Bug 1755682)
    Given I log in as "admin" with password "Kupuh1pa!"
    # Go to version page
    And I choose "Portfolios" in "Create" from main menu
    And I click on "This is the edited page title"
    And I click on "More options"
    And I click on "Timeline"

    Then I should see "Timeline"
    # check page can be deleted (Bug 1755682)
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Delete" in "This is the edited page" card menu
    And I should see "Do you really want to delete this page?"
    And I click on "Yes"
    Then I should see "Page deleted"
    And I should not see "This is the edited page"
