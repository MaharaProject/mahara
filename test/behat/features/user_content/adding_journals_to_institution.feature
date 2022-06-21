@javascript @core @core_institution @core_artefact
Feature: Adding journals to institution level
    In order to use journals at an institution level
    As a person and admin
    So I can create journals to share on pages

Background:
    Given the following "institutions" exist:
         | name | displayname | registerallowed | registerconfirm |
         | instone | Institution One | ON | OFF |
         | insttwo | Institution Two | ON | OFF |

    And the following "pages" exist:
         | title | description | ownertype | ownername |
         | Page mahara_01 | Page 01 | institution | mahara |


Scenario: Clicking on the journal sub menu headings and adding first journal (Bug 1472467)
    # log in as admin
    Given I log in as "admin" with password "Kupuh1pa!"
    When I choose "Journals" in "Configure site" from administration menu
    # Confirm page contains text "There are no site journals". (Bug 1017785)
    Then I should see "There are no site journals."
    # Confirm page contains link "Add one" that links to Create new Journal page. (Bug 1017785)
    When I follow "Add one"
    Then I should see "New site journal: Journal settings"
    And I move backward one page
    # Make sure more than one site journal can be created
    And I press "Create journal"
    And I should see "New site journal:"
    And I fill in "Title" with "Site Journal 1"
    And I set the following fields to these values:
    | Description | The first mahara institution journal |
    And I click on "Create journal"
    Then I should see "Site journal 1"
    And I press "Create journal"
    And I fill in "Title" with "Site Journal 2"
    And I set the following fields to these values:
    | Description | The second mahara institution journal |
    And I click on "Create journal"
    Then I should see "Site journal 2"

    # Make sure more than one institution journal can be created
    When I choose "Journals" in "Institutions" from administration menu
    # Confirm page contains text "There are no journals in this institution.". (Bug 1017785)
    Then I should see "There are no journals in this institution."
    # Confirm page contains link "Add one" that links to Create new Journal page. (Bug 1017785)
    When I follow "Add one"
    Then I should see "New \"Institution One\" journal: Journal settings"
    And I move backward one page
    And I press "Create journal"
    And I should see "New \"Institution One\" journal:"
    And I fill in "Title" with "Institution One Journal 1"
    And I set the following fields to these values:
    | Description | The Institution One journal |
    And I click on "Create journal"
    Then I should see "Institution One Journal 1"
    And I press "Create journal"
    And I fill in "Title" with "Institution One Journal 2"
    And I set the following fields to these values:
    | Description | Another Institution One journal |
    And I click on "Create journal"
    Then I should see "Institution One Journal 2"

    # try making a journal for another institution
    And I select "Institution Two" from "institutionselect_institution"
    And I should not see "Institution One Journal 1"
    And I press "Create journal"
    And I should see "New \"Institution Two\" journal:"
    And I fill in "Title" with "Institution Two Journal 1"
    And I set the following fields to these values:
    | Description | The Institution Two journal |
    And I click on "Create journal"
    Then I should see "Institution Two Journal 1"

    # Try adding some journal entries to the journal
    And I press "New entry"
    And I should see "New journal entry in journal \"Institution Two Journal 1\""
    And I fill in "Title *" with "Journal entry 1"
    And I set the following fields to these values:
    | Entry * | The contents of this entry |
    And I click on "Save entry"
    Then I should see "Journal entry saved"
    And I press "New entry"
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
    And I delete the "Journal entry 1" row
    Then I should not see "Journal entry 1"

Scenario: Newly created person can get a copy of the journal (Bug 1472467)
    Given I log in as "admin" with password "Kupuh1pa!"
    # Creating a site wide journal
    And I choose "Journals" in "Configure site" from administration menu
    And I press "Create journal"
    And I set the following fields to these values:
    | Title * | Site journal 1 |
    | Description | Contents of site journal 1 |
    And I press "Create journal"
    And I press "New entry"
    And I fill in "Title *" with "Spongebob"
    And I set the following fields to these values:
    | Entry * | *)_4442)&@*#&^%%!+_()**&gha~gsd |
    And I press "Save entry"
    And I should see "Journal entry saved"
    And I should see "Spongebob"
    And I choose "Pages and collections" in "Configure site" from administration menu
    And I click on "Edit" in "Page mahara_01" card menu
    # Adding journal block to the page
    When I click on the add block button
    And I press "Add"
    And I click on blocktype "Journal"
    And I select the radio "Site journal 1"
    And I select "Others will get their own copy of your journal" from "Block copy permission"
    And I press "Save"
    And I scroll to the id "main-nav"
    And I press "Share" in the "Toolbar buttons" "Nav" property
    And I select "Registered people" from "accesslist[0][searchtype]"
    And I press "Advanced options"
    And I set the following fields to these values:
    | Allow copying | 1 |
    | Copy into new accounts | 1 |
    And I press "Save"
    # Needs to add new people now to see if they get copy of page
    And I choose "Add a person" in "People" from administration menu
    And I fill in the following:
    | First name * | Pete |
    | Last name * | Mc |
    | Email * | UserA@example.org |
    | Username * | UserA |
    | Password * | KKJhh$$67686 |
    And I scroll to the center of id "adduser_submit"
    And I press "Create account"
    # Logging in as new person
    And I follow "Log in as this person"
    And I follow "log in anyway"
    # Checking I can see the page ...
    And I choose "Pages and collections" in "Create" from main menu
    Then I should see "Page mahara_01"
    # ... and the journal
    And I choose "Journals" in "Create" from main menu
    And I follow "Copy of Site journal 1"
    Then I should see "Spongebob"
