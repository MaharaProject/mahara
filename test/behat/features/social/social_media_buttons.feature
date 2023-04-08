@javascript @core @core_artefact
Feature: Visibility of social medial buttons
In order to view and click on the social media buttons
As a student
So I can view others social media pages

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |

And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01 | user | UserA |

And the following "blocks" exist:
     | title                     | type     | page                   | retractable | updateonly | data                                                |
     | Portfolios shared with me | newviews | Dashboard page: UserB  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |

Scenario: Creating and accessing social media buttons (Bug 1448948)
 Given I log in as "UserA" with password "Kupuh1pa!"
 And I choose "Profile" from account menu
 And I click on "Social media"
 And I click on "New social media account"
 And I select "Facebook URL" from "Social media"
 And I fill in "https://www.facebook.com/wellingtonphoenixfc" for "Your URL or username"
 And I click on "Save"
 And I click on "New social media account"
 And I select "Twitter username" from "Social media"
 And I fill in "mahara" for "Your URL or username"
 And I click on "Save"
 And I click on "New social media account"
 And I select "Instagram username" from "Social media"
 And I fill in "maharainst" for "Your URL or username"
 And I click on "Save"
 And I click on "New social media account"
 And I select "Skype username" from "Social media"
 And I fill in "maharaskype" for "Your URL or username"
 And I click on "Save"
 And I click on "New social media account"
 And I select "Yahoo Messenger" from "Social media"
 And I fill in "maharayahoo" for "Your URL or username"
 And I click on "Save"
 And I choose "Portfolios" in "Create" from main menu
 And I click on "Edit" in "Page UserA_01" card menu
 When I click on the add block button
 And I click on "Add" in the "Add new block" "Blocks" property
 And I click on blocktype "Social media"
 And I set the field "Block title" to "Social Media"
 And I check "Facebook (Social media)"
 And I check "Twitter (Social media)"
 And I check "Instagram (Social media)"
 And I check "Skype (Social media)"
 And I check "Yahoo Messenger (Social media)"
 And I click on "Display settings"
 And the field "buttons with icons and text" matches value "1"
 And I click on "Save"
 And I go to "/view/share.php"
 And I click on "Share" in "Page UserA_01" row
 And I select "Public" from "accesslist[0][searchtype]"
 And I click on "Save"
 And I should see "Access rules were updated for 1 page"
 And I log out

 # Logging in as UserB to try see the buttons
 Given I log in as "UserB" with password "Kupuh1pa!"
 And I click on "Page UserA_01"
 And I should see "Social Media"
 And I should see "Facebook"
 And I should see "Twitter"
 And I should see "Instagram"
 And I should see "Skype"
 And I should see "Yahoo Messenger"
