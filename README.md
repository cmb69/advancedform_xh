# Advancedform\_XH

Advancedform\_XH facilitates to create your own mail forms for
integration into CMSimple\_XH. Its possible usage ranges from customized
contact forms to complex ordering or booking forms. Even complex forms
can be constructed by using the form editor, so you do not have to write
any HTML, CSS or PHP, if the basic form functionality is enough for your
needs. Advanced customization is available through the template and
hook system, respectively.

- [Requirements](#requirements)
- [Download](#download)
- [Installation](#installation)
- [Settings](#settings)
- [Usage](#usage)
    - [Mail form administration](#mail-form-administration)
    - [Mail form editor](#mail-form-editor)
    - [Using the mail form](#using-the-mail-form)
    - [Replacing the built in mail form](#replacing-the-built-in-mail-form)
    - [Template system](#template-system)
    - [Hooks](#hooks)
    - [Demo forms](#demo-forms)
- [Limitations](#limitations)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Credits](#credits)

## Requirements

Advancedform\_XH is a plugin for CMSimple\_XH ≥ 1.7.0
with the Fa and Jquery plugins, which are available by default.
It requires PHP ≥ 5.5.0 with the ctype, filter and hash extensions.

## Download

The [lastest release](https://github.com/cmb69/advancedform_xh/releases/latest)
is available for download on Github.

## Installation

The installation is done as with many other CMSimple\_XH plugins.

1. Backup the data on your server.
1. Unzip the distribution on your computer.
1. Upload the whole directory `advancedform/` to your server into
   the `plugins/` directory of CMSimple\_XH.
1. Set write permissions to the subdirectories `config/`, `css/`,
   `languages/` and to the data folder of the plugin.
1. Protect the data folder of Advancedform\_XH against direct access by any
   means your Webserver provides. `.htaccess` files for Apache servers
   are already distributed in the default data folder. Note that the
   subfolders `css/` and `js/` must not deny public access.
1. Navigate to `Plugins` → `Advancedform` in the back-end to check if all
   requirements are fulfilled.

## Settings

The configuration of the plugin is done as with many other CMSimple\_XH
plugins in the back-end of the Website.
Select `Plugins` → `Advancedform`.

You can change the default settings of Advancedform\_XH under `Config`.
Hints for the options will be displayed
when hovering over the help icon with your mouse.
The setting `php_extension` could provide additional
security with regard to the template and the hook system, if enabled.
However, some of the demo forms do not work in this case.

Localization is done under `Language`.
You can translate the character strings to your own language,
if there is no appropriate language file available,
or customize them according to your needs.

The look of Advancedform\_XH can be customized under `Stylesheet`.
The upper part contains the styling of the forms displayed in the front-end.
The mail form is displayed as a table with two columns.
The left column shows the labels, the right colum the fields.
You can simply adjust the styles for the classes `div.advfrm-mailform td.label`
and `div.advfrm-mailform td.field`.
You can style individual forms by selecting `form[name=FORM_NAME]`,
and even individual fields by selecting `#advfrm-FORM_NAME-FIELD_NAME`.
If you prefer a one column layout,
you have to use the [template system](#template-system).

The lower part of the stylesheet contains the styling
of the mail form administration and the form editor.
If you want to style the property dialogs to fit to your template,
you should consider to add jQueryUI theming support to your template.
How this can be done is explained in the
[CMSimple\_XH forum](https://www.cmsimpleforum.com/viewtopic.php?f=29&t=3435&start=2).

Only the first part of the stylesheet above the line

    /* END OF MAIL CSS */

will be included inline into the sent HTML mails.
So put all style information that is appropriate
for the mails at the top of the stylesheet.

## Usage

### Mail form administration

In the back-end under `Mail Forms` there is the list of all defined mail forms.
You can add and import new forms and edit, delete, copy and export existing ones.
To the right of the mail form is the script code needed to show the according form on a page.
Just copy the code and paste it into the desired page.

### Mail form editor

The mail form editor allows you to construct your mail forms.
The details are explained in the following sections.

#### General form properties

In the upper part of the form editor you can edit the general form properties.

- `Name`:
  The name of the form may contain only alphanumeric characters and underscores.
  It must be unique for all defined forms.
  It is used to identify the form.
- `Title`:
  The title of the form is used in the mails subject only.
- `To (name)`:
  The name of the recipient of the mail.
- `To (e-mail)`:
  The address of the recipient of the mail.
- `CC`:
  The addresses of the CC recipients of the mail separated by semicolon.
- `BCC`:
  The addresses of the BCC recipients of the mail separated by semicolon.
- `CAPTCHA`:
  Whether a CAPTCHA should be included in the form.
- `Store data`:
  Whether the submitted data should additionally be stored in a CSV file.
- `Thanks page`:
  If empty, after sending the mail the sent information will be displayed.
  If set and if a sender email address is entered,
  after sending the mail visitors will be redirected to this page,
  and a confirmation mail with the sent information will be sent to them.

#### Form fields

In the lower part of the form editor you can edit the fields of the form.
Use the tool icons to add, delete or move fields.

- `Name`:
  The name of the field may contain only alphanumeric characters and underscores.
  It must be unique for all defined fields of the current form.
  It is used to identify the field.
- `Label`:
  The label that should be displayed next to the field.
- `Type`:
  The type of the field.
  Right to the selectbox is the property tool icon.
  Click it to open a dialog for editing the properties of the selected field type.
- `Req.`:
  Whether the field is required, i.e. must be filled in by the visitor.

#### Field types

- `Text`:
  A general text field.
- `Sender (name)`:
  A field to enter the name of the sender.
  This information will be included in the mail header.
  At most one field of type `Sender (name)` may be used for each form.
- `Sender (e-mail)`:
  A field to enter the email address of the sender, which will be validated.
  This information will be used as Reply-To header field of the mail,
  and as To header field of the confirmation mail.
  At most one field of type `Sender (e-mail)` may be used for each form.
- `E-mail`:
  A field to enter a general email address, which will be validated
- `Date`:
  A field to enter a date, which will be validated.
  In contemporary browsers, a datepicker is available.
- `Number`:
  A field to enter a non-negative integer, which will be validated.
- `Textarea`:
  A field to enter multi-line texts.
- `Radiobutton`:
  A field to choose one of several options.
- `Checkbox`:
  A field to choose any of several options.
- `Selectbox`:
  A field to choose one of several options.
- `Multi-Selectbox`:
  A field to choose any of several options.
- `Password`:
  A field to enter a password.
- `File`:
  A field to allow the visitor to upload a file as mail attachment.
  The attachment will not be sent back to the visitor in the confirmation mail.
- `Hidden`:
  A hidden field. Hidden fields will never be shown to the visitor.
  They might be useful in combination with the template system and the hooks.
- `Output`:
  A field to output arbitrary HTML.
- `Custom`:
  A field that is validated against a given regular expression.

#### Field properties

The field properties are edited in a dialog that is opened by clicking
on the property icon.
The different field types have different properties that can be set.

- `Size`:
  For text fields and such, the width of the field measured in characters.
  For selectboxes the height of the list. Use 1 for a dropdown box.
- `Orientation`:
  For radiobuttons and checkboxes only:
  whether these should be displayed horizontally or vertically.
- `Max. length`:
  The maximum number of characters that can be entered.
  For file fields it specifies the maximum file size in bytes.
- `Cols`:
  The width of the textarea in characters.
- `Rows`:
  The height of the textarea in characters.
- `Default`:
  The default value of the field.
- `Value`:
  The HTML for output fields.
- `File types`:
  For file fields only:
  a comma separated list of permissible file extensions,
  e.g. `jpeg,jpg,png,gif,bmp` for images.
- `Constraint`:
  For custom fields only:
  the regular expression the value should be checked against.
- `Error message`:
  For custom fields only: the error message that should be displayed,
  if the value does not match the regular expression.
  Use `%s` to insert the label of the field into the message.

Radiobuttons, checkboxes and selectboxes allow to enter different options.
Use the tool buttons to add, delete and rearrange them.
The radiobuttons or checkboxes besides the options allow to specify these as default(s).
Use the tool `clear defaults` to reset them.

### Using the mail form

Edit the page, that you want to display a mail form,
and insert the plugin call:

    {{{advancedform('FORM_NAME')}}}

It might be easiest to just copy&paste the necessary code
from the mail form administration.

Now the mail form is ready to be used by the visitors of your Website.
They can fill out the form and submit it.
If they make any mistakes, e.g. leaving empty an required field,
entering an invalid email address or number
or specifying a file that is larger than allowed,
the form will be displayed again with the already entered input,
and with the errors shown above,
so the visitors can correct the mistakes and submit the form again.
It is not necessary that JavaScript is enabled in the browser of the visitor,
but if it is, the first erroneous field will be focused.
But none of the field validations relies on JavaScript.

After successful submission of the form, an email will be sent
to the receivers (To, CC and BCC) specified in the form editor.
Then the sent information is displayed to the visitors as confirmation,
or, if a thanks page is specified, the visitors will be redirected there,
and a confirmation mail will be sent to them.
The thanks page/confirmation feature will only work,
if a required field of type `Sender (e-mail)` is specified for the form,
and `Mail` → `Confirmation` is enabled in the configuration.

The confirmation mail feature can be abused by anybody,
namely by sending abusive contents to an arbitrary recipient
by using the email address of that recipient.
**Therefore it is highly recommended to have
`Mail` → `Confirmation` disabled.**

Attempts to send an email via Advancedform are logged
in the system log of CMSimple\_XH (`Settings` → `Log File`).

Note that it is possible to have multiple forms on a single page,
which can be sent independent from each other.

### Replacing the built in mail form

It is possible to replace the built-in mail form of CMSimple\_XH
with a user defined one.
So just define the desired form, and enter its name
in the language settings of Advancedform\_XH as `contact form`.
Now the mailform link of CMSimple\_XH will directly call your custom form.
Note that an email address has to be configured for CMSimple\_XH
to display the mailform link,
but its value is completely ignored by Advancedform\_XH.

Alternatively create a hidden CMSimple\_XH page
and insert the required script code to call the form.
Then you have to modify your template.
Just replace

    <?=mailformlink()?>

with

    <?=advancedformlink('PAGE_URL')?>

where PAGE\_URL should be the part of the URL of the page after the question mark.
It is possible to specify multiple `advancedformlink()`s this way.

### Template system

The template system allows to build highly customized mail forms.
Power users, who need to create or alter mail forms frequently,
might have a look at the [Form Mailer](https://simplesolutions.dk/?Form_Mailer)
by Jerry Jakobsfeld, which is even more versatile than Advancedform\_XH.

If a file with the name `FORM_NAME.tpl(.php)`
(where `FORM_NAME` has to be replaced with the name of the form,
e.g. for a form named `contact`, the filename would be `contact.tpl(.php)`)
is found in the data folder of Advancedform\_XH,
it will be used as the template file.
Additionally the file `css/FORM_NAME.css`, if it exists,
will be included as stylesheet in the CMSimple\_XH page
and the top of this stylesheet (delimited as for the plugin stylesheet)
will be included in the email.
And if a file `js/FORM_NAME.js` exists,
it will be included in the page, too.

You can write the template file and its stylesheet manually,
but it might be easiest to let Advancedform\_XH
create a basic one in the mail form administration.
By default the generated template files will display the form similar
to the one column layout of the original Advancedform.
If that is all you want, you are done.

If you want to adjust the styling, have a look at the generated files.
In the template file you can see its simplistic structure.
Everything is encapsulated within `<div>`s for flexibility.
Have a look at the class of the container-`div`s.
This is set to `break` by default, so every field will start in a new row.
Change it to `float` and the fields will be put right besides each other.
If you want to have the labels to the left of the fields,
just remove the comments in the prepared styles for
`div.label` and `div.field`.

A template file is basically a PHP file with one addition to the syntax:

    <?field FIELD_NAME?>

will output the field with the name `FIELD_NAME`.
Do not use any additional characters such as whitespace
except a single space between `field` and `FIELD_NAME`.

The template file will be evaluated in the context of CMSimple\_XH,
so any global variables, constants and functions are available to use.
However, it is not possible to alter any global variables
(besides the superglobals, what might break the working of the system).
And do not call non-existent functions
as this will cause the PHP interpreter to fail.
**Particularly you should be cautious using template files from untrusted sources,
as those could contain malicious code that might harm your CMSimple\_XH installation.**

One particularly useful function is

    Advancedform_focusField($form_name, $field_name)

that will set the focus to the given field.

### Hooks

The hooks are provided to give you even more flexibility,
if you are able to write some PHP code.
Define them in a file `FORM_NAME.inc(.php)`
(where `FORM_NAME` has to be replaced with the name of the form,
e.g. for a form named `contact`, the filename would be `contact.inc(.php)`)
in the data folder of Advancedform\_XH.
Note that this file will be `include()`d,
so it has to be a proper PHP file.
The hooks will be called from Advancedform\_XH on certain occassions.
They are not tied to the template system.

    function advfrm_custom_field_default($form_name, $field_name, $opt, $is_resent)

This will be called before the form is sent to the browser.
It gives the opportunity to dynamically set default values for fields.
Just return the value you want to be used as the default value of the field.
If you do not want to change the default value, just return `null`.
The third parameter is set for radiobuttons, checkboxes and selects only.
It contains the option, that is currently processed.
Return `true` to check the option, `false` to uncheck it,
or `null` to use whatever was specified in the form editor.
The parameter `$is_resent` tells whether the form was resent
to the browser after submission, because of validation errors.
If this is the case the values already entered by the user
are sent back instead of the default values specified in the form editor.
You might consider to return `null` in this case,
to not overwrite the users input.

    function advfrm_custom_valid_field($form_name, $field_name, $value)

This will be called after the form was submitted,
and gives the opportunity to add additional validation to the field values.
Return `true`, if the given `$value` is valid,
otherwise return an error message,
which will be displayed to the user by Advancedform\_XH.
For fields of type `file`,
`$value` is the `$_FILES[]` array of the given field.

    function advfrm_custom_mail($form_name, $mail, $is_confirmation)

This will be called after the `$mail` object is initialized
with all information and just before the mail is sent,
and gives the opportunity to alter the `$mail` object.
The parameter `$form_name` specifies the currently processed form,
and the parameter `$is_confirmation` specifies
whether the `$mail` object contains the information for the mail
or the confirmation mail (i.e. the response).
If you want to suppress sending of the mail altogether,
just return `false`.

    function advfrm_custom_thanks_page($form_name, $fields)

This is called after the mail is sent,
and can be utilized to switch to a personalized thanks page.
Return the query string
(i.e. the part of the URL of the page after the question mark)
of the page you want to redirect to.
Returning an empty string will redirect
to the thanks page that is defined in the form editor.
If no default thanks page is defined,
the sent information will be displayed.
The parameter `$fields` is an array
holding the values of all submitted form fields.

### Demo forms

You should have a look at the shipped demo forms (see `data/README` for
further details) to see what can be done, and how it can be done.

**Caution:** of course you are free to use the demo forms as base for
your own forms. But as most of the demo forms use the template and
hook system, respectively, the result might not be as expected. Either remove the
unwanted template/hook files manually, or make a copy of the form in the
form administration and use this copy.

## Limitations

### Alternative mailers

The original AdvancedForm supported different kinds of mailers.
This appears to be a rarely needed feature.
Most Webhosters will provide the possibility to send mail via `mail()`,
which should be easily configured
and quite acceptable for the purposes of Advancedform\_XH.

### Spam protection

The original Advancedform offered several features regarding spam protection:
IP blacklists, a badword filter, a XSS detection facility.
The authors of Advancedform_XH are not convinced that these mechanisms
are reasonable ways to prevent spam.
So none of these are implemented (besides rendering XSS harmless),
but instead a CAPTCHA is available.
This is only a minimal text-based solution,
but more comprehensive CAPTCHAs can be used
by means of an additional conforming CAPTCHA plugin.
Currently [Cryptographp\_XH](https://github.com/cmb69/Cryptographp_XH)
is available.

## Troubleshooting

Report bugs and ask for support either on
[Github](https://github.com/cmb69/advancedform_xh/issues)
or in the [CMSimple\_XH Forum](https://cmsimpleforum.com/).

## License

Advancedform\_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Advancedform\_XH is distributed in the hope that it will be useful,
but *without any warranty*; without even the implied warranty of
*merchantibility* or *fitness for a particular purpose*. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Advancedform\_XH.  If not, see <https://www.gnu.org/licenses/>.

© 2005-2010 Jan Kanters  
© 2011-2021 Christoph M. Becker

Czech translation © 2011-2012 Josef Němec  
Danish translation © 2012 Jens Maegard  
Estonian translation © 2012 Alo Tänavots  
French translation © 2014 Patrick Varlet  
Slovak translation © 2012 Dr. Martin Sereday

## Credits

Advancedform\_XH is based on AdvancedForm Pro by Jan Kanters.
Many thanks to him for giving the permission to use his code for a
CMSimple\_XH conforming version,
and to *Holger* and *johnjdoe* who negotiated this permission.

The mail composition and sending is powered by
[PHPMailer](https://github.com/PHPMailer/PHPMailer).
Many thanks for publishing this advanced mail library under LGPL-2.1.

The regular expression to check for a valid email address is by courtesy
of [Jan Goyvaerts](https://www.regular-expressions.info/email.html).
Many thanks for the great regular expression tutorial and the examples.

The plugin icon is designed by Jack Cai.
Many thanks for publishing it under CC BY-ND.

Many thanks to the community at the [CMSimple\_XH forum](https://cmsimpleforum.com/)
for tips, suggestions and testing.
Especially, I want to thank *Tata* for having the idea to let
Advancedform\_XH generate a basic template and stylesheet,
and *manu* for requesting the hooks and co-designing their API.
And many thanks to *maeg*, who allowed me to do some debugging on his server,
so I was able to find and fix a bug,
which caused the sending of mails to fail on several servers.

And last but not least many thanks to
[Peter Harteg](https://www.harteg.dk/), the “father” of CMSimple,
and all developers of [CMSimple\_XH](https://www.cmsimple-xh.org)
without whom this amazing CMS would not exist.
