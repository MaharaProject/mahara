@javascript @core
Feature: Profile completion functionality
    Site admin sets up profile completion and determins what fields are required
    so that the profile completion block will display on dashboard indicating what needs to be completed

Background:
    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm |
    | instone | Institution One | ON | OFF |

    Given the following "users" exist:
    | username | password | email | firstname | lastname  | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org | Bob | User | instone | internal  | member |

    Given the following plugins are set:
    | plugintype | plugin | value |
    | blocktype  | annotation | 1 |

    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Site options" in "Configure site" from administration menu
    And I expand the section "Side block settings"
    And I enable the switch "Show profile completion"
    And I click on "Update site options"

Scenario Outline: 1) Site admin enables the side block 'Profile completion' in Admin menu > Configure site > Site options > Side block settings
    2) site admin verify sections on the profile completion page
    3) site admin verify field options for profile section
    4) site admin enable 4 fields
    5) Institution user verify that the Profile completion side block is displayed on the Dashboard
    6) Institution user verify progress bar has gone from 50% to 75%
    # site admin verify sections on the profile completion page
    When I choose "Profile completion" in "Institutions" from administration menu
    And I select "<institution>" from "Institution"
    Then I should see "Profile"
    And I should see "Résumé"
    And I should see "Plans"
    And I should see "Journals"
    And I should see "Files"
    And I should see "Annotation"
    And I should see "Comment"
    And I should see "Peer assessment"
    # site admin verify field options for profile section
    When I expand the section "Profile"
    Then I should see "First name"
    And I should see "Last name"
    And I should see "Student ID"
    And I should see "Display name"
    And I should see "Introduction"
    And I should see "Official website address"
    And I should see "Personal website address"
    And I should see "Blog address"
    And I should see "Postal address"
    And I should see "Town"
    And I should see "City/region"
    And I should see "Country"
    And I should see "Home phone"
    And I should see "Business phone"
    And I should see "Mobile phone"
    And I should see "Fax number"
    And I should see "Occupation"
    And I should see "Industry"
    And I should see "Social media"
    And I should see "Join a group"
    And I should see "Make a friend"
    # site admin enable 4 fields
    When I enable the switch "First name"
    And I enable the switch "Last name"
    And I enable the switch "Student ID"
    And I enable the switch "City/region"
    And I click on "Submit"
    Then I should see "Progress bar saved successfully."
    Then I log out
    # No institution user verify that the Profile completion side block is displayed on the Dashboard
    Given I log in as "<user>" with password "Kupuh1pa!"
    Then I should see "Profile completion" in the "Progressbar block" "Misc" property
    And I should see "Profile completion tips"
    And I should see "50%" in the "Progressbar" "Misc" property
    When I choose "Profile" from account menu
    Then I should see "Profile"
    And I click on "About me"
    When I fill in "Student ID" with "123456"
    And I click on "Save profile"
    Then I should see "Profile saved successfully"
    # Verify progress bar has gone from 50% to 75%
    And I should see "75%" in the "Progressbar" "Misc" property
    And I log out

    Examples:
    | institution | user |
    | No Institution | UserA |
    | Institution One | UserB |
