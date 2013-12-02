<?php
  // Start the session
  require_once('startsession.php');

  // Insert the page header
  $page_title = 'Edit Your Vault Profile';
  require_once('header.php');

  require_once('appvars.php');
  require_once('connectvars.php');

  // Make sure the user is logged in before going any further.
  if (!isset($_SESSION['user_id'])) {
    echo '<p class="login">Please <a href="login.php">login</a></p>';
    exit();
  }

  // Show the navigation menu
  require_once('navmenu.php');

  // Connect to the database
  $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  //birthdate regular expression
  $pattern = '/^\d{4}-\d{2}-\d{2}$/';
  if (isset($_POST['submit'])) {
    // Grab the profile data from the POST
    $first_name = mysqli_real_escape_string($dbc, trim($_POST['firstname']));
    $last_name = mysqli_real_escape_string($dbc, trim($_POST['lastname']));
    $gender = mysqli_real_escape_string($dbc, trim($_POST['gender']));
    $birthdate = mysqli_real_escape_string($dbc, trim($_POST['birthdate']));
    $city = mysqli_real_escape_string($dbc, trim($_POST['city']));
    $province = mysqli_real_escape_string($dbc, trim($_POST['province']));
    $old_picture = mysqli_real_escape_string($dbc, trim($_POST['old_picture']));
    $new_picture = mysqli_real_escape_string($dbc, trim($_FILES['new_picture']['name']));
    $new_picture_type = $_FILES['new_picture']['type'];
    $new_picture_size = $_FILES['new_picture']['size']; 
    list($new_picture_width, $new_picture_height) = getimagesize($_FILES['new_picture']['tmp_name']);
    $error = false;

    // Validate and move the uploaded picture file, if necessary
    if (!empty($new_picture)) {
      if ((($new_picture_type == 'image/gif') || ($new_picture_type == 'image/jpeg') || ($new_picture_type == 'image/pjpeg') ||
        ($new_picture_type == 'image/png')) && ($new_picture_size > 0) && ($new_picture_size <= MM_MAXFILESIZE) &&
        ($new_picture_width <= MM_MAXIMGWIDTH) && ($new_picture_height <= MM_MAXIMGHEIGHT)) {
        if ($_FILES['file']['error'] == 0) {
          // Move the file to the target upload folder
          $target = MM_UPLOADPATH . basename($new_picture);
        if (move_uploaded_file($_FILES['new_picture']['tmp_name'], $target)) {
            // The new picture file move was successful, now make sure any old picture is deleted
            if (!empty($old_picture) && ($old_picture != $new_picture)) {
              @unlink(MM_UPLOADPATH . $old_picture);
            }
          }
          else {
            // The new picture file move failed, so delete the temporary file and set the error flag
            @unlink($_FILES['new_picture']['tmp_name']);
            $error = true;
            echo '<p class="error">Sorry, there was an error while trying to upload your picture!</p>';
          }
        }
      }
      else {
        // The new picture file is not valid, so delete the temporary file and set the error flag
        @unlink($_FILES['new_picture']['tmp_name']);
        $error = true;
        echo '<p class="error">The picture your trying to upload must be a GIF, JPEG, or PNG image file no greater than ' . (MM_MAXFILESIZE / 1024) .
          ' KB and ' . MM_MAXIMGWIDTH . 'x' . MM_MAXIMGHEIGHT . ' pixels!</p>';
      }
    }

    // Update the profile data in the database
    if (!$error) {
        $subject = $birthdate;
        if(preg_match($pattern, $subject) == 1){ 
      if (!empty($first_name) && !empty($last_name) && !empty($gender) && !empty($birthdate) && !empty($city) && !empty($province)) {
        // Only set the picture column if there is a new picture
        if (!empty($new_picture)) {
          $query = "UPDATE friend_vault SET first_name = '$first_name', last_name = '$last_name', gender = '$gender', " .
            " birthdate = '$birthdate', city = '$city', province = '$province', picture = '$new_picture' WHERE user_id = '" . $_SESSION['user_id'] . "'";
        }
        else {
          $query = "UPDATE friend_vault SET first_name = '$first_name', last_name = '$last_name', gender = '$gender', " .
            " birthdate = '$birthdate', city = '$city', province = '$province' WHERE user_id = '" . $_SESSION['user_id'] . "'";
        }
      
      
        mysqli_query($dbc, $query);

        // Confirm success with the user
        echo '<p>Your Vault Profile has been successfully updated.<a href="viewprofile.php">View your updated Vault Profile</a>?</p>';

        mysqli_close($dbc);
      exit();
      }
      else {
        echo '<p class="error">Please enter in all profile info with birthdate format as YYYY-MM-DD (Profile picture not required).</p>';
            }
        }
    
    }
  } // End of check for form submission
  else {
    // Grab the profile data from the database
    $query = "SELECT first_name, last_name, gender, birthdate, city, province, picture FROM friend_vault WHERE user_id = '" . $_SESSION['user_id'] . "'";
    $data = mysqli_query($dbc, $query);
    $row = mysqli_fetch_array($data);

    if ($row != NULL) {
      $first_name = $row['first_name'];
      $last_name = $row['last_name'];
      $gender = $row['gender'];
      $birthdate = $row['birthdate'];
      $city = $row['city'];
      $province = $row['province'];
      $old_picture = $row['picture'];
    }
    else {
      echo '<p class="error">Error occured while trying to access your Vault</p>';
    }
  }

  mysqli_close($dbc);
?>

  <form enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
    <fieldset>
      <legend>Personal Information</legend>
      <label for="firstname">First name:</label>
      <input type="text" id="firstname" name="firstname" value="<?php if (!empty($first_name)) echo $first_name; ?>" /><br />
      <label for="lastname">Last name:</label>
      <input type="text" id="lastname" name="lastname" value="<?php if (!empty($last_name)) echo $last_name; ?>" /><br />
      <label for="gender">Gender:</label>
      <select id="gender" name="gender">
        <option value="M" <?php if (!empty($gender) && $gender == 'M') echo 'selected = "selected"'; ?>>Male</option>
        <option value="F" <?php if (!empty($gender) && $gender == 'F') echo 'selected = "selected"'; ?>>Female</option>
      </select><br />
      <label for="birthdate">Birthdate:</label>
      <input type="text" id="birthdate" name="birthdate" value="<?php if (!empty($birthdate)) echo $birthdate; else echo 'YYYY-MM-DD'; ?>" /><br />
      <label for="city">City:</label>
      <input type="text" id="city" name="city" value="<?php if (!empty($city)) echo $city; ?>" /><br />
      <label for="province">Province:</label>
      <input type="text" id="province" name="province" value="<?php if (!empty($province)) echo $province; ?>" /><br />
      <input type="hidden" name="old_picture" value="<?php if (!empty($old_picture)) echo $old_picture; ?>" />
      <label for="new_picture">Picture:</label>
      <input type="file" id="new_picture" name="new_picture" />
      <?php if (!empty($old_picture)) {
        echo '<img class="profile" src="' . MM_UPLOADPATH . $old_picture . '" alt="Profile Picture" />';
      } ?>
    </fieldset>
    <input type="submit" value="Save Profile" name="submit" />
  </form>

<?php
  // Insert the page footer
  require_once('footer.php');
?>