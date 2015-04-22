@javascript @core @core_administration
Feature: Missing language string when resizing images in plugin administration
 In order to know what the button does I need to see lang string
 As as admin
 So I know what I'm turning on or off.

Scenario: Checking the language string is visible (Bug 1446488)
 Given I log in as "admin" with password "Password1"
 When I go to "admin/extensions/pluginconfig.php?plugintype=artefact&pluginname=file&type=file"
 And I follow "Resize images on upload"
 Then I should see "Automatically resize large images on upload"

