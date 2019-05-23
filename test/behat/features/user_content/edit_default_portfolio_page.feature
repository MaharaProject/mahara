@javascript @core @core_administration
Feature: Edit the site default portfolio page
In order to update the site default portfolio page
As an admin
I can edit the site default portfolio page
As a user
I can create a new page from the site default portfolio page

  Background:
    Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

  Scenario: Add a text block into the site default portfolio page and create a new portfolio page (Bug 1488255)
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Configure site" from administration menu
    And I should see "Page template"
    And I click on "Edit" in "Page template" card menu
    And I should see "Drag 'Add a new block' onto the page to add one."
    # Add a text block
    When I follow "Add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on "Text" in the "Content types" property
    And I set the following fields to these values:
     | Block title | Sample text block |
     | Block content | <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p> |
    And I press "Save"
    And I should see "Sample text block"
    And I log out

    # Create a new portfolio page
    And I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I should see "Pages and collections"
    And I follow "Add"
    And I click on "Page" in the dialog
    And I should see "Settings"
    And I click on "Edit"
    Then I should see "Lorem Ipsum is simply dummy text of the printing and typesetting industry."
