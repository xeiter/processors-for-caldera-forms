=== Plugin Name ===
Contributors: xeiterochek
Tags: crm, lead, zoho, form, caldera
Requires at least: 4.7
Tested up to: 4.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add extra processors for Caldera Forms. Currently the following processors are available:

1. Zoho CRM integration - submit leads from Caldera Forms to Zoho CRM

== Description ==

Zoho CRM Integration

If you need the forms on your site to submit lead information to the installation of Zoho CRM, this plugin is for you.

You can map the form fields to any lead fields in Zoho CRM.

If different forms need to submit to different instances of Zoho CRM, that is possible too.

== Installation ==

1. Upload `processors-for-caldera-forms.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create a form using Caldera Forms
4. Start editing the form
5. Head to Processors and add one of the new processors
6. (Zoho CRM) Enter the Zoho CRM authentication token into the dedicated field
7. Save and reload the form
8. (Zoho CRM) Head back to Processors and click on "Zoho CRM Integration" processor
9. (Zoho CRM) Set up mapping for any required field. The dropdown field across each form field lists all the lead fields available in your Zoho CRM installation

== Frequently Asked Questions ==

= (Zoho CRM) What is authentication token? =

Authentication token is a way to authenticate your WordPres installation when working with Zoho CRM API. In other words, it is like a username and password used when saving details of new leads into your Zoho CRM installation. Authetincation token allows Zoho CRM to check that the submission that your website makes is legit. It also allows to identify what Zoho CRM account to save the lead information to.

= (Zoho CRM) How do I find what my authentication token is? =

Please see instructions at https://www.zoho.com/crm/help/api/using-authentication-token.html

= (Zoho CRM) I want different Caldera Forms to submit to different Zoho CRM installations. In this possible? =

Yes, definitely. For each Caldera Form, enter authentication tokens of the Zoho CRM installations that you need those form to send leads to.

= (Zoho CRM) I want one Caldera Form to submit to two or more different Zoho CRM installations. In this possible? =

Yes, definitely. Add multiple Zoho CRM processors to the same form and configure them using different authentication aokens.

== Changelog ==

= 0.1.2 =
* Switched to using the "caldera_forms_includes_complete" hook for checking if Caldera Forms core has been loaded

= 0.1.1 =
* Fixed bug with auto-initialisation of parameters array
* Minor refactoring of lead creation method for Zoho CRM

= 0.1.0 =
* Initial release
* Adding Zoho CRM integration processor
