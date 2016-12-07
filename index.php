<?php

// Start with a single user.
$configFile         = 'config.php';
$dataFile           = 'data.json';
$userPassword       = FALSE;
$multiUsers         = FALSE;
$userExists         = FALSE;
$userLogged         = FALSE;
$config             = FALSE;
$message            = FALSE;
$loginPasswordError = FALSE;
$userData           = array(
  'name'        => '',
  'firstName'   => '',
  'description' => '',
  'interests'   => ''
);

// User exists.
if (file_exists($configFile)) {
  require_once $configFile;
  $userExists   = TRUE;
  $userPassword = $config['userPassword'];
  // Log is saved into session.
  session_start();
  // Logout
  if (isset($_GET['logout'])) {
    session_destroy();
    header('location:.');
    exit;
  }
  // User as just submitted login form.
  if (isset($_POST['passwordLoginValue'])) {
    // User has been logged.
    if (md5($_POST['passwordLoginValue']) === $userPassword) {
      $_SESSION['userLogged'] = TRUE;
    }
    else {
      $message            = 'Your password has not been recognized.';
      $loginPasswordError = TRUE;
    }
  }
  // Messages management when user has just created his password.
  else if (isset($_GET['message']) && $_GET['message'] === 'passwordCreated') {
    $message = 'Your password have been saved, welcome to your first meter square!';
  }
  // User is logged.
  if (isset($_SESSION['userLogged']) && $_SESSION['userLogged']) {
    $userLogged = TRUE;
    // Form submitted.
    if (isset($_POST['editDataSubmit'])) {
      $userData = array(
        'firstName'   => $_POST['firstName'],
        'name'        => $_POST['name'],
        'description' => $_POST['description'],
        'interests'   => $_POST['interests']
      );
      file_put_contents($dataFile, json_encode($userData, JSON_OBJECT_AS_ARRAY));
      $message = 'Data updated.';
    }
  }
}
// User does not exists.
// Form has been submitted.
else if (isset($_POST['passwordSetSubmit'])) {
  // Save configuration to a separate file.
  // Allow to keep app sync to git repository.
  file_put_contents($configFile, '<?php $config = array(\'userPassword\' => \'' . md5($_POST['passwordSetValue']) . '\'); ?>');
  // Move to the current page.
  header('location:.?message=passwordCreated');
  exit;
}

// Load data.
if (file_exists($dataFile)) {
  $data = json_decode(file_get_contents($dataFile), JSON_OBJECT_AS_ARRAY);
  foreach ($userData as $key => $value) {
    if (isset($data[$key])) {
      $userData[$key] = $data[$key];
    }
  }
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
  <div>You are now logged | <a href="?logout=1">Logout</a></div>
  <nav>
    <a href=".">Edit</a>
    <a href=".?show=json">Json</a>
    <a href=".?show=turtle">Turtle</a>
  </nav>
  <form action="." method="post">
    <input name="firstName" placeholder="First Name"
           value="<?php print $userData['firstName']; ?>">
    <input name="name" placeholder="Name"
           value="<?php print $userData['name']; ?>">
    <textarea name="description"
              placeholder="Description"><?php print $userData['description']; ?></textarea>
    <textarea name="interests"
              placeholder="Interests"><?php print $userData['interests']; ?></textarea>
    <input type="submit" name="editDataSubmit" value="Save"/>
  </form>
<?php endif; ?>
</body>
</html>
