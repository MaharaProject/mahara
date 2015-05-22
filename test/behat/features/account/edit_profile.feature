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
        And I set the following fields to these values:
          | First name | Tiger |
          | Last name | Wood |
          | Student ID | 1234 |
          | Display name | Golf Legend |
          | Introduction | <p>This is my introduction.</p><p>My name is Wood</p> |
        And I click on "Contact information"
        And I should see "Email address"
        And I should see "Official website address"
        And I set the following fields to these values:
          | Official website address | www.catalyst.net.nz |
          | Personal website address | www.stuff.co.nz |
          | Blog address | www.blog.com |
          | Postal address | 150 Willis Street, Te Aro |
          | City/region | Wellington |
          | Country | New Zealand |
          | Home phone | +64-4-928375 |
          | Business phone | +64-4-0298375 |
          | Mobile phone | 0272093875482 |
          | Fax number | 09237842 |
        And I click on "Social media"
        And I should see "New social media account"
        And I click on "General"
        And I should see "Occupation"
        When I fill in "occupation" with "Software Engineer"
        When I fill in "industry" with "IT"
        When I press "Save profile"
        Then I should see "Profile saved successfully"
