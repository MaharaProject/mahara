@javascript @core_group @failed
Feature: Show the block "Group pages" in the group homepage
    In order to see group pages, shared and submitted pages/collections to a group
    As a group member or group admin
    So I can see these lists on the block "Group pages" in the group homepage

Background:
    Given the following "institutions" exist:
     | name | displayname | registerallowed | registerconfirm |
     | instone | Institution One | ON | OFF |
     | insttwo | Institution Two | ON | OFF |
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | instone | internal | staff |
     | userB | Kupuhipa1 | test02@example.com | Son | Nguyen | instone | internal | member |
     | userC | Kupuhipa1 | test03@example.com | Tim | Bruce | instone | internal | member |
    And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | Group Y | userA | This is group Y | standard | ON | OFF | all | OFF | OFF | userB, userC |  |
     | Group Z | userA | This is group Z | standard | ON | OFF | all | ON | OFF | userB, userC |  |
    And the following "pages" exist:
      | title | description| ownertype | ownername |
      | Page userA_01 | This is the page 01 | user | userA |
      | Page userA_02 | This is the page 02 | user | userA |
      | Page userA_03 | This is the page 03 | user | userA |
      | Page userA_04 | This is the page 04 | user | userA |
      | Page userA_05 | This is the page 05 | user | userA |
      | Page userA_06 | This is the page 06 | user | userA |
      | Page userA_07 | This is the page 07 | user | userA |
      | Page userA_08 | This is the page 08 | user | userA |
      | Page userA_09 | This is the page 09 | user | userA |
      | Page userA_10 | This is the page 10 | user | userA |
      | Page userA_11 | This is the page 11 | user | userA |
      | Page userA_12 | This is the page 12 | user | userA |
      | Page userB_01 | userB's page 01 | user | userB |
      | Page userB_02 | userB's page 02 | user | userB |
      | Page userB_03 | userB's page 03 | user | userB |
      | Page userB_04 | userB's page 04 | user | userB |
      | Page userB_05 | userB's page 05 | user | userB |
      | Page userB_06 | userB's page 06 | user | userB |
      | Page userB_07 | userB's page 07 | user | userB |
      | Page Group Z_01 | Group page 01 | group | Group Z |
      | Page Group Z_02 | Group page 02 | group | Group Z |
      | Page Group Z_03 | Group page 03 | group | Group Z |
      | Page Group Z_04 | Group page 04 | group | Group Z |
      | Page Group Z_05 | Group page 05 | group | Group Z |
      | Page Group Z_06 | Group page 06 | group | Group Z |
      | Page Group Z_07 | Group page 07 | group | Group Z |
      | Page Group Z_08 | Group page 08 | group | Group Z |
    And the following "collections" exist:
      | title | description| ownertype | ownername | pages |
      | Collection userA_01 | This is the collection 01 | user | userA | Page userA_06, Page userA_12 |
      | Collection userA_02 | This is the collection 02 | user | userA | Page userA_07 |
      | Collection userA_03 | This is the collection 03 | user | userA | Page userA_08 |
      | Collection userA_04 | This is the collection 04 | user | userA | Page userA_09 |
      | Collection userA_05 | This is the collection 05 | user | userA | Page userA_10 |
      | Collection userA_06 | This is the collection 06 | user | userA | Page userA_11 |

