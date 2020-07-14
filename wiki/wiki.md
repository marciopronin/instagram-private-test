# Instagram Private API Wiki

This Wiki will explain and address most of the common issues and questions with the API. For code documentation, please go to the `documentation` folder and open the `index.html`.

## Installing the API

Once you have run the command with your private composer access token:

`composer config --global --auth http-basic.instagram-private.repo.packagist.com token <YOUR_TOKEN>`

You can add the API to your project in the `composer.json`. Example:

```json
{
    "name": "foo/my-project",
    "repositories": [
        {"type": "composer", "url": "https://instagram-private.repo.packagist.com/<YOUR_USER>/"}
    ],
    "require": {
        "instagram-private/instagram": "*"
    }
}
```

## API updates

API updates are delivered via composer. You can update the API with the following command:

`composer update`

All API changes are described in the `CHANGELOG.md` file, if there is any backwards breaks or anything that you should be aware of before putting your code in production will be found in it.

## Getting started

### Constructor

The API constrcutor has the following arguments:

- `$debug`: It enables debud mode via command line interface (CLI).
- `$truncatedDebug`: It truncates debug log. Not recommended if you want to check debug logs.
- `array $storageConfig`: It tells the API what storage configuratio you are going to use. By default the API uses file storage, and it will save all data and sessions into a folder named `sessions`, this folder will be generated inside the vendor folder. **WARNING:** If you are going to use file storage, change the base folder path to avoid sessions getting deleted when updating the API.
- `$platform`: Selects wether to emulate `android` or `ios` platform.
- `$logger`: This can be used to specify custom loggers like MonoLog.

```php
$debug = true;
$ig = new \InstagramAPI\Instagram($debug);
```

If you want to use a MySQL storage:

```php
$ig = new \InstagramAPI\Instagram(true, true, [
	'storage'    => 'mysql',
	'dbhost'     => 'localhost',
    'dbname'     => 'mydatabase',
    'dbport'     => '3306',
	'dbusername' => 'root',
	'dbpassword' => '',
]);
```

**Note:** If you are using MySQL or other supported database, the API will automatically load and create everything.

You can find more examples of storage configurations in `/examples/General/customSettings.php`.

## Setting a proxy

**Note:** Using proxies in the API works fine, so if you don't get any response it's because Instagram's server is refusing to connect with the proxy (or because the proxy doesn't work).

```
// HTTP proxy needing authentication.
$ig->setProxy('http://user:pass@iporhost:port');

// HTTP proxy without authentication.
$ig->setProxy('http://iporhost:port');

// Encrypted HTTPS proxy needing authentication.
$ig->setProxy('https://user:pass@iporhost:port');

// Encrypted HTTPS proxy without authentication.
$ig->setProxy('https://iporhost:port');

// SOCKS5 Proxy needing authentication:
$ig->setProxy('socks5://user:pass@iporhost:port');

// SOCKS5 Proxy without authentication:
$ig->setProxy('socks5://iporhost:port');
```

The full list of proxy protocols is available [in the cURL documentation](https://curl.haxx.se/libcurl/c/CURLOPT_PROXY.html).

## Login

Once you have initialized the InstagramAPI class, you must login to an account.

```php
$ig->login($username, $password); // Will resume if a previous session exists.
```

**Note:** We always need to call `login()` function. It prepared the API, loads configuration and emulate user behaviour. If you have any existing session, it will reuse the session and won't be required to make an authentication. This is automatically managed by the API.

Your sessions will be stored a `sessions/<username>/` folder by default, if you're using the file-based settings backend. If you set use other custom settings path, a username folder will be created under it. By default, the hierarchy is as follows:

```
Instagram-API
|-- src
|-- sessions 
|    |-- username
|    |    |-- username-cookies.dat
|    |    |-- username-settings.dat
```

- `username-cookies.dat` contains the cookies for your session.
- `username-settings.dat` contains important information about your account.

Both files are generated automatically by the API.

If you want to manage more accounts at once, you can switch accounts by calling `login()` with the new account:

```php
$ig->login($username, $password);
```

## Two Factor Login

If you have an account with two factor authentication enabled, you need to login this way:

```php
try {
    $loginResponse = $ig->login($username, $password);

    if ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) {
        $twoFactorIdentifier = $loginResponse->getTwoFactorInfo()->getTwoFactorIdentifier();

        // The "STDIN" lets you paste the code via terminal for testing.
        // You should replace this line with the logic you want.
        // The verification code will be sent by Instagram via SMS.
        $verificationCode = trim(fgets(STDIN));
        $ig->finishTwoFactorLogin($username, $password, $twoFactorIdentifier, $verificationCode);
    }
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
```

Full example can be found in: `/examples/Login/twoFactorLogin.php`.

## Username ID

As you can see while using the API, most of the functions require a param called `$userId`. This is a string that represents Instagram's unique, internal ID for that username. For example:

`MyUsername` ---> `1959226924`

If you don't know how to obtain this ID, don't worry about it, you can use `getUserIdForName()`.

```php
$userId = $ig->people->getUserIdForName('MyUsername');
```

`$userId` now contains `1959226924`.

## Pagination

Everytime we scroll down in our devices to load more data (followers, photos, conversations...), that's called pagination.

When you get Instagram's response, it may contain a next_max_id key, which means there is more data you can load. In order to paginate, you will have to pass that param to the function. Here is an example:

```php
$maxId = null;
do {
    $response = $ig->getSelfUserFollowers($maxId);
    $followers = array_merge($followers, $response->getUsers());
    $maxId = $response->getNextMaxId();
} while ($maxId !== null);
```

Full example can be found in: `/examples/General/PaginationExample.php`.

## Managing responses

When you do a request, you can obtain all the information very easily since all responses are objects, for example:

```php
$response = $ig->people->getInfoById($userId);
echo $response->getUser()->getUsername(); // this will print username of user with id $userId 
```

You can find all responses and their object-functions `/src/Response`. Note that no objects have any defined functions. The functions are auto-created based on the object properties. For example a property `username` can be read via `getUsername()`, and a property `carousel_media` can be read via `getCarouselMedia()`. The list of auto-defined functions is `getX`, `setX`, and `isX`, where `X` is the name of an object property.

## Response Objects

Many people assume we are the ones building the objects/their values. We are not! It all comes from the Instagram server. `NULL` in a field means the Instagram server did not send any value for that field.

Most objects we have are re-used for lots of different responses, and most server responses do not fill every field. So just because people see something like for example a `$view_count` field on an object does not mean it will be filled with anything. Most server responses only fill 30% of all available object fields!

We do not control what the server will send you! Missing `NULL` fields is totally normal!

## Functions

All requests have now been organized into collections of related functions, which makes them much easier to find. Instead of painfully searching through hundreds of functions in the "huge main class" to find what you're looking for, you now simply have to look for the specific collection that seems to be the most likely one for your needs. And when we grouped all of these functions, we also took the opportunity to rename many of them to much cleaner, easier and more logical names! For example, if you now want to find hashtags, you would simply look in the Hashtag collection (`src/Request/Hashtag.php`), and there you would find the `search()` function, and your final function call would be `$ig->hashtag->search()`, which is very easy and clear to read. You can find all of the function groups in `src/Request`. Please go to the `documentation` folder and open the `index.html` for code documentation.

## Examples

We have provided a big set of examples that implements most (if not all!) the user behaviour and the Facebook logging events (Graph API). All examples can be found in: `/examples`.