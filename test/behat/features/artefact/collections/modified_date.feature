@javascript @core @core_artefact
Feature: Modified date on collections shared with a group
In order to seeing the last modified date on a collection shared to a group
As a student
So I can see when the collection was updated last


Scenario: Adding collection to group (Bug 1448807)
 Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
 And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | admins |
     | group 01 | userA | This is group 01 | standard | ON | ON | all | ON | ON | userA | userA |
 And the following "pages" exist:
     | title | description| ownertype | ownername |
     | Site Page 01 | This is the page 01 of the site | group 01 | userA |
 When I log in as "userA" with password "Kupuhipa1"
 And I choose "Collections" in "Portfolio"
 And I follow "New collection"
 And I set the following fields to these values:
 | Collection name * | The A team |
 And I press "Next: Edit collection pages"
 And I set the following fields to these values:
 | Site Page 01 | 1 |
 And I press "Add pages"
 And I wait until the page is ready
 And I follow "Done"
 And I choose "Shared by me" in "Portfolio"
 And I follow "Edit access"
 And I select "group 01" from "accesslist[0][searchtype]"
 And I press "Save"
 Then I should see "Access rules were updated for 1 page(s)"
 And I follow "Groups"
 And I follow "group 01"
 And I should see "Updated" in the "#sharedcollectionlist" element

