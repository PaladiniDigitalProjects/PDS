=== BlockMeister - Block Pattern Builder  ===
Contributors: blockmeister, bvl
Author URI: https://wpblockmeister.com
Tags: Gutenberg, patterns, block patterns, pattern builder
Donate link: https://wpblockmeister.com/
Requires at least: 6.0
Tested up to: 6.7.1
Requires PHP: 5.6
Stable tag: 3.1.12
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Visually create custom block patterns. No coding skills needed!
Categorize them easily and use keywords for easy discoverability. 

== Description ==

With BlockMeister creating custom block patterns becomes easy. The patterns can be designed just like you design a blog post or a page with the block editor.
You can assign any (custom) category or keywords. By doing so, your patterns are categorized in a way that makes sense to your users and keywords make it easier to find a pattern. Your custom patterns will be available from the block patterns tab in the inserter panel.

**Features**

- Visual design custom block patterns using the pattern builder
- Or create a custom pattern from one or more selected blocks in the post editor
- Import patterns made/shared by trusted Pro users [new in 3.0]
- Assign your patterns to one or more categories
- Optionally add any keywords (makes them easy to find in the block inserter)
- Create custom pattern categories
- Set a viewport width to optimize the scaled width of the preview in the block inserter


**Pro Features**
If you would like even more features like: group locking, cloning, activating or deactivating individual patterns, exporting, importing, controlling which core/third party pattern sets are allowed to load, setting block styles and more, please check out our premium versions here:
<a href="https://wpblockmeister.com/#info-pro" target="_blank">Professional Version</a>

== Installation ==

**Installation directly from your site

1. Log in and navigate to **Plugins → Add New**.
2. Type "BlockMeister" into the Search and hit Enter.
3. Locate the BlockMeister plugin in the list of search results and click **Install Now**.
4. Once installed, click the **Activate** link.


== Getting Started ==

1. Locate and click on the '**Block Patterns**' menu item in the sidebar admin menu.
2. Click on the '**Add New**' button.
3. Give your pattern a name.
4. Start writing or choose any blocks you want to be part of your pattern.
5. Publish the pattern
6. Go to a test page and locate your pattern in the Block Inserter under the pattern tab and click on it.
7. The block pattern will now be added to your page. All contained blocks are now independent of the original pattern and can be edited as any regular block.


== Frequently Asked Questions ==

= Can I add custom CSS? =

Yes, select the block you want to style:

- Open the 'Advanced' panel
- Add a 'Additional CSS class'
- Add the styles for that class to your (child) theme stylesheet or add them via the customizer's 'Additional CSS' section.

OR upgrade to a premium version which comes with a built-in block level CSS editor

= Does the plugin run on older WordPress installations? =

The plugin will require either the latest major release or the one before that.
So if the current major release is 6.2 then the plugin requires at least WP 6.1

This helps us to keep the code clean and limit the test cases.

= Why did we integrate Freemius? =
Freemius is a managed eCommerce platform for selling WordPress plugins and themes.

When you activate or upgrade to v3.0 or higher you will be asked if you would like to opt in to the freemius functionality. To be clear: this is 100% optional!

With Freemius, we have more time to focus on our products and deliver better features for your website while making strategic business decisions that are based on the data you are willing to share as a consumer.

We can also use the data to figure out optimum pricing strategies for our customers and their companies. This promotes business sustainability and offers you long-term support and reliability for your plugin or theme purchase.

We would be thankful when you opt in to this but if not, don't worry, all plugin features will remain 100% functional!

You can read more about this at the <a href="https://freemius.com/privacy/data-practices/" target="_blank">Freemius privacy data practices page</a>.

= What happens to my patterns, after I deactivate the plugin? =

- The patterns will no longer be available from the inserter.
- Patterns inserted in posts and pages will not be affected.
- As soon as you re-activate the plugin the patterns will be available again from the inserter.

= What happens to my patterns, after I uninstall the plugin? =

