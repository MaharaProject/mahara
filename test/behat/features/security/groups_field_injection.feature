@javascript @core @core_administration
 Feature:Injecting sql in groups search field
  In order to inject javascript in group search field and group name field
  As an admin
  To see if mahara is secure enough

Scenario:Injecting sql in groups search field
  Given I log in as "admin" with password "Kupuh1pa!"
  And I choose "My groups" in "Groups" from main menu
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
  And I press "search_submit"
  And I follow "About"
  And I should see "About us"
  Then I go to "homepage"
