# Device fingerprinting

Instagram tracks the device string you are using with the API. When using the API it is recommended to use the same device over the time. There are built-in function to set a specific device string:

```php
$ig->setDeviceString($deviceString);
```

In the API device string is being assigned randomly using the most popular devices. These devices are listed in `/Devices/GoodDevices.php`. We recommend seting custom device string based in the most popular devices in your country. If you are using an IP from a country to manage a user of that country, using a popular device from that country will increase the account score in terms of Instagram Account Classifier.

### Where to get device strings of devices?

You can use this website: [https://www.handsetdetection.com/device-detection-database/devices/](https://www.handsetdetection.com/device-detection-database/devices/)

### Are the device strings from the API safe?

Yes they are, however we recommend the user to use their own ones based on their country. The devices added in `GoodDevices` are the most popular and maybe it matches with your requirements. If that is the case, you can take the one you want and set it like this:

```php
$ig->setDeviceString('28/9.0; 560dpi; 1440x2960; samsung; SM-G960F; starlte; samsungexynos9810');
``