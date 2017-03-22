@javascript @core @core_artefact @core_content
Feature: Editing a Resume page
   In order to edit a resume page
   As a user I need to go to Content
   So I can edit the resume page

Background:

    Given the following site settings are set:
     | field | value |
     | licensemetadata | 1 |

    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |

Scenario: Editing admin resume page (Bug 1426983)
    Given I log in as "userA" with password "Kupuhipa1"
    # Editing resume
    When I choose "Résumé" in "Content" from Main menu
    And I follow "Introduction"
    And I fill in the following:
    | Date of birth   | 1970/01/07 |
    | Place of birth | Wellington |
    | Citizenship | NZ |
    | Visa status | Worker |
    And I set the following fields to these values:
    | Female | 1 |
    And I fill in the following:
    | Marital status | It's complicated |
    # Saving the information
    And I press "personalinformation_save"
    And I should see "Résumé saved"
    And I follow "Education and employment"
    # Adding Education history
    And I press "Add education history"
    And I set the following fields to these values:
     | addeducationhistory_startdate | 1 Jan 2009 |
     | addeducationhistory_enddate | 2 Dec 2010 |
     | addeducationhistory_institution | something |
     | addeducationhistory_institutionaddress | something again |
     | addeducationhistory_qualtype | 1 |
     | addeducationhistory_qualname | something |
     | addeducationhistory_qualdescription | qually |
    And I scroll to the base of id "addeducationhistory_attachments_list"
    And I attach the file "Image2.png" to "Attach file"
    # Saving the changes
    And I click on "addeducationhistory_submit"
    And I should see "Saved successfully"
    And I press "Add education history"
    And I set the following fields to these values:
     | addeducationhistory_startdate | 1 Jan 2009 |
     | addeducationhistory_enddate | 2 Dec 2010 |
     | addeducationhistory_institution | this is an institution |
     | addeducationhistory_institutionaddress | adjaskgljas |
     | addeducationhistory_qualtype | 2 |
     | addeducationhistory_qualname | wqoiretoiqswhogh |
     | addeducationhistory_qualdescription | qually1 |
    And I scroll to the base of id "addeducationhistory_attachments_list"
    And I attach the file "Image2.png" to "Attach file"
    And I click on "addeducationhistory_submit"
    And I should see "Saved successfully"
    And I click on "Move down" in "something" row
    And I click on "Move up" in "something" row
    # Adding an Employment history
    And I press "Add employment history"
    And I set the following fields to these values:
     | addemploymenthistory_startdate | 1 Jan 2009  |
     | addemploymenthistory_enddate | 02 Dec 2010 |
     | addemploymenthistory_employer | Test |
     | addemploymenthistory_employeraddress | Test |
     | addemploymenthistory_jobtitle | Test |
     | addemploymenthistory_positiondescription | Test |
    And I scroll to the base of id "addemploymenthistory_attachments_list"
    And I attach the file "Image2.png" to "addemploymenthistory_attachments_files_0"
    # Verifying it saved
    And I click on "addemploymenthistory_submit"
    Then I should see "Saved successfully"
    And I press "Add employment history"
    And I set the following fields to these values:
     | addemploymenthistory_startdate | 1 Jan 2009  |
     | addemploymenthistory_enddate | 02 Dec 2010 |
     | addemploymenthistory_employer | asdhgol |
     | addemploymenthistory_employeraddress | qouweyiugqs |
     | addemploymenthistory_jobtitle | aoshdguiahsg |
     | addemploymenthistory_positiondescription | aushghasdg |
    And I scroll to the base of id "addemploymenthistory_attachments_list"
    And I attach the file "Image2.png" to "addemploymenthistory_attachments_files_0"
    # Verifying it saved
    And I click on "addemploymenthistory_submit"
    Then I should see "Saved successfully"
    And I click on "Move down" in "Test" row
    And I click on "Move up" in "Test" row
    # Adding Achievements
    And I scroll to the top
    And I follow "Achievements"
    And I scroll to the base of id "addcertificationbutton"
    And I click on "Add certifications, accreditations and awards"
    # Adding Certifications, accreditations and awards
    And I set the following fields to these values:
    | Date | 12/07/2017 |
    | Title | Cert |
    | Description | somethings |
    And I scroll to the base of id "addcertification_attachments_list"
    And I attach the file "Image2.png" to "Attach file"
    And I press "Save"
    And I should see "Saved successfully"
    And I scroll to the base of id "addcertificationbutton"
    And I click on "Add certifications, accreditations and awards"
    And I set the following fields to these values:
    | Date | 13/07/2017 |
    | Title | dfgfdsg |
    | Description | alfksjgaasd |
    And I scroll to the base of id "addcertification_attachments_list"
    And I attach the file "Image2.png" to "Attach file"
    And I press "Save"
    And I should see "Saved successfully"
    And I click on "Move down" in "Cert" row
    And I click on "Move up" in "Cert" row
    # Adding Books and publications
    And I scroll to the base of id "addbookbutton"
    And I click on "Add books and publications"
    And I set the following fields to these values:
    | addbook_date | 13/07/2017 |
    | addbook_title | Book1 |
    | addbook_contribution | alfksjgaasd |
    | addbook_description | details ashgashg |
    And I scroll to the base of id "addbook_attachments_list"
    And I attach the file "Image2.png" to "addbook_attachments_files_0"
    And I click on "addbook_submit"
    And I should see "Saved successfully"
    And I scroll to the base of id "addbookbutton"
    And I click on "Add books and publications"
    And I set the following fields to these values:
    | addbook_date | 20/06/2018 |
    | addbook_title | dfgjj |
    | addbook_contribution | asdgfasg |
    | addbook_description | details asdfsda |
    And I scroll to the base of id "addbook_attachments_list"
    And I attach the file "Image2.png" to "addbook_attachments_files_0"
    And I click on "addbook_submit"
    And I should see "Saved successfully"
    And I click on "Move down" in "Book1" row
    And I click on "Move up" in "Book1" row
    # Adding Professional memberships
    And I scroll to the base of id "addmembershipbutton"
    And I press "Add professional membership"
    And I set the following fields to these values:
    | addmembership_startdate | 13/07/2017 |
    | addmembership_enddate | 14/09/2018 |
    | addmembership_title | Mr Membership |
    | addmembership_description | asdfsetew |
    And I scroll to the base of id "addmembership_attachments_list"
    And I attach the file "Image2.png" to "addmembership_attachments_files_0"
    And I click on "addmembership_submit"
    And I should see "Saved successfully"
    And I scroll to the base of id "addmembershipbutton"
    And I press "Add professional membership"
    And I set the following fields to these values:
    | addmembership_startdate | 15/07/2017 |
    | addmembership_enddate | 29/09/2018 |
    | addmembership_title | sdrtyh |
    | addmembership_description | sdfh |
    And I scroll to the base of id "addmembership_attachments_list"
    And I attach the file "Image2.png" to "addmembership_attachments_files_0"
    And I click on "addmembership_submit"
    And I should see "Saved successfully"
    And I click on "Move down" in "Mr Membership" row
    And I click on "Move up" in "Mr Membership" row
    And I scroll to the top
    And I follow "Goals and skills"
    And I should see "My goals"
    And I should see "My skills"
    And I follow "Personal goals"
    And I set the following fields to these values:
    | Description | whateve ry askdf |
    And I press "Add a file"
    And I wait "1" seconds
    And I attach the file "Image2.png" to "File"
    And I press "Close" in the "#editgoalsandskills_filebrowser_upload_browse" "css_element"
    And I wait "1" seconds
    And I press "Save"
    And I should see "Saved successfully"
    And I scroll to the top
    And I follow "Academic goals"
    And I set the following fields to these values:
    | Description | whateve ry askdf |
    And I press "Add a file"
    And I wait "1" seconds
    And I attach the file "Image2.png" to "File"
    And I press "Close" in the "#editgoalsandskills_filebrowser_upload_browse" "css_element"
    And I wait "1" seconds
    And I press "Save"
    And I should see "Saved successfully"
    And I scroll to the top
    And I follow "Career goals"
    And I set the following fields to these values:
    | Description | whateve ry askdf |
    And I press "Add a file"
    And I wait "1" seconds
    And I attach the file "Image2.png" to "File"
    And I press "Close" in the "#editgoalsandskills_filebrowser_upload_browse" "css_element"
    And I wait "1" seconds
    And I press "Save"
    And I should see "Saved successfully"
    And I scroll to the base of id "skills_edit_personalskill"
    And I follow "Personal skills"
    And I set the following fields to these values:
    | Description | whateve ry askdf |
    And I press "Add a file"
    And I wait "1" seconds
    And I attach the file "Image2.png" to "File"
    And I press "Close" in the "#editgoalsandskills_filebrowser_upload_browse" "css_element"
    And I wait "1" seconds
    And I press "Save"
    And I should see "Saved successfully"
    And I scroll to the base of id "skills_edit_academicskill"
    And I follow "Academic skills"
    And I set the following fields to these values:
    | Description | whateve ry askdf |
    And I press "Add a file"
    And I wait "1" seconds
    And I attach the file "Image2.png" to "File"
    And I press "Close" in the "#editgoalsandskills_filebrowser_upload_browse" "css_element"
    And I wait "1" seconds
    And I press "Save"
    And I should see "Saved successfully"
    And I scroll to the base of id "skills_edit_workskill"
    And I follow "Work skills"
    And I set the following fields to these values:
    | Description | whateve ry askdf |
    And I press "Add a file"
    And I wait "1" seconds
    And I attach the file "Image2.png" to "File"
    And I press "Close" in the "#editgoalsandskills_filebrowser_upload_browse" "css_element"
    And I wait "1" seconds
    And I press "Save"
    And I should see "Saved successfully"
    And I scroll to the top
    And I follow "Interests"
    And I press "Edit"
    And I set the following fields to these values:
    | resumefieldform_interest | test |
    And I press "Save"
    And I should see "Saved successfully"
    And I scroll to the top
    And I follow "License"
    And I set the following fields to these values:
    | License | Creative Commons Attribution 4.0 |
    And I follow "Advanced licensing"
    And I set the following fields to these values:
    | Licensor| test1 |
    | Original URL | something here |
    And I press "Save"
    And I should see "Résumé saved"
    # Logging out and loggin in as as student user/ then ending
    And I log out
