@javascript @core @core_view @core_portfolio
Feature: Contextual helps for Mahara pages
  In order to see a help message about a mahara page
  As an student
  So I can click the (i) icon next to the page title to get help about the page

Background:
  Given the following "users" exist:
  | username | password | email | firstname | lastname | institution | authname | role |
  | UserA | Kupuh1pa! | test01@example.com | Angela | User | mahara | internal | member |

  # Skins need to be enabled
  And the following site settings are set:
  | field | value |
  | skins | 1 |

  And the following "pages" exist:
  | title | description| ownertype | ownername |
  | Page 01 | UserA's page 01 | user | UserA |

  And the following "groups" exist:
  | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
  | GroupA | admin | GroupA owned by admin | standard | ON | ON | all | ON | ON | UserA |  |

Scenario: Showing contextual help for pages under menu "Portfolio" (Bug 809297).
  When I log in as "UserA" with password "Kupuh1pa!"
  # Test pages and collections help
  And I choose "Portfolios" in "Create" from main menu
  And I click on "Help"
  And I should see "A page contains a selection of artefacts"
  And I should see "A collection is a set of pages that are linked to one another and have the same access permissions."
  And I click on "Close help"
  # Pages
  And I click on "Page 01"
  And I click on "Edit"
  And I click on "Help"
  Then I should see "You can move blocks around the page either by dragging them or using your keyboard controls."
  And I click on "Close help"
  Given I click on "Configure" in the "Toolbar buttons" "Nav" property
  And I click on "Help"
  # Tags
  Then I should see "You can add tags to artefacts, pages and collections you create."
  # Collections
  Given I choose "Portfolios" in "Create" from main menu
  And I click on "Create" in the "Create" "Views" property
  And I click on "Collection"
  # Edit collection settings
  And I click on "Help" in the "H1 heading" "Common" property
  Then I should see "Here you may give your collection a title and description to give people an idea of what your collection is about."
  And I click on "Help for \"Tags\"" in the "Tags section" "Tags" property
  Then I should see "You can add tags to artefacts, pages and collections you create. Tags are descriptive labels that allow you to find your content later on more easily."
  And I set the field "Collection name" to "Collection 01"
  And I click on "Continue: Edit collection pages"
  # Edit collection pages
  And I click on "Help"
  Then I should see "Here you can add pages to your collection and set the order in which they will be displayed in the collection navigation."
  # Shared by me
  And I choose "Shared by me" in "Share" from main menu
  And I click on "Help"
  Then I should see "When you have created portfolios, you may wish to share them with others, e.g. to receive feedback on your work in form of comments."
  # Shared with me
  And I choose "Shared with me" in "Share" from main menu
  And I click on "Help"
  Then I should see "On this page you can list the most recently modified or commented on pages that have been shared with"
  # Skins
  And I choose "Skins" in "Create" from main menu
  And I click on "Help"
  Then I should see "Skins help you customise the look of your portfolio pages to give them a personal touch."
  And I click on "Close help"
  # Create skin
  And I click on "Create skin"
  And I click on "Help"
  Then I should see "You can design your own skin"
  # Import skin
  And I choose "Skins" in "Create" from main menu
  And I click on "More options"
  And I click on "Import" in the "Top right button group" "Nav" property
  And I click on "Help"
  And I should see "You can import skins from other Mahara sites."
  # Export
  # Note: The export page is not available if the export plugins is not installed and the zip command is not installed
  And I choose "Export" in "Manage" from main menu
  And I click on "Help"
  And I should see "You can export your portfolio to keep your files and content offline."
  # Import
  And I choose "Import" in "Manage" from main menu
  And I click on "Help"
  Then I should see "You can import your (or any valid Leap2a) portfolio from another Mahara site yourself."

Scenario: Showing correct external manual help file for mahara page
  #Test by going to pages and collections help for user / institution / site / group
  When I log in as "admin" with password "Kupuh1pa!"
  And I choose "Portfolios" in "Create" from main menu
  And I click on "Help" in the "Footer" "Footer" property
  And I wait "1" seconds
  And I switch to the new window
  And I scroll to the center of id "overview-page"
  And I should see "4.1.1. Overview page" in "h2" on the screen
  And I switch to the main window
  When I am on homepage
  And I choose "Portfolios" in "Institutions" from administration menu
  And I click on "Help" in the "Footer" "Footer" property
  And I switch to the new window
  And I scroll to the center of id "institution-portfolios"
  And I should see "11.6.13. Institution portfolios" in "h2" on the screen
  And I switch to the main window
  When I am on homepage
  And I choose "Portfolios" in "Configure site" from administration menu
  And I click on "Help" in the "Footer" "Footer" property
  And I switch to the new window
  And I scroll to the center of id "site-portfolios"
  And I should see "11.3.7. Site portfolios" in "h2" on the screen
  And I switch to the main window
  When I am on homepage
  And I click on "GroupA"
  And I click on "Portfolios" in the "Navigation" "Groups" property
  And I click on "Help" in the "Footer" "Footer" property
  And I switch to the new window
  And I scroll to the center of id "portfolios"
  And I should see "6.4.4. Portfolios" in "h2" on the screen
  And I switch to the main window
