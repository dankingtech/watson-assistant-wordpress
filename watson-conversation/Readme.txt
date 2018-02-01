=== IBM Watson Conversation ===
Contributors: cognitiveclass
Tags: chatbot, chat bot, artificial intelligence
Requires at least: 4.7
Tested up to: 4.9
Stable tag: 0.5.6
License: Apache v2.0
License URI: http://www.apache.org/licenses/LICENSE-2.0

This plugin allows you to easily add chatbots powered by IBM Watson Conversation to your website.

== Description ==

Watson Conversation is a chatbot service, one of many AI services offered by IBM to integrate cognitive computing into your applications. With the use of this plugin, you can easily add chatbots to your website created using the Watson Conversation service.

Currently supported features:

* **All New:** Easy VOIP calling powered by Twilio for users to contact a real person if they wish
* Simple plugin setup to get your Watson Conversation chatbot available to users as soon as possible
* Control usage of the Watson Conversation service directly from the plugin settings page
* Choose the pages and posts you want the visitors to see the chat bot on
* Customize the appearance of the chat box to your preference

Learn how to set up your Watson Conversation chatbot with [this free course](https://cocl.us/build-a-chatbot).

== Installation ==

= Requirements =

This plugin requires the [WordPress REST API Plugin](https://en-ca.wordpress.org/plugins/rest-api/) to be installed. If you have WordPress 4.7 or later, this is installed by default.

= Installing the Plugin =

1. Log in to your site’s Dashboard.
1. Click on the `Plugins` tab in the left panel, then click `Add New`.
1. Search for “Watson Conversation” and the latest version will appear at the top of the list of results.
1. Install the plugin by clicking the `Install Now` link.
1. When installation finishes, click `Activate Plugin`.

This plugin can also be installed manually.

**Note:**
If your WordPress site is hosted by WordPress (with a URL like `websitename.wordpress.com`), you need a paid plan to install plugins. If your WordPress is hosted separately, you should have no issue.

= Building Your Chatbot =

1. Learn how to set up your Watson Conversation chatbot with [this quick free course](https://cocl.us/build-a-chatbot).

1. [Sign up for a free IBM Cloud Lite account.](https://cocl.us/bluemix-registration)

1. You can see [the Watson Conversation documentation](https://cocl.us/watson-conversation-help) for more information.

Once you've created your workspace using the course or the link above, you must connect it to your Wordpress site.

= Setting up the Plugin =

1.  From the Deploy tab of your workspace, you must obtain your username and password credentials in addition to the Workspace URL of your new workspace.

1. Enter these on the "Main Setup" tab of your settings page. Once you click "Save Changes", the plugin will verify if the credentials are valid and notify you of whether or not the configuration was successful. 

1. (Optional) By default, the chatbot shows up on all pages of your website. In the Behaviour tab of your settings page, you can choose which pages to show the chat bot on.

**Note:**
If you have a server-side caching plugin installed such as WP Super Cache, you may need to clear your cache after changing settings or deactivating the plugin. Otherwise, your action may not take effect.

== Frequently Asked Questions ==

= What is the best place to learn how to create a chatbot? =

Check out [this free course](https://cocl.us/build-a-chatbot) to learn how to build your own chatbot.

= Why should I use this? =

Watson Conversation, when used with this plugin, allows you to build and deploy a fully customized chat bot with little technical knowledge. It can talk to your website's visitors about whatever you choose, from helping navigate the website and providing support with common questions, to just having a casual conversation on a topic of interest.

= Do I need to know how to code? =

Nope. This plugin allows you to easily deploy chatbots that you create using the Watson Conversation service on IBM Bluemix. [This free course](https://cocl.us/build-a-chatbot) will guide you through this intuitive process – no prior technical knowledge necessary.

= How do I see my chatbot's conversations with users? =

On the same page where you build your chatbot in Bluemix, you can click on the Improve tab to view and analyze past conversations with users.

== Screenshots ==
1. An example of your chatbot greeting a website visitor.

== Changelog == 

= 0.5.6 =
* Fixed issue with chat button remaining clickable when invisible

= 0.5.5 =
* Fixed browser caching issue preventing chatbox from appearing initially after updates

= 0.5.4 =
* Modified Wordpress hooks

= 0.5.3 =
* Fixed bug with credentials validation

= 0.5.2 =
* Added Wordpress hooks for sending and receiving messages
* Added extra debug information for credential validation failure
* Added Chat Button customization

= 0.5.1 =
* Fixed bug with Advanced page showing on wrong tab

= 0.5.0 =
* Added Preset Response Options feature
* Fixed issue where typing in message box caused media in previous messages to reload

= 0.4.2 =
* Added compatiblity with Internet Explorer
* Fixed chat box rendering for some Wordpress installations
* Fixed visual bug with long words in messages

= 0.4.1 =
* Fixed issue with voice call settings validation

= 0.4.0 =
* Added settings tab to help introduce plugin to new users
* Made some settings more intuitive
* Settings on all tabs are submitted together now

= 0.3.3 =
* Fixed bug with setting to start chat box minimized

= 0.3.2 =
* Fixed bug in Voice Call UI customization

= 0.3.1 =
* Removed font size cap, fixed font size issues for full screen

= 0.3.0 =
* Added voice calling feature using Twilio
* Improved compatibility with older PHP versions
* Added setting for full-screen UI on non-mobile devices

= 0.2.3 =
* Fixes bug causing links from chatbot to be same color as background.

= 0.2.2 =
* Improves backwards compatibility with older PHP versions
* Improves iOS support.

= 0.2.1 =
* Fixes bug where settings changes do not take effect.

= 0.2.0 =
* New UI for mobile devices.
* Added ability to clear messages.
* Fixed several small bugs.

= 0.1.4 =
* Fixed critical bug causing chat box to stick to cursor on some browsers, on some pages.

= 0.1.3 =
* Fixed some UI issues with the chat box being hidden and not staying minimized across pages.
* Adjusted `Show on Home Page` option to `Show on Front Page` instead.

= 0.1.2 =
* Changed UI to use floating action button for minimizing.

= 0.1.1 =
* Added setting allowing admin to specify API base URL.

== Upgrade Notice ==

= 0.4.2 =
This version adds compatiblity with Internet Explorer and fixes chat box rendering issues for some Wordpress installations.

= 0.2.1 =
This verison fixes a bug from 0.2.0 where settings changes do not take effect.

= 0.1.3 =
This version fixes some issues with UI and the Show on Home Page setting.

= 0.1.2 =
This version fixes issues with the UI on mobile devices by adding a floating action button.

= 0.1.1 =
This version adds support for custom API base URLs.