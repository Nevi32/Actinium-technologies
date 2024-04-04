<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notification Subscription</title>
<style>
  body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
  }
  .card {
    width: 400px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  label {
    display: block;
    margin-bottom: 10px;
  }
  input[type="text"], input[type="submit"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
    box-sizing: border-box;
  }
  input[type="submit"] {
    background-color: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
  }
</style>
</head>
<body>

<div class="card">
  <h2>Notification Subscription</h2>
  <form id="subscriptionForm">
    <label for="storename">Store Name:</label>
    <input type="text" id="storename" name="storename" required>
    <label for="subscription">Subscription:</label>
    <input type="text" id="subscription" name="subscription" required>
    <input type="submit" value="Subscribe">
  </form>
</div>

<script>
document.getElementById("subscriptionForm").addEventListener("submit", function(event) {
  event.preventDefault();
  var formData = new FormData(this);
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "notify.php", true);
  xhr.onload = function() {
    if (xhr.status === 200) {
      alert(xhr.responseText);
    } else {
      alert("Failed to subscribe.");
    }
  };
  xhr.send(formData);
});
</script>

</body>
</html>

