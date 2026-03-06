# Changelog
All notable changes to this project will be documented in this file and formatted via [this recommendation](https://keepachangelog.com/).

## [1.6.0] - 2025-06-05
### IMPORTANT
- Support for PHP 7.1 has been discontinued. If you are running PHP 7.1, you MUST upgrade PHP before installing this addon. Failure to do that will disable addon functionality.

### Changed
- The minimum WPForms version supported is 1.9.1.
- Improved compatibility with the Astra theme.

### Fixed
- Font color of the selected choice was incorrect in modern Dropdown with Lead Forms.
- Required Net Promoter Score field validation failure is now displayed before proceeding to the next step of the form.
- Missed styles for the PayPal subscription submit button.
- Country code alignment of the Phone field in the form builder.

## [1.5.0] - 2024-04-25
### Fixed
- Advanced styles were not applied to the Entry Preview and Order Summary in the confirmation message.
- The Single Item payment field had extra line space in case of a long label.
- Compatibility with the Signature field.
- Lead Forms styles were not applied for the Order Summary table.

## [1.4.0] - 2024-02-20
### Added
- Compatibility with the upcoming WPForms 1.8.7.

### Fixed
- Authorize.Net subfields alignment in Classic frontend mode.
- Active state of the Net Promoter Score field in Classic frontend mode.
- Geolocation map preview breaking out of container.
- In rare cases, Turnstile Captcha was not displayed correctly when it expired and was refreshed.
- The Field Size option was not blocked if it was in the Layout element.
- Regular Stripe card number field was not inheriting colors correctly.

## [1.3.0] - 2023-09-27
### IMPORTANT
- Support for PHP 5.6 has been discontinued. If you are running PHP 5.6, you MUST upgrade PHP before installing WPForms Lead Forms 1.3.0. Failure to do that will disable WPForms Lead Forms functionality.
- Support for WordPress 5.4 and below has been discontinued. If you are running any of those outdated versions, you MUST upgrade WordPress before installing WPForms Lead Forms 1.3.0. Failure to do that will disable WPForms Lead Forms functionality.

### Changed
- Minimum WPForms version supported is 1.8.4.
- Lead Forms and Coupons addons are now working together nicely.
- The Coupon field preview in the Form Builder now looks much better.

### Fixed
- Lead Forms styles weren't loaded in the Elementor builder preview.
- Required Rich Text field was broken in certain cases related to errors on the page.
- Field size option was enabled for fields inside the Layout field in the Form Builder after page refresh.
- Turnstile Captcha overlapped the "Submit" button in Lead Forms.
- Additional border appeared after unchecking a Likert Scale option.
- Long field labels overlapped the "Duplicate Field" and "Delete Field" icons in the Form Builder.
- The Rich Text field style was reset when Lead Forms was enabled.
- The Rich Text field in Visual mode didn't inherit secondary text color and focus styles.
- The Square Credit Card field had a larger height than it should.
- The Date/Time field was rendered incorrectly on the front end when in Classic markup mode.
- The legacy Credit Card field layout was partially broken on the front end.
- External Stripe payment field styles were not fully overridden by the addon styles.
- The progress bar wasn't updated properly when the form was used in an Elementor popup.
- The "Lead Forms Enabled" notice was duplicated if Lead Forms was turned off, the form was changed and converted back to Lead Forms again.

## [1.2.0] - 2023-06-28
### Added
- Compatibility with the WPForms Coupons addon.

## [1.1.0] - 2023-03-21
### Added
- Compatibility with the upcoming WPForms v1.8.1 release.
- A new option to control scrolling to the top of the form when proceeding to the next page, disabled by default.

### Fixed
- Choice layout of all Multiple Choice and Checkboxes fields was changed to inline when Icon or Image Choices option was activated on any of them.
- Custom field border styles were ignored in Safari on iOS.
- Progress indicator could get stuck while converting the form if it contained unavailable fields.
- Dropdown field placeholder was aligned incorrectly if Multiple Options Selection was turned on.
- Smart Phone field dropdown selector was breaking out of the container on smaller screens.
- It was possible to proceed to the next step if required Likert Scale field validation failed.

## [1.0.0] - 2023-01-11
- Initial release.
