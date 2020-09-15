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
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01| user | UserA |

    And the following "educationhistory" exist:
    | user  | startdate  | enddate    | institution        | institutionaddress    | qualtype              | qualname                        | qualdescription                                                                                 | attachment |
    | UserA | 1 Jan 2009 | 2 Dec 2010 | University College | 23a O'Dell Boulevard  | Masters of Philosophy | Machine Learning - Creation 2.1 | This qualification is a 4 to 6 year degree that ends in an alternate (self-contained) universe. | Image2.png |
    | UserA | 1 Jan 2009 | 2 Dec 2010 | University of Life | 2/103 Industrial Lane | Masters of Arts       | North American Cultural Studies | This qualification is a 4.5-year degree that ends in writing a Master's thesis.                 | Image2.png |

    And the following "employmenthistory" exist:
    | user  | startdate  | enddate     | employer    | employeraddress | jobtitle     | attachment | positiondescription |
    | UserA | 1 Jan 2009 | 02 Dec 2010 | Catalyst IT | 150 Willis St   | Test Analyst | Image2.png | Software testing can be described as the process which helps to identify the correctness, completeness, security and quality of developed computer software. In a nutshell, testing is finding out how well something works; a good tester will try multiple avenues to break whatever it is they are testing. In computer hardware and software development, testing is used at key checkpoints in the overall process to determine whether objectives are being met. |

    And the following "achievements" exist:
    | user  | date       | title                      | attachment | description |
    | UserA | 12/07/2017 | Scrum Master Certification | Image2.png | The main role of a Scrum Master is to ensure smooth establishment, efficient and healthy progress and continuous improvement of Scrum Practices in an agile Scrum team. Therefore, competence and perspective of every single Scrum Team Member in an agile Scrum team to be able to act on behalf of and with a Scrum Master is a fundamental factor which determines the success level and lifetime of an agile Scrum team. Whether you act as Scrum Master or not in your Scrum team, it is profoundly important for you to have a clear understanding about how and what makes Scrum far more successful, efficient and delightful to work with than other project management frameworks. Therefore, we recommend you to obtain your Scrum Master Accredited Certification™ (SMAC) if you are conducting one of the following Software Engineering roles: Architect, Business Analyst, Designer, Product Manager, Program Manager, Programmer, Project Manager, Team Leader, Tester |

    And the following "books and publications" exist:
    | user  | date       | title                                                                  | contribution                        | description      | attachment |
    | UserA | 13/07/2017 | Measurement of the neutron beta decay asymmetry using machine learning | Dissertation – Doctor of Philosophy | Details ashgashg | Image2.png |

    And the following "professionalmemberships" exist:
    | user  | startdate   | enddate | title                       | description        | attachment |
    | UserA | 13/07/2017  | 14/09/2022 |Accredited Technologist | Accredited Technologist is the new standard for IT Professionals within the first few years of their career. | Image2.png |

Scenario: Creating a Cover letter
    Given I log in as "UserA" with password "Kupuh1pa!"
    When I choose "Résumé" in "Create" from main menu
    And I follow "Introduction"
    And I click on "Edit"
    And I fill in "A whole bunch of Texty text" in first editor
    And I click on "Save"
    Then I should see "Saved successfully"
    And I should see "A whole bunch of Texty text"

Scenario: Editing admin resume page (Bug 1426983)
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Editing resume
    When I choose "Résumé" in "Create" from main menu
    And I follow "Introduction"
    And I fill in the following:
    | Date of birth   | 1970/01/07 |
    | Place of birth | Wellington |
    | Citizenship | NZ |
    | Visa status | Worker |
    And I set the following fields to these values:
    | Woman | 1 |
    And I fill in the following:
    | Marital status | It's complicated |
    # Saving the information
    And I press "personalinformation_save"
    And I should see "Résumé saved"

