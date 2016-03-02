@javascript @core @core_messages
Feature: Clicking on Inbox
   In order to click on the Inbox block's 'More' link
   As a student user
   So I can see more of my messages

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Andrea | Andrews | mahara | internal | member |
     | userB | Kupuhipa1 | test02@example.com | Barry | Bishop | mahara | internal | member |
     | userC | Kupuhipa1 | test03@example.com | Catriona | Carson | mahara | internal | member |
     | userD | Kupuhipa1 | test04@example.com | Doug | Davies | mahara | internal | member |
     | userE | Kupuhipa1 | test05@example.com | Elise | Edwards | mahara | internal | member |
     | userF | Kupuhipa1 | test06@example.com | Fred | Flintstone | mahara | internal | member |
     | userG | Kupuhipa1 | test07@example.com | Gillian | Granger | mahara | internal | member |

And the following "messages" exist:
     | emailtype | to | from | subject | messagebody | read | url | urltext |
     | friendrequest | userA | userB | New friend request | Friend request from Barry | 1 | user/view.php?id=[from] | Requests |
     | friendrequest | userA | userC | New friend request | Friend request from Catriona | 1 | user/view.php?id=[from] | Requests |
     | friendrequest | userA | userD | New friend request | Friend request from Doug | 1 | user/view.php?id=[from] | Requests |
     | friendrequest | userA | userE | New friend request | Friend request from Elise | 1 | user/view.php?id=[from] | Requests |
     | friendrequest | userA | userF | New friend request | Friend request from Fred | 1 | user/view.php?id=[from] | Requests |
     | friendrequest | userA | userG | New friend request | Friend request from Gillian | 1 | user/view.php?id=[from] | Requests |
     | friendaccept | userG | userA | Friend request accepted | Friend request accepted from Andrea | 1 | user/view.php?id=[to] | |

Scenario: Clicking on the Inbox link on the right menu (Bug 1427019)
   # Log in as users
   Given I log in as "userA" with password "Kupuhipa1"
   # Navigating to the Inbox via the 'more' link in the Inbox block
   And I wait "1" seconds
   And I follow "More"
   # Verifying that you do not see a page full of error messages
   And I should not see "Call stack"
   And I should see "Notifications"
   And I should see "Compose"
