@javascript @core @core_group
Feature: Show the block "Group portfolios" in the group homepage
    In order to see group pages, shared and submitted pages/collections to a group
    As a group member or group admin
    So I can see these lists on the block "Group portfolios" in the group homepage

Background:
    Given the following "institutions" exist:
     | name | displayname | registerallowed | registerconfirm |
     | instone | Institution One | ON | OFF |
     | insttwo | Institution Two | ON | OFF |

    And the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | instone | internal | staff |
     | UserB | Kupuh1pa! | UserB@example.org | Bob | User | instone | internal | member |
     | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | instone | internal | member |

    And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | GroupA | UserA | GroupA owned by UserA | standard | ON | OFF | all | ON | OFF | UserB, UserC |  |
     | GroupB | UserA | GroupB owned by UserA | standard | ON | OFF | all | OFF | OFF | UserB, UserC |  |
     | GroupC | UserA | GroupC owned by UserA | course | ON | OFF | all | ON | OFF | UserC | UserB |
     | GroupD | UserA | GroupD owned by UserA | standard | ON | OFF | all | ON | OFF | UserB, UserC |  |

    And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01 | user | UserA |
     | Page UserA_02 | Page 02 | user | UserA |
     | Page UserA_03 | Page 03 | user | UserA |
     | Page UserA_04 | Page 04 | user | UserA |
     | Page UserA_05 | Page 05 | user | UserA |
     | Page UserA_06 | Page 06 | user | UserA |
     | Page UserA_07 | Page 07 | user | UserA |
     | Page UserA_08 | Page 08 | user | UserA |
     | Page UserA_09 | Page 09 | user | UserA |
     | Page UserA_10 | Page 10 | user | UserA |
     | Page UserA_11 | Page 11 | user | UserA |
     | Page UserA_12 | Page 12 | user | UserA |
     | Page UserB_01 | UserB's page 01 | user | UserB |
     | Page UserB_02 | UserB's page 02 | user | UserB |
     | Page UserB_03 | UserB's page 03 | user | UserB |
     | Page UserB_04 | UserB's page 04 | user | UserB |
     | Page UserB_05 | UserB's page 05 | user | UserB |
     | Page UserB_06 | UserB's page 06 | user | UserB |
     | Page UserB_07 | UserB's page 07 | user | UserB |
     | Page GroupA_01 | Group page 01 | group | GroupA |
     | Page GroupA_02 | Group page 02 | group | GroupA |
     | Page GroupA_03 | Group page 03 | group | GroupA |
     | Page GroupA_04 | Group page 04 | group | GroupA |
     | Page GroupA_05 | Group page 05 | group | GroupA |
     | Page GroupA_06 | Group page 06 | group | GroupA |
     | Page GroupA_07 | Group page 07 | group | GroupA |
     | Page GroupA_08 | Group page 08 | group | GroupA |
    # To test shared/submitted views
     | Page UserC_01 | Page 01 | user | UserC |
     | Page UserC_02 | Page 02 | user | UserC |
     | Page UserC_03 | Page 03 | user | UserC |
     | Page UserC_04 | Page 04 | user | UserC |
     | Page UserC_05 | Page 05 | user | UserC |
     | Page UserC_06 | Page 06 | user | UserC |
     | Page UserC_07 | Page 07 | user | UserC |
     | Page UserC_08 | Page 08 | user | UserC |
     | Page UserC_09 | Page 09 | user | UserC |
     | Page UserC_10 | Page 10 | user | UserC |
     | Page UserC_11 | Page 11 | user | UserC |
     | Page UserC_12 | Page 12 | user | UserC |
     | Page UserC_13 | Page 13 | user | UserC |
     | Page UserC_14 | Page 14 | user | UserC |
     | Page UserC_15 | Page 15 | user | UserC |
     | Page UserC_16 | Page 16 | user | UserC |

    And the following "collections" exist:
     | title | description | ownertype | ownername | pages |
     | Collection UserA_01 | Collection 01 | user | UserA | Page UserA_06, Page UserA_12 |
     | Collection UserA_02 | Collection 02 | user | UserA | Page UserA_07 |
     | Collection UserA_03 | Collection 03 | user | UserA | Page UserA_08 |
     | Collection UserA_04 | Collection 04 | user | UserA | Page UserA_09 |
     | Collection UserA_05 | Collection 05 | user | UserA | Page UserA_10 |
     | Collection UserA_06 | Collection 06 | user | UserA | Page UserA_11 |
    # To test shared/submitted views
     | Collection UserC_01 | Collection 01 | user | UserC | Page UserC_05 |
     | Collection UserC_02 | Collection 02 | user | UserC | Page UserC_06 |
     | Collection UserC_03 | Collection 03 | user | UserC | Page UserC_07 |
     | Collection UserC_04 | Collection 04 | user | UserC | Page UserC_08 |
     | Collection UserC_05 | Collection 05 | user | UserC | Page UserC_13 |
     | Collection UserC_06 | Collection 06 | user | UserC | Page UserC_14 |
     | Collection UserC_07 | Collection 07 | user | UserC | Page UserC_15 |
     | Collection UserC_08 | Collection 08 | user | UserC | Page UserC_16 |

