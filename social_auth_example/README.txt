SOCIAL AUTH EXAMPLE MODULE

CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Recommended modules
 * Installation
 * Configuration
 * How it works
 * Support requests

INTRODUCTION
------------

Social Auth Example Module is a Google Authentication integration for Drupal. It
is based on the Social Auth and Social API projects. This module serves as a
guide to create new implementers for Social Auth.

It adds to the site:
* A new url: /user/login/example.
* A settings form on /admin/config/social-api/social-auth/example page.
* A Google+ Logo in the Social Auth Login block.

REQUIREMENTS
------------

This module requires the following modules:

 * Social Auth (https://drupal.org/project/social_auth)
 * Social API (https://drupal.org/project/social_api)

INSTALLATION
------------

 * Download Google API PHP Client library
   (https://github.com/google/google-api-php-client). We recommend to use
   Composer module to install the library.

 * Install the dependencies: Social API and Social Auth.

 * Install as you would normally install a contributed Drupal module. See:
   https://drupal.org/documentation/install/modules-themes/modules-8
   for further information.

 * A more comprehensive installation instruction for Drupal 8 can be found at
   https://www.drupal.org/node/2764227.

CONFIGURATION
-------------

 * Add your Google project OAuth information in
   Configuration » User Authentication » Google.

 * Place a Social Auth Login block in Structure » Block Layout.

 * If you already have a Social Auth Login block in the site, rebuild the cache.


HOW IT WORKS
------------

Users can click on the Google+ logo on the Social Auth Login block
You can also add a button or link anywhere on the site that points 
to /user/login/example, so theming and customizing the button or link
is very flexible.

When the user opens the /user/login/example link, it automatically takes
the user to Google Accounts for authentication. Google then returns the user to
Drupal site. If we have an existing Drupal user with the same email address
provided by Google, that user is logged in. Otherwise a new Drupal user is
created.

SUPPORT REQUESTS
----------------

Before posting a support request, carefully read the installation
instructions provided in module documentation page.

Before posting a support request, check Recent log entries at
admin/reports/dblog

Once you have done this, you can post a support request on github:
https://github.com/drupalsocialinitiative/social_api_examples.

When posting a support request, please inform if you were able to see any errors
in Recent log entries.
