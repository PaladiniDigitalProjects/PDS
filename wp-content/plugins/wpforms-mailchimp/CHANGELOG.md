# Changelog
All notable changes to this project will be documented in this file, formatted via [this recommendation](https://keepachangelog.com/).

## [2.5.1] - 2025-07-01
### Changed
- The minimum WPForms version supported is 1.9.6.

### Fixed
- It was impossible to map any of the Name-related fields when the "First Name" and/or "Last Name" fields were marked as required in Mailchimp.

## [2.5.0] - 2025-05-06
### IMPORTANT
- Support for PHP 7.1 has been discontinued. If you are running PHP 7.1, you MUST upgrade PHP before installing this addon. Failure to do that will disable addon functionality.
- Addon requires the cURL extension to work. If you are running PHP without the cURL extension, you MUST enable it before upgrading this addon. Failure to do that will disable addon functionality.

### Changed
- Improved UI/UX for the List field in the builder.
- The minimum WPForms version supported is 1.9.5.

## [2.4.1] - 2025-04-25
### Fixed
- Address was not saved in the Mailchimp audience if the state subfield was not set.

## [2.4.0] - 2024-10-10
### IMPORTANT
- Support for PHP 5.6 has been discontinued. If you are running PHP 5.6, you MUST upgrade PHP before installing WPForms Mailchimp 2.4.0. Failure to do that will disable WPForms Mailchimp functionality.
- Support for WordPress 5.4 and below has been discontinued. If you are running any of those outdated versions, you MUST upgrade WordPress before installing WPForms Mailchimp 2.4.0. Failure to do that will disable WPForms Mailchimp functionality.

### Added
- WPForms 1.9.1 compatibility.

### Changed
- The minimum WPForms version supported is 1.9.1.

## [2.3.0] - 2023-06-12
### Changed
- Checkboxes have been replaced with fancy toggles on the addon settings screen in the Form Builder.
- Minimum WPForms version supported is 1.8.2.

## [2.2.0] - 2022-09-22
### IMPORTANT
- Support for PHP 5.5 has been discontinued. If you are running PHP 5.5, you MUST upgrade PHP before installing the new WPForms Mailchimp and WPForms 1.7.5.5 (that the addon is relying on). Failure to do that will disable the WPForms Mailchimp plugin.
- Support for WordPress 5.1 has been discontinued. If you are running WordPress 5.1, you MUST upgrade WordPress before installing the new WPForms Mailchimp. Failure to do that will disable the new WPForms Mailchimp functionality.

### Changed
- Improved translations by removing confusion if non-translatable placeholders are used.
- Minimum WPForms version supported is 1.7.5.5.

### Fixed
- PHP Fatal error occurred during a form processing.
- Smart Tags could not be inserted in the Note setting.

## [2.1.1] - 2021-09-07
### Fixed
- Compatibility with WordPress Multisite installs.
- Compatibility with WPForms 1.6.8 and the updated Form Builder.

## [2.1.0] - 2021-04-08
### Added
- Ability to define which tags you wish to remove from contact when updating an existing one in your Audience.
- New option in the form Mailchimp settings to notify users when they are already subscribed.

### Changed
- Send the note to Mailchimp when the contact is updated because of the "Update the profile" option enabled.

### Fixed
- Send to Mailchimp form submission data even when the "Entry storage" option is disabled in the Form Builder.
- Properly handle the situation when trying to change the template for the same form multiple times.

## [2.0.0] - 2021-03-16
### Added
- New actions: unsubscribe, archive, delete, record event.
- Assign existing or new tags to subscribers.
- Add a note to subscribers (with support of Smart Tags).
- Mark subscribers as VIP.
- Update subscribers' information on Mailchimp if they already exist in your Audience.
- Map form field values to any Mailchimp custom fields.

### Changed
- Rename "Lists" to "Audiences".
- Improved integration with Mailchimp groups (segments) of your Audience (inheriting their radio/dropdown/checkbox multiple selection status).

### Fixed
- Convert form field values properly when sending data to Mailchimp.

## [1.4.2] - 2020-03-03
### Changed
- Make the addon consistent with the updated Mailchimp branding (MailChimp to Mailchimp).

### Fixed
- Simultaneous change of Date format and Datepicker type in "Date/Time" field may produce an error on Mailchimp form submission.

## [1.4.1] - 2020-01-09
### Changed
- Upgraded the Mailchimp library version to v2.5.4.

## [1.4.0] - 2019-07-23
### Added
- Complete translations for French and Portuguese (Brazilian).

## [1.3.0] - 2019-02-06
### Added
- Complete translations for Spanish, Italian, Japanese, and German.

### Fixed
- Typos, grammar, and other i18n related issues.

## [1.2.0] - 2017-12-04
### Changed
- Birthday field special integration rules.
- Improved localization support and translatable strings.
- Update Mailchimp PHP library (2.4)

## [1.1.1] - 2017-03-30
### Fixed
- Issue for some users connecting v3 accounts.

## [1.1.0] - 2017-03-30
### Added
- Mailchimp API version 3 support.

### Changed
- Mailchimp API version 2 support has been deprecated, please reconnect forms using the new version 3.

## [1.0.6] - 2017-03-09
### Changed
- Adjust display order so that the providers show in alphabetical order.

## [1.0.5] - 2016-10-24
### Changed
- Namespaced Mailchimp PHP classes to prevent conflicts.

## [1.0.4] - 2016-07-07
### Changed
- Improved error logging.

## [1.0.3] - 2016-06-23
### Changed
- Prevent plugin from running if WPForms Pro is not activated.

## [1.0.2] - 2016-05-30
### Changed
- Disable SSL verify which causes issues with some web hosts.

## [1.0.1] - 2016-04-12
### Changed
- Improved error logging.
