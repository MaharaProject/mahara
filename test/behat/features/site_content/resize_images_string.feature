@javascript @core @core_administration
Feature: Missing language string when resizing images in plugin administration
 In order to know what the button does I need to see lang string
 As as admin
 So I know what I'm turning on or off.

Scenario: Checking the language string is visible (Bug 1446488)
 Given I log in as "admin" with password "Kupuh1pa!"
 And I go to the "artefact" plugin "file" configuration "file" type
 And I click on "Resize images on upload"
 Then I should see "Automatically resize large images on upload"

