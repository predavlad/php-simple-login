php-simple-login
================

A simple login script

Usage:

     include 'includes/config.php';
     $user = User::isAuthorized(); // redirects user to login.php if not logged in, then back to requested page


To create a user write 

    <?php User::createUser('admin', 'pass'); ?>
    
in any file, and run it once.

You need to create the database from the file: db_user.sql


 - Users the password hashing system from https://crackstation.net/hashing-security.htm
 - If you have a PHP version lower than 5.3.0, you need to replace static:: with self:: More info here: http://php.net/manual/ro/language.oop5.late-static-bindings.php

