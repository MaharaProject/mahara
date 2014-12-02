Feature: Login
    @javascript
    Scenario: Login
        Given I am on homepage
        When I fill in "login_username" with "admin"
        And I fill in "login_password" with "Password1"
        And I press "Login"
        Then I should see "Dashboard"