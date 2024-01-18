<?php
session_start();

// Destroy the session
session_destroy();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
   <head>
      <meta charset="utf-8">
      <title>Logout</title>
      <!-- Add meta tags to prevent caching -->
      <meta http-equiv="cache-control" content="no-store, no-cache, must-revalidate, max-age=0">
      <meta http-equiv="cache-control" content="post-check=0, pre-check=0">
      <meta http-equiv="pragma" content="no-cache">
      <meta http-equiv="expires" content="0">
   </head>
   <body>
      <script>
         // Redirect the user to the login page
         window.location.href = 'index.html';
      </script>
   </body>
</html>

