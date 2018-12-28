# Outgoing Webmentions
Basic *outgoing* Webmention support for WordPress.

Attempts to notify URLs mentioned in the post's body. Uses the [Webmention Client for PHP](https://github.com/indieweb/mention-client-php) to do so. Does not currently support Vouch.

Though this plugin works as expected, it is more of an experiment than anything else. Some things aren't implemented as cleanly as possible. E.g., I've used Composer to include 3rd-party libraries, but have not added them to my `.gitignore`, as I can't expect end users to run `composer install`. (A rather poor excuse, I know, when there aren't any end users.)
