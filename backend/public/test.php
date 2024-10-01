<?php

// Gmail IMAP
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$email = 'cmhown@gmail.com';
$accessToken = 'ya29.a0AcM612w6Lah4pHQentqzr69qKWRHeZYjnwXJpJ0pVXfiOHegyMChXSrA6UG1KkTZL-8vNeF8UX0a5SUr4X02GIt6Bb3vxoJ6tN-OftKx6HhUGDiBAsnHM4xeWpsXnyRSA7zzUsmPxJx78ExEabd0XNYOmoqC8Jqwm1_2r9DNaCgYKAfESARISFQHGX2Mi4jv3mkwMmviXumFesobvDw0175';


// Outlook IMAP details
// $hostname = '{outlook.office365.com:993/imap/ssl}INBOX';
// $email = 'mhussainbsit@outlook.com';  // Your Outlook email address
// $accessToken = 'EwCIA8l6BAAUbDba3x2OMJElkF7gJ4z/VbCPEz0AAWPqcQ0knyLIElP2Ex2UbWvj+ixckSK5sFLgq4D8BKx07DP4Eahyh1UALmAu+0y6TlASLXLlY29QzAP6mJX/YTWt31M9oijDWDy8n0C9OmL/mO7Ozu1eOqmc0RGau/9xPRjG6PiDwu+yPnhDT5FwcoF5lBVs2+F8UkImcp6HPE2Lk9/5VZQiUNw0IF4waPEv2kF1kLzvkL8OuJwcyISxm/9qK4pwV1rL8d2Ocn5trJhqp46pMl3aJG/rtxOdw6AFv1/Vk7X/3klEBZYI9WcR051BzusMVJ2vTWjEkpaN+8lVZ1uQd4Rz7z26dpeFyU7kX+uvaVHDLBBQSexW5wvHeDkQZgAAEOTRS4NkJHP3KMvMn1pbgbNQAjdQEXDY554BJxyzMJf4X2iNBYCHlaeGgrbNiXCniPyjahcKR87x89nDwWIWRt7BPt4rILf8UXd4gnDm7vuU7kdgGzrdYJNMh6vD0rdwH+2L+4Un7zSrSoXZ6qq3mA9SkcPS1bSS9KKwiyFgMkW0A2KILdH+Fz45qTKWofAFqnq9i8BPC/PEoFBmq/aB1z/7FjpoMM8NgLbw55Mdkq5G/5SMMzu51FExdZ5uUCURB7D/bSrujRrwm3PwY/fHFWVruo+wQ/nXGkmfF8VrPymujguZljYuEol9Gm5EmDLhRDFcySzWQ7bsOnRbxA0FTa0oH1U3V48WnG6Q0BOj+KhsQaa7rE6uMmnARgd7Ui6A1Qm6Ch3jwCn5XYyoE6ROTFn7YU+hJcXRJpqXXQKMlCJDta5Qv6UG6yGI+9I8A3RDfuy6hqSfh5tj4Yf1xFq7NVhGarl7aHot2z7L5Jl81r0RDTlHFEv01xWUSieOp5SMI1NRxtNKojjy40xKiOSaA25jRgEM8TtJ0X2WEvDY+nZ9KmTRiwNhoup7E/4q2cjS8Hbu9AYfmtoV/Bgn+yEtPJ9p+1uZO5wFOZszEpSc7srWEvCeLrqdojTKCGnOODfMyuRd1fJ16fA/uXKnry0gRzL+Y0R4f/EzoqgEBEcW7cvTCPSzZ/DREADpElDSzH5ydyE31QdOcooS7IGwHXQrEDkX4XjwDcIgeWylOSfXQgVZofzJttPjcH3Am5wZ4aYo0g1YrMWdXyrFQ1zGLHLZ9jG5xV3iAsBBO3x0OskB02c5b8GPAg==';  // Your OAuth2 access token from Microsoft

// Create the OAuth2 authentication string
$authString = base64_encode("user=$email\x01auth=Bearer $accessToken\x01\x01");

// Open an IMAP connection using the OAuth2 token
$imapStream = imap_open($hostname, $email, $authString, 0, 1, [
    'DISABLE_AUTHENTICATOR' => 'PLAIN'
]);

// Check if the connection was successful
if (!$imapStream) {
    echo 'Connection failed: ' . imap_last_error();
} else {
    echo 'Connection successful!';
    
    // Fetch emails (if connection is successful)
    $emails = imap_search($imapStream, 'ALL');
    
    if ($emails) {
        foreach ($emails as $emailNumber) {
            $overview = imap_fetch_overview($imapStream, $emailNumber, 0);
            echo "Subject: " . $overview[0]->subject . "\n";
        }
    }
    
    // Close the IMAP connection
    imap_close($imapStream);
}
