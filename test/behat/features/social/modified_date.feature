@javascript @core @core_artefact
Feature: Modified date on collections shared with a group
In order to seeing the last modified date on a collection shared to a group
As a student
So I can see when the collection was updated last


Scenario: Adding collection to group (Bug 1448807)
 Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |
 And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | admins |
     | GroupA | UserA | GroupA owned by UserA  | standard | ON | ON | all | ON | ON | UserA | UserA |
 And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01 | GroupA | UserA |
 When I log in as "UserA" with password "Kupuhipa1"
 And I choose "Pages and collections" in "Portfolio" from main menu
 And I follow "Add"
 And I click on "Collection" in the dialog
 And I set the following fields to these values:
 | Collection name * | The A team |
 And I press "Next: Edit collection pages"
 And I set the following fields to these values:
 | Page UserA_01 | 1 |
 And I press "Add pages"
 And I follow "Done"
 And I choose "Shared by me" in "Portfolio" from main menu
 And I follow "Edit access"
 And I select "GroupA" from "accesslist[0][searchtype]"
 And I press "Save"
 Then I should see "Access rules were updated for 1 page"
 And I choose "Groups" from main menu
 And I follow "GroupA"
 # the formats "strftimedate" and "j F Y" both resolve to dd Month YYYY, which is wanted here.
 And I should see the date "today" in the "#sharedcollectionlist" element with the format "d F Y"
