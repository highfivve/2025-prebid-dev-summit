import { ortbConverter } from '../libraries/ortbConverter/converter.js';
import { BidderSpec, registerBidder } from '../src/adapters/bidderFactory.js';
import { BANNER } from '../src/mediaTypes.js';

const BIDDER_CODE = 'muki';
const ENDPOINT_URL = 'https://frag-muki.de/ortb2/auction.php';
const GVLID = 91;

// ORTB standard for the win!
const converter = ortbConverter({
  context: { netRevenue: true, ttl: 20, },
  imp(buildImp, bidRequest, context) {
    return buildImp(bidRequest, context);
  },
  request(buildRequest, imps, bidderRequest, context) {
    return buildRequest(imps, bidderRequest, context);
  },
  bidResponse(buildBidResponse, bid, context) {
    return buildBidResponse(bid, context);
  }
});

export const spec: BidderSpec<typeof BIDDER_CODE> = {
  code: BIDDER_CODE,
  gvlid: GVLID,
  supportedMediaTypes: [BANNER],
  aliases: [
    // This is the dirty hack to hijack another bidder in the setup of a publisher. Don't do this at home!
    { code: 'criteo' }
  ],

  isBidRequestValid: (_bid): boolean => {
    return true;
  },

  buildRequests: (validBidRequests, bidderRequest) => {
    if (validBidRequests && validBidRequests.length === 0) return [];
    const request = converter.toORTB({ bidRequests: validBidRequests, bidderRequest });
    return { method: 'POST', url: ENDPOINT_URL, data: request };
  },

  interpretResponse: (response, request) => {
    if (response?.body) {
      return converter.fromORTB({ response: response.body, request: request.data });
    }
    return [];
  },
};

registerBidder(spec);
