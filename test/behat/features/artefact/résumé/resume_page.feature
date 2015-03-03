@javascript @core @core_artefact
Feature: Editing a Resume page
   In order to edit a resume page
   As an admin I need to go to Content
   So I can edit the resume page

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |

Scenario Outline: Editing admin resume page (Bug 1426983)
    # Log in as "<username>" user
    Given I log in as "<username>" with password "Password1"
    # Editing resume
    When I choose "Résumé" in "Content"
    And I follow "Introduction"
    And I fill in the following:
    | Date of birth   | 1970/01/07   |
    | Place of birth | Wellington |
    | Marital status | It's complicated |
    # Saving the information
    And I press "personalinformation_save"
    And I follow "Education and employment"
    # Adding employment history
    And I press "addemploymenthistorybutton"
    And I fill in the following:
    | addemploymenthistory_startdate | 1 Jan 2009  |
    | addemploymenthistory_employer  | Test   |
    | addemploymenthistory_jobtitle | Test   |
    # Saving the changes
    And I press "addemploymenthistory_submit"
    # Adding an education history
    And I press "addeducationhistorybutton"
    And I fill in the following:
    | Start date | 1 Jan 2008  |
    | Institution | test   |
    # Saving the information
    And I press "addeducationhistory_submit"
    # Verifying it saved
    Then I should see "Saved successfully"
    # Logging out and loggin in as as student user/ then ending
    And I follow "Logout"

    Examples:
    | username |
    | admin |
    | userA |
