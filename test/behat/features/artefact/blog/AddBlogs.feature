@javascript @core @core_artefact
Feature: Mahara users can create their blogs
    As a mahara user
    I need to create blogs

    Background:
        Given the following "users" exist:
            | username | password | email | firstname | lastname | institution | authname | role |
            | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |
    Scenario: create blogs
        Given I log in as "userA" with password "Password1"
        When I follow "Settings"
        And I set the following fields to these values:
        | Multiple journals | 1 |
        | Maximum tags in cloud | 10 |
        And I press "Save"
        When I go to "artefact/blog/index.php"
        Then I should see "Journals"

        When I follow "Create journal"
        And I fill in the following:
            | title | My new journal |
            | tags | blog |
        And I set the field "description" to "<em> This is my new blog </em>"
        And I press "Create journal"
        Then I should see "My new journal"