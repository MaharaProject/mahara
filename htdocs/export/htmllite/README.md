# HTML lite export

This export format allows you to export a stripped down portfolio as HTML files that can then be sent to a similarity / plagiarism checker such as Ouriginal as HTML file. The export only includes file types that the similarity checker accepts. It requires that your learning management system supports these web services calls.

In the case of Moodle (or Tōtara) there is an Ouriginal plugin that [Catalyst IT Limited Europe](https://www.catalyst-eu.net) enhanced to integrate with the HTML Lite web services calls. If you'd like to know more about the changes and when they will be open sourced, please get in touch with Catalyst IT Limited Europe.

If you use a different learning management system, additional changes would be needed as it depends on how the similarity checker integrates with the LMS.

To use this feature:

1. Enable the plugin in 'Administration menu → Extensions → Plugin administration → HTML Lite' by clicking the 'Show' button. Make sure that the normal HTML export is enabled, as it is a prerequisite for HTML lite to work.
2. Enable incoming web service requests and select the protocol that you want to use at 'Administration menu →  Web services → Configuration'.
3. Set up a web service on the same page:
  * Create a new service group and assign the function `mahara_submission_generate_view_for_plagiarism_test` to the group and make sure that 'Service' and 'User token access' are enabled.
  * Create a new service access token and select the newly created service group.
4. You can now access this service from an external source, for example to access it from a terminal via Curl:

```
curl --location --request POST 'http://example.com/webservice/rest/server.php' \
-F 'views[0][viewid]="8"' \
-F 'views[0][iscollection]=0' \
-F 'views[0][submittedhost]="fakeexternalsite"' \
-F 'views[0][exporttype]="htmllite"' \
-F 'wstoken="...your generated webservice token..."' \
-F 'wsfunction="mahara_submission_generate_view_for_plagiarism_test"'
```
Refer to `htdocs/export/htmllite/lib.php` for the file types that will be included in the export via the variabla `$validfiles`. Since similarity checkers only accept certain file types, Mahara can be configured to only send those along, hence a lightweight export format.
