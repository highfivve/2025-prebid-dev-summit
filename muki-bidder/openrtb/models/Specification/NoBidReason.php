<?php
/**
 * NoBidReason.php
 *
 * @copyright App\Message
 * @author Manuel Kanah <manuel@App\Message.com>
 * Date: 10/09/15 - 17:11
 */

namespace openrtb\models\Specification;

class NoBidReason
{
    const UNKNOWN_ERROR = 0;
    const TECHNICAL_ERROR = 1;
    const INVALID_REQUEST = 2;
    const KNOWN_WEB_SPIDER = 3;
    const SUSPECTED_NON_HUMAN_TRAFFIC = 4;
    const CLOUD_DATA_CENTER_PROXY_IP = 5;
    const UNSUPPORTED_DEVICE = 6;
    const BLOCKED_PUBLISHER_SITE = 7;
    const UNMATCHED_USER = 8;
}