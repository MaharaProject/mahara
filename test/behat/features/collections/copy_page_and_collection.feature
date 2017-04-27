@javascript @core @core_artefact
Feature: Previewing copy a page or collection and then copying it
In order to use the pop out window on copy a page or collection
As an admin/user
I need to be able to click on the links

Background:
  Given the following "pages" exist:
     | title | description| ownertype | ownername |
     | As Page 01 | admins page 01 | admin | admin |
     | As Page 02 | admins page 02 | admin | admin |
     | As Page 03 | admins page 03 | admin | admin |

  Given the following "collections" exist:
     | title | description| ownertype | ownername | pages |
     | User Col 01 | My collection 01 | user | admin | As Page 01, As Page 03 |


Scenario: Accessing the popup window in the Copy or page or collection (Bug 1361450)
  Given I log in as "admin" with password "Kupuhipa1"
  And I choose "Pages and collections" in "Portfolio" from main menu
  And I follow "Copy"
  And I follow "User Col 01"
  And I wait "1" seconds
  And I should see "User Col 01 by admin"
  And I press "Close"
  Then I should not see "User Col 01 by admin"

  # Add a block to the page
  When I choose "Pages and collections" in "Portfolio" from main menu
  And I click on "As Page 02" panel menu
  And I click on "Edit" in "As Page 02" panel menu
  And I follow "Text"
  And I wait "1" seconds
  And I press "Add"
  And I wait "1" seconds
  Then I should see "Text: Configure"
  And I set the field "Block title" to "Text Block 1"
  And I set the field "Block content" to "Here is a new block."
  And I press "Save"

  # Copy a page
  And I choose "Pages and collections" in "Portfolio" from main menu
  And I follow "Copy"
  And I click on "Copy page" in "As Page 02" row
  And I press "Save"
  Then I should see "Text Block 1"
  And I should see "Here is a new block."

  # Add the page to the collection which has block
  When I choose "Pages and collections" in "Portfolio" from main menu
  And I click on "User Col 01" panel menu
  And I click on "Manage" in "User Col 01" panel menu
  And I check "As Page 02"
  And I wait "1" seconds
  And I press "Add pages"
  And I should see "1 page added to collection"
  And I follow "Done"

  # Copy a collection
  And I choose "Pages and collections" in "Portfolio" from main menu
  And I follow "Copy"
  And I click on "Copy collection" in "User Col 01" row
  And I press "Next: Edit collection pages"
  And I follow "Done"

  #Veryfying if the page that has block been copied to collection
  And I click on "User Col 01 v.2"
  And I press "Next page"
  And I wait "1" seconds
  And I press "Next page"
  Then I should see "Text Block 1"
  And I should see "Here is a new block."
