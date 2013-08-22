<?php

include 'includes/config.php';
$user = User::isAuthorized();
?>
You are authorized <?php echo $user->user ?>!