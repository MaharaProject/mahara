@javascript @core @core_content @core_artefact @doris
Feature: Creating a journal
In order write in my journal
As an admin
I need to have a journal

Background:
 Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm |
    | pcnz | Institution One | ON | OFF |

 And the following "users" exist:
 | username | password | email | firstname | lastname | institution | authname | role |
 | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
 | UserB | Kupuh1pa! | UserB@example.org | Britta | Briggs | mahara | internal | member |

 And the following "pages" exist:
 | title | description | ownertype | ownername |
 | Page UserA_01 | Page 01 | user | UserA |

Scenario: Creating a Journal, publishing a draft, using tagged entry block
 # Create draft entry
 Given I log in as "UserA" with password "Kupuh1pa!"
 When I choose "Journals" in "Create" from main menu
 And I press "New entry"
 And I fill in the following:
 | Title * | My diary entry one |
 And I set the following fields to these values:
 | Entry * | I love my mum |
 | Draft | 1 |
 | Allow comments | 0 |
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "mildred" and select "mildred"
 And I press "Save entry"
 Then I should see "Journal entry saved"
 And I should see "Draft"

 # Make entry public
 Given I press "Publish"
 Then I should see "Published"

 # Add another entry
 And I press "New entry"
 And I fill in the following:
 | Title * | My diary entry two |
 And I set the following fields to these values:
 | Entry | I love my dad |
 | Draft | 0 |
 | Allow comments | 0 |
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "george" and select "george"
 And I press "Save entry"

 # Remove tag from first journal and save
 Given I click on "Edit" in "My diary entry one" row
 And I clear value "mildred (1)" from select2 field "editpost_tags"
 And I press "Save entry"
 And I should not see "mildred"

 # Display tagged journals in block
 And I choose "Pages and collections" in "Create" from main menu
 And I click on "Edit" in "Page UserA_01" card menu
 When I click on the add block button
 And I press "Add"
 And I click on blocktype "Tagged journal entries"
 And I fill in select2 input "instconf_tagselect" with "george" and select "george"
 And I press "Save"
 And I wait "1" seconds
 Then I should see "My diary entry two"
 And I go to portfolio page "Page UserA_01"
 And I press "Edit"
 And I press "Share" in the "Toolbar buttons" property
 # PCNZ customisation WR349184
 And I select "Person" from "accesslist[0][searchtype]"
 And I select "Britta Briggs" from select2 nested search box in row number "1"
 And I press "Save"
 ## End customisation
 And I log out
 And I log in as "UserB" with password "Kupuh1pa!"
 And I go to portfolio page "Page UserA_01"
 Then I should see "My diary entry two"
 And I should not see "My diary entry one"
