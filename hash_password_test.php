<?php
// Temporary script to generate a password hash for testing purposes.
// DELETE THIS FILE FROM YOUR SERVER AFTER USE!

$plainTextPassword = 'user1234'; // <--- IMPORTANT: REPLACE THIS with the EXACT plain-text password you want to use for login.

// Generate the hash
$hashedPassword = password_hash($plainTextPassword, PASSWORD_DEFAULT);

echo "<h2>Password Hashing for Testing</h2>";
echo "Plain Text Password (used to generate hash): <strong>" . htmlspecialchars($plainTextPassword) . "</strong><br><br>";
echo "Generated Hashed Password (copy this):<br><strong>" . htmlspecialchars($hashedPassword) . "</strong><br><br>";
echo "<p><strong>Copy the 'Generated Hashed Password' above.</strong> You will use this to update the database for the user 'ndauwo@gmail.com'.</p>";
echo "<p style='color: red;'><strong>IMPORTANT: Delete this 'hash_password_test.php' file from your server after you are done!</strong> It poses a security risk if left accessible.</p>";
?>