@javascript @core @core_content
Feature: Creating a journal entry and configuring the form
In order to change the configuration of my Journal entry
As a user
So I can benefit from the different settings

Background:
  Given the following "pages" exist:
  | title | description| ownertype | ownername |
  | Page 01 | admins page 01 | admin | admin |

Scenario: Turning on and of switches in Journal configuration block (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 # Navigating to switchbox in Journal block
 And I choose "Journals" in "Content"
 And I follow "New entry"
 # Checking the default fields match
 And the following fields match these values:
 | Draft | 0 |
 | Allow comments | 1 |
 # Changing the switches once
 And I set the following fields to these values:
 | Draft | 1 |
 | Allow comments | 0 |
 # Changing the switches back
 And I set the following fields to these values:
 | Draft | 0 |
 | Allow comments | 1 |
 And I press "Save entry"
 And I should see "There was an error with submitting this form. Please check the marked fields and try again."


Scenario: Creating a Journal entry
 Given I log in as "admin" with password "Kupuhipa1"
 # Navigating to switchbox in Journal block
 And I choose "Journals" in "Content"
 And I follow "New entry"
 # Checking the default fields match
 And the following fields match these values:
 | Title * | |
 | Entry * | |
 | Draft | 0 |
 | Allow comments | 1 |
 # Changing the switches once and filling out a journal
 And I set the following fields to these values:
 | Title * | Story of my life |
 | Allow comments | 0 |
 | Entry | Preventing bugs from appearing :D |
 And I fill in select2 input "editpost_tags" with "one" and select "one"
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "test" and select "test"

 And I press "Save entry"

 And I follow "New entry"
 And I set the following fields to these values:
 | Title * | Story of my life, part 2 |
 | Allow comments | 0 |
 | Entry | Testing tags |
 And I fill in select2 input "editpost_tags" with "two" and select "two"
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "test" and select "test"
 And I press "Save entry"

 And I follow "New entry"
 And I set the following fields to these values:
 | Title * | Story of my life, part 3 |
 | Allow comments | 0 |
 | Entry | Testing tags some more |
 And I fill in select2 input "editpost_tags" with "three" and select "three"
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "test" and select "test"
 And I press "Save entry"

 And I choose "Pages" in "Portfolio"
 And I follow "Page 01"
 And I follow "Edit this page"
 And I expand "Journals" node in the "div#content-editor-foldable" "css_element"
 And I follow "Tagged journal entries" in the "div#blog" "css_element"
 And I press "Add"
 And I fill in select2 input "instconf_tagselect" with "one" and select "one"
 And I wait "1" seconds
 And I fill in select2 input "instconf_tagselect" with "two" and select "two"
 And I wait "1" seconds
 And I press "Save"
 And I scroll to the base of id "column-container"
 And I configure the block "Tagged journal entries"
 And I wait "1" seconds
 And I clear value "one" from select2 field "instconf_tagselect"
 And I press "Save"
