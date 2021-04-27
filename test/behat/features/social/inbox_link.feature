@javascript @core @core_messages
Feature: Clicking on Inbox
   In order to click on the Inbox block's 'More' link
   As a student user
   So I can see more of my messages

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
     | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | mahara | internal | member |
     | UserD | Kupuh1pa! | UserD@example.org | Dmitri | User | mahara | internal | member |
     | UserE | Kupuh1pa! | UserE@example.org | Evonne | User | mahara | internal | member |
     | UserF | Kupuh1pa! | UserF@example.org | Fergus | User | mahara | internal | member |
     | UserG | Kupuh1pa! | UserG@example.org | Gabi | User | mahara | internal | member |

And the following "messages" exist:
     | emailtype | to | from | subject | messagebody | read | url | urltext |
     | friendrequest | UserA | UserB | New contact request | Contact request from Bob | 1 | user/view.php?id=[from] | Requests |
     | friendrequest | UserA | UserC | New contact request | Contact request from Cecilia | 1 | user/view.php?id=[from] | Requests |
     | friendrequest | UserA | UserD | New contact request | Contact request from Dmitri | 1 | user/view.php?id=[from] | Requests |
     | friendrequest | UserA | UserE | New contact request | Contact request from Evonne | 1 | user/view.php?id=[from] | Requests |
     | friendrequest | UserA | UserF | New contact request | Contact request from Fergus | 1 | user/view.php?id=[from] | Requests |
     | friendrequest | UserA | UserG | New contact request | Contact request from Gabi | 1 | user/view.php?id=[from] | Requests |
     | friendaccept | UserG | UserA | Contact request accepted | Contact request accepted from Angela | 1 | user/view.php?id=[to] | |

Scenario: Clicking on the Inbox link on the right menu (Bug 1427019)
   # Log in as users
   Given I log in as "UserA" with password "Kupuh1pa!"
   # Navigating to the Inbox via the 'more' link in the Inbox block
   And I scroll to the base of id "column-container"
   And I follow "More"
   # Verifying that you do not see a page full of error messages
   And I should not see "Call stack"
   And I should see "Notifications"
   And I should see "Compose"
