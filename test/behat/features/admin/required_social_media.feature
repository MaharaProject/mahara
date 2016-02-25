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
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |

Scenario: Social media credentials upon logging in as student (Bug 1432988)
 When I log in as "userA" with password "Kupuhipa1"
 And I set the following fields to these values:
 | Social network | Facebook URL |
 | Your URL or username | https://www.facebook.com |
 And I press "Submit"
 Then I should see "Latest changes I can view"
