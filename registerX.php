<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Responsive Registration Form | CodingLab</title>
    <link rel="stylesheet" href="style2.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="container">
        <div class="title">Registration</div>
        <div class="content">
            <form id="registrationForm" action="register.php" method="post">
                <div class="user-details">
                    <div class="input-box">
                        <span class="details">Full Name</span>
                        <input type="text" placeholder="Enter your name" name="full-name" required>
                    </div>
                    <div class="input-box">
                        <span class="details">Username</span>
                        <input type="text" placeholder="Enter your username" name="username" required>
                    </div>
                    <div class="input-box">
                        <span class="details">Email</span>
                        <input type="email" placeholder="Enter your email" name="email" required>
                    </div>
                    <div class="input-box">
                        <span class="details">Phone Number</span>
                        <input type="text" placeholder="Enter your number" name="phone-number" required>
                    </div>
                    <div class="input-box">
                        <span class="details">Password</span>
                        <input type="password" placeholder="Enter your password" name="password" required>
                    </div>
                    <div class="input-box">
                        <span class="details">Confirm Password</span>
                        <input type="password" placeholder="Confirm your password" name="confirm-password" required>
                    </div>
                    <div class="input-box">
                        <span class="details">Store Name</span>
                        <input type="text" placeholder="Enter your store name" name="store-name" required>
                    </div>
                    <div class="input-box">
                        <span class="details">Location</span>
                        <input type="text" placeholder="Enter your store location" name="store-location" required>
                    </div>
                    <div class="input-box">
                        <span class="details">Role</span>
                        <select name="role" id="roleSelect" required>
                            <option value="owner">Owner</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="input-box" id="compStaffOption" style="display:none;">
                        <span class="details">Comp Staff</span>
                        <input type="checkbox" name="comp-staff">
                    </div>
                </div>
                <div class="gender-details">
                    <span class="gender-title">Gender</span>
                    <div class="category">
                        <input type="radio" name="gender" id="dot-1" value="Male" required>
                        <label for="dot-1">
                            <span class="dot one"></span>
                            <span class="gender">Male</span>
                        </label>

                        <input type="radio" name="gender" id="dot-2" value="Female" required>
                        <label for="dot-2">
                            <span class="dot two"></span>
                            <span class="gender">Female</span>
                        </label>
                    </div>
                </div>
                <div class="button">
                    <input type="submit" value="Register">
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('registrationForm');
            var roleSelect = document.getElementById('roleSelect');
            var compStaffOption = document.getElementById('compStaffOption');

            // Function to show/hide the Comp Staff option based on the selected role
            function toggleCompStaffOption() {
                compStaffOption.style.display = roleSelect.value === 'staff' ? 'block' : 'none';
            }

            // Initial state
            toggleCompStaffOption();

            // Event listener for role select change
            roleSelect.addEventListener('change', toggleCompStaffOption);

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                var formData = new FormData(form);
                fetch('register.php', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        location.reload(); // Reload the page after displaying the message
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    </script>
</body>

</html>

