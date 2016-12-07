<?php

$userName     = '';
$userPassword = FALSE;

// Start with a single user.
$multiUsers = FALSE;
$configFile = 'config.php';
$userExists = FALSE;
$userLogged = FALSE;
$config     = FALSE;

// User exists.
if (file_exists($configFile)) {
  require_once $configFile;
  $userExists   = TRUE;
  $userPassword = $config['userPassword'];
}
// User does not exists.
else {
  // Form has been submitted.
  if (isset($_POST['passwordSetSubmit'])) {
    // Save configuration to a separate file.
    // Allow to keep app sync to git repository.
    file_put_contents($configFile, '<?php $config = array(\'userPassword\' => \'' . md5($_POST['passwordSetValue']) . '\'); ?>');
    // Move to the current page.
    header('location:');
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>1m2</title>
</head>
<body>
<?php if (!$userLogged): ?>
  <?php if (!$userPassword) : ?>
    <form action="" method="post">
      <div>Hello, you are the first user on this site.</div>
      <label for="passwordSetValue">Create your password</label>
      <input id="passwordSetValue" name="passwordSetValue" type="password"/>
      <input name="passwordSetSubmit" type="submit"/>
    </form>
  <?php else: ?>
    <form action="" method="post">
      <div>A user has created an account to this site.</div>
      <label for="passwordSetValue">Enter your password</label>
      <input id="passwordSetValue" name="passwordSetValue" type="password"/>
      <input type="submit"/>
    </form>
  <?php endif; ?>
<?php else: ?>

<?php endif; ?>
</body>
</html>
