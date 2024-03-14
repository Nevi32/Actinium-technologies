<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Memo</title>
    <link rel="stylesheet" href="nstyle.css">
</head>
<body>
    <h1>Create Memo</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="memo-form">
        <div class="message-container">
            <label for="message">Message:</label>
            <textarea name="message" id="message" rows="10" placeholder="Write your message here..."></textarea>
            <div class="formatting-options">
                <select name="font-family" id="font-family">
                    <option value="Arial">Arial</option>
                    <option value="Courier New">Courier New</option>
                    <option value="Verdana">Verdana</option>
                </select>
                <button type="button" id="emoji-picker">&#128512;</button> <input type="text" id="emoji-search" placeholder="Search emojis..." disabled> <div id="emoji-list">  </div>
            </div>
        </div>
        <div class="subscriptions">
            <h2>Send to:</h2>
            <ul>
                <li><input type="checkbox" name="subscriptions[]" value="generalstaff"> General Staff</li>
                <li><input type="checkbox" name="subscriptions[]" value="specificstaff"> Specific Staff</li>
                </ul>
        </div>
        <button type="submit">Send Memo</button>
        <a href="home.php">View Dashboard</a>
    </form>
    <script>
</script>
    <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Fixed typo here
            // Process form submission
            $message = $_POST['message'];
            $fontFamily = $_POST['font-family'];
            $subscriptions = $_POST['subscriptions'] ?? []; // Handle empty selection

            // API call logic using ntfy.sh
            $url = 'https://ntfy.sh/'; // Replace with your ntfy endpoint
            foreach ($subscriptions as $subscription) {
                $postData = [
                    'message' => $message,
                    // You can add additional data like 'title' or 'priority' here
                ];
                $context = stream_context_create([
                    'http' => [
                        'method' => 'POST', // PUT also works
                        'header' => "Content-Type: application/json\r\n",
                        'content' => json_encode($postData),
                    ],
                ]);
                file_get_contents($url . $subscription, false, $context);
            }

            // Display success message or handle errors (optional)
            echo '<p>Memo sent successfully!</p>';
        }
    ?>
</body>
</html>

