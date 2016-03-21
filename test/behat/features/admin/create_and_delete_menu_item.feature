@javascript @core @core_administration
Feature: Creating/Deleting external links from the Links and Resources sideblock
   In order to use external links
   As an admin I need to create an external link and delete it
   So I can verify that it's usable

Scenario: Creating and deleting external links (Selenium 1426983)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in as successful
    And I should see "Admin User"
    # Entering an external link
    When I follow "Administration"
    And I choose "Menus" in "Configure site"
    And I select "Logged-in links and resources" from "Edit:"
    And I fill in "namenew" with "Test Menu Link"
    And I fill in "linkedtonew" with "https://mahara.org/"
    And I press "Add"
    # Verifying item was saved
    And I should see "Item saved"
    And I press "Save changes"
    # Verifying the link as been added successfully
    And I follow "Return to site"
    Then I should see "Test Menu Link"
    And I follow "Administration"
    And I follow "Configure site"
    And I choose "Menus" in "Configure site"
    And I select "Logged-in links and resources" from "Edit:"
    #And I wait until the page is ready
    #And I press "Delete"
    And I delete the link and resource menu item "Test Menu Link"
    #And I accept the confirm popup
    And I should see "Item deleted"
    And I press "Save changes"
   # Checking the default settings are correct
    And the following fields match these values:
     | Terms and conditions | 0 |
     | Privacy statement | 1 |
     | About | 1 |
     | Contact us | 1 |
   # Flicking the switches to the opposite
    And I enable the switch "Terms and conditions"
    And I disable the following switches:
     | Privacy statement |
     | About |
     | Contact us |
    And I press "Save changes"
  # Checking the switches held the setting
    And the following fields match these values:
     | Terms and conditions | 1 |
     | Privacy statement | 0 |
     | About | 0 |
     | Contact us | 0 |


Scenario: Make sure blogs do not show in site file link options (Bug #1537426)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    When I follow "Administration"

    # I create a site journal
    And I choose "Journals" in "Configure site"
    And I follow "Create journal"
    And I fill in "Title" with "Site blog"
    And I press "Create journal"

    # I upload some site files
    And I choose "Files" in "Configure site"
    And I attach the file "Image1.jpg" to "File"

    # Entering an external link
    And I choose "Menus" in "Configure site"
    And I select "Logged-in links and resources" from "Edit:"
    And I set the following fields to these values:
    | Site file | 1 |
    And the "linkedtonew" select box should not contain "Site blog"
    And I press "Add"