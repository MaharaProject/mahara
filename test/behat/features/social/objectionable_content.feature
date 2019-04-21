@javascript @core @core_portfolio
Feature: objectionable content functionality

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuhipa1! | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuhipa1! | UserB@example.org | Bob    | User | mahara | internal | member |

Given the following "pages" exist:
     | title | description| ownertype | ownername |
     | Page UserA_01 | Page 01 description Aliquam eu nunc. In dui magna, posuere eget, vestibulum et, tempor auctor, justo. Nullam vel sem. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Aliquam erat volutpat. | user | UserA |
     | Page UserA_02 | Page 02 | user | UserA |

  And the following "permissions" exist:
     | title | accesstype |
     | Page UserA_01 | public |
     | Page UserA_02 | public |

Scenario: User reports objectionable content on a page and admin responds
    Given I log in as "UserB" with password "Kupuhipa1!"
    And I wait "1" seconds
    And I follow "Page UserA_01"
    And I press "More options"
    And I follow "Report objectionable material"
    And I fill in "Complaint" with "Some complain text"
    And I press "Notify administrator"
    And I should see "Your report has been sent."
    And I log out

    Given I log in as "UserA" with password "Kupuhipa1!"
    And I follow "Page UserA_01"
    Then I should see "Someone reported your page to contain objectionable content. Please review your page and make adjustments where needed."
    And I log out

    # Admin to Review objectionable material and click Still objectionable button and respond
    Given I log in as "admin" with password "Kupuh1pa!"
    And I wait "1" seconds
    And I follow "Page UserA_01"
    And I should see "This page, or something within it, has been reported as containing objectionable content. If this is no longer the case, you can click the button to remove this notice and notify the other administrators."
    When I press "Still objectionable"
    Then I should see "Review objectionable material"
    And I should see "Remove access"
    When I fill in "Review of complaint" with "As admin of this site, I think this is objectionable"
    And I press "Submit"
    Then I should see "Your message has been sent"
    And I log out

    # UserA log in and view Objectionable message
    When I log in as "UserA" with password "Kupuhipa1!"
    And I follow "Page UserA_01"
    And I should see "An administrator reviewed the page again and still finds it to contain objectionable content. Please check your notification for more information. You can then make changes and send a message to the administrator by clicking the \"Review objectionable content\" button or ask for clarification."
    And I log out

    # admin to Review objectionable material and click Not objectionable button
    Given I log in as "admin" with password "Kupuh1pa!"
    And I wait "1" seconds
    And I follow "Page UserA_01"
    Then I should see "This page, or something within it, has been reported as containing objectionable content. If this is no longer the case, you can click the button to remove this notice and notify the other administrators."
    When I press "Not objectionable"
    Then I should see "Your message has been sent"
    And I should not see "This page, or something within it, has been reported as containing objectionable content. If this is no longer the case, you can click the button to remove this notice and notify the other administrators."
