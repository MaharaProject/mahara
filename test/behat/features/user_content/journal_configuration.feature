@javascript @core @core_content
Feature: Creating a journal entry and configuring the form
In order to change the configuration of my Journal entry
As a user
So I can benefit from the different settings

Background:
  Given the following "pages" exist:
  | title | description | ownertype | ownername |
  | Page admin_01 | Page 01 | admin | admin |

  And the following "groups" exist:
  | name | owner | description | grouptype | open | invitefriends | editroles |
  | GroupA | admin | GroupA owned by admin | standard | ON | OFF | all |

Scenario: Turning on and of switches in Journal configuration block (Bug 1431569)
 Given I log in as "admin" with password "Kupuh1pa!"
 # Navigating to switchbox in Journal block
 And I choose "Journals" in "Create" from main menu
 And I follow "New entry"
 And I press "Save entry"
 And I should see "There was an error with submitting this form. Please check the marked fields and try again."


Scenario: Creating a Journal entry
 Given I log in as "admin" with password "Kupuh1pa!"
 # Navigating to switchbox in Journal block
 And I choose "Journals" in "Create" from main menu
 And I follow "New entry"
 And I fill in "Title *" with "Story of my life"
 And I set the following fields to these values:
 | Allow comments | 0 |
 | Entry | Preventing bugs from appearing :D |
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "one" and select "one"
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "test" and select "test"

 And I press "Save entry"

 And I follow "New entry"
 And I fill in "Title *" with "Story of my life, part 2"
 And I set the following fields to these values:
 | Allow comments | 0 |
 | Entry | Testing tags |
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "two" and select "two"
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "test" and select "test"
 And I press "Save entry"

 And I follow "New entry"
 And I fill in "Title *" with "Story of my life, part 3"
 And I set the following fields to these values:
 | Allow comments | 0 |
 | Entry | Testing tags some more |
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "three" and select "three"
 And I scroll to the base of id "editpost_tags_container"
 And I fill in select2 input "editpost_tags" with "test" and select "test"
 And I press "Save entry"

 # Adding journal entry to group 'GroupA'
 When I choose "Groups" in "Engage" from main menu
 And I follow "GroupA"
 And I follow "Journals" in the "Arrow-bar nav" property
 # Confirm page contains text "There are no journals in this group" (Bug 1017785)
 Then I should see "There are no journals in this group."
 # Confirm page contains link "Add one" that links to Create new Journal page. (Bug 1017785)
 When I follow "Add one"
 Then I should see "New journal | GroupA"
 And I move backward one page
 And I follow "Create journal"
 And I fill in "Title *" with "My group journal"
 And I press "Create journal"
 And I follow "New entry"
 And I fill in "Title *" with "My group entry one"
 And I set the following fields to these values:
 | Entry | I love my mum |
 And I press "Save entry"

 # Adding journal blocks to a page
 And I choose "Pages and collections" in "Create" from main menu
 And I follow "Page admin_01"
 And I follow "Edit"
 When I follow "Drag to add a new block" in the "blocktype sidebar" property
 And I press "Add"
 And I click on blocktype "Tagged journal entries"
 And I set the field "Block title" to "Tagged journal entries"
 And I scroll to the base of id "instconf_tagselect_container"
 And I fill in select2 input "instconf_tagselect" with "one" and select "one"
 And I scroll to the base of id "instconf_tagselect_container"
 And I fill in select2 input "instconf_tagselect" with "two" and select "two"
 And I press "Save"
 And I scroll to the base of id "column-container"
 And I configure the block "Tagged journal entries"
 And I wait "1" seconds
 And I clear value "one" from select2 field "instconf_tagselect"
 And I press "Save"
 When I follow "Drag to add a new block" in the "blocktype sidebar" property
 And I press "Add"
 And I click on blocktype "Recent journal entries"
 And I check "Admin Account's Journal"
 And I press "Save"
