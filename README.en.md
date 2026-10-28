# PS Chat

[Deutsch](README.md) | **English**

[![Version](https://img.shields.io/badge/Version-1.1.5-2271b1?style=flat-square)](readme.txt)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777bb4?style=flat-square&logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-up%20to%207.1.0-21759b?style=flat-square&logo=wordpress&logoColor=white)
![ClassicPress](https://img.shields.io/badge/ClassicPress-2.7.4-03768e?style=flat-square)
[![License](https://img.shields.io/badge/License-GPL--2.0--or--later-2ea44f?style=flat-square)](https://www.gnu.org/licenses/gpl-2.0.html)

**Get your community talking.**

With **PS Chat**, you can add powerful real-time chat directly to your ClassicPress or WordPress website.

Create topic-specific chat rooms, enable an always-visible corner chat for your community, or use chat sessions for support, consultations, and live events.

Whether you run a community, support service, social network, or simply a place for direct conversation, **you decide how and where your users can chat.**

## Chat wherever you need it

PS Chat offers several ways to provide chat on your website:

* **Corner Chat** - an always-visible frontend chat
* **Page and post chat** - topic-specific rooms directly within your content
* **Chat widget** - chat in any available widget area
* **BuddyPress group chat** - a dedicated chat for individual groups
* **Private chats** - direct conversations between connected users

You can run multiple chat instances at the same time, even on the same page.

### Mobile floating chats

At widths up to and including **768 pixels**, corner, network, and private
chats are grouped into a shared window with horizontally scrollable tabs
by default, both on the frontend and in the ClassicPress/WordPress admin.
Only the active chat is visible. New private sessions automatically appear
as additional tabs. Each tab counts unread messages and resets the count
when opened.
The active chat title remains visible in the compact header beneath the tabs,
with chat actions on the right.

Under **Frontend Chat → Position**, you can disable mobile grouping
(`mobile_chat_grouping`: `tabs` or `disabled`) or change the breakpoint
(`mobile_chat_grouping_breakpoint`: 320–1024 pixels, default 768).
These defaults also apply to existing installations. If a network chat is
displayed, its corresponding network chat settings apply.

The shared minimize button remembers the last active tab.
Individual minimize/maximize settings and existing user cookies are preserved
for separate desktop windows. Above the breakpoint, the original chat elements
are moved back to their positions rather than copied.
In the tab bar, **Left/Right** and **Home/End** move focus;
**Enter/Space** opens the selected chat.

## Private guest support with PS Support

The support chat is **completely separate from public rooms and ordinary
private member chats**. Visitors do not need a user account.
Each browser gets its own conversation; other visitors cannot see its
messages or contact details.

1. **Activate PS Support** and configure categories with an assigned staff
   member or team.
2. Under **Network Admin → Support → Settings → Support Chat**, enable the
   network support chat and set its name.
   It appears automatically on the central support site configured in
   PS Support, provided PS Chat is active there.
3. Optionally enable **Allow subsites to use support chats**.
   Each subsite must also have PS Chat active and select local or network
   support under **Support → Settings → Support Chat**.
   Without network permission, or when locally disabled, the chat stays off.
   Local chat support does not require the ticket feature to be enabled.
   Network support inherits required visitor details, the visitor notice,
   retention periods, and staff centrally; the chat name and placement remain local.
4. Under **Who receives requests without an assigned handler?**, select at
   least one support staff member by name. Autocomplete searches names or
   usernames after two characters and returns at most 20 suggestions.
   Click a suggestion to add the person to the visible team; use **Remove**
   to take them out again.
   Eligible people are members of the configured support staff roles,
   administrators, and explicitly assigned category teams within the relevant
   support scope. With CRM integration enabled, only the existing CRM agent
   pool is used. Ordinary website users are not automatically offered.
   Eligibility is also checked when saving and when routing new chats.
5. Use the floating support icon, the `[ps_chat_support]` shortcode, or the
   **PS Chat: Private Support** widget. An embedded chat suppresses additional
   instances and the floating icon on the same page.

Under **Visitor notice**, administrators can write a short explanation of
the process or expected response times. This field is empty by default:
visitors are not shown predefined explanations about cookies, retention
periods, or internal processes. Network support uses the network notice;
local support uses its local text. The notice appears only before a
conversation begins.

An optional **Support Bot** is configured in PS Support under **Settings →
Support Bot**. Visitors can choose a topic and read its help without creating
a session or sending an email. On handoff, the category and an editable
summary are transferred to the request form. Only explicit submission creates
the private chat. A direct route to support remains available, and ongoing
chats are not interrupted by the bot.

Only the **first message** creates a session and emails each responsible
category team member individually. The email contains a link, but no
confidential messages or guest contact details.
Staff must sign in normally and click **Take over chat**.
If several people try at once, only one succeeds; only that person can reply
or close the chat. Opening the link alone does not claim it.
Site administrators can view conversations belonging to their own support
service; super administrators can view all conversations in their network.
Otherwise, access follows the category/fallback recipients saved at the start
and the assigned handler. Local staff must remain members of the responsible
site. Later category changes do not redistribute ongoing chats.

The **Support → Support Chats** inbox is also available without an email link.
There is no separate top-level support chat menu. Settings are managed in
PS Support and remain accessible when PS Chat is inactive; the inbox then
explains that the plugin is missing.
Failed email notifications are shown and can be retried by authorized staff.
Successful sending does not guarantee delivery or indicate that staff are
online; an immediate response is not promised.
There is **no automatic ticket creation**.

### Guest access and retention

- Name and email are optional by default; administrators can require either
  independently. Email addresses are not verified.
- A random **HttpOnly/SameSite-Strict cookie** grants access to the session,
  for **168 hours** by default (configurable from 1–2160 hours).
  HTTPS is required in production. Deleted cookies, a different browser,
  or expiry prevent resuming a session; a public session identifier alone
  does not grant access.
- Messages and contact details are retained for **30 days from the start**
  by default (1–365 days, at least as long as browser access).
  Hourly WP-Cron cleanup then removes the session and messages.
  Configure a system cron for reliable execution. Periods run from the start
  and are not extended by messages; changed settings apply only to new sessions.
- Closed chats can be read in the original browser until access expires.
  The guest can then begin a new, separate request.
- Conversation content is stored as plain text and safely rendered as text,
  without public chat filters or media/upload processing.
  A guest is a browser identity, not a verified user account.
- Starts are limited to five per IP and network per hour; messages are limited
  to 30 per session/author per minute. Rate limiting stores temporary HMAC
  keys, not plain-text IP addresses.

REST routes are under `/psource-chat/v1/support/`. Guest access is protected
by the browser cookie; guest writes additionally require an origin check.
Staff use normal sign-in and a REST nonce. Every conversation is also checked
against its support scope and staff assignment.
Private responses are not cacheable. Tables:
`{base_prefix}ps_chat_support_sessions`, `ps_chat_support_messages`, and
`ps_chat_support_limits`. Data is not end-to-end encrypted:
authorized administrators and operators with database access can read it.
Account for this in privacy notices and server backups; chat cleanup does
not modify backups.

### Testing the integration

On a **disposable test installation** with both plugins active:

```sh
PS_CHAT_SUPPORT_TEST=1 wp eval-file tests/support-chat-integration.php
wp eval-file ../ps-support/tests/category-teams.php
```

The support chat test checks real REST permissions and database access,
including browser isolation, forged tokens, origin checks, team takeover,
reply permissions, retries, mail failures, and cleanup.
Test emails are intercepted; users, sessions, and changed settings are restored.

## Adapts to your website

![PS Chat settings](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/chat-settings.jpg)

*Match the appearance of PS Chat to your website design.*

PS Chat offers extensive options to match your theme and website.
Different chat types have their own appearance settings, including:

* Position
* Size
* Borders
* Colors
* Font family and size
* Buttons
* Spacing
* User lists
* Avatars
* Time and date displays
* Emoticons
* System messages

This lets you adapt each chat to your website.

## User roles and access control

![PS Chat on a page](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/chat-page.jpg)

*Create topic-specific chat rooms directly on pages and posts.*

You decide who can see and use your chats.
Open a chat to all visitors, or restrict it to signed-in users and specific
user roles. This makes PS Chat suitable for:

* Public community chats
* Members-only areas
* Internal team communication
* Support sessions
* Customer areas
* Private events

## Chat history and moderation

![PS Chat logs](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/logs.jpg)

*Save chat histories and access them later.*

Chat history is especially useful for support and community communication.
Built-in moderation features let you:

* Save chat histories
* Review past conversations
* Hide individual messages
* Ban users
* Block users by email address
* Block IP addresses
* Block specific words
* Customize messages for blocked content

Stay in control even as your community grows.

## BuddyPress and social networks

![PS Chat user status](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/chat-available-735x470.jpg)

*Let other users know whether you are online.*

PS Chat works well with **BuddyPress**.
Groups can have their own live chat, accessible only to their members.
Users can also display their online status and, depending on configuration,
chat directly with their friends.

## Chat from anywhere

![PS Chat window](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/chat-window-735x4701.jpg)

*Place chat where your users need it.*

You can use PS Chat almost anywhere on your website:

* As a frontend corner chat
* In the admin area
* Within posts and pages
* In custom post types
* In widget areas
* Within BuddyPress groups

Provide a dedicated discussion for a film, event, product, or article.

## Multiple chat instances

PS Chat is not limited to a single chat.
Run multiple instances simultaneously and configure each one individually:

* A public community chat
* A support chat
* A members-only chat
* Dedicated chats for specific posts
* BuddyPress group chats
* Private conversations between users

## Insert chat directly in the editor

PS Chat provides editor integration for page and post chats.

![PS Chat editor button](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-page-wysiwyg-button.png)

Choose which post types should offer chat in the editor.
A chat button then appears while editing the relevant content.

![PS Chat editor settings](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-page-wysiwyg-modal.png)

Insert an individual chat and configure its settings in just a few clicks.
This is especially useful for:

* Film and TV databases
* Event portals
* Product pages
* Knowledge bases
* Community projects
* Gaming websites
* Discussion pages

## Chat as a widget

![PS Chat widget](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-widget.png)

Want to display a chat in your sidebar or another widget area?
Add the **PS Chat widget** through widget management and configure it there.

## BuddyPress group chat

![PS Chat BuddyPress settings](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-buddypress-group-settings.png)

PS Chat integrates directly with **BuddyPress groups**.
Group administrators can enable chat for their group.
It is then accessible only to members of that group.

![PS Chat BuddyPress group chat](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-buddypress-group-chat-2.png)

Once enabled, all group members can join the conversation.

## Private messages

![PS Chat private chat](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-private-chat.png)

Users can start private conversations with other users who are online.
With BuddyPress, private chats can be started directly from friends lists.

![PS Chat BuddyPress private chat](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-private-buddypress1.png)

## Online status

![PS Chat online status](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-online-status.png)

Signed-in users can manage their online status from the WordPress toolbar,
so other members can immediately see who is available.
Each user can adjust the available features in their profile.

![PS Chat user profile](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-user-profile-settings.png)

## General settings

PS Chat offers shared settings for all chat instances as well as individual
options for each chat type.

### Polling

Set how often PS Chat checks for new messages and which method it uses.

![PS Chat polling](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-common-polling.png)

### Moderation

Configure global moderation rules for your chats.

![PS Chat moderation](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-common-moderation.png)

Block users by IP or email address, and block specific words.

### URL controls

Choose which pages and URLs may display chat instances.

![PS Chat URL controls](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-common-url-blocking.png)

These settings can affect both the frontend and the admin area.

### WordPress administration

Set defaults for users who have not yet customized their personal settings.

![PS Chat WP Admin settings](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-common-wpadmin.png)

### BuddyPress

Additional group chat settings are available when BuddyPress is active.

![PS Chat BuddyPress settings](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-common-buddypress.png)

## Corner Chat

Corner Chat is ideal when you want your community to be able to communicate
at any time.

![PS Chat corner settings](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-site-bottom-corner.png)

Enable it on the frontend, in the admin area, or both, and give it a custom title.

### Appearance

![PS Chat appearance](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-site-appearance.png)

Match colors, sizes, fonts, and other elements to your website design.

### Position

![PS Chat position](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-site-position.png)

Chat does not have to appear in the bottom-right corner.
You can position it at the top left, top right, bottom left, or bottom right,
and set its distance from the page edges.

### Messages

![PS Chat messages](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-site-messages.png)

Choose how messages are displayed, including:

* Avatars
* Usernames
* Timestamps
* Dates
* Emoticons

### User list

![PS Chat user list](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-site-users-list.png)

Display currently available users and choose the list's position and appearance.
You can display avatars or usernames.

### Authentication

![PS Chat authentication](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-site-authentication.png)

Choose the sign-in methods available for each chat.
You can also configure:

* What information visitors who are not signed in can see
* Which user roles can act as moderators
* Which authentication methods are used

### Advanced settings

![PS Chat advanced settings](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-site-advanced.png)

Advanced options let you:

* Limit the number of displayed messages
* Define custom session messages
* Exclude specific URLs from chat

## Page and post chats

Additional settings are available for chats within pages and posts.

![PS Chat editor settings](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-page-wysiwyg-settings.png)

Choose which post types offer the chat button in the editor and which users
can use it. This is particularly useful for dedicated discussions on
individual pieces of content.

![PS Chat editor button](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-page-wysiwyg-button.png)

![PS Chat editor dialog](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-page-wysiwyg-modal.png)

## BuddyPress groups

![PS Chat BuddyPress groups](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-buddypress-group-settings.png)

Each BuddyPress group can have its own chat.
The group administrator enables it directly for the group.

![PS Chat BuddyPress group chat](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-buddypress-group-chat-2.png)

The chat is then available only to members of that group.

## Personal settings

Signed-in users can set their online status from the WordPress toolbar.

![PS Chat online status](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-online-status.png)

Users can also manage their personal chat preferences through their profiles.

![PS Chat user profile settings](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-user-profile-settings.png)

With BuddyPress, users can also start private chats directly from their
friends lists.

![PS Chat private BuddyPress chat](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-private-buddypress1.png)

## Performance and server resources

Real-time chat can require additional server resources, particularly with
many simultaneously active users.

PS Chat is designed to keep the load as low as possible. On busy websites
with many active chat instances, you should nevertheless monitor available
server resources.

On larger websites, increasing the PHP memory limit available to WordPress
may help. For example:

```php
define( 'WP_MEMORY_LIMIT', '256M' );
```

Add this setting to `wp-config.php` before:

```php
/* That's all, stop editing! Happy publishing. */
```

Shared hosting with many concurrent chat users may also require a more
powerful hosting plan.

## PS Chat features

### Chat types

* Corner Chat
* Chat within posts and pages
* Chat widget
* BuddyPress group chat
* Private chats
* Multiple simultaneous chat instances

### Appearance

* Avatars
* Gravatar support
* Emoticons
* Chat history
* Chat sounds
* Time and date displays
* Custom colors
* Custom fonts
* Custom font sizes
* Configurable window sizes
* Configurable positioning
* Pop-out chat windows
* Fullscreen mode for tablets and smartphones

### Users and access

* WordPress sign-in
* User roles
* Moderators
* Online status
* Private messages
* BuddyPress integration
* Individual access control

### Moderation

* Chat histories
* Message moderation
* Block users by email address
* Block users by IP address
* Block specific words
* Custom moderation messages
* URL-based display restrictions

### Integration

* ClassicPress
* WordPress
* WordPress Multisite
* BuddyPress
* BuddyPress groups
* Widgets
* Posts
* Pages
* Custom post types

## Compatibility

PS Chat is developed and maintained as a **PSOURCE project** for ClassicPress
and WordPress.

The plugin can be used on both **ClassicPress** and **WordPress**.

## Installation

1. Download PS Chat.
2. Extract the plugin.
3. Upload the plugin folder to `/wp-content/plugins/`.
4. Activate **PS Chat** through plugin management.
5. Open the new **Chat** menu in the admin area.
6. Configure the general settings.
7. Create your first chat.

On a Multisite installation, PS Chat can be network-activated.
Individual sites manage their chat instances independently.

## Notes

If security plugins interfere with PHP execution, you may need to change
the polling method.

Check the polling source under:

**Chat → Settings → General → Polling**

If **Plugin AJAX** causes problems, **WordPress AJAX** can be used instead.

---

## PSOURCE

**Open source from the Weser Uplands.**

PS Chat is part of the **PSOURCE** plugin collection. It is developed to
provide powerful, fully usable open-source solutions for ClassicPress and
WordPress.

**No stripped-down edition. No artificial restrictions. Just software that does its job.**
