<?php
if (isset($formError) && $formError) {
    echo '<p>Invalid username or password</p>';
}
?>
<form method="post" action="">
    <label for="name">Username:
        <input type="text" name="user" id="user" />
    </label>

    <label for="pass">Password:
        <input type="text" name="pass" id="pass"/>
    </label>

    <button type="submit">Login</button>
</form>