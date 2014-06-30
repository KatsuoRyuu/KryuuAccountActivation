User Account Activation system
=====

About
-----
This is one of the extenstions to the KryuuAccount module for zf2.
This will check if the user is activated, if not you can choose to logout the user, redirect, for restrict the users abilities by changing the users role in the system


Installation
-----

This module is using doctrine 2 to initialize the database run the the schema-tool
    
    ./vendor/doctrine-module orm:schema-tool:update

You can add a --force to the end to force the changes.

Future
-----

Composer needs to be written.
Need optimization for performance improvement.
