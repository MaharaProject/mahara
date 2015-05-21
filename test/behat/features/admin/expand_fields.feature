@javascript @core @core_administration
Feature: Expanding all the fieldsets
In order to see all the fields that are available on the page
As as admin
I need to expand all the fieldsets

Scenario: Expanding all the fieldsets
 Given I log in as "admin" with password "Password1"
When I follow "Administration"
 And I follow "Configure site"
 Then I expand all fieldsets
 And I should see "Event log expiry"
