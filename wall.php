 <?php
  require_once('appvars.php');
  require_once('connectvars.php');
    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    
    $postquery = "SELECT * FROM wall_vault";
    $single= mysqli_query($dbc, $postquery);
    $tyrone = mysqli_fetch_array($single);
    $wallposter = $tyrone['poster_name'];
    $postname = "SELECT username FROM friend_vault WHERE user_id = '$wallposter' ";
    
    $double = mysqli_query($dbc, $postname);
    $jimmy = mysqli_fetch_array($double);
    
    
  echo '<section id = wall>';
  echo '<h4>Wall Posts:</h4>';
  echo '<table>';
  while ($tyrone = mysqli_fetch_array($single)) {
    if (isset($_SESSION['user_id'])) {
      echo '<p>' . $tyrone['poster_name'] . ': ' .$tyrone['post_content'].'</p></td></tr>';
    }
    else {
      echo '<td><p>Log in to see wall posts!</p></td></tr>';
    }
  }
  echo '</table>';
  echo '</section>';
  
  
  mysqli_close($dbc);
  
  
  if (isset($_POST['submit'])) {
    // Connect to the database
    
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $wall_id = $_SESSION['user_id'];
    $post_user = $_SESSION['username'];
    $post_content = $_POST['post_content'];
 

    if (!empty($post_content)) {
      
            $query = "INSERT INTO wall_vault (posted_user_id, post_content, poster_name) VALUES ( '$wall_id', '$post_content', '$post_user')";
            //echo $query;
            mysqli_query($dbc, $query);

            // Confirm success with the user
            echo '<p class="error">Post Successful, please refresh! </p><br />';
            
            // Clear the form
            $wall_id = "";
            $post_content = "";

            mysqli_close($dbc);
          }
          else {
            echo '<p class="error">Sorry, there was a problem.</p>';
          }

    }
 ?>
<section id="wallpost">
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <fieldset>
      <legend>Wall</legend>
      <label for="post_content">Post To Wall:</label>
      <input type="text" id="post_content" name="post_content" value="<?php if (!empty($username)) echo $username; ?>" /><br />
    </fieldset>
    <input type="submit" value="Post" name="submit" />
  </form>
</section>












