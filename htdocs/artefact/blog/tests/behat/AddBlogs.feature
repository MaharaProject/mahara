@javasript @plugin @artefact.blog
Feature: Mahara users can create their blogs
    As a mahara user
    I need to create blogs

    Scenario: create blogs
        Given the following "users" exist:
            | user | password | | institution | role |
            | userA | Password1 | | mahara | member |
        And the following account settings have set:
            | Multiple journals | ON |
        When I log in as "userA" with password "Password1"
        And I follow "Content"
        And I follow "Journal"
        Then I should see "Journals"
        When I press "Create journal"
        And I fill in the following:
            | title | My new journal |
            | description | <p>This is my new journal</p> |
            | tags | blog |
        And I press "Create journal"
        Then I should see "My new journal"