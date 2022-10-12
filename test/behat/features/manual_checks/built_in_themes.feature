@javascript @core @core_administration @manual
Feature: Visual check of homepage elements in built in themes.
  Core :: Homepage
  Ensure "make css" has been run.
  This is only a visual check that the elements are on the page and display
  without distortions.
  The tests will stop on a breakpoint so you can perform the manual check.

Background:
  Given I log in as "admin" with password "Kupuh1pa!"

Scenario Outline: Testing Themes
  Given the following site settings are set:
  | field | value   |
  | theme | <theme> |
  And I choose "Overview" in "Admin home" from administration menu
  And I wait until the page is ready
  And I scroll to the center of id "clear_caches"
  And I press "Clear caches"
  And I wait to be redirected
  And I go to the homepage
  And I insert breakpoint

  Examples:
    | theme         |
    | raw           |
    | default       |
    | maroon        |
    | ocean         |
    | primaryschool |
    | modern        |

