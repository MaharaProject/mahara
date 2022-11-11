@javascript @core @core_artefact
Feature: Creating, sharing and deleting skins
As a user
I want to create a new skin, share a skin and delete a skin

Background:
    # Skins need to be enabled
    Given the following site settings are set:
     | field | value |
     | skins | 1 |

    And the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Betty | User | mahara | internal | member |
     | UserB | Kupuh1pa! | UserB@example.org | Veronica | User | mahara | internal | member |

    And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01| user | UserA |
     | Page UserB_01 | Page 01| user | UserB |

 Scenario: Create a skin with many customizations then apply it to a new page, then delete it
     Given I log in as "UserA" with password "Kupuh1pa!"
     # Attach file to be applied to skin
     And I choose "Files" in "Create" from main menu
     And I attach the file "Image1.jpg" to "File"
     And I choose "Skins" in "Create" from main menu
     And I click on "Create skin"
     And I click on "General"
     And I set the following fields to these values:
      | Skin title |A fabulous new skin |
      | Skin description | A fabulous new skin |
     And I select "This is a private skin" from "designskinform_viewskin_access"
     And I click on "Page" in the "Share tabs" "Misc" property
     And I scroll to the base of id "designskinform_header_background_image_open_upload_browse_container"
     And I click on "Add a file"
     And I click on "Select \"Image1.jpg\""
     And I select "Repeat both directions" from "designskinform_body_background_repeat"
     And I click on "Text" in the "Share tabs" "Misc" property
     And I uncheck "designskinform_view_text_heading_color_optional"
     And I fill in "Header text colour" with "DEB6D5"
     And I select "Century Gothic" from "designskinform_view_block_header_font"
     And I select "Theme default" from "designskinform_view_text_font_family"
     And I click on "Save"
     And I should see "A fabulous new skin"
     #Apply the skin to a page
     Given I choose "Portfolios" in "Create" from main menu
     And I click on "Edit" in "Page UserA_01" card menu
     And I click on "Configure" in the "Toolbar buttons" "Nav" property
     And I expand the section "Skin"
     And I scroll to the base of id "settings_skin_open"
     Then I select the skin "A fabulous new skin" from "userskins"
     And I wait "1" seconds
     And I should see "A fabulous new skin" in the "Current skin" "Misc" property
     And I click on "Save"
     And I click on "Display page"
     #Delete the skin
     And I choose "Skins" in "Create" from main menu
     And I should see "A fabulous new skin"
     And I click on "Delete"
     And I click on "Yes"
     And I should not see "A fabulous new skin"
     #Check the deleted skin has been removed from the page
     Given I choose "Portfolios" in "Create" from main menu
     And I click on "Edit" in "Page UserA_01" card menu
     And I click on "Configure" in the "Toolbar buttons" "Nav" property
     And I expand the section "Skin"
     And I should not see "A fabulous new skin"

Scenario: Create a private skin and check its visibility
    Given I log in as "UserA" with password "Kupuh1pa!"
    Given I choose "Skins" in "Create" from main menu
    And I click on "Create skin"
    And I click on "General"
    And I set the following fields to these values:
     | Skin title | "A fabulous private skin |
    # Create a private skin
    And I select "This is a private skin" from "designskinform_viewskin_access"
    And I click on "Save"
    And I click on "Create skin"
    And I click on "General"
    And I set the following fields to these values:
     | Skin title | A fabulous public skin |
    # Create a public skin
    And I select "This is a public skin" from "designskinform_viewskin_access"
    And I click on "Save"
    And I should see "A fabulous private skin"
    And I should see "A fabulous public skin"
    And I log out
    # Check privacy restrictions for skin
    Given I log in as "UserB" with password "Kupuh1pa!"
    Given I choose "Skins" in "Create" from main menu
    And I should see "A fabulous public skin"
    And I should not see "A fabulous private skin"
    And I click on "Add \"A fabulous public skin\" to favourites"
    Given I choose "Portfolios" in "Create" from main menu
    And I click on "Edit" in "Page UserB_01" card menu
    And I click on "Configure" in the "Toolbar buttons" "Nav" property
    And I expand the section "Skin"
    And I scroll to the base of id "settings_skin_container"
    # Apply a a skin saved to favourite skins
    And I click on "Favourite skins"
    Then I select the skin "A fabulous public skin" from "favorskins"
    And I wait "1" seconds
    And I should see "A fabulous public skin" in the "Current skin" "Misc" property
    And I click on "Save"
    And I click on "Display page"

