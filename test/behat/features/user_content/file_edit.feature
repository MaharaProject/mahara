@javascript @core
Feature: Rename file and add description. Create folder and add files
    As a user
    I want to be able to rename files and add descriptions
    So that I can manage my content

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

Scenario: Add file, rename and add description
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Files" in "Create" from main menu
    And I attach the file "mahara_about.pdf" to "File"
    And I press "Edit \"mahara_about.pdf\""
    And I set the field "Name" to "renamed.pdf"
    And I set the field "Description" to "I hope I can see my saved changes"
    And I press "Save changes"
    Then I should see "renamed.pdf"
    And I should see "I hope I can see my saved changes"
    And I reload the page
    And I should not see "mahara_about.pdf"