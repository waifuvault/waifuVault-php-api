# waifuvault-php-api

![tests](https://github.com/waifuvault/waifuVault-php-api/actions/workflows/tests.yml/badge.svg)

This contains the official API bindings for uploading, deleting and obtaining files
with [waifuvault.moe](https://waifuvault.moe/). Contains a full up-to-date API for interacting with the service via PHP

## Installation via Composer

```sh
composer require ernestmarcinko/waifuvault-php-api
```

## Usage

This API contains 4 interactions:

1. [Upload File](#upload-file)
2. [Get File Info](#get-file-info)
3. [Delete File](#delete-file)
4. [Get File](#get-file)
5. [Modify Entry](#modify-entry)

The only class needed is the `WaifuApi` class from `ErnestMarcinko\WaifuVault`:

```php
use ErnestMarcinko\WaifuVault\WaifuApi;
```

### Upload File<a id="upload-file"></a>

To Upload a file, use the `WaifuApi::uploadFile` function.

#### Parameters

The function accepts an **associative array** of arguments.

| Param (key)     | Type      | Description                                                                        | Required                                               | Extra info                                             |
|-----------------|-----------|------------------------------------------------------------------------------------|--------------------------------------------------------|--------------------------------------------------------|
| `file`          | `string`  | The file path to upload                                                            | true only if `url` or `file_contents` is not supplied  | If `url` is supplied, this prop can't be set           |
| `url`           | `string`  | The URL to a file that exists on the internet                                      | true only if `file` or `file_contents` is not supplied | If `url` is supplied, this prop has no effect          |
| `file_contents` | `string`  | The file contents                                                                  | true only if `url` or `file` is not supplied           | If `url` or `file` is supplied, this prop can't be set |
| `expires`       | `string`  | A string containing a number and a unit (1d = 1day)                                | false                                                  | Valid units are `m`, `h` and `d`                       |
| `hideFilename`  | `boolean` | If true, then the uploaded filename won't appear in the URL                        | false                                                  | Defaults to `false`                                    |
| `password`      | `string`  | If set, then the uploaded file will be encrypted                                   | false                                                  |                                                        |
| `filename`      | `string`  | Only used if `file_contents` or `file` is set, will set the filename of the upload | false                                                  |                                                        |

#### WaifuResponse Return value<a id="waifuresponse-object"></a>

The function returns a `WaifuResponse` object, throws and `Exception` or `WaifuException`. Almost all SDK functions will use this object as their return values.
```php
// WaifuResponse object:

ErnestMarcinko\WaifuVault\WaifuResponse (4) {
  ["token"]=> string(36) "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx"
  ["url"]=> string(74) "https://waifuvault.moe/f/{timestamp}/{filename}.{file_ext}"
  ["protected"]=> bool(true)
  ["retentionPeriod"]=> string(39) "334 days 20 hours 16 minutes 23 seconds"
}
```

#### Examples

Using a URL:

```php
use ErnestMarcinko\WaifuVault\WaifuApi;

$waifu = new WaifuApi();
$response = $waifu->uploadFile(array(
	'url' =>   'https://waifuvault.moe/assets/custom/images/08.png',
));
var_dump($response);
```

Using a file path:

```php
use ErnestMarcinko\WaifuVault\WaifuApi;

$waifu = new WaifuApi();
$response = $waifu->uploadFile(array(
	'file' =>   __DIR__ . '/image.jpg',
));
var_dump($response);
```

Using a file contents:

```php
use ErnestMarcinko\WaifuVault\WaifuApi;

$waifu = new WaifuApi();
$response = $waifu->uploadFile(array(
	'file_contents' => file_get_contents(__DIR__ . '/image.jpg'),
	'filename' => 'image.jpg',
));
var_dump($response);
```

### Get File Info<a id="get-file-info"></a>

The `WaifuApi::getFileInfo` function is used to get the file information.

#### Parameters

| Param       | Type      | Description                                                        | Required | Extra info        |
|-------------|-----------|--------------------------------------------------------------------|----------|-------------------|
| `token`     | `string`  | The token of the upload                                            | true     |                   |
| `formatted` | `boolean` | If you want the `retentionPeriod` to be human-readable or an epoch | false    | defaults to false |

#### Return value
`WaifuApi::getFileInfo` returns a [WaifuResponse](#waifuresponse-object)

#### Examples
```php
use ErnestMarcinko\WaifuVault\WaifuApi;

$waifu = new WaifuApi();
$response = $waifu->getFileInfo('someToken');
var_dump($response);
```

Human-readable timestamp retention period:

```php
use ErnestMarcinko\WaifuVault\WaifuApi;

$waifu = new WaifuApi();
$response = $waifu->getFileInfo('someToken');
var_dump($response->retentionPeriod); // 328 days 18 hours 51 minutes 31 seconds
```

### Delete File<a id="delete-file"></a>

The `WaifuApi::deleteEntry` function is used to get the file information.

#### Parameters

| Param  | Type     | Description                              | Required | Extra info |
|---------|----------|------------------------------------------|----------|------------|
| `token` | `string` | The token of the file you wish to delete | true     |            |

#### Return values

> **NOTE:** `deleteFile` will only ever either return `true` or throw an exception if the token is invalid

#### Examples

```php
use ErnestMarcinko\WaifuVault\WaifuApi;

$waifu = new WaifuApi();
$response = $waifu->deleteEntry('someToken');
```

### Get File<a id="get-file"></a>

The `WaifuApi::getFile` function is used to get the file contents from the server by supplying either the token or the unique identifier
of the file (epoch/filename).

#### Parameters

The function accepts an **associative array** of arguments.

| Param (key) | Type     | Description                                                                                      | Required                           | Extra info                                               |
|-------------|----------|--------------------------------------------------------------------------------------------------|------------------------------------|----------------------------------------------------------|
| `token`     | `string` | The token of the file you want to download                                                       | true only if `filename` is not set | if `filename` is set, then this can not be used          |
| `filename`  | `string` | The Unique identifier of the file, this is the epoch time stamp it was uploaded and the filename | true only if `token` is not set    | if `token` is set, then this can not be used             |
| `password`  | `string` | The password for the file if it is protected                                                     | false                              | Must be supplied if the file is uploaded with `password` |

> **Important!** The Unique identifier filename is the epoch/filename only if the file uploaded did not have a hidden
> filename, if it did, then it's just the epoch.
> For example: `1710111505084/08.png` is the Unique identifier for a standard upload of a file called `08.png`, if this
> was uploaded with hidden filename, then it would be `1710111505084.png`

#### Return value

The function returns the file contents.

#### Examples

Obtain an encrypted file

```php
use ErnestMarcinko\WaifuVault\WaifuApi;

$response = $waifu->uploadFile(array(
	'url' => 'https://waifuvault.moe/assets/custom/images/08.png',
	'password' => 'epic'
));
$contents = $waifu->getFile(array(
	'token' => $response->token,
	'password' => 'epic'
));
var_dump($contents);
```

Obtain a file from Unique identifier

```php
use ErnestMarcinko\WaifuVault\WaifuApi;

$contents = $waifu->getFile(array(
	'filename' => '/1710111505084/08.png'
));
var_dump($contents);
```

### Modify Entry<a id="modify-entry"></a>

The `WaifuApi::modifyEntry` is used to modify aspects of your entry such as password, removing password, decrypting the file, encrypting the
file, changing the expiry, etc.

#### Parameters

The function accepts an **associative array** of arguments.

| Option             | Type      | Description                                                                                              | Required                                                           | Extra info                                                                             |
|--------------------|-----------|----------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------|----------------------------------------------------------------------------------------|
| `token`            | `string`  | The token of the file you want to modify                                                                 | true                                                               |                                                                                        |
| `password`         | `string`  | The new password or the password you want to use to encrypt the file                                     | false                                                              |                                                                                        |
| `previousPassword` | `string`  | If the file is currently protected or encrpyted and you want to change it, use this for the old password | true only if `password` is set and the file is currently protected | if the file is protected already and you want to change the password, this MUST be set |
| `customExpiry`     | `string`  | a new custom expiry, see `expires` in `uploadFile`                                                       | false                                                              |                                                                                        |
| `hideFilename`     | `boolean` | make the filename hidden                                                                                 | false                                                              | for the new URL, check the response URL prop                                           |

#### Return value
`WaifuApi::getFileInfo` returns a [WaifuResponse](#waifuresponse-object)

#### Examples

Set a password on a non-encrypted file:

```php
use ErnestMarcinko\WaifuVault\WaifuApi;

$response = $waifu->modifyEntry(array(
	'token' => 'token',
	'password' => 'apple'
));
var_dump($response->protected);
```
Change a password:

```php
use ErnestMarcinko\WaifuVault\WaifuApi;

$response = $waifu->modifyEntry(array(
	'token' => 'token',
	'password' => 'newPass'
	'previousPassword' => 'apple'
));
var_dump($response->protected);
```

change expire:

```php
use ErnestMarcinko\WaifuVault\WaifuApi;

$response = $waifu->modifyEntry(array(
	'token' => 'token',
	'customExpiry' => "1d"
));
var_dump($response->retentionPeriod);
```

decrypt a file and remove the password:

```php
use ErnestMarcinko\WaifuVault\WaifuApi;

$response = $waifu->modifyEntry(array(
	'token' => 'token',
	'password' => ''
	'previousPassword' => 'apple'
));
var_dump($response->protected);
```

## WaifuVault SDKs for other languages
- [Node.js SDK](https://www.npmjs.com/package/waifuvault-node-api)
- [Python SDK](https://pypi.org/project/waifuvault/)
- [Go SDK](https://pkg.go.dev/github.com/waifuvault/waifuVault-go-api)
- [C# SDK](https://www.nuget.org/packages/Waifuvault)