Scenario: The list of group pages, shared/submitted pages and collections should
be displayed page by page and sorted by "page title (A-Z)" or "most recently updated".
These list must take into account the sort option choosen in the block config (Bug 1457246)
    # Log in as a normal user
    Given I log in as "userA" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Pete"
    And I should see "Group Z"
    # Share pages and collections to the "Group Z"
    # Edit access for Page userA_01
    And I choose "Shared by me" in "Portfolio"
    And I follow "Pages" in the "div#main-column-container" "css_element"
    And I click on "Edit access" in "Page userA_01" row
    And I set the select2 value "Page userA_01, Page userA_02, Page userA_03, Page userA_04, Page userA_05" for "editaccess_views"
    And I select "Group Z" from "accesslist[0][searchtype]"
    And I press "Save"
    # Edit access for Collection userA_01
    And I choose "Shared by me" in "Portfolio"
    And I follow "Collections" in the "div#main-column-container" "css_element"
    And I click on "Edit access" in "Collection userA_01" row
    And I should not see "Collection userA_02" in the "ul.select2-selection__rendered" "css_element"
    And I set the select2 value "Collection userA_01, Collection userA_02, Collection userA_03, Collection userA_04, Collection userA_05, Collection userA_06" for "editaccess_collections"
    And I select "Group Z" from "accesslist[0][searchtype]"
    And I press "Save"
    And I log out
    # Log in as a normal user
    Given I log in as "userB" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Son"
    And I should see "Group Z"
    # Share pages and collections to the "Group Z"
    # Edit access for pages
    And I choose "Shared by me" in "Portfolio"
    And I click on "Edit access" in "Page userB_01" row
    And I set the select2 value "Page userB_01, Page userB_02, Page userB_03, Page userB_04, Page userB_05, Page userB_06, Page userB_07" for "editaccess_views"
    And I select "Group Z" from "accesslist[0][searchtype]"
    And I press "Save"
    And I log out
    # Check the list of shared pages to group "Group Z"
    Given I log in as "userC" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Tim"
    And I should see "Group Z"
    And I follow "Group Z"
    # Group pages
    And I should see "Page Group Z_01" in the "ul#groupviewlist" "css_element"
    And I should see "Page Group Z_05" in the "ul#groupviewlist" "css_element"
    And I should not see "Page Group Z_06" in the "ul#groupviewlist" "css_element"
    #And I follow "Next page" in the "div#groupviews_pagination" "css_element"
    And I jump to next page of the list "groupviews_pagination"
    And I wait "10" seconds
    And I should see "Page Group Z_06" in the "ul#groupviewlist" "css_element"
    And I should see "Page Group Z_08" in the "ul#groupviewlist" "css_element"
    And I should not see "Page Group Z_05" in the "ul#groupviewlist" "css_element"
    # Shared pages
    And I should see "Page userA_01" in the "ul#sharedviewlist" "css_element"
    And I should see "Page userA_05" in the "ul#sharedviewlist" "css_element"
    And I should not see "Page userB_01" in the "ul#sharedviewlist" "css_element"
    #And I follow "2" in the "div#sharedviews_pagination" "css_element"
    And I jump to page "2" of the list "sharedviews_pagination"
    And I should see "Page userB_01" in the "ul#sharedviewlist" "css_element"
    And I should see "Page userB_05" in the "ul#sharedviewlist" "css_element"
    And I should not see "Page userA_05" in the "ul#sharedviewlist" "css_element"
    And I should not see "Page userB_06" in the "ul#sharedviewlist" "css_element"
    #And I follow "3" in the "div#sharedviews_pagination" "css_element"
    And I jump to page "3" of the list "sharedviews_pagination"
    And I should see "Page userB_06" in the "ul#sharedviewlist" "css_element"
    And I should see "Page userB_07" in the "ul#sharedviewlist" "css_element"
    And I should not see "Page userA_01" in the "ul#sharedviewlist" "css_element"
    And I should not see "Page userB_01" in the "ul#sharedviewlist" "css_element"
    # Shared collections
    And I should see "Collection userA_01" in the "ul#sharedcollectionlist" "css_element"
    And I should see "Collection userA_05" in the "ul#sharedcollectionlist" "css_element"
    And I should not see "Collection userA_06" in the "ul#sharedcollectionlist" "css_element"
    #And I follow "2" in the "div#sharedcollections_pagination" "css_element"
    And I jump to page "2" of the list "sharedcollections_pagination"
    And I should see "Collection userA_06" in the "ul#sharedcollectionlist" "css_element"
    And I should not see "Collection userA_05" in the "ul#sharedcollectionlist" "css_element"
    And I log out
    # Change the sort options in the "Group pages" block
    Given I log in as "userA" with password "Kupuhipa1"
    And I should see "Group Z"
    And I follow "Group Z"
    And I follow "Pages" in the "ul.nav-inpage" "css_element"
    And I click on "Edit \"Group homepage\""
    And I configure the block "Group pages"
    And I set the following fields to these values:
    | Sort group pages by | Most recently updated |
    | Sort shared pages and collections by | Most recently updated |
    | Sort submitted pages and collections by | Most recently submitted |
    And I press "Save"
    And I display the page
    # Update the group page "Page Group Z_06"
    And I follow "Pages" in the "ul.nav-inpage" "css_element"
    And I click on "Edit \"Page Group Z_06\""
    And I follow "Edit title and description"
    And I set the field "Page description" to "<p>Group page 06 (updated)</p>"
    And I press "Save"
    And I display the page
    # Check if it is now in the first page of the list of group pages
    And I follow "Groups"
    And I follow "Group Z"
    And I should see "Page Group Z_06" in the "ul#groupviewlist" "css_element"
    #And I follow "Next" in the "div#groupviews_pagination" "css_element"
    And I jump to next page of the list "groupviews_pagination"
    And I should not see "Page Group Z_06" in the "ul#groupviewlist" "css_element"
    # Update the shared page "Page userA_01"
    And I choose "Portfolio"
    And I click on "Edit \"Page userA_01\""
    And I follow "Edit title and description"
    And I set the field "Page description" to "<p>This is the page 01 (updated)</p>"
    And I press "Save"
    And I display the page
    # Check if it is now in the first page of the list of shared pages
    And I choose "Groups"
    And I follow "Group Z"
    And I should see "Page userA_01" in the "ul#sharedviewlist" "css_element"
    #And I follow "2" in the "div#sharedviews_pagination" "css_element"
    And I jump to page "2" of the list "sharedviews_pagination"
    And I should not see "Page userA_01" in the "ul#sharedviewlist" "css_element"
    #And I follow "3" in the "div#sharedviews_pagination" "css_element"
    And I jump to page "3" of the list "sharedviews_pagination"
    And I should not see "Page userA_01" in the "ul#sharedviewlist" "css_element"
    # Update the shared collection "Collection userA_06"
    And I choose "Collections" in "Portfolio"
    And I click on "Edit \"Collection userA_06\""
    And I set the field "Collection description" to "This is the collection 06 (updated)"
    And I press "Save"
    # Check if it is now in the first page of the list of shared collections
    And I choose "Groups"
    And I follow "Group Z"
    And I should see "Collection userA_06" in the "ul#sharedcollectionlist" "css_element"
    #And I follow "2" in the "div#sharedcollections_pagination" "css_element"
    And I jump to page "2" of the list "sharedcollections_pagination"
    And I should not see "Collection userA_06" in the "ul#sharedcollectionlist" "css_element"
    # Submit some pages and collections to the group "Group Z"
    And I select "Page userA_01" from "group_view_submission_form_2_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Page userA_02" from "group_view_submission_form_2_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Page userA_03" from "group_view_submission_form_2_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Collection userA_01" from "group_view_submission_form_2_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Collection userA_02" from "group_view_submission_form_2_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Collection userA_03" from "group_view_submission_form_2_options"
    And I press "Submit"
    And I press "Yes"
    And I log out
    Given I log in as "userB" with password "Kupuhipa1"
    And I should see "Group Z"
    And I follow "Group Z"
    And I select "Page userB_01" from "group_view_submission_form_2_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Page userB_02" from "group_view_submission_form_2_options"
    And I press "Submit"
    And I press "Yes"
    And I log out
    # Check the list of submitted pages/collections
    Given I log in as "userA" with password "Kupuhipa1"
    And I follow "Group Z"
    And I should see "Page userB_02" in the "ul#allsubmissionlist" "css_element"
    And I should see "Page userB_01" in the "ul#allsubmissionlist" "css_element"
    And I should see "Collection userA_03" in the "ul#allsubmissionlist" "css_element"
    And I should not see "Page userA_03" in the "ul#allsubmissionlist" "css_element"
    #And I follow "2" in the "div#allsubmitted_pagination" "css_element"
    And I jump to page "2" of the list "allsubmitted_pagination"
    And I should see "Page userA_03" in the "ul#allsubmissionlist" "css_element"
    And I should see "Page userA_01" in the "ul#allsubmissionlist" "css_element"
    And I should not see "Page userB_02" in the "ul#allsubmissionlist" "css_element"
    And I log out
