@javascript @core @core_messages @core_administration
Feature: Admins are allowed to see more types of messages than a user
In order to see what types are visible to me
As an admin/student
So I can filter messages

Background:
 Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |

Scenario Outline: Selection options to filter messages as an admin  (Bug 1433342)
 Given I log in as "admin" with password "Password1"
 When I follow "Inbox"
 And I select "<types>" from "Activity type"

Examples:
| types |
| Administration messages |
| Contact us |
| Feedback |
| Feedback on annotations |
| Group message |
| Institution message |
| Message from other users |
| New forum post |
| New page access |
| Objectionable content |
| Objectionable content in forum |
| Repeat virus upload |
| System message |
| Virus flag release |
| Watchlist |

Scenario Outline: Selecting options to filter messages as a student (Bug 1433342)
 Given I log in as "userA" with password "Password1"
 And I follow "Groups"
 And I follow "Create group"
  And I fill in "Group name" with "Jurassic Park"
  And I press "Save group"
  And I am on homepage
 When I follow "Inbox"
 And I select "<types>" from "Activity type"

 Examples:
 | types |
 | Feedback |
 | Feedback on annotations |
 | Group message |
 | Institution message |
 | Message from other users |
 | New forum post |
 | New page access |
 | Objectionable content in forum |
 | System message |
 | Watchlist |

