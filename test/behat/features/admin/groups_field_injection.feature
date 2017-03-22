@javascript @core @core_administration
 Feature:Injecting sql in groups search field
  In order to inject javascript in group search field and group name field
  As an admin
  To see if mahara is secure enough

Scenario:Injecting sql in groups search field
  Given I log in as "admin" with password "Kupuhipa1"
  And I choose "My groups" in "Groups" from Main menu
  And I click on "Create group"
  And I set the following fields to these values:
  | Group name | <script>alert(1);</script> |
  | Group description | <script>alert(1);</script> |
  | Open| Off |
  | Hide group | Off |
  And I press "Save group"
  And I should see "Group saved successfully"
  And I choose "Administer groups" in "Groups" from Admin menu
  When I set the following fields to these values:
   | search_query | <script>alert(1);</script> |
  And I press "search_submit"
  And I follow "Privacy statement"
  And I should see "Introduction"
  Then I go to "homepage"
