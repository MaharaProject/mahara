@javascript @core @core_user @core_portfolio @friends
Feature: Contact feature functionality
    1. UserA finds people within and outside of their institution
        a. Verify page elements
            - Search field
            - Side blocks
            - Results - User image, user name, institution member and action buttons (send contact request, send message)
    2. UserA requests to be a contact - add message (make 4 contact requests)

Background:
    Given the following "institutions" exist:
    | name | displayname |
    | instone | Institution One |
    | insttwo | Institution Two |

    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname |role |
    | UserA | Kupuh1pa! | UserA@example.org  | Angela  | UserA | instone | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org  | Bob     | UserB | instone | internal | member |
    | UserC | Kupuh1pa! | UserC@example.org  | Cecilia | UserC | instone | internal | member |
    | UserD | Kupuh1pa! | UserD@example.org  | Dave    | UserD | insttwo | internal | member |
    | UserE | Kupuh1pa! | UserE@example.org  | Earl    | UserE | insttwo | internal | member |

Scenario: UserA sends contact requests to UserB, UserC, User E
    # PCNZ customisation - connections are made difficult to exist - cannot receive messages from anyone
    # Given I log in as "UserA" with password "Kupuh1pa!"
    # And I choose "People" in "Engage" from main menu
    # Then I should see "Bob UserB"
    # And I should see "Cecilia UserC"
    # And I should not see "Dave UserD"
    # When I click on "Send contact request" in "Bob UserB" row
    # Then I should see "Send Bob UserB a contact request"
    # When I fill in "Would you like to be my contact?" for "Message"
    # And I press "Make contact request"
    # Then I should see "Sent a contact request to Bob UserB"
    # When I click on "Send contact request" in "Cecilia UserC" row
    # Then I should see "Send Cecilia UserC a contact request"
    # When I fill in "Would you like to be my contact Cecilia?" for "Message"
    # And I press "Make contact request"
    # Then I should see "Sent a contact request to Cecilia UserC"
    # When I select "Everyone" from "Filter"
    # And I press "Search"
    # And I click on "Send contact request" in "Dave UserD" row
    # Then I should see "Send Dave UserD a contact request"
    # When I fill in "Would you like to be my contact Dave?" for "Message"
    # And I press "Make contact request"
    # Then I should see "Sent a contact request to Dave UserD"

    # # sending a contact request from a profile page
    # When I select "Everyone" from "Filter"
    # And I press "Search"
    # Then I should see "Earl UserE"
    # When I follow "Earl UserE"
    # Then I should see "Earl UserE"
    # And I should see "Member of Institution Two"
    # When I click on "Make contact request"
    # Then I should see "Send Earl UserE a contact request"
    # When I fill in "Would you like to be my contact Earl?" for "Message"
    # And I press "Make contact request"
    # Then I should see "Sent a contact request to Earl UserE"
    # And I log out

    # # log in as Earl and view pending contact requests and accept
    # Given I log in as "UserE" with password "Kupuh1pa!"
    # And I follow "pending contact"
    # Then I should see "Angela UserA (UserA)"
    # And I should see the date "today" in the "Pending since" "People" property with the format "l, d F Y"
    # And I should see "Member of Institution One"
    # When I press "Approve"
    # Then I should see "Accepted contact request"
    # And I log out

    # # Admin sets contact control so "Nobody" may add them as a contact
    # Given I log in as "admin" with password "Kupuh1pa!"
    # And I choose "People" in "Engage" from main menu
    # And I select the radio "Nobody may add me as a contact"
    # And press "Save"
    # Then I should see "Updated contact control"
    # And I log out

    # # User B accepts the Contact request
    # Given I log in as "UserB" with password "Kupuh1pa!"
    # When  I follow "pending contact"
    # Then I should see "Angela UserA (UserA)"
    # And I should see the date "today" in the "Pending since" "People" property with the format "l, d F Y"
    # And I should see "Member of Institution One"
    # When I press "Approve"
    # Then I should see "Accepted contact request"
    # And I log out
    # Given I log in as "UserC" with password "Kupuh1pa!"
    # When  I follow "pending contact"
    # Then I should see "Angela UserA (UserA)"
    # And I should see the date "today" in the "Pending since" "People" property with the format "l, d F Y"
    # And I should see "Member of Institution One"
    # When I click on "Deny"
    # Then I should see "Reason for rejecting request"
    # When I fill in "I don't know who you are" for "Reason for rejecting request"
    # And I press "Deny contact request"
    # Then I should see "Rejected contact request"
    # Then I log out

    # # UserC logs in and tries to add UserA who has set their contact control to Nobody may add me as a contact
    # Given I log in as "UserE" with password "Kupuh1pa!"
    # And I choose "People" in "Engage" from main menu
    # When I select "Everyone" from "Filter"
    # And I press "Search"
    # Then I should see "This person does not want any new contacts." in the "Admin Account" row
    # And I click on "Send contact request" in "Dave UserD" row
