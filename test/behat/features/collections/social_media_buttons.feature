@javascript @core @core_artefact
Feature: Visibility of social medial buttons
In order to view and click on the social media buttons
As a student
So I can view others social media pages

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Thor | Almighty | mahara | internal | member |
And the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userB | Kupuhipa1 | test02@example.com | Iron | Man | mahara | internal | member |


Scenario: Creating and accessing social media buttons (Bug 1448948)
 Given I log in as "userA" with password "Kupuhipa1"
 And I choose "Content" from main menu
 And I follow "Social media"
 And I follow "New social media account"
 And I select "Facebook URL" from "Social network"
 And I fill in "https://www.facebook.com/wellingtonphoenixfc" for "Your URL or username"
 And I press "Save"
 And I choose "Portfolio" from main menu
 And I follow "Add"
 And I click on "Page" in the dialog
 And I set the following fields to these values:
 | Page title * | Open source is for winners |
 And I press "Save"
 And I expand "Personal info" node
 And I follow "Social media"
 And I press "Add"
 And I check "Facebook (Social media)"
 And I follow "Display settings"
 And the field "buttons with icons and text" matches value "1"
 And I press "Save"
 And I go to "/view/share.php"
 And I click on "Edit access" in "Open source is for winners" row
 And I select "Public" from "accesslist[0][searchtype]"
 And I press "Save"
 And I should see "Access rules were updated for 1 page"
 And I log out
 # Logging in as userB to try see the buttons
 Given I log in as "userB" with password "Kupuhipa1"
 And I follow "Open source is for winners"
 And I scroll to the base of id "messages"
 And I should see "Social Media"
 And I should see "Facebook"
