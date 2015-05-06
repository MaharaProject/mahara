@javascript @core @core_artefact
Feature: Visibility of social medial buttons
In order to view and click on the social media buttons
As a student
So I can view others social media pages

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Password1 | test01@example.com | Thor | Almighty | mahara | internal | member |
And the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userB | Password1 | test02@example.com | Iron | Man | mahara | internal | member |


Scenario: Creating and accessing social media buttons (Bug 1448948)
 Given I log in as "userA" with password "Password1"
 And I follow "Content"
 And I follow "Social media"
 And I follow "New social media account"
 And I set the following fields to these values:
 | Social network * | Facebook URL |
 | Your URL or username * | https://www.facebook.com/wellingtonphoenixfc |
 And I press "Save"
 And I follow "Portfolio"
 And I press "Create page"
 And I set the following fields to these values:
 | Page title * | Open source is for winners |
 And I press "Save"
 And I expand "Personal info" node
 And I wait until the page is ready
 And I follow "Social media"
 And I press "Add"
 And I set the following fields to these values:
 | artefactids_14 | 1 |  # too many things on the page with string 'Social media' so hitting actual one via it's id
 And I follow "Display settings"
 And the field "buttons with icons and text" matches value "1"
 And I press "Save"
 And I follow "Share page »"
 And I press "Add access for \"Public\""
 And I press "Save"
 And I should see "Access rules were updated for 1 page(s)"
 And I follow "Logout"
 # Logging in as userB to try see the buttons
 Given I log in as "userB" with password "Password1"
 And I follow "Open source is for winners"
 And I should see "Social Media"
 And I should see "Facebook"

