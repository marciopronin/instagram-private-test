# Costumozation for iOS users

## Custom iPhone model

By default the iPhone model used is the iPhone 7 (GSM) which is `iPhone9,3`. You can change this model with:

```php
$ig->setIosModel($model);
```

The following list constains the current available iPhone models:

```
            'iPhone9,1', // iPhone 7 (Global)
            'iPhone9,2', // iPhone 7 Plus (Global)
            'iPhone9,3', // iPhone 7 (GSM)
            'iphone9,4', // iPhone 7 Plus (GSM)
            'iPhone10,1', // iPhone 8 (Global)
            'iPhone10,2', // iPhone 8 Plus (Global)
            'iPhone10,4', // iPhone 8 (GSM)
            'iPhone10,5', // iPhone 8 Plus (GSM)
            'iPhone10,3', // iPhone X (Global)
            'iPhone11,6', // iPhone XS Max
            'iPhone11,4', // iPhone XS Max (China)
            'iPhone11,8', // iPhone XR
            'iPhone12,1', // iPhone 11
            'iPhone12,3', // iPhone 11 Pro
            'iPhone12,5', // iPhone 11 Pro Max
            'iPhone12,8', // iPhone SE (2020)
```

**IMPORTANT:** If you change the iOS Model, YOU MUST change the iOS DPI as well!