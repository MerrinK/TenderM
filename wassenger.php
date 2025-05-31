<?php

 
function sendWhatsappMessage($number, $message) {
    // $apiKey = '4241a6345eb975d027fd790510af0f9a33ee6a0ab2d49dc7d482dbaf0d63bf144dce90f0bff61a18';
    $apiKey = '007c88735f878962834b6449ad749f8a2146fdd6996b5c814cd768e13fa1ed9cf3f261d4e31cb6e1';

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.wassenger.com/v1/messages",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            "phone" => $number,
            "message" => $message
        ]),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Token: $apiKey"
        ],
        CURLOPT_FAILONERROR => false  // set to true if you want cURL to treat HTTP 4xx/5xx as errors
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if ($err) {
        return "cURL Error #: " . $err;
    } else {
        if ($http_code >= 200 && $http_code < 300) {
            return "Success: " . $response;
        } else {
            return "HTTP Error $http_code: " . $response;
        }
    }
}



function sendWhatsappGroupMessage($groupId, $message) {
    $apiKey = '4241a6345eb975d027fd790510af0f9a33ee6a0ab2d49dc7d482dbaf0d63bf144dce90f0bff61a18';

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.wassenger.com/v1/messages",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            "group" => $groupId,  // ← notice we use "group" instead of "phone"
            "message" => $message
        ]),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Token: $apiKey"
        ],
        CURLOPT_FAILONERROR => false
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if ($err) {
        return "cURL Error #: " . $err;
    } else {
        if ($http_code >= 200 && $http_code < 300) {
            return "Success: " . $response;
        } else {
            return "HTTP Error $http_code: " . $response;
        }
    }
}


function sendWhatsappImage($number, $imageUrl, $caption) {
    $apiKey = '4241a6345eb975d027fd790510af0f9a33ee6a0ab2d49dc7d482dbaf0d63bf144dce90f0bff61a18';

    $payload = [
        "phone" => $number,
        "mediaMessage" => [
            "url" => $imageUrl,
            "caption" => $caption,
            "type" => "image"
        ]
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.wassenger.com/v1/messages",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Token: $apiKey"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        echo "Response: " . $response;
    }
}



// Example usage
sendWhatsappImage('+919940887123', 'https://www.devengineers.com/tenderm/UpDoc/61/Challans/67fff5054c424image.jpg', 'Here is your image');


// Example usage:
// echo sendWhatsappGroupMessage('120363400295096893@g.us', '*Hello world, this is a sample message*\nName: *$name*');

// Example usage:
// echo sendWhatsappMessage('+919940887123', '*Hello world, this is a sample message*\nName: *$name*');

?>
