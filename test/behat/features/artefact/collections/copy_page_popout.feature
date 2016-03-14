@javascript @core @core_artefact
Feature: Previewing copy a page or collection
In order to use the pop out window on copy a page or collection
As an admin/user
I need to be able to click on the links

Background:
Given the following "pages" exist:
     | title | description| ownertype | ownername |
     | As Page 01 | admins page 01 | admin | admin |
     | As Page 03 | admins page 03 | admin | admin |

Given the following "collections" exist:
     | title | description| ownertype | ownername | pages |
     | User Col 01 | My collection 01 | user | admin | As Page 01, As Page 03 |


Scenario: Accessing the popup window in the Copy or page or collection (Bug 1361450)
 Given I log in as "admin" with password "Kupuhipa1"
  And I go to "view/choosetemplate.php"
  And I follow "User Col 01"
  And I wait "1" seconds
  And I should see "User Col 01 by admin"
  And I press "Close"
  Then I should not see "User Col 01 by admin"


