<?php

function sendWhatsappImage($number, $imageUrl, $caption) {
    $apiKey = '4241a6345eb975d027fd790510af0f9a33ee6a0ab2d49dc7d482dbaf0d63bf144dce90f0bff61a18';

    $payload = [
        "phone" => $number,
        "message" => $caption,
        "media" => $imageUrl,  // ✅ correct key name: 'media'
        "type" => "image"      // ✅ required type
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.wassenger.com/v1/messages",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
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
sendWhatsappImage(
    '+919940887123',
    'https://www.devengineers.com/tenderm/UpDoc/61/Challans/67fff5054c424image.jpg',
    "🔔 *Challan Added*\n\n*Amount:* ₹1234\n*Challan No:* 5678\n*Date:* 05-05-2025"
);

?>
