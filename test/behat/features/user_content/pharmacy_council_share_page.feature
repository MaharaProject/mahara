@javascript @core
Feature: Share page with verifier
As a pharmacist
I only want to see the minimal sharing options when sharing my recert portfolio
so that I am not confronted with choices that are not relevant to me.

Background:
 Given the following "users" exist:
 | username | password | email | firstname | lastname | institution | authname | role |
 | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

 And the following "pages" exist:
 | title | description | ownertype | ownername |
 | Page UserA_01 | Page 01 | user | UserA |

 # PCNZ customisation WR349184
 Scenario: Check that date options, advanced panel unavailable and sharing is only with "Person"
 Given I log in as "UserA" with password "Kupuh1pa!"
 # Edit access for Page 03
 And I choose "Pages and collections" in "Create" from main menu
 And I click on "Manage access" in "Page UserA_01" card access menu
 # Check that Advanced options panel is not displayed
 And I should not see "Advanced options"
 # Check that Secret URLs is not visible
 And I should not see "Secret URLs"
 # Check that date options are not visible
 And I should not see "From"
 And I select "Person" from "accesslist[0][searchtype]"
 And I press "Save"
