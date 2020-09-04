@javascript @core @core_administration
Feature: Creating/Deleting links from the Links and Resources sideblock
   As an admin
   I need to create and delete both public and private links to both external and internal resources
   So I can verify that they are usable

Scenario: Creating and deleting both types of external links (Selenium 1426983 - extended Bug 1892950)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"
    # Creating external links
    And I choose "Menus" in "Configure site" from administration menu
    # Creating an external link on the Dashboard page (i.e. private link)
    When I select "Logged-in links and resources" from "Edit"
    And I fill in "namenew" with "Dashboard: test external resource link"
    And I fill in "linkedtonew" with "https://mahara.org/"
    And I press "Add"
    # Verifying item was saved
    Then I should see "Item saved"
     # Creating an external link on the Homepage (i.e. public link)
    When I select "Public links and resources" from "Edit"
    And I fill in "namenew" with "Homepage: test external resource link"
    And I fill in "linkedtonew" with "https://mahara.org/"
    And I press "Add"
    # Verifying item was saved
    Then I should see "Item saved"

    # Verifying both types of external links have been added successfully
    When I choose "Dashboard" from main menu
    Then I should see "Dashboard: test external resource link"
    And I should not see "Homepage: test external resource link"
    When I log out
    Then I should see "Homepage: test external resource link"
    And I should not see "Dashboard: test external resource link"

    # Verifying that both types of external links can be removed
    When I log in as "admin" with password "Kupuh1pa!"
    And I choose "Menus" in "Configure site" from administration menu
    And I select "Logged-in links and resources" from "Edit"
    And I delete the link and resource menu item "Dashboard: test external resource link"
    Then I should see "Item deleted"
    When I select "Public links and resources" from "Edit"
    And I delete the link and resource menu item "Homepage: test external resource link"
    Then I should see "Item deleted"

    # Verifying both types of external links have been removed successfully
    When I choose "Dashboard" from main menu
    Then I should not see "Dashboard: test external resource link"
    When I log out
    Then I should not see "Homepage: test external resource link"

Scenario: Creating and deleting both types of internal/'site file' links
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"

    # I upload both types of site files
    And I choose "Files" in "Configure site" from administration menu
    And I attach the file "testvid3.mp4" to "File"
    And I follow "public"
    And I attach the file "mahara_about.pdf" to "File"

    # Create file resource link on Homepage (for public use)
    When I choose "Menus" in "Configure site" from administration menu
    And I select "Public links and resources" from "Edit"
    And I set the following fields to these values:
    | Site file | 1 |
    Then the "linkedtonew" select box should contain "mahara_about.pdf"
    And the "linkedtonew" select box should not contain "testvid3.mp4"
    When I fill in "namenew" with "Homepage: test file resource link"
    And I press "Add"
    Then I should see "Item saved"

    # Create file resource link on Dashboard page (for Mahara account holder use)
    When I select "Logged-in links and resources" from "Edit"
    And I set the following fields to these values:
    | Site file | 1 |
    Then the "linkedtonew" select box should not contain "mahara_about.pdf"
    And the "linkedtonew" select box should contain "testvid3.mp4"
    When I fill in "namenew" with "Dashboard: test file resource link"
    And I press "Add"
    Then I should see "Item saved"

    # Verifying both types of file links have been added successfully
    When I choose "Dashboard" from main menu
    Then I should see "Dashboard: test file resource link"
    And I should not see "Homepage: test file resource link"
    When I log out
    Then I should see "Homepage: test file resource link"
    And I should not see "Dashboard: test file resource link"

    # Verifying that both types of file resource links can be removed
    When I log in as "admin" with password "Kupuh1pa!"
    And I choose "Menus" in "Configure site" from administration menu
    And I select "Logged-in links and resources" from "Edit"
    And I delete the link and resource menu item "Dashboard: test file resource link"
    Then I should see "Item deleted"
    When I select "Public links and resources" from "Edit"
    And I delete the link and resource menu item "Homepage: test file resource link"
    Then I should see "Item deleted"

    # Verifying that both types of file links have been removed successfully
    When I choose "Dashboard" from main menu
    Then I should not see "Dashboard: test file resource link"
    When I log out
    Then I should not see "Homepage: test file resource link"

Scenario: Make sure blogs (ie site journals) do not show in site file link options (Bug #1537426)
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
