<?php

// Start with a single user.
$configFile         = 'config.php';
$dataFile           = 'data.json';
$userPassword       = FALSE;
$userExists         = FALSE;
$userLogged         = FALSE;
$config             = FALSE;
$message            = FALSE;
$loginPasswordError = FALSE;
$userData           = array(
  'title'     => '',
  'name'      => '',
  'firstName' => '',
  'nickname'  => '',
  'email'     => '',
  'homepage'  => '',
  'picture'   => '',
  'phone'     => '',
);

// Log is saved into session.
session_start();

// User exists.
if (file_exists($configFile)) {
  require_once $configFile;
  $userExists   = TRUE;
  $userPassword = $config['userPassword'];
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
      $saveData = array();
      // Get posted fields regarding expected keys.
      foreach ($userData as $key => $value) {
        $saveData[$key] = isset($_POST[$key]) ? $_POST[$key] : '';
      }
      file_put_contents($dataFile, json_encode($saveData, JSON_OBJECT_AS_ARRAY));
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
  // Mark as logged.
  $_SESSION['userLogged'] = TRUE;
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

// Show data.
if (isset($_GET['show'])) {
  // Set HTTP headers.
  header('Content-Type: application/rdf+xml');
  if (file_exists($dataFile)) {
    print '<rdf:RDF
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
      xmlns:foaf="http://xmlns.com/foaf/0.1/"
      xmlns:admin="http://webns.net/mvcb/">
      <foaf:Person rdf:ID="me">
      <foaf:name>' . $userData['firstName'] . ' ' . $userData['name'] . '</foaf:name>
      <foaf:title>' . $userData['title'] . '</foaf:title>
      <foaf:givenName>' . $userData['firstName'] . '</foaf:givenName>
      <foaf:familyName>' . $userData['name'] . '</foaf:familyName>
      <foaf:nick>' . $userData['nickname'] . '</foaf:nick>
      <foaf:mbox_sha1sum>' . sha1($userData['email']) . '</foaf:mbox_sha1sum>
      <foaf:homepage rdf:resource="http://network.wexample.com"/>
      <foaf:depiction rdf:resource="http://network.wexample.com/mypic.jpg"/>
      <foaf:phone rdf:resource="tel:652626602"/></foaf:Person>
      </rdf:RDF>';
    exit;
  }
  else {
    $message = 'Please fill up your form to make your profile available.';
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>1m2</title>
  <!-- Load Bootstrap -->
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
        integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
        crossorigin="anonymous">
  <!-- Optional theme -->
  <link rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
        integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp"
        crossorigin="anonymous">
  <!-- Latest compiled and minified JavaScript -->
  <script
    src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
    integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
    crossorigin="anonymous"></script>
  <style>
    input.error {
      border-color: red;
    }
    .col-profile {
      text-align: center;
    }
    .profile-image {
      font-size: 30px;
      color: #DDD;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="row">
    <h1>First digital square meter</h1>
    <?php if ($message): ?>
      <div class="alert alert-info" role="alert">
        <?php print $message; ?>
      </div>
    <?php endif; ?>
    <?php if (!$userLogged): ?>
      <?php if (!$userPassword) : ?>
        <form action="." method="post">
          <div class="alert alert-info" role="alert">You are the first
            user on this site.
          </div>
          <div class="col-lg-12">
            <label for="passwordSetValue">Create your password</label>
            <div class="input-group">
              <input id="passwordSetValue"
                     name="passwordSetValue"
                     type="password" class="form-control"
                     placeholder="Type a new password...">
                <span class="input-group-btn">
                  <input
                    class="btn btn-default"
                    type="submit"
                    name="passwordSetSubmit" value="Create"/>
                </span>
            </div>
          </div>
        </form>
      <?php else: ?>
        <form action="." method="post">
          <?php if ($loginPasswordError): ?>
            <div class="alert alert-danger" role="alert">Your password has not
              been recognized.
            </div>
          <?php else: ?>
            <div class="alert alert-info" role="alert">A user has created an
              account to this site.
            </div>
          <?php endif; ?>
          <div class="col-lg-12">
            <label for="passwordLoginValue">Enter your password</label>
            <div class="input-group">
              <input id="passwordLoginValue"
                     name="passwordLoginValue"
                     type="password" class="form-control
                     <?php print $loginPasswordError ? 'error' : ''; ?>"
                     placeholder="Type your master password...">
                <span class="input-group-btn">
                  <input
                    class="btn btn-default"
                    type="submit"
                    name="passwordLoginSubmit" value="Login"/>
                </span>
            </div>
          </div>
        </form>
      <?php endif; ?>
    <?php else: ?>

      <div id="exTab1" class="container">
        <div class="row">
          <ul class="nav nav-tabs">
            <li class="active">
              <a href="." data-toggle="tab">Edit</a>
            </li>
            <li>
              <a href="?show=rdf" target="_blank">RDF</a>
            </li>
            <li>
              <a href="?logout=1">Logout</a>
            </li>
          </ul>
        </div>
        <br/>
      </div>

      <form action="." method="post">

        <div class="col col-sm-2 col-profile">
          <div class="profile-image glyphicon glyphicon-user"></div>
        </div>
        <div class="col col-sm-10">
          <div class="form-group">
            <select class="form-control" name="title"
                    placeholder="First Name"
                    value="<?php print $userData['firstName']; ?>">
              <option value="Mr">Mr</option>
              <option value="Mrs">Mrs</option>
              <option value="Ms">Ms</option>
              <option value="Dr">Dr</option>
              <option value="">- none -</option>
            </select>
          </div>

          <div class="form-group">
            <input class="form-control" name="firstName"
                   placeholder="First Name"
                   value="<?php print $userData['firstName']; ?>">
          </div>

          <div class="form-group">
            <input class="form-control" name="name" placeholder="Name"
                   value="<?php print $userData['name']; ?>">
          </div>
          <div class="form-group">
            <input class="form-control" name="nickname"
                   placeholder="Nickname"
                   value="<?php print $userData['nickname']; ?>">
          </div>

          <div class="form-group">
            <input class="form-control" name="email"
                   placeholder="Email"
                   value="<?php print $userData['email']; ?>">
          </div>

          <div class="form-group">
            <input class="form-control" name="homepage"
                   placeholder="Homepage http://..."
                   value="<?php print $userData['homepage']; ?>">
          </div>

          <div class="form-group">
            <input class="form-control" name="phone"
                   placeholder="Phone number"
                   value="<?php print $userData['phone']; ?>">
          </div>

          <div class="form-group">
            <input class="btn btn-default" type="submit" name="editDataSubmit"
                   value="Save"/>
          </div>
        </div>

      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
