@javascript @core @core_administration
Feature: Required social media
In order to make social media profile information required for the site
As an admin
So students have to provide mandatory credentials

Background:
 Given the following plugin settings are set:
 | plugintype | plugin  | field | value |
 | artefact | internal | profilemandatory | socialprofile |

Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

Scenario: Social media credentials upon logging in as student (Bug 1432988)
 When I log in as "UserA" with password "Kupuh1pa!"
 And I set the following fields to these values:
 | Social media | Facebook URL |
 | Your URL or username | https://www.facebook.com |
 And I click on "Submit"
 Then I should see "Portfolios shared with me"