Scenario: Check public/private skins on copied pages.
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Create a Public skin.
    And I choose "Skins" in "Create" from main menu
    And I click on "Create skin"
    And I click on "General"
    And I set the following fields to these values:
        | Skin title | Publicskin |
        | Skin description | A fabulous new skin |
    And I select "This is a public skin" from "designskinform_viewskin_access"
    And I click on "Save"
    # Create a Private skin.
    And I click on "Create skin"
    And I click on "General"
    And I set the following fields to these values:
        | Skin title | Privateskin |
        | Skin description | Another fabulous new skin |
    And I select "This is a private skin" from "designskinform_viewskin_access"
    And I click on "Save"
    # Create a public page with a Public Skin.
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Create" in the "Create" "Views" property
    And I click on "Page" in the dialog
    And I fill in the following:
    | Page title       | Public page with a skin that is public |
    | Page description | First description                      |
    # Open the 'Advanced' accordion
    And I expand the section "Skin"
    And I scroll to the base of id "settings_skin_open"
    And I select the skin "Publicskin" from "userskins"
    And I wait "1" seconds
    And I click on "Save"
    And I click on "Share" in the "Page action buttons" "Views" property
    And I click on "Advanced"
    And I enable the switch "Allow copying"
    And I select "Public" from "accesslist[0][searchtype]"
    And I click on "Save"
    # Create a public page with a Private Skin.
    And I choose "Portfolios" in "Create" from main menu
    And I should see "Portfolios" in the "H1 heading" "Common" property
    And I click on "Create" in the "Create" "Views" property
    And I click on "Page" in the dialog
    And I fill in the following:
    | Page title       | Public page with a skin that is private |
    | Page description | Second description                      |
    # Open the 'Advanced' accordion.
    And I expand the section "Skin"
    And I scroll to the base of id "settings_skin_open"
    And I select the skin "Privateskin" from "userskins"
    And I wait "1" seconds
    And I click on "Save"
    And I click on "Share" in the "Page action buttons" "Views" property
    And I click on "Advanced"
    And I enable the switch "Allow copying"
    And I select "Public" from "accesslist[0][searchtype]"
    And I click on "Save"
    And I log out
    And I log in as "UserB" with password "Kupuh1pa!"
    # View public pages.
    When I choose "Shared with me" in "Share" from main menu
    And I check "Public"
    And I click on "Search" in the "#search_submit_container" "css_element"
    # Test Public skin.
    And I scroll to the base of id "sharedviewlist"
    And I click on "Public page with a skin that is public"
    And I click on "More options" in the "Page action buttons" "Views" property
    And I click on "Copy"
    And I expand the section "Skin"
    And I scroll to the base of id "settings_skins_html_container"
    Then I should see "Current skin"
    And I should see "Publicskin"
    # Back to viewing public pages.
    When I choose "Shared with me" in "Share" from main menu
    And I check "Public"
    And I click on "Search" in the "#search_submit_container" "css_element"
    # Test Private skin.
    And I scroll to the base of id "sharedviewlist"
    And I click on "Public page with a skin that is private"
    And I click on "More options" in the "Page action buttons" "Views" property
    And I click on "Copy"
    And I expand the section "Skin"
    And I scroll to the base of id "settings_skins_html_container"
    Then I should see "Current skin"
    And I should not see "Privateskin"