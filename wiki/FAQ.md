# F.A.Q.

## What is checkpoint?

The suspicious code attempt or as it is known as “checkpoint required” happens when you are trying to log into an account from a  different location than the usual. For example if you are using your account on your device in Spain, and then you try to login from a server in Germany, it will trigger that. It is a security measure Instagram added to avoid people hijacking into accounts of other people. 

This can be solved by: 

- Use `checkpoint` script to verify and solve the checkpoint.
- Using an IP from the same location or bypassing the checkpoint by telling it was you. 
- If the code approach isn’t working for you, when you are logged in from the browser or other device, you can click on “It was me” after it, it should work fine.

## What are events?

Whenever your check someones' profile, search a hastag or do any action in Instagram, that information is being collected and sent to Instagram. This is mostly used for analytics, however they use this as a mechanism to verify legit users and prevent spamming.


### How does it works?

The API has an implementation of these events, and with every action (request) you do to Instagram you will need to send the related event for each action. You will find examples with event implementation in the `/examples` folder. Whenever you send an event, it goes to a queue of events, and when this queue reaches 50 events, it is being sent automatically by the API in a batch. 

What happens if somehow you are terminating the script before reaching that number? You will need to force the API to send the batch with just the ones are already in the queue with the following function:

```php 
// forceSendBatch() should be only used if you are "closing" the app so all the events that
// are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
$ig->event->forceSendBatch();
```

## Can I run this library via a website?

The API it is optimised for its usage in command line interface (CLI), however **you can use the API in a webserver.**

To disable the warning, make sure to set the following flag:

```php
\InstagramAPI\$allowDangerousWebUsageAtMyOwnRisk = true;
```

## Web login is not working for me

In order to be able to login using the web class, you will need to install the `sodium` for working with elliptic curve keys.

**Dependency:** `Libsodium` ([https://github.com/jedisct1/libsodium-php](https://github.com/jedisct1/libsodium-php) 

**Installation:**

```
git clone https://github.com/jedisct1/libsodium-php cd libsodium-php
phpize
./configure
make
sudo make install
```

You will need to add the following line to your `php.ini` file:

`extension=sodium.so`

In order to check if all was done correctly, run the following command: `php -m`. 

Module `sodium` should be listed there.

## HTTP2

If you are getting the following error:

```
CURLE_HTTP2 (16)

A problem was detected in the HTTP2 framing layer. This is somewhat generic and can be one out of several problems, see the error buffer for details. 
```

Means you are having troubles with HTTP2, you can disable HTTP2 at any time by setting the following global flag:

```php
\InstagramAPI\Instagram::$disableHttp2 = true;
```

# Known issues

## Privacy checkpoint

If you are receiving the following error:

```
"message": "checkpoint_required",
    "checkpoint_url": "https://i.instagram.com/privacy/checks/?cookie_consent=1&next=instagram://checkpoint/dismiss",
    "lock": false,
    "flow_render_type": 0,
    "status": "fail"
```

It means you are using an outdated version of the API. The new version of the API does NOT use cookies, instead authorization headers are being used. If you encounter this issue you will need to update the API with composer: `composer update`