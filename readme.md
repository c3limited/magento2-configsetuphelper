Magento 2 setup-script auto generator for system config settings
================================================================

Automatically creates a setup script for selected options in system configuration page.

It's great to be able to try out settings on a development/staging environment before going live, but to ensure that the live site safely ends up with the same settings, and any other developers also have matching details, we create setup scripts to set config settings.

This is tedious and time-consuming, so why not automate it? Enabling this module will take out the drudgery of setting config settings by hand or writing manual setup scripts by letting you visually pick the settings you want and creating a setup script for you.

How it works: When enabled, there is a link shown in the header to add check-boxes next to each entry in the system config view. By checking some of these and hitting save, you are presented with the complete code to set these options via a setup script. You will need to put this in an installation or upgrade script.

Features:

* Simple to use: Click the link, check the boxes to the left of labels in system config and hit save
* Speed up development - ready to copy and paste into a setup script and save
* Works with any extension that displays options in the system config
* Self-commenting: Gives section and group names to aid visibility

Compatibility
-------------
* Magento >= 2.0.2
