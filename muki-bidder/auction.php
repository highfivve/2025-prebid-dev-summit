<?php
// Manual requires for all dependencies (no Composer, no autoloader)
require_once __DIR__ . '/openrtb/abstractions/BaseModel.php';
require_once __DIR__ . '/openrtb/abstractions/ParentModel.php';
require_once __DIR__ . '/openrtb/exceptions/ValidationException.php';
// Models
require_once __DIR__ . '/openrtb/models/App.php';
require_once __DIR__ . '/openrtb/models/Format.php';
require_once __DIR__ . '/openrtb/models/Banner.php';
require_once __DIR__ . '/openrtb/models/Bid.php';
require_once __DIR__ . '/openrtb/models/Content.php';
require_once __DIR__ . '/openrtb/models/Data.php';
require_once __DIR__ . '/openrtb/models/Deal.php';
require_once __DIR__ . '/openrtb/models/Device.php';
require_once __DIR__ . '/openrtb/models/Extension.php';
require_once __DIR__ . '/openrtb/models/Geo.php';
require_once __DIR__ . '/openrtb/models/Impression.php';
require_once __DIR__ . '/openrtb/models/Native.php';
require_once __DIR__ . '/openrtb/models/PMP.php';
require_once __DIR__ . '/openrtb/models/Producer.php';
require_once __DIR__ . '/openrtb/models/Publisher.php';
require_once __DIR__ . '/openrtb/models/Regulation.php';
require_once __DIR__ . '/openrtb/models/SeatBid.php';
require_once __DIR__ . '/openrtb/models/Segment.php';
require_once __DIR__ . '/openrtb/models/Site.php';
require_once __DIR__ . '/openrtb/models/User.php';
require_once __DIR__ . '/openrtb/models/Video.php';
require_once __DIR__ . '/openrtb/models/Specification/BitType.php';
require_once __DIR__ . '/openrtb/models/Specification/NoBidReason.php';
// Main OpenRTB classes
require_once __DIR__ . '/openrtb/BidRequest.php';
require_once __DIR__ . '/openrtb/BidResponse.php';

use openrtb\BidRequest;
use openrtb\BidResponse;
use openrtb\models\SeatBid;
use openrtb\models\Bid;

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// CORS headers
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
$allowCredentials = false;
if ($origin !== '*') {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    $allowCredentials = true;
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
if ($allowCredentials) {
    header('Access-Control-Allow-Credentials: true');
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Read and parse the request body
$body = file_get_contents('php://input');
$bidRequest = new BidRequest();
try {
    $bidRequest->hydrate($body, true);
} catch (Exception $e) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request', 'details' => $e->getMessage()]);
    exit;
}

// Example image creatives with size and url
$imageCreatives = [
    [ 'w' => 300, 'h' => 250, 'url' => 'https://picsum.photos/id/1015/300/250' ],
    [ 'w' => 320, 'h' => 100, 'url' => 'https://picsum.photos/id/1025/320/100' ],
    [ 'w' => 320, 'h' => 50,  'url' => 'https://picsum.photos/id/1035/320/50'  ],
    [ 'w' => 728, 'h' => 90,  'url' => 'https://picsum.photos/id/1045/728/90'  ],
];

// UUIDv4 generator compatible with all PHP
// https://www.delftstack.com/howto/php/php-uuid/
function uuidv4() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for the time_low
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        // 16 bits for the time_mid
        mt_rand(0, 0xffff),
        // 16 bits for the time_hi,
        mt_rand(0, 0x0fff) | 0x4000,

        // 8 bits and 16 bits for the clk_seq_hi_res,
        // 8 bits for the clk_seq_low,
        mt_rand(0, 0x3fff) | 0x8000,
        // 48 bits for the node
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

$seatBids = [];
$impList = $bidRequest->get('imp');
if (!is_array($impList)) $impList = [];

foreach ($impList as $imp) {
    $foundCreative = null;
    $w = null;
    $h = null;
    // Check for banner.format sizes
    $banner = $imp->get('banner');
    if ($banner && is_object($banner) && $banner->get('format')) {
        $formats = $banner->get('format');
        foreach ($formats as $format) {
            $fw = $format->get('w');
            $fh = $format->get('h');
            foreach ($imageCreatives as $img) {
                if ($img['w'] == $fw && $img['h'] == $fh) {
                    $foundCreative = $img;
                    $w = $fw;
                    $h = $fh;
                    break 2; // stop at first match
                }
            }
        }
    }
    // fallback: check imp.w/h or banner.w/h
    if (!$foundCreative) {
        $w = $imp->get('w');
        $h = $imp->get('h');
        if ((!$w || !$h) && $banner) {
            $w = $banner->get('w');
            $h = $banner->get('h');
        }
        foreach ($imageCreatives as $img) {
            if ($img['w'] == $w && $img['h'] == $h) {
                $foundCreative = $img;
                break;
            }
        }
    }
    if (!$foundCreative) continue;
    $img = $foundCreative;

    $bid = new Bid();
    $bid->set('id', $imp->get('id'));
    $bid->set('impid', $imp->get('id'));
    $bid->set('price', mt_rand(500, 2000) / 100); // random float 5-20
    $bid->set('adm', '<a href="https://frag-muki.de" target="_blank"><img src="' . $img['url'] . '" width="' . $img['w'] . '" height="' . $img['h'] . '" alt="Muki Ad"></a>');
    $bid->set('iurl', $img['url']);
    $bid->set('adomain', ['frag-muki.de']);
    $bid->set('w', $img['w']);
    $bid->set('h', $img['h']);
    $bid->set('cat', ['1', '30']);
    $bid->set('mtype', 1);
    $bid->set('cid', 'aabbcc');
    $bid->set('crid', 'bsacebsx');

    $seatBid = new SeatBid();
    $seatBid->set('bid', [$bid]);
    $seatBid->set('seat', '2010');
    $seatBids[] = $seatBid;
}

$bidResponse = new BidResponse();
$bidResponse->set('id', uuidv4());
$bidResponse->set('seatbid', $seatBids);
$bidResponse->set('cur', 'EUR');

header('Content-Type: application/json');
echo $bidResponse->getDataAsJson();
