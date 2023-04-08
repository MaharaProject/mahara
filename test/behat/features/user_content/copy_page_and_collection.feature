@javascript @core @core_artefact
Feature: Previewing copy a page or collection and then copying it
In order to use the pop out window on copy a page or collection
As an admin/user
I need to be able to click on the links

Background:
  Given the following "pages" exist:
     | title | description | ownertype | ownername | tags |
     | Page admin_01 | Page 01 | admin | admin | page,one |
     | Page admin_02 | Page 02 | admin | admin | page,two |
     | Page admin_03 | Page 03 | admin | admin | page,three |

  Given the following "collections" exist:
     | title | description | ownertype | ownername | pages |
     | Collection admin_01 | Collection 01 | user | admin | Page admin_01, Page admin_03 |

Scenario: Accessing the popup window in the Copy or page or collection (Bug 1361450)
  Given I log in as "admin" with password "Kupuh1pa!"
  And I choose "Portfolios" in "Create" from main menu
  And I click on "Copy"
  And I click on "Collection admin_01"
  And I should see "Collection admin_01 by admin"
  And I click on "Close"
  Then I should not see "Collection admin_01 by admin"

  # Add a block to the page
  When I choose "Portfolios" in "Create" from main menu
  And I click on "Edit" in "Page admin_02" card menu
  When I click on the add block button
  And I click on "Add" in the "Add new block" "Blocks" property
  And I click on blocktype "Text"
  And I set the field "Block title" to "Text Block 1"
  And I set the field "Block content" to "Here is a new block."
  And I fill in select2 input "instconf_tags" with "block" and select "block"
  And I click on "Save"

  # Copy a page directly from its location
  And I display the page
  And I click on "More options"
  And I click on "Copy"
  And I click on "Save"
  And I display the page
  And I should see "Page admin_02 v.2"
  And I should see "Tags: block, page, two"

  # Copy a page
  And I choose "Portfolios" in "Create" from main menu
  And I click on "Copy"
  And I click on "Copy page" in "Page admin_02" row
  And I click on "Save"
  And I should see "Here is a new block."

  # Add the page to the collection which has block
  When I choose "Portfolios" in "Create" from main menu
  And I click on "Manage" in "Collection admin_01" card menu
  And I check "Page admin_02"
  And I click on "Add pages"
  And I should see "1 page added to collection"
  And I click on "Continue: Share"

  # Copy a collection directly from its location
  And I choose "Portfolios" in "Create" from main menu
  And I click on "Collection admin_01"
  And I click on "More options"
  And I click on "Copy"
  And I click on "Collection" in the "#copyview-form" "css_element"
  And I click on "Continue: Edit collection pages"
  And I click on "Continue: Share"

  # Copy a collection
  And I choose "Portfolios" in "Create" from main menu
  And I click on "Copy"
  And I click on "Copy collection" in "Collection admin_01" row
  And I click on "Continue: Edit collection pages"
  And I click on "Continue: Share"

  #veryfying if the collection is copied directly from its location
  And I choose "Portfolios" in "Create" from main menu
  And I click on "Collection admin_01 v.2"
  And I click on "Next page"
  And I click on "Next page"
  Then I should see "Text Block 1"

  #Veryfying if the page that has block been copied to collection
  And I choose "Portfolios" in "Create" from main menu
  And I click on "Collection admin_01 v.3"
  And I click on "Next page"
  And I click on "Next page"
  Then I should see "Text Block 1"
