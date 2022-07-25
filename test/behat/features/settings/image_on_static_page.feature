@javascript @core @core_administration
Feature: Static page image visibility
    As a site visitor
    I want to see images/logos on the static pages when logged in and not logged in
    So I know I am on the right website

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | admin |

Scenario: Admin user can add an image to the "Logged -out home" static page and see it displayed
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Upload an image
    And I choose "Files" in "Create" from main menu
    And I attach the file "Image2.png" to "files_filebrowser_userfile"
    And I click on "Edit" in "Image2.png" row
    And I set the field "Alt text" to "Image two"
    And I click on "Save changes"
    When I choose "Static pages" from administration menu
    And I select "Home (Dashboard)" from "pagename"
    When I click the "Insert/edit image" button in the editor
    And I expand the section "Image"
    And I wait "1" seconds
    And I click on "Select \"Image2.png\""
    And I click on "Submit"
    And I wait "1" seconds
    And I click on "Save changes"
    And I choose "Dashboard" from main menu
    # check that the image uploaded is the image being seen on the page
    Then I should see image 'Image two' on the page

Scenario: Admin user can add an image to the "Home (Dashboard)" static page and see it displayed
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Upload an image
    And I choose "Files" in "Create" from main menu
    And I attach the file "Image2.png" to "files_filebrowser_userfile"
    And I click on "Edit" in "Image2.png" row
    And I set the field "Alt text" to "Image two"
    And I click on "Save changes"
    When I choose "Static pages" from administration menu
    And I select "Logged-out home" from "pagename"
    When I click the "Insert/edit image" button in the editor
    And I expand the section "Image"
    And I wait "1" seconds
    And I click on "Select \"Image2.png\""
    And I click on "Submit"
    And I wait "1" seconds
    And I click on "Save changes"
    And I log out
    And I am on homepage
    # check that the image uploaded is the image being seen on the page
    Then I should see image 'Image two' on the page
