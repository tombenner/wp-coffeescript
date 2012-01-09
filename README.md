WP CoffeeScript
==================================================
Allows developers to easily use CoffeeScript in WordPress. Simply use enqueue_coffeescript(); the compilation is done automatically behind the scenes.

Description
-----------

WP CoffeeScript is a WordPress plugin that makes enqueueing [CoffeeScript](http://coffeescript.org) as easy as enqueueing JavaScript.  Instead of using [`wp_enqueue_script()`](http://codex.wordpress.org/Function_Reference/wp_enqueue_script), as you would for JS, you just use `enqueue_coffeescript()`, which takes almost exactly the same arguments.  The only difference is that the second argument should be the file path instead of the URL.  If you'd like to compile multiple CS files into a single JS file, you can use an array of file paths as the second argument.

Please note that the [CoffeeScript executable](http://coffeescript.org/#installation) must be installed on the server.  You can also set a custom path to the executable (see the [examples](https://github.com/tombenner/wp-coffeescript)).

If you'd like to grab development releases, see what new features are being added, or browse the source code please visit the [GitHub repo](http://github.com/tombenner/wp-coffeescript)

Installation
------------

1. Put `wp-coffeescript` into the `wp-content/plugins` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Make sure that the [CoffeeScript executable](http://coffeescript.org/#installation) is installed on the server or set a custom path to the executable (see the [examples](https://github.com/tombenner/wp-coffeescript))

Examples
--------

Enqueue a CoffeeScript file that's in the theme directory:

	enqueue_coffeescript('my-handle', get_template_directory().'/my-script.coffee');

Enqueue multiple CS files, compiling them into a single output file:

	$script1 = get_template_directory().'/script1.coffee';
	$script2 = get_template_directory().'/script2.coffee';
	enqueue_coffeescript('my-handle', array($script1, $script2));

Enqueue a CS file in the footer with dependencies (the arguments are exactly the same as in [`wp_enqueue_script()`](http://codex.wordpress.org/Function_Reference/wp_enqueue_script)):

	enqueue_coffeescript('my-handle', get_template_directory().'/my-script.coffee', array('dep1', 'dep2'), false, true);

Set a custom path to the CS executable (the default value is `coffeescript`):

	add_filter('wpcs_executable', 'set_wpcs_executable');
	function set_wpcs_executable($path) {
		return '/my/path/to/coffeescript';
	}

Compile the CS on every page load (the default behavior is to only compile when the JS has been modified):

	add_filter('wpcs_caching_enabled', 'disable_wpcs_caching');
	function disable_wpcs_caching($is_enabled) {
		return false;
	}

Frequently Asked Questions
--------------------------

####Where should I go for support questions or to ask for a new feature?

Please feel free to either add a topic in the WordPress forum or contact me through GitHub for any questions:

* [WordPress Forum](http://wordpress.org/tags/wp-coffeescript?forum_id=10)
* [GitHub](http://github.com/tombenner)