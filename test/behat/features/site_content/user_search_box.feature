@javascript @core
Feature: Front page search box
In order to edit display name settings for search box
As an admin I need to go to Account settings
So I can hide the display name of the user


Background:

Given the following "users" exist:
 | username | password | email | firstname | lastname | institution | authname | role |
 | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
 | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |

 Scenario: Verifying the "User search" box functionality
 # Log in as a normal user
 Given I log in as "UserA" with password "Kupuh1pa!"
 And I fill in "Bob" for "usf_query"
 #click on the search icon
 And I press "usf_submit"
 #Verifying if UserA can see User B in the search results
 And I should see "UserB"
 #Change the display name of "UserA"
 And I choose "Profile" from account menu
 And I follow "About me"
 And I fill in the following:
 # PCNZ customisation: Display name -> Preferred name
 | Preferred name  | Alpha |
 And I press "Save profile"
 #Log out as UserA
 And I log out
 #Log in as UserB and verify the display names
 Given I log in as "UserB" with password "Kupuh1pa!"
 And I fill in "Alpha" for "usf_query"
 And I press "usf_submit"
 And I should see "Angela"
 #Log out as UserB
 And I log out
 #Log in as admin and change the display name settings
 Then I log in as "admin" with password "Kupuh1pa!"
 And I choose "Site options" in "Configure site" from administration menu
 And I expand all fieldsets
 And I set the following fields to these values:
 | Never display usernames | 1 |
 And I press "Update site options"
 #Log out as admin user
 And I log out
 #Verify if the never display username functionality works
 Given I log in as "UserA" with password "Kupuh1pa!"
 And I fill in "UserB" for "usf_query"
 And I press "usf_submit"
 And I should see "No search results found"