- By default all data including your custom patterns will remain in the database, unless you prefer a complete data removal. This can be set on the settings page.
- Patterns already inserted in posts and pages will not be removed, regardless your uninstall preference.
- For test/debug purposes we advise to temporarily deactivate the plugin instead.

== Screenshots ==

1. Block patterns list table screen.
2. Categories list table screen.
3. Block pattern settings sidebar.

== Changelog ==
= [3.1.12] - 2025-01-27 =
- Fixed list table error when post_type query var not set.
- Updated Freemius SDK to the latest version

= [3.1.11] - 2024-10-08 =
- Fixed vulnerability: unescaped form URL (thank you 'vgo0' for reporting)

= [3.1.10] - 2023-07-05 =
- Upgraded dependencies to latest versions:
  - NPM package classnames 2.3.2
  - NPM package prismjs 1.29.0
  - NPM package react-simple-code-editor 0.13.1
  - Freemius SDK v2.5.10

= [3.1.9] - 2023-05-11 =
- Upgraded to Freemius SDK v2.5.7 (fixes PHP 8.1 issues)

= [3.1.8] - 2022-02-11 =
- Fixed: composer issue autoloader when both free and premium version activated (replaced with custom loader)
- Fixed: test if user is allowed to add unfiltered html fails
- Fixed [Pro]: handle AFC block not supporting custom block CSS

= [3.1.7] - 2022-11-23 =
- Tested up to WP 6.1.1
- Upgraded to Freemius SDK v2.5.2
- Fixed: Toolset compatibility issue
- Removed obsolete context checks
- Fixed [Pro]: cloning of pattern (since 3.1.5) when using non default category slug produces an error notice

= [3.1.6] - 2022-08-02 =
- Fixed: backwards compatibility error due to call to new WP 6 API method

= [3.1.5] - 2022-08-01 =
- New: default category (initially set to site name) can now be edited, name will get 'Default' suffix
- Improved: patterns list table will hide content in third party columns in non-custom pattern rows (applies e.g. to WPML & Polylang translations column)
- Improved: the context checking system (due to reported edge cases)
- Improved: Non-custom categories no longer listed in the category admin screen (to stop user confusion)
- Compatibility issue fixed: some third party plugins caused a redirect (loosing preview parameter) for preview requests, which made previewing patterns impossible
- Fixed: clicking view link in the pattern editor directs to home page not the pattern
- Fixed: uncaught error due to failed syncing of registered category
- Updated dependencies: classnames 2.31, prismjs 1.280, react-simple-code-editor 0.11.2
- Updated: language pot file
- Updated: Dutch translation

= [3.1.4] - 2022-07-02 =
- Fixed: Context/run check for rest calls fails when permalinks set to plain
- Fixed: Category list table term edit link active when it should not be (and visa versa)
- Fixed: (De-)activation namespace issue when both free and premium version are installed
- Fixed: Optional loading of core/featured pattern directory patterns in patterns admin screen sometimes fails
- Fixed: Inactivated patterns from remote source still show in the inserter
- Fixed: Parent selector shouldn't be added to pattern editor screen
- Fixed: admin notice after pattern table list quick action no longer shows name of pattern
- Fixed: bulk action handler ignores filters in redirect
- Fixed: With PHP 5 internally setting source of default category fails
- Improved: Pattern category syncing when different set is registered (after e.g theme switch) or on new BlockMeister installs

= [3.1.3] - 2022-06-03 =
- Fixed: Label of import button on pattern list table hidden when PHP directive short_open_tag is off.

= [3.1.2] - 2022-06-02 =
- Fixed: Since 3.1.0 newly added custom categories show source 'unknown' instead of 'user'.

= [3.1.1] - 2022-05-25 =
- Fixed: In WP 5.9, the site editor isn't loading any custom pattern category or custom pattern. In WP 6.0 or 5.9.x i.c.w Gutenberg 13.x this does work as expected though.

