@javascript @core @core_administration @allow_popups
 Feature:Injecting sql in groups search field
    In order to inject javascript in group search field and group name field
    As an admin
    To see if mahara is secure enough

Background:
    Given the following site settings are set:
    | field | value |
    | skins | 1 |

Scenario:Injecting sql in groups search field
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I click on "Create group"
    And I set the following fields to these values:
    | Group name | <script>alert(1);</script> |
    | Group description | <script>alert(1);</script> |
    | Open| Off |
    | Hide group | Off |
    And I press "Save group"
    And I should see "Group saved successfully"
    And I choose "Administer groups" in "Groups" from administration menu
    When I set the following fields to these values:
    | search_query | <script>alert(1);</script> |
    And I press "search_search"
    And I follow "About"
    And I should see "About us"
    Then I go to "homepage"

# admin inject javascript in Skin title field.  To see if mahara is secure enough
Scenario: Skin title not escaped in page settings form (Bug 1707076)
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Skins" in "Create" from main menu
    And I click on "Create skin"
    When I set the following fields to these values:
    | Skin title | <script>alert(1);</script> |
    | Skin description | <script>alert(1);</script> |
    | Skin access | This is a private skin |
    And I press "Save"
    And I should see "Skin saved successfully"
    And I should not see a popup

# check to see if "I should not see a popup" step definition fails when there is a page that has a popup
Scenario: I should see a popup
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Résumé" in "Create" from main menu
    And I follow "Education and employment"
    # Adding Education history
    And I press "Add education history"
    And I set the following fields to these values:
    | addeducationhistory_startdate | 1 Jan 2009 |
    | addeducationhistory_enddate | 2 Dec 2010 |
    | addeducationhistory_institution | University of Life |
    | addeducationhistory_institutionaddress | 2/103 Industrial Lane |
    | addeducationhistory_qualtype | Masters of Arts |
    | addeducationhistory_qualname | North American Cultural Studies |
    | addeducationhistory_qualdescription | This qualification is a 4.5-year degree that ends in writing a Master's thesis. |
    And I scroll to the base of id "educationhistoryform"
    And I attach the file "Image2.png" to "Attach file"
    When I press "Save"
    And I follow "Delete"
    And I should see "Are you sure you want to delete this?" in popup
    And I accept the confirm popup
