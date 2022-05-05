@javascript @core @blocktype @blocktype_online_users
Feature: Check the Recaptcha box is present if configured for the site

Background:
    Given the following site settings are set:
      | field                   | value                                    |
      | recaptchaonregisterform | 1                                        |
      | recaptchaprivatekey     | 6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe |
      | recaptchapublickey      | 6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI |

    And the following "institutions" exist:
      | name    | displayname     | registerallowed | registerconfirm |
      | instone | Institution One | ON              | ON              |

Scenario: Check for recaptcha box on the Contact page.
    Given I am on "/contact.php"
    Then I should see "reCAPTCHA challenge"

Scenario: Check for recaptcha box on the Registration page.
    Given I am on "/register.php"
    Then I should see "reCAPTCHA challenge"
