@javascript @plugin @artefact.blog
Feature: Mahara users can create their blogs
    As a mahara user
    I need to create blogs

    Background:
        Given the following "users" exist:
            | username | password | email | firstname | lastname | institution | authname | role |
            | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |
    Scenario: create blogs
        Given I log in as "userA" with password "Password1"
        And I set the following account settings values:
            | field | value |
            | multipleblogs | 1 |
            | tagssideblockmaxtags | 10 |
        When I follow "Settings"
        And I fill in the following:
            | tagssideblockmaxtags | 10 |
        And I check "multipleblogs"
        And I press "Save"
        And I wait "1" seconds
        When I go to "artefact/blog/index.php"
        And I wait until the page is ready
        Then I should see "Journals"
        When I click on "Create journal"
        And I fill in the following:
            | title | My new journal |
            | tags | blog |
#        And I set the field "description" to "<em> This is my new blog </em>"
        And I press "Create journal"
        Then I should see "My new journal"