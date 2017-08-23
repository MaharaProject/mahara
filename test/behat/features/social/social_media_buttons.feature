@javascript @core @core_artefact
Feature: Visibility of social medial buttons
In order to view and click on the social media buttons
As a student
So I can view others social media pages

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuhipa1 | UserB@example.org | Bob | User | mahara | internal | member |

Scenario: Creating and accessing social media buttons (Bug 1448948)
 Given I log in as "UserA" with password "Kupuhipa1"
 And I choose "Content" from main menu
 And I follow "Social media"
 And I follow "New social media account"
 And I select "Facebook URL" from "Social network"
 And I fill in "https://www.facebook.com/wellingtonphoenixfc" for "Your URL or username"
 And I press "Save"
 And I follow "New social media account"
 And I select "Twitter username" from "Social network"
 And I fill in "mahara" for "Your URL or username"
 And I press "Save"
 And I follow "New social media account"
 And I select "Instagram username" from "Social network"
 And I fill in "maharainst" for "Your URL or username"
 And I press "Save"
 And I follow "New social media account"
 And I select "Skype username" from "Social network"
 And I fill in "maharaskype" for "Your URL or username"
 And I press "Save"
 And I follow "New social media account"
 And I select "Yahoo Messenger" from "Social network"
 And I fill in "maharayahoo" for "Your URL or username"
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
 And I check "Twitter (Social media)"
 And I check "Instagram (Social media)"
 And I check "Skype (Social media)"
 And I check "Yahoo Messenger (Social media)"
 And I follow "Display settings"
 And the field "buttons with icons and text" matches value "1"
 And I press "Save"
 And I go to "/view/share.php"
 And I click on "Edit access" in "Open source is for winners" row
 And I select "Public" from "accesslist[0][searchtype]"
 And I press "Save"
 And I should see "Access rules were updated for 1 page"
 And I log out
 # Logging in as UserB to try see the buttons
 Given I log in as "UserB" with password "Kupuhipa1"
 And I follow "Open source is for winners"
 And I should see "Social Media"
 And I should see "Facebook"
 And I should see "Twitter"
 And I should see "Instagram"
 And I should see "Skype"
 And I should see "Yahoo Messenger"
