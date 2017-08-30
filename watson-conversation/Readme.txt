=== IBM Watson Conversation ===
Contributors: cognitiveclass
Tags: chatbot, chat bot, artificial intelligence
Requires at least: 4.7
Tested up to: 4.8
Stable tag: 0.1.4
License: Apache v2.0
License URI: http://www.apache.org/licenses/LICENSE-2.0

This plugin allows you to easily add chatbots powered by IBM Watson Conversation to your website.

== Description ==

Watson Conversation is a chatbot service, one of many AI services offered by IBM to integrate cognitive computing into your applications. With the use of this plugin, you can easily add chatbots to your website created using the Watson Conversation service.

Currently supported features:

* Control usage of the Watson Conversation service directly from the plugin settings page
* Choose the pages and posts you want the visitors to see the chat bot on
* Customize the appearance of the chat box to your preference

**Note:** This is an alpha release.

== Installation ==

= Requirements =

This plugin requires the [WordPress REST API Plugin](https://en-ca.wordpress.org/plugins/rest-api/) to be installed. If you have WordPress 4.7 or later, this is installed by default.

= Installing the Plugin =

1. Log in to your site’s Dashboard.
1. Click on the `Plugins` tab in the left panel, then click `Add New`.
1. Find the Plugin through one of these two methods:
    - Search for “Watson Conversation” and the latest version will appear at the top of the list of results.
    - Click `Upload Plugin` and upload the `watson-conversation.0.2.0.zip` file

1. Install the plugin by clicking the `Install Now` link.
1. When installation finishes, click `Activate Plugin`.

This plugin can also be installed manually.

**Note:**
If your WordPress site is hosted by WordPress (with a URL like `websitename.wordpress.com`), you need a paid plan to install plugins. If your WordPress is hosted separately, you should have no issue.

= Building Your Chatbot =

1. Sign up for a free IBM Bluemix trial [here](https://cocl.us/bluemix-registration).

1. Create a workspace and build a customized chat bot using Watson Conversation's intuitive interface. To do this, you can follow [these instructions](https://cocl.us/watson-conversation-help), from the `Getting Started` section to `Building a Dialog`.

= Configuring the Plugin =

1. From the Deploy tab, you can obtain your username and password credentials in addition to the Workspace ID of your new workspace. Enter these in the Workspace Credentials section of the settings page for your Watson Conversation plugin on Wordpress.

1. In your plugin settings, you can choose which pages to show the chat bot on. Your chat bot should now pop up on the pages you chose.

**Note:**
If you have a server-side caching plugin installed such as WP Super Cache, you may need to clear your cache after changing settings or deactivating the plugin. Otherwise, your action may not take effect.

== Frequently Asked Questions ==

= Why should I use this? =

Watson Conversation, when used with this plugin, allows you to build and deploy a fully customized chat bot with little technical knowledge. It can talk to your website's visitors about whatever you choose, from helping navigate the website and providing support with common questions, to just having a casual conversation on a topic of interest.

= Do I need to know how to code? =

Nope. This plugin allows you to easily deploy chatbots that you create using the Watson Conversation service on IBM Bluemix. [These instructions](https://cocl.us/watson-conversation-help) will guide you through this intuitive process, and there's a course coming soon that's specifically designed to teach you how to do this! No prior technical knowledge necessary.

= How do I see my chatbot's conversations with users? =

On the same page where you build your chatbot in Bluemix, you can click on the Improve tab to view and analyze past conversations with users.

== Screenshots ==
1. An example of your chatbot greeting a website visitor.

== Changelog == 

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

= 0.1.3 =
This version fixes some issues with UI and the Show on Home Page setting.

= 0.1.2 =
This version fixes issues with the UI on mobile devices by adding a floating action button.

= 0.1.1 =
This version adds support for custom API base URLs.