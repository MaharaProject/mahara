@javascript @core @core_view @core_portfolio
Feature: Contextual helps for Mahara pages
  In order to see a help message about a mahara page
  As an student
  So I can click the (i) icon next to the page title to get help about the page

Background:
  Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm |
    | pcnz | Institution One | ON | OFF |

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
  And I choose "Pages and collections" in "Create" from main menu
  # PCNZ customisation: Help -> Manual
  And I click on "Manual"
  And I should see "A page contains a selection of artefacts"
  And I should see "A collection is a set of pages that are linked to one another and have the same access permissions."
  And I follow "Close help"
  # Pages
  And I follow "Page 01"
  And I press "Edit"
  And I click on "Manual"
  Then I should see "You can move blocks around the page either by dragging them or using your keyboard controls."
  And I follow "Close help"
  Given I click on "Settings" in the "Toolbar buttons" "Nav" property
  And I click on "Manual"
  # Tags
  Then I should see "You can add tags to artefacts, pages and collections you create."
  # Collections
  Given I choose "Pages and collections" in "Create" from main menu
  And I click on "Add"
  And I click on "Collection"
  # Edit collection settings
  And I click on "Manual" in the "H1 heading" "Common" property
  Then I should see "Here you may give your collection a title and description to give people an idea of what your collection is about."
  And I follow "Manual" in the "Tags section" "Tags" property
  Then I should see "You can add tags to artefacts, pages and collections you create. Tags are descriptive labels that allow you to find your content later on more easily."
  And I set the field "Collection name" to "Collection 01"
  And I press "Next: Edit collection pages"
  # Edit collection pages
  And I follow "Manual"
  Then I should see "Here you can add pages to your collection and set the order in which they will be displayed in the collection navigation."
  # Shared by me
  And I choose "Shared by me" in "Share" from main menu
  And I click on "Manual"
  Then I should see "You can share your portfolio with your verifier. Click the 'Edit access' icon, i.e. the padlock, and select a person to share your portfolio with. Save your selection."
  # Shared with me
  And I choose "Shared with me" in "Share" from main menu
  And I click on "Manual"
  Then I should see "On this page you can list the most recently modified or commented on pages that have been shared with"
  # Skins
  And I choose "Skins" in "Create" from main menu
  And I click on "Manual"
  Then I should see "Skins help you customise the look of your portfolio pages to give them a personal touch."
  And I follow "Close help"
  # Create skin
  And I press "Create skin"
  And I click on "Manual"
  Then I should see "You can design your own skin"
  # Import skin
  And I choose "Skins" in "Create" from main menu
  And I press "More options"
  And I follow "Import" in the "Top right button group" "Nav" property
  And I click on "Manual"
  And I should see "You can import skins from other Mahara sites."
  # Export
  # Note: The export page is not available if the export plugins is not installed and the zip command is not installed
  And I choose "Export" in "Manage" from main menu
  And I click on "Manual"
  And I should see "You can export your portfolio to keep your files and content offline."
  # Import
  And I choose "Import" in "Manage" from main menu
  And I click on "Manual"
  Then I should see "You can import your (or any valid Leap2a) portfolio from another Mahara site yourself."

Scenario: Showing correct external manual help file for mahara page
  #Test by going to pages and collections help for user / institution / site / group
  When I log in as "admin" with password "Kupuh1pa!"
  And I choose "Pages and collections" in "Create" from main menu
  # PCNZ customisation: Help -> Manual
  And I click on "Manual" in the "Footer" "Footer" property
  And I wait "1" seconds
  And I switch to the new window
  And I scroll to the center of id "overview-page"
  And I should see "4.1.1. Overview page" in "h2" on the screen
  And I switch to the main window
  When I am on homepage
  And I choose "Pages and collections" in "Institutions" from administration menu
  # PCNZ customisation: Help -> Manual
  And I follow "Manual" in the "Footer" "Footer" property
  And I switch to the new window
  And I scroll to the center of id "institution-pages"
  And I should see "11.6.13. Institution pages and collections" in "h2" on the screen
  And I switch to the main window
  When I am on homepage
  And I choose "Pages and collections" in "Configure site" from administration menu
  # PCNZ customisation: Help -> Manual
  And I click on "Manual" in the "Footer" "Footer" property
  And I switch to the new window
  And I scroll to the center of id "site-pages-and-collections"
  And I should see "11.3.7. Site pages and collections" in "h2" on the screen
  And I switch to the main window
  When I am on homepage
  And I follow "GroupA"
  And I follow "Pages and collections (tab)"
  # PCNZ customisation: Help -> Manual
  And I click on "Manual" in the "Footer" "Footer" property
  And I switch to the new window
  And I scroll to the center of id "pages-and-collections"
  And I should see "6.4.4. Pages and collections" in "h2" on the screen
  And I switch to the main window
