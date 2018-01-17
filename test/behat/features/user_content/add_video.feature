@javascript @core @core_artefact @play
Feature: Uploading videos
In order to upload a video
As an admin/user
So I can display videos on my pages

Scenario Outline: Uploading videos with a different file type (Bug 1445653)
  Given I log in as "admin" with password "Kupuh1pa!"
  When I choose "Files" in "Content" from main menu
  And I attach the file "<videoname>" to "File"
  Then I should see "<videolink>" in the "Filelist table" property

Examples:
  | videoname | videolink |
  | testvid1.3gp | testvid1.3gp |
  | testvid2.mkv | testvid2.mkv |
  | testvid3.mp4 | testvid3.mp4 |
