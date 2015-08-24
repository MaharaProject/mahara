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
      | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |

  Scenario: Add a text block into the site default portfolio page and create a new portfolio page (Bug 1488255)
    Given I log in as "admin" with password "Password1"
    When I go to "admin/site/views.php"
    And I should see "Page template"
    And I click on "Edit content and layout" in "Page template" row
    And I should see "Drag blocks below this line to add them to your page layout. You can drag blocks around your page layout to position them."
    # Add a text block
    And I follow "Text"
    And I press "Add"
    And I wait "1" seconds
    And I set the following fields to these values:
     | Block title | Sample text block |
     | Block content | <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p> |
    And I press "Save"
    And I should see "Sample text block"
    And I should see "Lorem Ipsum is simply dummy text of the printing and typesetting industry."
    And I log out
    And I should see "Login"
    # Create a new portfolio page
    And I log in as "userA" with password "Password1"
    And I choose "Portfolio"
    And I should see "Pages"
    And I should see "Create page"
    And I press "Create page"
    And I should see "Copied 1 blocks and 0 artefacts from the page template"
    And I should see "Edit title and description"
    And I click on "Edit content"
    Then I should see "Lorem Ipsum is simply dummy text of the printing and typesetting industry."
