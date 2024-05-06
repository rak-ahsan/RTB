<?php

//customized by me to check if it working 

// $bidRequestJson = file_get_contents('./match_bit_request.json');
// $bidRequest = json_decode($bidRequestJson, true);



//original code according to test pdf (you have to change country(of bid_request.json) and damnation of device according to campaign to get campaign)
$bidRequestJson = file_get_contents('./bid_request.json');
$bidRequest = json_decode($bidRequestJson, true);


$campaignsJson = file_get_contents('./campaign.json');
$campaigns = json_decode($campaignsJson, true);

// Check if bid request and campaigns are valid JSON
if (!$bidRequest || !$campaigns) {
    die('Error: Unable to parse JSON');
}


// Parse bid request
$deviceInfo = $bidRequest['device'];
$geo = $deviceInfo['geo'];
$deviceType = $deviceInfo['os'];
$country = $geo['country'] ?? null;
$dimensions = $bidRequest['imp'][0]['banner']['format'][0] ?? null;

// Find suitable campaign
$selectedCampaign = null;
$maxBidPrice = 0;

foreach ($campaigns as $campaign) {
    // Campaign targeting criteria
    $targetCountry = $campaign['country'];
    $targetDimensions = explode('x', $campaign['dimension']);
    $targetDeviceTypes = array_map('strtolower', explode(',', $campaign['hs_os']));

    // Check if campaign matches bid request criteria
    if ($targetCountry == $country && in_array($dimensions['w'], $targetDimensions) && in_array($deviceType, $targetDeviceTypes)) {
        if ($campaign['price'] > $maxBidPrice) {
            $selectedCampaign = $campaign;
            $maxBidPrice = $campaign['price'];
        }
    }
}

// Generate banner campaign response
if ($selectedCampaign) {
    $response = [
        'id' => $bidRequest['id'],
        'bid_price' => $selectedCampaign['price'],
        'ad_id' => $selectedCampaign['code'],
        'creative_id' => $selectedCampaign['creative_id'],
        'campaign_name' => $selectedCampaign['campaignname'],
        'advertiser' => $selectedCampaign['advertiser'],
        'creative_type' => $selectedCampaign['creative_type'],
        'image_url' => $selectedCampaign['image_url'],
        'landing_page_url' => $selectedCampaign['url']
    ];

    // Output JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // No suitable campaign found
    die('No suitable campaign found');
}
