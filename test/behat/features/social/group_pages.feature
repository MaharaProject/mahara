@javascript @core @core_group
Feature: Copied group page keeps title
In order to make sure group page title copies correctly
As a group member
So I can see group page and check the title

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
     | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | mahara | internal | member |

Given the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | GroupA | admin | Group A owned by admin | standard | ON | ON | all | ON | ON | UserA, UserB | UserC |

Given the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page GroupA_01 | Page 1 | group | GroupA |

Given the following site settings are set:
    | field | value |
    | skins | 0 |

Scenario: Copying a group page retains title (Bug 1519374)
 # Make the group page copyable
 Given I log in as "UserA" with password "Kupuh1pa!"
 When I go to group "GroupA"
 And I follow "Share" in the "Arrow-bar nav" "Nav" property
 And I click on "Edit access" in "Page GroupA_01" row
 And I press "Advanced options"
 And I set the following fields to these values:
 | Allow copying | 1 |
 And I press "Save"
 And I log out

 Given I log in as "UserB" with password "Kupuh1pa!"
 And I am on homepage
 # PCNZ customisation: The 'Portfolios shared with me' has custom settings
 # in this case, only 'me' is selected.
 When I press "Edit dashboard"
 Then I configure the block "Portfolios shared with me"
 And I set the following fields to these values:
| My groups | 1 |
 When I press "Save"
 And I follow "Page GroupA_01"
 And I press "More options"
 And I follow "Copy"
 Then I go to "/view/choosetemplate.php?searchcollection=1"
 And I click on "Copy page" in "Page GroupA_01" row
 And the following fields match these values:
 | Page title | Page GroupA_01 v.2 |
 Then I press "Save"

Scenario: Group homepage basic settings and skins can't be edited ($cfg->skins must not be set) - Bug 1718806
 Given I log in as "UserC" with password "Kupuh1pa!"
 When I go to group "GroupA"
 And I press "Edit"
 And I should not see "Settings"