Scenario: The list of group pages, shared/submitted pages and collections should
be displayed page by page and sorted by "page title (A-Z)" or "most recently updated".
These list must take into account the sort option chosen in the block config (Bug 1457246)
    # Log in as a normal user
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Verifying log in was successful
    And I should see "Angela"
    And I should see "GroupA"
    # Share pages and collections to the "GroupA"
    # Edit access for Page UserA_01
    And I choose "Shared by me" in "Share" from main menu
    And I follow "Pages"
    And I click on "Edit access" in "Page UserA_01" row
    And I set the select2 value "Page UserA_01, Page UserA_02, Page UserA_03, Page UserA_04, Page UserA_05" for "editaccess_views"
    And I select "GroupA" from "accesslist[0][searchtype]"
    And I press "Save"
    # Edit access for Collection UserA_01
    And I choose "Shared by me" in "Share" from main menu
    And I follow "Collections"
    And I click on "Edit access" in "Collection UserA_01" row
    And I should not see "Collection UserA_02" in the "Collections text-box" property
    And I set the select2 value "Collection UserA_01, Collection UserA_02, Collection UserA_03, Collection UserA_04, Collection UserA_05, Collection UserA_06" for "editaccess_collections"
    And I select "GroupA" from "accesslist[0][searchtype]"
    And I press "Save"
    And I log out
    # Log in as a normal user
    Given I log in as "UserB" with password "Kupuh1pa!"
    # Verifying log in was successful
    And I should see "Bob"
    And I should see "GroupA"
    # Share pages and collections to the "GroupA"
    # Edit access for pages
    And I choose "Shared by me" in "Share" from main menu
    And I click on "Edit access" in "Page UserB_01" row
    And I set the select2 value "Page UserB_01, Page UserB_02, Page UserB_03, Page UserB_04, Page UserB_05, Page UserB_06, Page UserB_07" for "editaccess_views"
    And I select "GroupA" from "accesslist[0][searchtype]"
    And I press "Save"
    And I log out
    # Check the list of shared pages to group "GroupA"
    Given I log in as "UserC" with password "Kupuh1pa!"
    # Verifying log in was successful
    And I should see "Cecilia"
    And I should see "GroupA"
    And I scroll to the base of id "groups"
    And I follow "GroupA" in the "My groups box" property
    # Group portfolios
    And I should see "Page GroupA_05" in the "Group portfolios" property
    And I should not see "Page GroupA_06" in the "Group portfolios" property
    #And I follow "Next page" in the "div#groupviews_pagination" "css_element"
    And I jump to next page of the list "groupviews_pagination"
    And I should see "Page GroupA_08" in the "Group portfolios" property
    And I should not see "Page GroupA_05" in the "Group portfolios" property
    # Shared pages
    And I should see "Page UserA_01" in the "Pages shared with this group" property
    And I should see "Page UserA_05" in the "Pages shared with this group" property
    And I should not see "Page UserB_01" in the "Pages shared with this group" property
    #And I follow "2" in the "div#sharedviews_pagination" "css_element"
    And I jump to page "2" of the list "sharedviews_pagination"
    And I should see "Page UserB_05" in the "Pages shared with this group" property
    And I should not see "Page UserA_05" in the "Pages shared with this group" property
    #And I follow "3" in the "div#sharedviews_pagination" "css_element"
    And I jump to page "3" of the list "sharedviews_pagination"
    And I should see "Page UserB_06" in the "Pages shared with this group" property
    And I should not see "Page UserB_01" in the "Pages shared with this group" property
    # Shared collections
    And I should see "Collection UserA_05" in the "Collections shared with this group" property
    And I should not see "Collection UserA_06" in the "Collections shared with this group" property
    #And I follow "2" in the "div#sharedcollections_pagination" "css_element"
    And I jump to page "2" of the list "sharedcollections_pagination"
    And I should see "Collection UserA_06" in the "Collections shared with this group" property
    And I should not see "Collection UserA_05" in the "Collections shared with this group" property
    And I log out
    # Check that we can see submitted pages before editing/saving the configuration for group pages block
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I should see "GroupA"
    And I choose "Groups" in "Engage" from main menu
    And I scroll to the base of id "findgroups"
    And I follow "GroupA"
    And I select "Page UserB_01" from "group_view_submission_form_1_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Page UserB_02" from "group_view_submission_form_1_options"
    And I press "Submit"
    And I press "Yes"
    And I log out
    # Change the sort options in the "Group pages" block
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I follow "GroupA (Administrator)"
    And I wait "1" seconds
    And I should see "Page UserB_01"
    And I follow "Pages and collections (tab)"
    And I click on "Edit" in "Group homepage" card menu

    And I scroll to the id "column-container"
    And I configure the block "Group portfolios"
    And I set the following fields to these values:
    | Sort group pages and collections by | Most recently updated |
    | Sort shared pages and collections by | Most recently updated |
    | Sort submitted pages and collections by | Most recently submitted |
    And I press "Save"
    And I display the page
    # Update the group page "Page GroupA_06"
    And I follow "Pages and collections (tab)"
    And I click on "Edit" in "Page GroupA_06" card menu
    And I follow "Settings" in the "Toolbar buttons" property
    And I set the field "Page description" to "<p>Group page 06 (updated)</p>"
    And I scroll to the base of id "settings_submitform"
    And I press "Save"
    And I display the page
    #add test for group button lang string (Bug 1772327)
    And I click on "Edit"
    And I click on "Return to group pages and collections"
    And I should see "Pages and collections | GroupA"
    # Check if it is now in the first page of the list of group pages
    And I choose "Groups" in "Engage" from main menu
    And I scroll to the base of id "findgroups"
    And I follow "GroupA"
    And I should see "Page GroupA_06" in the "Group portfolios" property
    #And I follow "Next" in the "div#groupviews_pagination" "css_element"
    And I jump to next page of the list "groupviews_pagination"
    And I should not see "Page GroupA_06" in the "Group portfolios" property
    # Update the shared page "Page UserA_01"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    And I follow "Settings" in the "Toolbar buttons" property
    And I set the field "Page description" to "<p>Page 01 (updated)</p>"
    And I scroll to the base of id "settings_submitform"
    And I press "Save"
    And I display the page
    # Check if it is now in the first page of the list of shared pages
    And I choose "Groups" in "Engage" from main menu
    And I scroll to the base of id "findgroups"
    And I follow "GroupA"
    And I should see "Page UserA_01" in the "Pages shared with this group" property
    #And I follow "2" in the "div#sharedviews_pagination" "css_element"
    And I jump to page "2" of the list "sharedviews_pagination"
    And I should not see "Page UserA_01" in the "Pages shared with this group" property
    # Update the shared collection "Collection UserA_06"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Collection UserA_06" card menu
    And I fill in "Collection description" with "Collection 06 (updated)"
    And I scroll to the base of id "edit_submitform"
    And I press "Save"
    # Check if it is now in the first page of the list of shared collections
    And I choose "Groups" in "Engage" from main menu
    And I scroll to the base of id "findgroups"
    And I follow "GroupA"
    And I should see "Collection UserA_06" in the "Collections shared with this group" property
    #And I follow "2" in the "div#sharedcollections_pagination" "css_element"
    And I jump to page "2" of the list "sharedcollections_pagination"
    And I should not see "Collection UserA_06" in the "Collections shared with this group" property
    # Submit some pages and collections to the group "GroupA"
    And I select "Page UserA_01" from "group_view_submission_form_1_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Page UserA_02" from "group_view_submission_form_1_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Page UserA_03" from "group_view_submission_form_1_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Collection UserA_01" from "group_view_submission_form_1_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Collection UserA_02" from "group_view_submission_form_1_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Collection UserA_03" from "group_view_submission_form_1_options"
    And I press "Submit"
    And I press "Yes"
    # Check the list of submitted pages/collections
    And I should see "Page UserA_03" in the "Submissions to this group" property
    And I should see "Collection UserA_03" in the "Submissions to this group" property
    And I should not see "Page UserB_01" in the "Submissions to this group" property
    #And I follow "2" in the "div#allsubmitted_pagination" "css_element"
    And I jump to page "2" of the list "allsubmitted_pagination"
    And I should see "Page UserA_01" in the "Submissions to this group" property
    And I should not see "Page UserA_02" in the "Submissions to this group" property
    And I log out
    # Check pages and collections are shown in correct section
    # Share and submit pages and collections
    # Log in as a normal user
    Given I log in as "UserC" with password "Kupuh1pa!"
    # Verifying log in was successful
    And I should see "Cecilia"
    And I should see "GroupD"
    # Share pages and collections to the standard "GroupD"
    # Edit access for Page UserC_01, Page UserC_03, Page UserC_04
    And I choose "Shared by me" in "Share" from main menu
    And I follow "Pages"
    And I click on "Edit access" in "Page UserC_01" row
    And I set the select2 value "Page UserC_01, Page UserC_03, Page UserC_04" for "editaccess_views"
    And I select "GroupD" from "accesslist[0][searchtype]"
    And I press "Save"
    # Edit access for Collection UserC_01, Collection UserC_03, Collection UserC_04
    And I choose "Shared by me" in "Share" from main menu
    And I follow "Collections"
    And I click on "Edit access" in "Collection UserC_01" row
    And I set the select2 value "Collection UserC_01, Collection UserC_03, Collection UserC_04" for "editaccess_collections"
    And I select "GroupD" from "accesslist[0][searchtype]"
    And I press "Save"
    # Submit pages and collections to the "GroupD" and "GroupA"
    And I choose "Groups" in "Engage" from main menu
    And I follow "GroupD"
    And I scroll to the base of id "group_view_submission_form_4_options_container"
    And I select "Page UserC_03" from "group_view_submission_form_4_options"
    And I press "Submit"
    And I press "Yes"
    And I scroll to the base of id "group_view_submission_form_4_options_container"
    And I select "Collection UserC_03" from "group_view_submission_form_4_options"
    And I press "Submit"
    And I press "Yes"
    And I choose "Groups" in "Engage" from main menu
    And I scroll to the base of id "findgroups"
    And I follow "GroupA"
    And I select "Page UserC_04" from "group_view_submission_form_1_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Collection UserC_04" from "group_view_submission_form_1_options"
    And I press "Submit"
    And I press "Yes"
    And I log out
    #Check cases
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Verifying log in was successful
    And I should see "Angela"
    And I follow "GroupD"
    And I should not see "Page UserC_03" in the "Pages shared with this group" property
    And I should see "Page UserC_03" in the "Submissions to this group" property
    And I should see "Collection UserC_01" in the "Collections shared with this group" property
    And I should not see "Collection UserC_03" in the "Collections shared with this group" property
    And I should see "Collection UserC_04" in the "Collections shared with this group" property
    And I should see "Collection UserC_03" in the "Submissions to this group" property
    And I log out
    Given I log in as "UserB" with password "Kupuh1pa!"
    # Verifying log in was successful
    And I should see "Bob"
    And I should see "GroupD"
    And I follow "GroupD"
    And I should see "Page UserC_01" in the "Pages shared with this group" property
    And I should not see "Page UserC_02" in the "Pages shared with this group" property
    And I should see "Page UserC_03" in the "Pages shared with this group" property
    And I should see "Page UserC_04" in the "Pages shared with this group" property
    And I should see "Collection UserC_01" in the "Collections shared with this group" property
    And I should not see "Collection UserC_02" in the "Collections shared with this group" property
    And I should see "Collection UserC_03" in the "Collections shared with this group" property
    And I should see "Collection UserC_04" in the "Collections shared with this group" property
    And I log out
    # Share and submit pages and collections - for course group "GroupC"
    # Log in as a normal user
    Given I log in as "UserC" with password "Kupuh1pa!"
    # Verifying log in was successful
    And I should see "Cecilia"
    And I should see "GroupC"
    # Share pages and collections to the "GroupC"
    # Edit access for Page UserC_09, Page UserC_11, Page UserC_12
    And I choose "Shared by me" in "Share" from main menu
    And I follow "Pages"
    And I click on "Edit access" in "Page UserC_09" row
    And I set the select2 value "Page UserC_09, Page UserC_11, Page UserC_12" for "editaccess_views"
    And I select "GroupC" from "accesslist[0][searchtype]"
    And I press "Save"
    # Edit access for Collection UserC_05, Collection UserC_07, Collection UserC_08
    And I choose "Shared by me" in "Share" from main menu
    And I follow "Collections"
    And I click on "Edit access" in "Collection UserC_05" row
    And I set the select2 value "Collection UserC_05, Collection UserC_07, Collection UserC_08" for "editaccess_collections"
    And I select "GroupC" from "accesslist[0][searchtype]"
    And I press "Save"
    # Submit pages and collections to the "GroupC" and "GroupA"
    And I choose "Groups" in "Engage" from main menu
    And I follow "GroupC"
    And I scroll to the base of id "group_view_submission_form_3_options_container"
    And I select "Page UserC_10" from "group_view_submission_form_3_options"
    And I press "Submit"
    And I press "Yes"
    And I scroll to the base of id "group_view_submission_form_3_options_container"
    And I select "Page UserC_11" from "group_view_submission_form_3_options"
    And I press "Submit"
    And I press "Yes"
    And I scroll to the base of id "group_view_submission_form_3_options_container"
    And I select "Collection UserC_06" from "group_view_submission_form_3_options"
    And I press "Submit"
    And I press "Yes"
    And I scroll to the base of id "group_view_submission_form_3_options_container"
    And I select "Collection UserC_07" from "group_view_submission_form_3_options"
    And I press "Submit"
    And I press "Yes"
    And I choose "Groups" in "Engage" from main menu
    And I scroll to the base of id "findgroups"
    And I follow "GroupA"
    And I select "Page UserC_12" from "group_view_submission_form_1_options"
    And I press "Submit"
    And I press "Yes"
    And I select "Collection UserC_08" from "group_view_submission_form_1_options"
    And I press "Submit"
    And I press "Yes"
    And I log out
    #Check cases
    Given I log in as "UserB" with password "Kupuh1pa!"
    # Verifying log in was successful
    And I should see "Bob"
    And I should see "GroupC"
    And I follow "GroupC"
    And I should see "Page UserC_09" in the "Pages shared with this group" property
    And I should not see "Page UserC_10" in the "Pages shared with this group" property
    And I should not see "Page UserC_11" in the "Pages shared with this group" property
    And I should see "Page UserC_12" in the "Pages shared with this group" property
    And I should see "Page UserC_10" in the "Submissions to this group" property
    And I should see "Page UserC_11" in the "Submissions to this group" property
    And I should see "Collection UserC_05" in the "Collections shared with this group" property
    And I should not see "Collection UserC_06" in the "Collections shared with this group" property
    And I should not see "Collection UserC_07" in the "Collections shared with this group" property
    And I should see "Collection UserC_08" in the "Collections shared with this group" property
    And I should see "Collection UserC_06" in the "Submissions to this group" property
    And I should see "Collection UserC_07" in the "Submissions to this group" property
