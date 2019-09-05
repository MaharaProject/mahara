@javascript @core @core_institution @core_administration
Feature: Create Institution tags
   In order to create institution tags
   As an admin I need to go to Institution tags page
   So I can add institution tags

Background:
    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm | tags |
    | instone | Institution One | ON | OFF | 1 |

    And the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | instone | internal | member |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01| user | UserA |

    And the following "journals" exist:
    | owner | ownertype | title | description | tags |
    | UserA | user | Mars journal | My Mars Mission | Mars |

    And the following "journalentries" exist:
    | owner | ownertype | title | entry | blog | tags | draft |
    | UserA | user | Mars party | I just landed on Mars, mission success | Mars journal | Mars | 0 |

Scenario: Creating institution tags
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"

    # Creating an Institution
    And I choose "Tags" in "Institutions" from administration menu
    And I follow "Create tag"
    And I set the field "Institution tag" to "One tag"
    And I press "Save"
    Then I should see "Institution tag saved"
    And I follow "Create tag"
    And I set the field "Institution tag" to "Two tag"
    And I press "Save"
    And I click on "Delete institution tag" in "Two tag" row
    Then I should see "Institution tag deleted successfully"
    And I log out

    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Journals" in "Create" from main menu
    And I click on "Mars journal"
    And I click on "Edit" in "Mars party" row
    And I fill in select2 input "editpost_tags" with "One tag" and select "Institution One: One tag (0)"
    And I press "Save entry"

    Given I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    And I follow "Settings" in the "Toolbar buttons" property
    And I fill in select2 input "settings_tags" with "One tag" and select "Institution One: One tag (1)"
    And I fill in select2 input "settings_tags" with "Test" and select "Test"
    And I press "Save"
    When I follow "Drag to add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on "Show more"
    And I click on "Tagged journal entries" in the "Content types" property
    And I fill in select2 input "instconf_tagselect" with "One tag" and select "Institution One: One tag"
    And I press "Save"
    Then I should see "Journal entries with tag \"Institution One: One tag\""

    And I display the page
    Then I should see "Institution One: One tag"

    Given I choose "Files" in "Create" from main menu
    And I attach the file "Image2.png" to "files_filebrowser_userfile"
    And I click on "Edit" in "Image2.png" row
    And I fill in select2 input "files_filebrowser_edit_tags" with "One tag" and select "Institution One: One tag (2)"
    And I fill in select2 input "files_filebrowser_edit_tags" with "Image" and select "Image"
    And I press "Save changes"

    Given I choose "Pages and collections" in "Create" from main menu
    And I follow "Tags" in the "#sb-tags" "css_element"
    And I follow "Edit tags"
    Then I should see "Test" in the "My tags list" property
    Then I should not see "Institution One: One tag" in the "My tags list" property
