=== Plugin Name ===
Contributors: thesify
Donate link: http://thesify.com/
Tags: backup, thesis
Requires at least: 2.9
Tested up to: 3.1
Stable tag: trunk

Thesis Restore Points allows to you create and restore multiple backups of your Thesis (or a Thesis child theme)'s custom folder.

== Description ==

Thesis Restore Points allows to you create and restore multiple backups of your Thesis (or a Thesis child theme)'s
custom folder.

Ability to restore any of the existing backups with a single click is planned for future.

I'd love to get some feedback on this plugin. Feel free to [contact](http://thesify.com/contact).

Thanks to [Gregg](http://www.7thpixel.com/) for the initial idea.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `thesis-restore-points` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

or

1. Install this plugin from the 'Plugins' -> 'Add' screen. Search for 'Thesis Restore Points'

== Frequently Asked Questions ==

= What all is backed up? =

1. The current Thesis custom folder. If a child theme is being used, then the child theme's custom folder.
1. Thesis Design Options and Thesis Site Options

= Where are the backups stored? =

The backups are stored in `your-uploads-folder/thesis-restore-points/filename.zip`, where filename is a random string.
A blank index.html is created by the plugin, to prevent directory listing of the folder.
If you'd like to change the location of the saved backups, you'll have to edit the plugin. I hope to add a better way
to do this in the future.

== Screenshots ==

1. Settings Page

== Changelog ==

= 1.0 =
* Initial Release

== Upgrade Notice ==

= 1.0 =
Initial Release

