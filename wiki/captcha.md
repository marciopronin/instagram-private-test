# Checkpoint

## What is checkpoint?

The suspicious code attempt or as it is known as “checkpoint required” happens when you are trying to log into an account from a  different location than the usual. For example if you are using your account on your device in Spain, and then you try to login from a server in Germany, it will trigger that. It is a security measure Instagram added to avoid people hijacking into accounts of other people. 

This can be solved by: 

- Use `checkpoint` script to verify and solve the checkpoint.
- Using an IP from the same location or bypassing the checkpoint by telling it was you. 
- If the code approach isn’t working for you, when you are logged in from the browser or other device, you can click on “It was me” after it, it should work fine.

## How to solve checkpoint with captchas?

You would need to edit the `Checkpoint.php` example:

1) Include captcha classes:

```php
include('anticaptcha.php');
include('nocaptcha.php');
include('nocaptchaproxyless.php');
```

2) Update `Checkpoint.php` example in the `RecaptchaChallengeException` case:

```php
case $e instanceof InstagramAPI\Exception\Checkpoint\RecaptchaChallengeException:
    $api = new NoCaptcha();
    //your anti-captcha.com account key
    $api->setKey('');
    //target website address. DO NOT CHANGE!
    $api->setWebsiteURL('https://www.fbsbx.com/captcha/recaptcha/iframe/?compact=0&referer=https://www.instagram.com');
    //recaptcha key from target website. DO NOT CHANGE!
    $api->setWebsiteKey('6Lc9qjcUAAAAADTnJq5kJMjN9aD1lxpRLMnCS2TR');      
    
    //proxy access parameters
    $api->setProxyType('http');
    $api->setProxyAddress('127.0.0.1'); // proxy ip
    $api->setProxyPort('8080'); // proxy port       

    $api->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116');

    //create task in API
    if (!$api->createTask()) {
        $api->debout('API v2 send failed - '.$api->getErrorMessage(), 'red');
        break 2;
    }

    $taskId = $api->getTaskId();

    //wait in a loop for max 300 seconds till task is solved
    if (!$api->waitForResult(300)) {
        echo "could not solve captcha\n";
        echo $api->getErrorMessage()."\n";
        break 2;
    } else {
        $googleResponse = $api->getTaskSolution();
        echo "\nyour recaptcha token: $gResponse\n\n";
        $ig->checkpoint->sendCaptchaResponse($e->getResponse()->getChallenge()->getUrl(), $googleResponse);
    }                        
    break;
```