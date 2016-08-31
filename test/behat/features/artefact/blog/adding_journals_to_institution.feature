@javascript @core @core_institution @core_artefact
Feature: Adding journals to institution level
In order to use journals at an institution level
As a user and admin
So I can create journals to share on pages

Background:
Given the following "institutions" exist:
     | name | displayname | registerallowed | registerconfirm |
     | instone | Institution One | ON | OFF |
     | insttwo | Institution Two | ON | OFF |

Scenario: Clicking on the journal sub menu headings and adding first journal (Bug 1472467)
  # log in as admin
  Given I log in as "admin" with password "Kupuhipa1"
  # Make sure more than one site journal can be created
  When I choose "Journals" in "Configure site" from administration menu
  And I follow "Create journal"
  And I should see "New site journal:"
  And I fill in "Title" with "Site Journal 1"
  And I set the following fields to these values:
  | Description | The first mahara institution journal |
  And I click on "Create journal"
  Then I should see "Site journal 1"
  And I follow "Create journal"
  And I fill in "Title" with "Site Journal 2"
  And I set the following fields to these values:
  | Description | The second mahara institution journal |
  And I click on "Create journal"
  Then I should see "Site journal 2"

  # Make sure more than one institution journal can be created
  When I choose "Journals" in "Institutions" from administration menu
  And I follow "Create journal"
  And I should see "New \"Institution One\" journal:"
  And I fill in "Title" with "Institution One Journal 1"
  And I set the following fields to these values:
  | Description | The Institution One journal |
  And I click on "Create journal"
  Then I should see "Institution One Journal 1"
  And I follow "Create journal"
  And I fill in "Title" with "Institution One Journal 2"
  And I set the following fields to these values:
  | Description | Another Institution One journal |
  And I click on "Create journal"
  Then I should see "Institution One Journal 2"

  # try making a journal for another institution
  And I select "Institution Two" from "institutionselect_institution"
  And I should not see "Institution One Journal 1"
  And I follow "Create journal"
  And I should see "New \"Institution Two\" journal:"
  And I fill in "Title" with "Institution Two Journal 1"
  And I set the following fields to these values:
  | Description | The Institution Two journal |
  And I click on "Create journal"
  Then I should see "Institution Two Journal 1"

  # Try adding some journal entries to the journal
  And I follow "New entry"
  And I should see "New journal entry in journal \"Institution Two Journal 1\""
  And I fill in "Title *" with "Journal entry 1"
  And I set the following fields to these values:
  | Entry * | The contents of this entry |
  And I click on "Save entry"
  Then I should see "Journal entry saved"
  And I follow "New entry"
  And I fill in "Title *" with "Journal entry 2"
  And I set the following fields to these values:
  | Entry * | The contents of this entry |
  And I click on "Add a file"
  And I attach the file "Image1.jpg" to "File"
  Then I should see "Upload of Image1.jpg complete"
  When I close the dialog
  And I press "Save entry"
  Then I should see "Journal entry 1"
  And I should see "Journal entry 2"
  # And I click on "Delete"
  # And I accept the currently displayed dialog (waiting on fix for bug 1415252 to be able to do this step)
  # Then I should not see "Journal entry 1"

Scenario: Newly created user can get a copy of the journal (Bug 1472467)
  Given I log in as "admin" with password "Kupuhipa1"
  # Creating a site wide journal
  And I choose "Journals" in "Configure site" from administration menu
  And I follow "Create journal"
  And I set the following fields to these values:
  | Title * | Site journal 1 |
  | Description | Contents of site journal 1 |
  And I press "Create journal"
  And I follow "New entry"
  And I fill in "Title *" with "Spongebob"
  And I set the following fields to these values:
  | Entry * | *)_4442)&@*#&^%%!+_()**&gha~gsd |
  And I press "Save entry"
  And I should see "Journal entry saved"
  And I should see "Spongebob"
  # Creating a site page
  And I choose "Pages and collections" in "Configure site" from administration menu
  And I follow "Add"
  And I click on "Page" in the dialog
  And I set the following fields to these values:
  | Page title | Square pants |
  | Page description | hsdfhjkl78695t 8677y8 |
  And I press "Save"
  # Adding journal block to the page
  # Need to access the adding "Journal" block more directly than normal now that "Journals" is a menu item also
  And I expand "Journals" node in the "div#content-editor-foldable" "css_element"
  And I follow "Journal" in the "div#blog" "css_element"
  And I press "Add"
  And I select the radio "Site journal 1"
  And I select "Others will get their own copy of your journal" from "Block copy permission"
  And I press "Save"
  And I scroll to the id "main-nav"
  And I follow "Share page"
  And I select "Registered users" from "accesslist[0][searchtype]"
  And I follow "Advanced options"
  And I set the following fields to these values:
  | Allow copying | 1 |
  | Copy for new user | 1 |
  And I press "Save"
  # Needs to add new user now to see if they get copy of page
  And I choose "Add user" in "Users" from administration menu
  And I fill in the following:
  | First name * | Pete |
  | Last name * | Mc |
  | Email * | test01@example.com |
  | Username * | userA |
  | Password * | KKJHH$$67686 |
  And I press "Create user"
  # Logging in as new user
  And I follow "Log in as this user"
  And I follow "log in anyway"
  # Checking I can see the page ...
  And I choose "Portfolio" from main menu
  Then I should see "Square pants"
  # ... and the journal
  And I choose "Journals" in "Content" from main menu
  And I follow "Copy of Site journal 1"
  Then I should see "Spongebob"
