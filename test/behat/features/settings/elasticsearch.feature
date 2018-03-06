@javascript @core @core_administration
Feature: Checking that elasticsearch plugin is installed
In order to index search results
As an admin
So I can search via elasticsearch

Scenario: Check elasticsearch plugin is ready
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Plugin administration" in "Extensions" from administration menu
 When I follow "Configuration for search elasticsearch"
 Then I should see "Failed to connect to 127.0.0.1 port 9200"
 # TODO: allow actual indexing / searching
