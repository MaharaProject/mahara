@javascript @core @core_administration
Feature: Creating/Deleting external links from the Links and Resources sideblock
   In order to use external links
   As an admin I need to create an external link and delete it
   So I can verify that it's usable

Scenario: Creating and deleting external links (Selenium 1426983)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"
    # Entering an external link
    And I choose "Menus" in "Configure site" from administration menu
    And I select "Logged-in links and resources" from "Edit"
    And I fill in "namenew" with "Test Menu Link"
    And I fill in "linkedtonew" with "https://mahara.org/"
    And I press "Add"
    # Verifying item was saved
    And I should see "Item saved"
    And I press "Save changes"
    # Verifying the link as been added successfully
    And I choose "Dashboard" from main menu
    Then I should see "Test Menu Link"
    And I choose "Menus" in "Configure site" from administration menu
    And I select "Logged-in links and resources" from "Edit"
    And I delete the link and resource menu item "Test Menu Link"
    And I should see "Item deleted"
    And I press "Save changes"

Scenario: Make sure blogs do not show in site file link options (Bug #1537426)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"
    # I create a site journal
    And I choose "Journals" in "Configure site" from administration menu
    And I follow "Create journal"
    And I fill in "Title" with "Site blog"
    And I press "Create journal"
    # I upload some site files
    And I choose "Files" in "Configure site" from administration menu
    And I attach the file "Image1.jpg" to "File"
    # Entering an external link
    And I choose "Menus" in "Configure site" from administration menu
    And I select "Logged-in links and resources" from "Edit"
    And I set the following fields to these values:
    | Site file | 1 |
    And the "linkedtonew" select box should not contain "Site blog"
    And I press "Add"
