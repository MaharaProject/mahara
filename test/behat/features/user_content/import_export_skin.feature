@javascript @core @core_artefact
Feature: Import and export skins
    As a user
    I want to import and export skins created in release 20.10
    so that I can create and use backups of my skins
    # This feature was created for partial coverage of Bug #1877497: Sorting out problems with skin export / import
    # The import file may need replacing in future releases, it contains:
    # - a simple private skin (only header background and page colours options reset, others all at default values)
    # - a complex public skin (all default options have been reset, two images selected).

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

Scenario: Import and check the two exported Release 20-10 skins
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Import the two skins
    And I choose "Skins" in "Create" from main menu
    And I click on "More options"
    And I click on "Import" in the ".dropdown-menu" "css_element"
    And I attach the file "20-10_2ExportedSkins.xml" to "Valid XML file "
    And I click on "Import" in the "#importskinform_submit_container" "css_element"
    Then I should see "Skin imported successfully"
    And I should see "2 skins"
    And I should see "20-10 SimplePersonalSkin (created Dan)"
    And I should see "20-10 CompPersSkin (Dan-public)"

    # Check that the simple private skin has imported correctly, only the header and page background colors
    # have been changed, the other options should retain the correct default values.
    # Note: the export was created using the Raw theme thus default colours will relate to those values.
    # The following value is
    When I click on "Preview of \"20-10 SimplePersonalSkin (created Dan)\" - click to edit"
    Then the following fields match these values:
    | Skin title | 20-10 SimplePersonalSkin (created Dan) |
    | Skin description | Dan's simple skin (private) - retain all default values except header and page background colours. |
    | Skin access | This is a private skin |
    And I set the following fields to these values:
    | Skin title | A fabulous new skin |
    When I follow "Page"
    Then the "Header background colour" field should contain "#CBF706"
    When I follow "Text"
    And I select "This is a private skin" from "designskinform_viewskin_access"
    And I follow "Page"
    And I press "Add a file"
    And I press "Select \"sunset-1645103_1920by655.jpg\""
    And I select "Repeat both directions" from "designskinform_body_background_repeat"
    And I scroll to the top
    And I follow "Text"
    And I uncheck "designskinform_view_text_heading_color_optional"
    And I fill in "Header text colour" with "DEB6D5"
    And I select "Century Gothic" from "designskinform_view_block_header_font"
    And I select "Theme default" from "designskinform_view_text_font_family"
    And I press "Save"
    And I should see "A fabulous new skin"