Scenario: Editing Education and Employment info
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Editing resume
    When I choose "Résumé" in "Create" from main menu
    And I follow "Education and employment"
    # Adding Education history
    And I click on "Move down" in "North American Cultural Studies" row
    And I wait "1" seconds
    And I click on "Move up" in "North American Cultural Studies" row
    And I scroll to the id "main-nav"
    And I press "Add education history"
    And I set the following fields to these values:
    | addeducationhistory_startdate | 1 Jan 2017 |
    | addeducationhistory_institution | Mail-order PhD |
    | addeducationhistory_institutionaddress | 45 Empty St |
    And I click on "addeducationhistory_submit"
    And I should see "Saved successfully"
    # Adding an Employment history
    And I press "Add employment history"
    And I set the following fields to these values:
     | addemploymenthistory_startdate | 1 Jan 2009  |
     | addemploymenthistory_enddate | 02 Dec 2010 |
     | addemploymenthistory_employer | Xero |
     | addemploymenthistory_employeraddress | 3 Cable Street |
     | addemploymenthistory_jobtitle | Code Ninja |
     | addemploymenthistory_positiondescription | A programmer, computer programmer, developer, dev, coder, or software engineer is a person who creates computer software. The term computer programmer can refer to a specialist in one area of computer programming or to a generalist who writes code for many kinds of software. One who practices or professes a formal approach to programming may also be known as a programmer analyst. |
    And I scroll to the base of id "addemploymenthistory"
    And I attach the file "Image2.png" to "addemploymenthistory_attachments_files_0"
    # Verifying it saved
    And I click on "addemploymenthistory_submit"
    Then I should see "Saved successfully"
    And I click on "Move down" in "Test Analyst" row
    And I wait "1" seconds
    And I click on "Move up" in "Test Analyst" row
    # delete employment and education history  (Bug 1755669)
    And I scroll to the base of id "employmenthistorylist"
    And I wait "1" seconds
    And I click on "Delete \"North American Cultural Studies (Masters of Arts) at University of Life\"" delete button
    And I click on "Delete \"Code Ninja: Xero\"" delete button

    # When entire resume is displayed on Profile page, it should include employment address (Bug 1529750)
    Given I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Profile page" card menu
    When I follow "Drag to add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on blocktype "My entire résumé"
    And I set the field "Block title" to "My entire résumé"
    And I select "Automatically retract" from "Retractable"
    And I press "Save"
    And I display the page
    And I expand "My entire résumé" node
    # Check employment and education history deleted successfully (Bug 1755669)
    Then I should not see "Code Ninja"
    And I should not see "North American Cultural Studies"
    # Test whether Employment history shows address
    When I follow "Test Analyst at Catalyst IT"
    And I wait "1" seconds
    Then I should see "Address: 150 Willis St"
    # Test whether Education history shows address
    When I follow "Machine Learning - Creation 2.1 (Masters of Philosophy) at University College"
    And I wait "1" seconds
    And I scroll to the base of id "bottom-pane"
    Then I should see "Address: 23a O'Dell Boulevard"
    # Test whether a qualification with just start date and title also shows address
    When I scroll to the base of id "bottom-pane"
    And I follow "Mail-order PhD"
    Then I should see "45 Empty St"

Scenario: Adding Achievements
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Editing resume
    When I choose "Résumé" in "Create" from main menu
    And I follow "Achievements"
    And I click on "Add certifications, accreditations and awards"
    And I set the following fields to these values:
    | addcertification_date | 13/07/2017 |
    | addcertification_title | ISTQB Foundation Agile Tester Extension |
    | addcertification_description | Designed for testers holding the ISTQB® Foundation Certificate, this extension course provides an understanding of the fundamentals of testing in Agile projects. |
    And I scroll to the base of id "addcertification"
    And I attach the file "Image2.png" to "Attach file"
    And I wait "1" seconds
    And I press "Save"
    And I scroll to the id "main-nav"
    And I should see "Saved successfully"
    And I click on "Move down" in "Scrum Master Certification" row
    And I wait "1" seconds
    And I click on "Move up" in "Scrum Master Certification" row

    And I scroll to the base of id "addbookbutton"
    And I click on "Add books and publications"
    And I set the following fields to these values:
    | addbook_date | 20/06/2018 |
    | addbook_title | Normalising Te Reo Māori in Technology |
    | addbook_contribution | asdgfasg |
    | addbook_description | details asdfsda |
    And I scroll to the base of id "addbook"
    And I attach the file "Image2.png" to "addbook_attachments_files_0"
    And I click on "addbook_submit"
    And I scroll to the id "main-nav"
    And I wait "1" seconds
    And I should see "Saved successfully"
    And I click on "Move down" in "Measurement of the neutron beta decay asymmetry using machine learning" row
    And I wait "1" seconds
    And I click on "Move up" in "Measurement of the neutron beta decay asymmetry using machine learning" row

    # Adding Professional memberships
    And I scroll to the base of id "addmembershipbutton"
    And I press "Add professional membership"
    And I set the following fields to these values:
    | addmembership_startdate | 15/07/2017 |
    | addmembership_enddate | 29/09/2018 |
    | addmembership_title | sdrtyh |
    | addmembership_description | sdfh |
    And I scroll to the base of id "addmembership"
    And I attach the file "Image2.png" to "addmembership_attachments_files_0"
    And I click on "addmembership_submit"
    And I scroll to the id "main-nav"
    And I should see "Saved successfully"
    And I click on "Move down" in "Accredited Technologist" row
    And I wait "1" seconds
    And I click on "Move up" in "Accredited Technologist" row
    # check achievements can be deleted (Bug 1755669)
    And I click on "Delete \"sdrtyh\"" delete button
    And I wait "1" seconds
    And I should not see "sdrtyh"

