@javascript @core @core_account @core_content
Feature: Editing a profile page
   In order to edit a profile page
   As an admin I need to go to Content
   So I can edit the admin profile page

Scenario: Editing admin profile page (Bug: 1426983)
    # Log in as users
    Given I log in as "admin" with password "Kupuhipa1"
    # Updating Profile
    When I follow "Content"
    And I fill in the following:
    | First name | Test     |
    | Last name | Admin     |
    | Student ID | 1234     |
    | Display name  | Test Admin   |
    # Updating Contact information tab
    And I follow "Contact information"
    And I follow "Add email address"
    And I fill in the following:
    | addnewemail | example22@example.com |
    | Official website address | www.catalyst.net.nz   |
    | Personal website address | www.stuff.co.nz |
    | Blog address | www.blog.com |
    | Postal address  | 150 Willis Street  |
    | Town   | Wellington |
    | City/region | CBD  |
    | Home phone | 04928375 |
    | Business phone | 040298375 |
    | Mobile phone | 0272093875482 |
    | Fax number   | 09237842 |
    And I press "Save profile"
    # Verifying the settings saved
    And I should see "Profile saved successfully"
    # Updating Social media tab
    And I follow "Social media"
    And I follow "New social media account"
    And I fill in the following:
    | editprofileform_profiletype_other | http://github.com/MaharaProject |
    | Your URL or username | https://twitter.com/MaharaProject |
    And I press "Save"
    # Verifying the settings held, navitgating to dashboard page to check
    Then I follow "Dashboard"
    And I should see "Test Admin"
    # Resetting/Editing details
    And I follow "Content"
    And I fill in the following:
    | First name   | Admin  |
    | Last name | User  |
    | Student ID ||
    |  Display name   | Admin User|
    And I press "Save profile"
    # Verifing settings saved
    And I should see "Profile saved successfully"
    # Editing contact information
    And I follow "Contact information"
    And I follow "Add email address"
    And I fill in the following:
    | Official website address ||
    | Personal website address ||
    | Blog address ||
    | Postal address  ||
    | Town   ||
    | City/region ||
    | Home phone ||
    | Business phone ||
    | Mobile phone ||
    | Fax number   ||
    And I press "Save profile"
    And I should see "Profile saved successfully"
    # Verifying changes has been made navigating to dashboard page to checked
    Then I follow "Dashboard"
    And I should see "Admin User"
