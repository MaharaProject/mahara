# General TODO

## Requeue

Many of the content types do not have a Requeue option yet.

## Testing

### Manual testing

Refer to the BUGS tab on Stevens testing spreadsheet.

Note: Verbose output and steps for + re-indexing/re-saving bugs in [TODO_re-indexing_re-saving_bugs.md](./TODO_re-indexing_re-saving_bugs.md)

#### Bug 2

> **Ready to test**
Looking at the `Elasticsearch7Type_group` class the function `get_record_data_by_id()` is commented out.  This function is available on the majority of other type classes.  Hopefully it is just a matter of uncommenting, verifying what it is doing and pushing it out.

### Behat testing

Work in progress.

#### [wishlist] es7_files_search.feature

Note: This is the expected response from ES5. Improving this would be good but isn't priority.

The search term `cameila` gets no hits, but `cameila.pdf` does.

Options:

* Strip the file extension and add the remainder of the filename to the `catch_all`
* Alter the test
* Create a `catch_partial` field that allows for wildcard matching so `cameila` would catch `cameila*`

#### es7_journals_plans_tags_search.feature

There are comments on the patchset about this.

#### es7_pages_collections_blocks_search.feature

Collections appear to be not being indexed at all.

#### es7_resume_search.feature

Are profile fields being indexed?

YES - Profile fields are ing indexed.

ES5 global search | filter: Text > Profile (27)

* ABOUT ME
  * [ ] Introduction - WYSIWYG
  * [ ] Student ID 🔴 Missing - probably for privacy reasons
  * [ ] Last name 🔴 Missing - probably for privacy reasons
  * [ ] First name 🔴 Missing - probably for privacy reasons
  * [ ] Display name 🔴 Missing - probably for privacy reasons
* [x] Contact information - there is an entry with no description + *Used on pages* + Created by *username*
  * [ ] Email address 🔴 Missing - probably for privacy reasons
  * [x] Official website address
  * [x] Personal website ADDRESS
  * [x] Blog address
  * [x] Postal address
  * [x] Town
  * [x] City/region
  * [x] Country
  * [x] Home phone
  * [x] Business phone
  * [x] Mobile phone
  * [x] Fax number
* Social media
  * [x] icon with URL
  * [x] Used on pages...Profile, and ...
* General
  * [x] Occupation
  * [x] Industry

Every entry has a 'Created by [username]'
Some have a Used on pages: Phone, Contact information, social media

Note: It's hard to figure out which field of the profile section the value is from simply based on the icon, maybe we should have some alternative text on the icon to know which profile field it references.

* A road is used for 'postal address' but it's not super obvious
* An RSS feed icon is used for the 'official web address'
* In the 'Profile' search, the value is the heading whereas in 'Resume' the title is the Resume field and the value is the displayed text...

ES5 global search | filter: Text > Resume (15)

* INTRODUCTION
  * [x]  cover letter - WYSIWYG
  * [ ]  dob
  * [ ]  place of birth
  * [ ]  citizenship
  * [ ]  visa status
  * [ ]  gender
  * [ ]  marital status
* EDUCATION AND EMPLOYMENT
  * [x]  edu history - attachments, start date, end date, institution, address, qualtype+name, desc - In 'Contains:'
  * [x]  employment history - Yes, in 'Contains...' + used on page
* ACHIEVEMENTS
  * certifications, accreditation and awards
    * [x]  date, title, desc, attachment - Yes, in 'Contains..'
  * books and publications
    * [x]  date, title, contribution, details of contribution, attachment
    * [x]  search entry has... 'contains: The life-changing magic...' + 'Used on page'
  * professional memberships
    * [x]  start, end date, title, description,attachments - in contains
* GOALS AND SKIlLS
  * my goals - in description +  'Used on page'
    * [x]  personal goals
    * [x]  academic goals - in description + 'Used on page'
    * [x]  career goals - in description + 'Used on page'
  * my skills
    * [x]  personal skills - in description
    * [x]  academic skills - in description + 'Used on page'
      * [x]  attachment
    * [x]  work skills 0 in description
* INTERESTS + 'Used on page'
  * [x]  WYSIWYG in description + 'Used on page'

Notes:

* 'Used on page' could be changed to 'Appears on page(s)'
* Everything in 'resume' had 'used on page' except for the attachment for academic skills

ES7 - numbers match the results of the ES5 searches for profile and resume :)

* Fonts are no longer coloured, e.g. Instagram was coloured before, now it's back and white
* Fixed error when looking for interaction_forum_post subjects because it couldn't index
* Everything else looks pretty much the same

#### es7_tabs_filter_sort_owner_pagination.feature

Is this the test or the index?  Adding a breakpoint before the failed step, I can see that the text that should not be visible is actually visible.  Clicking that link I am able to view the page it links to.  This suggests that it is a valid result to see.

## Unassigned Shards

I see we get unassigned shards frequently. Need to figure out why this is happening and if it is an 'us' thing or an ES thing. At least figuring out how to tidy them up would be good.

To see this just hit the Plugin config page.

# Places to check for behat

## Locations does_search_plugin_have() is called

These are checking for a particular method on the search class. This is an
initial list to check for behaviours that use search that may be outside of
the standard search results page.

* htdocs/lib/dml.php:1313
  * Not sure how to test this with Behat. It appears to ensure all bits of a view are added to the queue for indexing.
  * Unit Test?

Starting at Reports: https://master-testing-mahara.sites.catlearn.nz/admin/users/statistics.php

Click Configure Report to get the form. The reports here are presented in the Report type selector.

* htdocs/lib/statistics.php:1511
  * User Activity: People > Account Activity
  * This looks to be returning an aggregation of EventType, LastLogin, and LastActivity.
* htdocs/lib/statistics.php:1840
  * Collaboration Stats: People > Collaboration
  * I'm unsure what this one is doing. It looks to be giving totals for 'YearWeek', 'EventType', 'ResourceType', 'ParentResourceType'.  This looks to be a nested aggregation too so it will be 'YearWeek' > 'EventType' > 'ResourceType' > 'ParentResourceType'. i.e. Multiple YearWeeks. Each with multiple EventTypes, each with multiple Resource Types, each with multiple ParentResourceTypes.
* htdocs/lib/statistics.php:3386
  * Group Stats: Groups > Groups
  * The ES7 query here is returning an aggregation array and an array of Group IDs. The code that works with this is dense. Too dense.  I don't know what it does and I've been looking at it for about half an hour now.

## Search results page.

Delete this file?

This page is/was a proof of concept. I don't believe that it is currently in
use. The expectation was that this would be the default search page.  e.g. we
wouldn't have a plugin specific endpoint for search results. This required
extensive modification to the `elasticsearch` and `internal` search plugins so
was out of scope for the `elasticsearch7` work.

This is safe to ignore for now.

* htdocs/search/index.php:57
* htdocs/search/index.php:59
* htdocs/search/index.php:76
* htdocs/search/index.php:132
* htdocs/search/index.php:193
