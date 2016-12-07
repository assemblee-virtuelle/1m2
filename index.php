<?php

// Start with a single user.
$configFile         = 'config.php';
$userPassword       = FALSE;
$multiUsers         = FALSE;
$userExists         = FALSE;
$userLogged         = FALSE;
$config             = FALSE;
$message            = FALSE;
$loginPasswordError = FALSE;

// User exists.
if (file_exists($configFile)) {
  require_once $configFile;
  $userExists   = TRUE;
  $userPassword = $config['userPassword'];
  // User as just submitted login form.
  if (isset($_POST['passwordLoginValue'])) {
    if (md5($_POST['passwordLoginValue']) === $userPassword) {
      $userLogged = TRUE;
    }
    else {
      $message            = 'Your password has not been recognized.';
      $loginPasswordError = TRUE;
    }
  }
  // Messages management.
  else if (isset($_GET['message']) && $_GET['message'] === 'passwordCreated') {
    $message = 'Your password have been saved, welcome to your first meter square!';
  }
}
// User does not exists.
// Form has been submitted.
else if (isset($_POST['passwordSetSubmit'])) {
  // Save configuration to a separate file.
  // Allow to keep app sync to git repository.
  file_put_contents($configFile, '<?php $config = array(\'userPassword\' => \'' . md5($_POST['passwordSetValue']) . '\'); ?>');
  // Move to the current page.
  header('location:?message=passwordCreated');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>1m2</title>
  <?php if ($message): ?>
    <script>
      // Wait for page load.
      window.addEventListener('DOMContentLoaded', function () {
        alert("<?php print $message; ?>");
      });
    </script>
  <?php endif; ?>
  <style>
    input.error {
      border-color: red;
    }
  </style>
</head>
<body>
<?php if (!$userLogged): ?>
  <?php if (!$userPassword) : ?>
    <form action="." method="post">
      <div>Hello, you are the first user on this site.</div>
      <label for="passwordSetValue">Create your password</label>
      <input id="passwordSetValue" name="passwordSetValue" type="password"/>
      <input name="passwordSetSubmit" type="submit"/>
    </form>
  <?php else: ?>
    <form action="." method="post">
      <div>A user has created an account to this site.</div>
      <label for="passwordLoginValue">Enter your password</label>
      <input id="passwordLoginValue" name="passwordLoginValue" type="password"
             class="<?php print $loginPasswordError ? 'error' : ''; ?>"/>
      <input type="submit"/>
    </form>
  <?php endif; ?>
<?php else: ?>

<?php endif; ?>
</body>
</html>
