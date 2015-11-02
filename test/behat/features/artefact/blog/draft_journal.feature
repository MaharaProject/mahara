@javascript @core @core_content @core_artefact
Feature: Creating a journal
In order write in my journal
As an admin
I need to have a journal

Background:
 Given the following "users" exist:
 | username | password | email | firstname | lastname | institution | authname | role |
 | userA | Kupuhipa1 | test01@example.com | Andrea | Andrews | mahara | internal | member |
 | userB | Kupuhipa1 | test02@example.com | Britta | Briggs | mahara | internal | member |

 And the following "pages" exist:
 | title | description| ownertype | ownername |
 | Journal page | Page to contain the tagged journal block | user | userA |

Scenario: Creating a Journal, publishing a draft, using tagged entry block
 # Create draft entry
 Given I log in as "userA" with password "Kupuhipa1"
 When I choose "Journals" in "Content"
 And I follow "New entry"
 And I set the following fields to these values:
 | Title * | My diary entry one |
 | Entry | I love my mum |
 | Draft | 1 |
 | Allow comments | 0 |
 And I fill in select2 input "editpost_tags" with "mildred" and select "mildred"
 And I press "Save entry"
 Then I should see "Journal entry saved"
 And I should see "Draft"

 # Make entry public
 Given I press "Publish"
 Then I should see "Published"

 # Add another entry
 And I follow "New entry"
 And I set the following fields to these values:
 | Title * | My diary entry two |
 | Entry | I love my dad |
 | Draft | 0 |
 | Allow comments | 0 |
 And I fill in select2 input "editpost_tags" with "george" and select "george"
 And I press "Save entry"
 Then I should see "Journal entry saved"

 # Remove tag from first journal and save
 Given I click on "Edit" in "My diary entry one" row
 And I wait "1" seconds
 And I clear the select2 field "editpost_tags"
 And I press "Save entry"
 Then I should see "Journal entry saved"
 And I should not see "mildred"

 # Display tagged journals in block
 When I follow "Portfolio"
 And I follow "Edit \"Journal page\""
 And I expand "Journals" node in the "div#content-editor-foldable" "css_element"
 And I wait "1" seconds
 And I follow "Tagged journal entries" in the "div#blog" "css_element"
 And I press "Add"
 And I fill in select2 input "instconf_tagselect" with "george" and select "george"
 And I press "Save"
 Then I should see "My diary entry two"
 And I go to portfolio page "Journal page"
 And I follow "Edit this page"
 And I follow "Share page"
 And I select "Public" from "accesslist[0][searchtype]"
 And I press "Save"
 And I follow "Logout"
 And I log in as "userB" with password "Kupuhipa1"
 And I go to portfolio page "Journal page"
 Then I should see "My diary entry two"
 And I should not see "My diary entry one"
