@javascript @core_institution @core_artefact @failed
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
  And I follow "Administration"
  # Make sure more than one site journal can be created
  When I choose "Journals" in "Configure site"
  And I follow "Create journal"
  And I should see "New site journal:"
  And I set the following fields to these values:
  | Title | Site Journal 1 |
  | Description | The first mahara institution journal |
  And I click on "Create journal"
  Then I should see "Site journal 1"
  And I follow "Create journal"
  And I set the following fields to these values:
  | Title | Site Journal 2 |
  | Description | The second mahara institution journal |
  And I click on "Create journal"
  Then I should see "Site journal 2"

  # Make sure more than one institution journal can be created
  When I choose "Journals" in "Institutions"
  And I follow "Create journal"
  And I should see "New \"Institution One\" journal:"
  And I set the following fields to these values:
  | Title | Institution One Journal 1 |
  | Description | The Institution One journal |
  And I click on "Create journal"
  Then I should see "Institution One Journal 1"
  And I follow "Create journal"
  And I set the following fields to these values:
  | Title | Institution One Journal 2 |
  | Description | Another Institution One journal |
  And I click on "Create journal"
  Then I should see "Institution One Journal 2"

  # try making a journal for another institution
  And I select "Institution Two" from "institutionselect_institution"
  And I should not see "Institution One Journal 1"
  And I follow "Create journal"
  And I should see "New \"Institution Two\" journal:"
  And I set the following fields to these values:
  | Title | Institution Two Journal 1 |
  | Description | The Institution Two journal |
  And I click on "Create journal"
  Then I should see "Institution Two Journal 1"

  # Try adding some journal entries to the journal
  And I follow "New entry"
  And I should see "New journal entry in journal \"Institution Two Journal 1\""
  And I set the following fields to these values:
  | Title * | Journal entry 1 |
  | Entry * | The contents of this entry |
  And I click on "Save entry"
  Then I should see "Journal entry saved"
  And I follow "New entry"
  And I set the following fields to these values:
  | Title * | Journal entry 2 |
  | Entry * | The contents of this entry |
  And I click on "Add a file"
  And I wait "1" seconds
  And I attach the file "Image1.jpg" to "File"
  And I wait "1" seconds
  Then I should see "Image1.jpg" in the "table#editpost_filebrowser_filelist" element
  When I close the dialog
  And I press "Save entry"
  Then I should see "Journal entry 1"
  And I should see "Journal entry 2"
  # And I click on "Delete"
  # And I accept the currently displayed dialog (waiting on fix for bug 1415252 to be able to do this step)
  # Then I should not see "Journal entry 1"

Scenario: Newly created user can get a copy of the journal (Bug 1472467)
  Given I log in as "admin" with password "Kupuhipa1"
  And I follow "Administration"
  # Creating a site wide journal
  And I choose "Journals" in "Configure site"
  And I follow "Create journal"
  And I set the following fields to these values:
  | Title * | Site journal 1 |
  | Description | Contents of site journal 1 |
  And I press "Create journal"
  And I follow "New entry"
  And I set the following fields to these values:
  | Title * | Spongebob |
  | Entry * | *)_4442)&@*#&^%%!+_()**&gha~gsd |
  And I press "Save entry"
  And I should see "Journal entry saved"
  And I should see "Spongebob"
  # Creating a site page
  And I choose "Pages" in "Configure site"
  And I press "Create"
  And I set the following fields to these values:
  | Page title | Square pants |
  | Page description | hsdfhjkl78695t 8677y8 |
  And I press "Save"
  # Adding journal block to the page
  And I wait until the page is ready
  # Need to access the adding "Journal" block more directly than normal now that "Journals" is a menu item also
  And I expand "Journals" node in the "div#content-editor-foldable" "css_element"
  And I wait "1" seconds
  And I follow "Journal" in the "div#blog" "css_element"
  And I press "Add"
  #And I select the radio "Site journal 1"
  And I set the field "Site journal 1" to "1"
  And I select "Others will get their own copy of your journal" from "Block copy permission"
  And I press "Save"
  And I follow "Share page"
  And I select "Registered users" from "accesslist[0][searchtype]"
  And I follow "Advanced options"
  And I set the following fields to these values:
  | Allow copying | 1 |
  | Copy for new user | 1 |
  And I press "Save"
  # Needs to add new user now to see if they get copy of page
  And I choose "Add user" in "Users"
  And I set the following fields to these values:
  | First name * | Pete |
  | Last name * | Mc |
  | Email * | test01@example.com |
  | Username * | userA |
  | Password * | KKJHH$$67686 |
  And I click on "Create user"

  # Logging in as new user
  And I follow "Log in as"
  And I follow "log in anyway"
  # Checking I can see the page ...
  And I follow "Portfolio"
  Then I should see "Square pants"
  # ... and the journal
  When I follow "Content"
  And I choose "Journals" in "Content"
  And I follow "Copy of Site journal 1"
  Then I should see "Spongebob"
