 @javascript @core @core_view @core_portfolio
 Feature: Contextual helps for Mahara pages
  In order to see a help message about a mahara page
  As an student
  So I can click the (i) icon next to the page title to get help about the page

 Scenario: Showing contextual help for pages under menu "Portfolio" (Bug 809297).
  Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
  When I log in as "userA" with password "Kupuhipa1"
  And I choose "Pages" in "Portfolio"
  And I click on "Help icon"
  And I should see "A page contains a selection of artefacts"
  And I choose "Collections" in "Portfolio"
  And I click on "Help icon"
  And I should see "A collection is a set of pages that are linked to one another and have the same access permissions."
  And I choose "Shared by me" in "Portfolio"
  And I click on "Help icon"
  And I should see "When you have created portfolio pages and collections, you may wish to share them with others, e.g. to receive feedback."
  And I choose "Shared with me" in "Portfolio"
  And I click on "Help icon"
  And I should see "On this page you can list the most recently modified or commented on pages that have been shared with"
  And I choose "Export" in "Portfolio"
 # Note: The export page is not available if the export plugins is not installed and the zip command is not installed
  And I click on "Help icon"
  And I should see "You can export your portfolio to keep your files and content offline."
  And I choose "Import" in "Portfolio"
  And I click on "Help icon"
  Then I should see "You can import your (or any valid Leap2a) portfolio from another Mahara site yourself."
