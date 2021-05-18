# Office 365 Connector module

## INTRODUCTION

The Office 365 Connector module gives you a connector service that makes it
possible for developers to easilly connect to the Microsoft Graph API.

There are some submodules added to this module:

* o365_sso: This module makes it possible for site users to login via their
  Office 365 credentials. A "Login via SSO" link is added to the login form
  from where the user can login and is redirected back to the website. A Drupal
  user will be created (if non-existant) and logged in. This module is required
  and enabled automatically.
* o365_onedrive: This is a example module of how to implement a basic OneDrive
  page and block.
* o365_outlook_calendar: This is a example module of how to implement a basic
  calendar from Office 365.
* o365_outlook_mail: This is a example module of how to implement a basic
  mail implementation.
* o365_profile: This is a example module of how to implement a basic profile
  block.

### More information

* For a full description of the module, visit the project page:
  https://www.drupal.org/project/o365
* To submit bug reports and feature suggestions, or track changes:
  https://www.drupal.org/project/issues/o365

## REQUIREMENTS

This module requires the following modules (downloaded via composer.json):

* OAuth2 Client (https://www.drupal.org/project/oauth2_client)
* Microsoft Graph Library for PHP (https://github.com/microsoftgraph/msgraph-sdk-php)

## INSTALLATION

* Install as you would normally install a contributed Drupal module. Visit
  https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
  for further information.

## CONFIGURATION

* Configure the user permissions in Administration » People » Permissions

* Create a app in your Microsoft Portal (https://portal.azure.com/). Be sure
  to use the url "https://www.example.com/o365/callback" as your redirect url.
  This is needed for login purposes.

* Enter you app data on the API settings page: Configuration > System >
  Office 365 Settings > API settings (or
  https://www.example.com/admin/config/system/o365/settings/api).

* Optionally enable Verbose logging on the settings page: Configuration >
  System > Office 365 Settings (or
  https://www.example.com/admin/config/system/o365/settings).


## MAINTAINERS

Current maintainers:

* Fabian de Rijk (fabianderijk) - https://www.drupal.org/u/fabianderijk

This project has been sponsored by:

* Finalist
  Finalist implements Drupal CMS based on the needs of our customers: smart &
  lightweight to highly complex & sophisticated websites, communities and
  portals. Through our proven project approach and high involvement we are
  able to produce websites within a stipulated time period. Regardless of
  complexity or scale, we guarantee full service, a risk-free project
  approach, transparent pricing models and vendor independency.
