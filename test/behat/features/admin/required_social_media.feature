@javascript @core @core_administration
Feature: Required social media
In order to make social media profile information required for the site
As an admin
So students have to provide mandatory credentials

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |


Scenario: Social media credentials upon logging in as student (Bug 1432988)
 Given I log in as "admin" with password "Kupuhipa1"
 And I go to "/admin/extensions/pluginconfig.php?plugintype=artefact&pluginname=internal&type=profile"
 And I set the following fields to these values:
  | Social media | 1 |
 And I press "Save"
 And I should see "Settings saved"
 And I should not see "[[webpage/artefact.internal]]"
 And I follow "Logout"
 When I log in as "userA" with password "Kupuhipa1"
 And I set the following fields to these values:
 | Social network * | Facebook URL |
 And I set the following fields to these values:
 | Your URL or username * | https://www.facebook.com |
And I press "Submit"
And I should see "Latest pages"
