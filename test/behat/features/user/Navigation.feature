@javascript @core @admin @user @navigation
Feature: Mahara users can navigate their portfolio
  As a mahara user
  I can navigate my portfolio

    Scenario: Navigate portfolio
        Given the following "institutions" exist:
          | name | displayname | registerallowed | registerconfirm |
          | instone | Institution One | ON | OFF |
          | insttwo | Institution Two | ON | OFF |
        And the following "users" exist:
          | username | password | email | firstname | lastname | institution | authname | role |
          | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | admin |
          | userB | Password1 | test02@example.com | Son | Nguyen | instone | internal | admin |
          | userC | Password1 | test03@example.com | Jack | Smith | insttwo | internal | admin |
        Given I am on homepage
        When I fill in "login_username" with "userA"
        And I fill in "login_password" with "Password1"
        And I press "Login"
        Then I should see "Dashboard"
        And I choose "Content"
        And I should see "Profile"
        And I choose "Collections" in "Portfolio"
        And I should see "Collections"
