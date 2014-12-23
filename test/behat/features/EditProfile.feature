@javascript @core @profile
Feature: Mahara users can have their profile
    As a mahara user
    I can manage my profile
    Background:
        Given the following "users" exist:
          | username | password | email | firstname | lastname | institution | authname | role |
          | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |
          | userB | Password1 | test02@example.com | Son | Nguyen | mahara | internal | member |
          | userC | Password1 | test03@example.com | Jack | Smith | mahara | internal | member |
          | userD | Password1 | test04@example.com | Eden | Wilson | mahara | internal | member |
          | userE | Password1 | test05@example.com | Emily | Pham | mahara | internal | member |
        And the following "institutions" exist:
          | name | displayname | registerallowed | registerconfirm | members | staff | admins |
          | instone | Institution One | ON | OFF | userB | | userA |
          | insttwo | Institution Two | ON | ON | userB,userC | userD | userA |
        And the following "groups" exist:
          | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
          | group 01 | userA | This is group 01 | standard | ON | ON | all | ON | ON | userB, userC | userD |
    @javascript
    Scenario: Edit Profile
        Given I log in as "userE" with password "Password1"
        When I click on "Content"
        Then I should see "Profile"
        And the "firstname" field should contain "Emily"
        When I fill in "firstname" with "Tiger"
        When I fill in "lastname" with "Wood"
        When I fill in "studentid" with "1234"
        When I fill in "preferredname" with "Golf Legend"
        When I click on "Contact information"
        Then I should see "Email address"
        And I should see "Official website address"
        When I fill in "officialwebsite" with "www.catalyst.net.nz"
        And I fill in "personalwebsite" with "www.stuff.co.nz"
        When I fill in "blogaddress" with "www.blog.com"
        When I fill in "profileform_address" with "150 Willis Street"
        When I fill in "city" with "CBD"
        When I fill in "homenumber" with "04928375"
        When I fill in "businessnumber" with "040298375"
        When I fill in "mobilenumber" with "0272093875482"
        When I fill in "faxnumber" with "09237842"
        When I click on "Social media"
        Then I should see "Social network"
        When I click on "General"
        Then I should see "Occupation"
        When I fill in "occupation" with "Software Engineer"
        When I fill in "industry" with "it"
        When I press "Save profile"
        Then I should see "Profile saved successfully"
