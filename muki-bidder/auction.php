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

// Example creatives with size and file
$imageCreatives = [
    [ 'w' => 300, 'h' => 250, 'file' => 'creative_300x250.html' ],
    [ 'w' => 300, 'h' => 250, 'file' => 'creative_300x250_bike.html' ],
    [ 'w' => 300, 'h' => 250, 'file' => 'creative_300x250_knight.html' ],
    [ 'w' => 300, 'h' => 250, 'file' => 'creative_300x250_thinking.html' ],
    [ 'w' => 300, 'h' => 600, 'file' => 'creative_300x600.html' ],
    [ 'w' => 300, 'h' => 600, 'file' => 'creative_300x600_prebid.html' ],
    [ 'w' => 300, 'h' => 600, 'file' => 'creative_300x600_prebid2.html' ],
    [ 'w' => 160, 'h' => 600, 'file' => 'creative_160x600.html' ],
    [ 'w' => 160, 'h' => 600, 'file' => 'creative_160x600_prebid.html' ],
    [ 'w' => 160, 'h' => 600, 'file' => 'creative_160x600_prebid2.html' ],
    # TODO those look pretty shitty
  #  [ 'w' => 120, 'h' => 600, 'file' => 'creative_120x600.html' ],
  #  [ 'w' => 120, 'h' => 600, 'file' => 'creative_120x600_prebid.html' ],
  #  [ 'w' => 120, 'h' => 600, 'file' => 'creative_120x600_prebid2.html' ],
    [ 'w' => 320, 'h' => 100, 'file' => 'creative_320x100.html' ],
    [ 'w' => 320, 'h' => 50,  'file' => 'creative_320x50.html'  ],
    [ 'w' => 728, 'h' => 90,  'file' => 'creative_728x90.html'  ],
    [ 'w' => 800, 'h' => 250, 'file' => 'creative_800x250.html' ],
    [ 'w' => 900, 'h' => 250, 'file' => 'creative_900x250.html' ],
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
    $matchingCreatives = [];
    $w = null;
    $h = null;
    $banner = $imp->get('banner');
    if ($banner && is_object($banner) && $banner->get('format')) {
        $formats = $banner->get('format');
        foreach ($formats as $format) {
            $fw = $format->get('w');
            $fh = $format->get('h');
            foreach ($imageCreatives as $img) {
                if ($img['w'] == $fw && $img['h'] == $fh) {
                    $matchingCreatives[] = $img;
                }
            }
        }
    }
    // fallback: check imp.w/h or banner.w/h
    if (empty($matchingCreatives)) {
        $w = $imp->get('w');
        $h = $imp->get('h');
        if (!$w || !$h) {
            if ($banner && is_object($banner)) {
                $w = $banner->get('w');
                $h = $banner->get('h');
            }
        }
        foreach ($imageCreatives as $img) {
            if ($img['w'] == $w && $img['h'] == $h) {
                $matchingCreatives[] = $img;
            }
        }
    }
    if (empty($matchingCreatives)) continue;
    // Find the largest area
    $maxArea = 0;
    foreach ($matchingCreatives as $img) {
        $area = $img['w'] * $img['h'];
        if ($area > $maxArea) $maxArea = $area;
    }
    // Filter to only creatives with the largest area
    $largestCreatives = array_filter($matchingCreatives, function($img) use ($maxArea) {
        return $img['w'] * $img['h'] === $maxArea;
    });
    // Shuffle and pick one
    $largestCreatives = array_values($largestCreatives);
    shuffle($largestCreatives);
    $img = $largestCreatives[0];
    // Load creative HTML from file
    $creativePath = __DIR__ . '/creatives/' . $img['file'];
    $adm = file_exists($creativePath) ? file_get_contents($creativePath) : '<!-- Creative not found -->';
    $bid = new Bid();
    $bid->set('id', $imp->get('id'));
    $bid->set('impid', $imp->get('id'));
    $bid->set('price', mt_rand(500, 2000) / 100); // random float 5-20
    $bid->set('adm', $adm);
    $bid->set('adomain', ['frag-muki.de']);
    $bid->set('w', $img['w']);
    $bid->set('h', $img['h']);
    $bid->set('cat', ['1', '30']);
    $bid->set('mtype', 1);
    $bid->set('cid', 'aabbcc');
    $bid->set('crid', md5($adm));

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
