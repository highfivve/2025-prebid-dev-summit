import { AdapterRequest, BidderSpec, registerBidder } from '../src/adapters/bidderFactory.ts';
import { BANNER } from '../src/mediaTypes.ts';
import { ortbConverter } from '../libraries/ortbConverter/converter.ts';

const BIDDER_CODE = 'muki';
const ENDPOINT = 'https://frag-muki.de/ortb2/auction.php';
const GVLID = 91;

type MukiBidParams = {
  test?: boolean
}

declare module '../src/adUnits' {
  interface BidderParams {
    [BIDDER_CODE]: MukiBidParams;
  }
}

const converter = ortbConverter<typeof BIDDER_CODE>({
  context: { netRevenue: true, ttl: 300, currency: 'EUR', mediaType: 'banner' },
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
    { code: 'criteo', gvlid: GVLID }
  ],
  isBidRequestValid: (_bid): boolean => {
    return true;
  },
  buildRequests(validBidRequests, bidderRequest): AdapterRequest | AdapterRequest[] {
    if (validBidRequests && validBidRequests.length === 0) return [];
    const request = converter.toORTB({ bidRequests: validBidRequests, bidderRequest });
    return { method: 'POST', url: ENDPOINT, data: request };
  },
  interpretResponse(response: { body: any; }, request) {
    if (response.body) {
      return converter.fromORTB({ response: response.body, request: request.data });
    }
    return [];
  },
}

registerBidder(spec);
