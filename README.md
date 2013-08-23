php-simple-login
================

A simple login script

Usage:

     include 'includes/config.php';
     $user = User::isAuthorized(); // redirects user to login.php if not logged in


To create a user write <?php User::createUser('admin', 'pass'); ?> and run the file once.

You need to create the database from the file: db_user.sql


