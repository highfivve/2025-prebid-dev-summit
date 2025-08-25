# Running muki-bidder Locally with Docker

To run the `muki-bidder` PHP code locally in a Docker container and have code changes reflected immediately (no rebuild required):

Build the Docker image

```bash
docker build -t muki-bidder .
```

Run the container with your code mounted

```bash
docker run --rm -p 8080:80 -v "$PWD/muki-bidder":/var/www/html muki-bidder
```

- This mounts your local `muki-bidder` directory into the container, so any changes to PHP files are instantly available.
- The service will be available at: [http://localhost:8080/auction.php](http://localhost:8080/auction.php)
  - If you get a connection error with `localhost`, try [http://127.0.0.1:8080/auction.php](http://127.0.0.1:8080/auction.php) instead. This can happen on some systems (e.g., macOS) due to IPv6/IPv4 differences.

# How to setup prebid.js

Directly from the prebid.js [Getting Started](https://docs.prebid.org/getting-started.html):

```bash
git clone https://github.com/prebid/Prebid.js.git
cd Prebid.js
npm ci
```

Then open with IntelliJ, VSCode, Cursor, Kiro or whatever IDE you prefer that supports the [devcontainer.jsnon](https://containers.dev/implementors/json_schema/) file.

## Build our bid adapter

See [mukiBidAdapter](mukiBidApater.ts) for the code.

## Build our own prebid distribution

```bash
gulp build --modules=mukiBidAdapter,currency,gptPreAuction,priceFloors
gulp bundle --tag devsummit --modules=mukiBidAdapter,currency,gptPreAuction,priceFloors
```

## Use Chrome Dev Tools network override to hijack prebid.js

See https://developer.chrome.com/blog/devtools-tips-34

## Replace the prebid.js distribution in the config

The custom distribution can be found here: https://frag-muki.de/ortb2/prebid.devsummit.js

# Credits

- inlined OpenRTB models from https://github.com/beishanwen/php-openrtb