Scenario: Adding Goals and Skills
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Editing resume
    When I choose "Résumé" in "Create" from main menu
    And I follow "Goals and skills"
    And I should see "My goals"
    And I should see "My skills"
    And I follow "Personal goals"
    And I set the field "Description" to "Become a certified diver"
    And I press "Add a file"
    And I attach the file "Image2.png" to "File"
    And I press "Close" in the "Upload dialog" property
    And I press "Save"
    And I should see "Saved successfully"
    And I scroll to the top
    And I follow "Academic goals"
    And I set the following fields to these values:
    | Description | Become tenured professor |
    And I press "Add a file"
    And I attach the file "Image2.png" to "File"
    And I press "Close" in the "Upload dialog" property
    And I press "Save"
    And I should see "Saved successfully"
    And I scroll to the top
    And I follow "Career goals"
    And I set the following fields to these values:
    | Description | whateve ry askdf |
    And I press "Add a file"
    And I attach the file "Image2.png" to "File"
    And I press "Close" in the "Upload dialog" property
    And I press "Save"
    And I should see "Saved successfully"
    And I scroll to the base of id "skills_edit_personalskill"
    And I follow "Personal skills"
    And I set the following fields to these values:
    | Description | whateve ry askdf |
    And I press "Add a file"
    And I attach the file "Image2.png" to "File"
    And I press "Close" in the "Upload dialog" property
    And I press "Save"
    And I should see "Saved successfully"
    And I scroll to the base of id "skills_edit_academicskill"
    And I follow "Academic skills"
    And I set the following fields to these values:
    | Description | whateve ry askdf |
    And I press "Add a file"
    And I attach the file "Image2.png" to "File"
    And I press "Close" in the "Upload dialog" property
    And I press "Save"
    And I should see "Saved successfully"
    And I scroll to the base of id "skills_edit_workskill"
    And I follow "Work skills"
    And I set the following fields to these values:
    | Description | whateve ry askdf |
    And I press "Add a file"
    And I attach the file "Image2.png" to "File"
    And I press "Close" in the "Upload dialog" property
    And I press "Save"
    And I should see "Saved successfully"

Scenario: Adding interests
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Editing resume
    When I choose "Résumé" in "Create" from main menu
    And I follow "Interests"
    And I press "Edit"
    And I set the following fields to these values:
    | Interest | running, swimming, skydiving, clarinet |
    And I press "Save"
    And I should see "Saved successfully"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    When I follow "Drag to add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on blocktype "One résumé field"
    And I select the radio "Interests"
    And I press "Save"
    And I display the page
    And I should see "clarinet" in the "Resume field block" property

Scenario: Adding license info
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Editing resume
    When I choose "Résumé" in "Create" from main menu
    And I follow "License"
    And I fill in the following:
    | License | http://creativecommons.org/licenses/by/4.0/ |
    And I follow "Advanced licensing"
    And I fill in the following:
    | Licensor| test1 |
    | Original URL | something here |
    And I press "Save"
    And I should see "Résumé saved"
