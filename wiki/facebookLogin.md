# Facebook Login

An access token is an opaque string that identifies a user, app, or Page and can be used by the app to login. When someone connects with an app using Facebook Login and approves the request for permissions, the app obtains an access token that provides temporary, secure access to Facebook APIs. 

## Getting access token

Although each platform generates access tokens through different APIs, all platforms follow the basic strategy to get a user token:

<center><img src="https://mgp25.com/instaprivatedocs/fb-oauth-graph.png" width=250></center>

| Access Token Type | Description                                                                                                                                                                                                                                                            |
|-------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| User Access Token | This kind of access token is needed any  time the app calls an API to read, modify or write a specific person's  Facebook data on their behalf.  User access tokens are generally  obtained via a login dialog and require a person to permit your app to  obtain one. |

## Procedure

The user must have an active session of facebook in the browser (Firefox, Chrome, Safari...). Then the user should be directed to the following URL:

```
https://m.facebook.com/v2.3/dialog/oauth?client_id=124024574287414&e2e={"init":1558262654061}&scope=email&default_audience=friends&redirect_uri=fbconnect://success&display=touch&response_type=token,signed_request&return_scopes=true
```

Once the process has finished, you should land in a blank page (`https://m.facebook.com/
dialog/oauth/skip/submit/`). If you get this page’s content, you should see something like this:

```
<script type="text/javascript">window.location.href="fbconnect:\/\/success#granted_scopes=email\u00252Cpublic_profile&denied_scopes=&signed_request=qv9sVOembdF1jk4p0kNsYoDCL7n-Hp2PV3Yg9kVuOu8.eyJ1c2VyX2lkIjoiMTAwMDM2OTIxOTQwNjc5IiwiY29kZSI6IkFRQVJxbnBYVmJsMEFWaU1NbmROOWszVDZQZjVSV1NlX2FOaE05dHRaTmc2QTJMS3piNUtHblpqaU5YUG0yYnI1YlowLS1SZndSQUVWSkx4Qllza0lNOFZORjA5TWg2MzlkZE9USmdfRFAtR2xpc09CeDZXNVNwR1pBcDRwdzZyV1gtVW5NY0Z5RXpwTkJOdERXTTdjRlVKRGFVYWh5aUhkSzNpNDJuQmctOEFIMzRGcnZZUGEtb005SmdSMnU5aldRVkNrYXBmOG81S1JnTDFZdDlIT1VwY3VHTExTMW9fUERLSzlXREl3cGpuTHc1SjNTUW1ZQlhzWURzelRyUVVMVEM5NTN4NElZRDFYMXVlN21iUW1oT1RnMGdWcmVMR290SnMwc0RUTjJBdU1UdnZBZ3ZYRGxRenMxUGhGWTAxRU1nRWpJb3VhOGJnQmxldDdqM28yYm4tIiwiYWxnb3JpdGhtIjoiSE1BQy1TSEEyNTYiLCJpc3N1ZWRfYXQiOjE1NTgyMDQyOTh9&access_token=EAABwzLixnjYBAEMkldERnQjsXJMW7xDqfxvF5ZCNQeAVKpxB7ZAcbEYSYQkSLL32p2ZCR2yAhqBEcG62MgorhphO4O8sfGQauGRKBJUVswWQrsZBmEF27T2QP6GkZBoiAOpPqNzWxHSKu3MID7IWbwgCZBEgbQV0XNUEbbwmkUguJAlB3F3OAo&data_access_expiration_time=0&expires_in=5158215";</script>
```

As you can see above, there is an access_token parameter, which is:

`Access token`: `EAABwzLixnjYBAEMkldERnQjsXJMW7xDqfxvF5ZCNQeAVKpxB7ZAcbEYSYQkSLL32p2ZCR2yAhqBEcG62MgorhphO4O8sfGQauGRKBJUVswWQrsZBmEF27T2QP6GkZBoiAOpPqNzWxHSKu3MID7IWbwgCZBEgbQV0XNUEbbwmkUguJAlB3F3OAo`

This value is the one used for Facebook Login:

```php
$ig->loginWithFacebook($username, $fbAccessToken);
```

## Limitations

There are different security checks that has been implemented by Facebook to prevent this information been hijacked:

<center><img src="https://mgp25.com/instaprivatedocs/fb-oauth-sec.png" width=700></center>

- `X-Frame-Options`: The `X-Frame-Options` HTTP response header can be used to indicate whether or not a browser should be allowed to render a page in a `<frame>`, `<iframe>`, `<embed>` or `<object>`. Sites can use this to avoid click-jacking attacks, by ensuring that their content is not embedded into other sites. When `X-Frame-Options` is set to `DENY`, the page cannot be displayed in a frame, regardless of the site attempting to do so.

- `content-security-policy`: Content Security Policy (CSP) is an added layer of security that helps to detect and mitigate certain types of attacks, including Cross Site Scripting (XSS) and data injection attacks. These attacks are used for everything from data theft to site defacement to distribution of malware.

Knowing what was mentioned, the only way to acquire the `access_token` is either by manually doing it or by installing some sort of addon in the browser that is capable of reading the content of the response.