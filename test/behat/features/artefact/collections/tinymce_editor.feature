@javascript @core @core_artefact
Feature: Editing description of tinymce editor

Scenario: Typing a description into the text editor (Bug 1447449)
Given I log in as "admin" with password "Kupuhipa1"
And I follow "Portfolio"
And I press "Create page"
And I set the following fields to these values:
| Page title | Shark attacks |
| Page description | This page describes when sharks attack ^(^^%^%^^^ |
And I press "Save"
And I press "Done"
