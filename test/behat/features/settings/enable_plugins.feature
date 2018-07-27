@javascript @core @core_administration
Feature: Required plugins
In order to customise mahara we can turn off/on plugins
As an admin
I check that the plugins are activated/deactivated

Background:
    Given the following plugins are set:
    | plugintype | plugin | value |
    | blocktype  | annotation | 0 |
    | artefact   | plans | 0 |

Scenario: Checking that we can turn on/off plugins
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Plugin administration" in "Extensions" from administration menu
    Then I should see "Show" in the "annotation/annotation" row
    Then I should see "Show" in the "plans " row
    Then I should see "Show" in the "plans/plans" row
    Given the following plugins are set:
    | plugintype | plugin | value |
    | artefact   | plans | 1 |
    Then I should see "Hide" in the "plans " row
    Then I should see "Hide" in the "plans/plans" row
