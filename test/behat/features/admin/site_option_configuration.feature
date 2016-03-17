@javascript @core @core_administration
Feature: Configuring the site options page
In order to change the configuration settings on the site options page
As an admin
So I can configure the site to the way the client wants

Scenario: Turning the switches on and off on the Site Options page (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 And I go to "admin/site/options.php"
 # Verifying I'm on the right page
 And I should see "Here you can set some global options that will apply by default throughout the entire site."
 And I expand "Site settings" node
 And I expand "User settings" node
 And I expand "Search settings" node
 And I expand "Group settings" node
 And I expand "Institution settings" node
 And I expand "Account settings" node
 And I expand "Security settings" node
 And I expand "Proxy settings" node
 And I expand "Email settings" node
 And I expand "Notification settings" node
 And I expand "General settings" node
 And I expand "Logging settings" node
 # Checking that the default settings are correct
 And the following fields match these values:
| Drop-down navigation | 0 |
| Show homepage / dashboard information | 1 |
| Send weekly updates? | 0 |
| Users can choose page themes | 0 |
| Display remote avatars | 0 |
| Users can hide real names | 0 |
| Never display usernames | 0 |
| Show users in public search | 0 |
| Anonymous comments | 1 |
| Staff report access | 0 |
| Staff statistics access | 0 |
| Users can disable device detection | 0 |
| Require reason for masquerading | 0 |
| Notify users of masquerading | 0 |
| Show profile completion | 0 |
| Export to queue | 0 |
| Multiple journals | 0 |
| Allow group categories | 0 |
| Confirm registration | 0 |
| Users allowed multiple institutions | 1 |
| Auto-suspend expired institutions | 0 |
| Virus checking | 0 |
| Spamhaus URL blacklist | 0 |
| SURBL URL blacklist | 0 |
| Disable external resources in user HTML | 0 |
| reCAPTCHA on user registration form | 0 |
| Allow public pages | 1 |
| Allow public profiles | 1 |
| Allow anonymous pages | 0 |
| Sitemap | 1 |
| Portfolio search | 0 |
| Tag cloud | 1 |
| Maximum tags in cloud | 20 |
| Small page headers | 0 |
| Show online users | 1 |
| Registration agreement | 0 |
| License metadata | 0 |
| Custom licenses | 0 |
| Mobile uploads | 0 |
 # Changing all the switches from their default settings
 And I set the following fields to these values:
| Drop-down navigation | 1 |
| Show homepage / dashboard information | 0 |
| Send weekly updates? | 1 |
| Users can choose page themes | 1 |
| Display remote avatars | 1 |
| Users can hide real names | 1 |
| Never display usernames | 1 |
| Show users in public search | 1 |
| Anonymous comments | 0 |
| Staff report access | 1 |
| Staff statistics access | 1 |
| Users can disable device detection | 1 |
| Require reason for masquerading | 1 |
| Notify users of masquerading | 1 |
| Show profile completion | 1 |
| Export to queue | 1 |
| Multiple journals | 1 |
| Allow group categories | 1 |
| Confirm registration | 1 |
| Users allowed multiple institutions | 0 |
| Auto-suspend expired institutions | 1 |
| Virus checking | 1 |
| Spamhaus URL blacklist | 1 |
| SURBL URL blacklist | 1 |
| Disable external resources in user HTML | 1 |
| reCAPTCHA on user registration form | 1 |
| Allow public pages | 0 |
| Allow public profiles | 0 |
| Allow anonymous pages | 1 |
| Sitemap | 0 |
| Portfolio search | 1 |
| Tag cloud | 0 |
| Maximum tags in cloud | 20 |
| Small page headers | 1 |
| Show online users | 0 |
| Registration agreement | 1 |
| License metadata | 1 |
| Custom licenses | 1 |
| Mobile uploads | 1 |
# Setting the values back to their defaults
And I set the following fields to these values:
| Drop-down navigation | 0 |
| Show homepage / dashboard information | 1 |
| Send weekly updates? | 0 |
| Users can choose page themes | 0 |
| Display remote avatars | 0 |
| Users can hide real names | 0 |
| Never display usernames | 0 |
| Show users in public search | 0 |
| Anonymous comments | 1 |
| Staff report access | 0 |
| Staff statistics access | 0 |
| Users can disable device detection | 0 |
| Require reason for masquerading | 0 |
| Notify users of masquerading | 0 |
| Show profile completion | 0 |
| Export to queue | 0 |
| Multiple journals | 0 |
| Allow group categories | 0 |
| Confirm registration | 0 |
| Users allowed multiple institutions | 1 |
| Auto-suspend expired institutions | 0 |
| Virus checking | 0 |
| Spamhaus URL blacklist | 0 |
| SURBL URL blacklist | 0 |
| Disable external resources in user HTML | 0 |
| reCAPTCHA on user registration form | 0 |
| Allow public pages | 1 |
| Allow public profiles | 1 |
| Allow anonymous pages | 0 |
| Sitemap | 1 |
| Portfolio search | 0 |
| Tag cloud | 1 |
| Maximum tags in cloud | 20 |
| Small page headers | 0 |
| Show online users | 1 |
| Registration agreement | 0 |
| License metadata | 0 |
| Custom licenses | 0 |
| Mobile uploads | 0 |
And I press "Update site options"

