 @javascript @core @core_view @core_portfolio
 Feature: Adding the contextual help for pages in "Portfolio"/"Skins" menu
  In order to see the contextual help for pages in "Portfolio"/"Skins" menu
  As a student
  So I can click the (i) icon next to the page header to see its description

Background:
  Given the following site settings are set:
      | field | value |
      | skins | 1 |

  Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |

Scenario: Accessing help icons under Skin tabs (Bug 1411070)
    Given I log in as "userA" with password "Password1"
    And I follow "Portfolio"
    And I choose "Skins" in "Portfolio"
    When I click on "Help icon"
    And I should see "Skins help you customise the look of your portfolio pages to give them a personal touch." in the "div#helpstop" element
    And I press "Create skin"
    And I click on "Help icon"
    Then I should see "You can design your own skin" in the "div#helpstop" element
    And I choose "Skins" in "Portfolio"
    And I press "Import skin(s)"
    And I click on "Help icon"
    And I should see "You can import skins from other Mahara sites." in the "div#helpstop" element