= [3.1.0] - 2022-05-19 =
- Tested with upcoming WP 6.0 release.
- Fixed backward compatibility issue introduced by WP 6.0: Custom block patterns and pattern_categories no longer loaded into block inserter.
- Fixed backward compatibility issue introduced by WP 6.0: Inactive patterns no longer available on block patterns admin screen.
- Fixed: Reset button for Block Setting "Viewport Width" reverts to last user setting after saving.
- Fixed: Pattern registration of custom draft patterns, which correctly are nameless, lead to internal registry overriding of previous draft patterns.
- Fixed: [Pro] Cloned and imported patterns don't get all category and keyword terms of original pattern.
- New: "Add to Block patterns" feature is now also available from within the site (/template) editor.
- New: Filtering on category in the Pattern list table screen.
- Improved: More logical redirection in response to handled quick actions in pattern table list
- Removed: Not applicable (core added) 'Template' inspector panel from block pattern editor.
- Removed: Category list table 'Counts' column, due to current inability to show correct counts in all cases. May be reintroduced in later release.

= [3.0.5] - 2022-03-01 =
- Updated: Freemius SDK version 2.4.3 with security fix

= [3.0.4] - 2022-01-31 =
- Fixed: missing return bug

= [3.0.3] - 2022-01-26 =
- Fixed: WP 5.9 incompatibility bug, related to _disable_block_editor_for_navigation_post_type (not yet loaded in 5.9 when we applied related filter)

= [3.0.2] - 2021-10-09 =
- Fixed: regression of old excerpt related bug
- Fixed: inserter not showing custom and default (generated) block pattern category on draft post (until first reload)

= [3.0.1] - 2021-10-01 =
- Fixed: unreadable properties of undefined in reusable block editor

= [3.0.0] - 2021-10-01 =

**All versions**

- made compatible with WP 5.8
- bumped required WP version to 5.8

- Added: Freemius SDK
- Added: User can now control (via a setting) whether all data is deleted during uninstall
- Added: Import patterns made/shared by trusted Pro users
- Added: Bulk action for trashing custom patterns

**Premium versions **
- New: first release of premium version
- Added: Settings to control the loading of local and remote core block patterns and theme and plugin based block patterns
- Added: Activate/deactivate custom/core and third party block patterns (individual or by source via settings)
- Added: Clone patterns (including core and third party patterns).
- Added: Export/download (for backup or sharing) patterns.
- Added: Add custom styles to block patterns and blocks.
- Added: Lock pattern group or column (so users cannot move/remove/add child blocks).

**All versions**
- Improved: default block pattern category name will now be auto updated after user renames the site
- Improved: no longer (core/theme/plugin) registered categories are now auto removed as a custom block pattern taxonomy term

= [2.0.8] - 2021-04-16 =

**Fixed**
- Allow unfiltered pattern HTML for users who have the 'unfiltered_html' capability

= [2.0.7] - 2021-03-06 =

**Fixed**
- Too strict run context check excluded post-new screen

= [2.0.6] - 2021-02-27 =

**Fixed**
- Custom categories missing in inserter due to failed run context check; WP core test method not yet loaded

= [2.0.5] - 2021-02-19 =

**Fixed**
- Unnecessary term updates in synchronization method (leading to unintended cache purging in cache plugins)

**Improved**
- syncing and re-translating of dynamically registered categories and custom category terms
- run context

**Optimized**
- code run contexts

= [2.0.4] - 2021-01-19 =

**Fixed**
- prevention of term deletion of third party custom taxonomy

= [2.0.3] - 2020-09-18 =

**Fixed**
- excerpt screen option and control panel unintentionally removed from other post type edit screens

= [2.0.2] - 2020-09-12 =

**Fixed**
- path normalizing issue

= [2.0.1] - 2020-09-03 =

**Fixed**
- keywords taxonomy's edit capability

= [2.0.0] - 2020-09-01 =
Initial public release.

**Improved**
- completely refactored v1.0

**Added**
- Block settings menu item to add selected blocks to a new block pattern
- Categories
- Keywords
- Viewport width setting
- Added pattern editing related capabilities
- Limit pattern building to administrators by default
- Made translatable
- Added Dutch translation

== Upgrade Notice ==
