@javascript @core @core_content
Feature: Creating a journal entry and configuring the form
In order to change the configuration of my Journal entry
As a user
So I can benefit from the different settings

Background:
  Given the following "pages" exist:
  | title | description| ownertype | ownername |
  | Page 01 | admins page 01 | admin | admin |

  And the following "groups" exist:
  | name | owner | description | grouptype | open | invitefriends | editroles |
  | Groupies | admin | This is group for groupies | standard | ON | OFF | all |

Scenario: Turning on and of switches in Journal configuration block (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 # Navigating to switchbox in Journal block
 And I choose "Journals" in "Content"
 And I follow "New entry"
 And I press "Save entry"
 And I should see "There was an error with submitting this form. Please check the marked fields and try again."


Scenario: Creating a Journal entry
 Given I log in as "admin" with password "Kupuhipa1"
 # Navigating to switchbox in Journal block
 And I choose "Journals" in "Content"
 And I follow "New entry"
 And I set the following fields to these values:
 | Title * | Story of my life |
 | Allow comments | 0 |
 | Entry | Preventing bugs from appearing :D |
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "one" and select "one"
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "test" and select "test"

 And I press "Save entry"

 And I follow "New entry"
 And I set the following fields to these values:
 | Title * | Story of my life, part 2 |
 | Allow comments | 0 |
 | Entry | Testing tags |
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "two" and select "two"
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "test" and select "test"
 And I press "Save entry"

 And I follow "New entry"
 And I set the following fields to these values:
 | Title * | Story of my life, part 3 |
 | Allow comments | 0 |
 | Entry | Testing tags some more |
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "three" and select "three"
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "test" and select "test"
 And I press "Save entry"

 # Adding journal entry to group 'Groupies'
 When I choose "My groups" in "Groups"
 And I follow "Groupies"
 And I follow "Journals" in the "div.arrow-bar" "css_element"
 And I follow "Create journal"
 And I set the following fields to these values:
 | Title * | My group journal |
 And I press "Create journal"
 And I follow "New entry"
 And I set the following fields to these values:
 | Title * | My group entry one |
 | Entry | I love my mum |
 And I press "Save entry"

 # Adding journal blocks to a page
 And I choose "Pages" in "Portfolio"
 And I follow "Page 01"
 And I follow "Edit this page"
 And I expand "Journals" node in the "div#content-editor-foldable" "css_element"
 And I follow "Tagged journal entries" in the "div#blog" "css_element"
 And I press "Add"
 And I scroll to the base of id "instconf_tagselect_container"
 And I fill in select2 input "instconf_tagselect" with "one" and select "one"
 And I scroll to the base of id "instconf_tagselect_container"
 And I fill in select2 input "instconf_tagselect" with "two" and select "two"
 And I wait "1" seconds
 And I press "Save"
 And I scroll to the base of id "column-container"
 And I configure the block "Tagged journal entries"
 And I wait "1" seconds
 And I clear value "one" from select2 field "instconf_tagselect"
 And I press "Save"

 And I follow "Recent journal entries" in the "div#blog" "css_element"
 And I press "Add"
 And I check "Admin User's Journal"
 And I press "Save"
