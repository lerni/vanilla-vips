## Overview

Silverstripe instance with vips and a fork of focuspoint to provide an example how it fails / succeeds.

## Installation

```bash
mkdir vanilla-vips
cd vanilla-vips
git clone https://github.com/lerni/vanilla-vips .
ddev start
ddev composer install

```
- Login to https://vanilla-vips.ddev.site/admin/pages with admin & password
- Create a FocusPointImagePage, upload an image & publish - all works fine

- Remove forked repositories (below) from composer.json and `ddev composer update`, flush, create a FocusPointImagePage again with a new image.

```json
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/lerni/silverstripe-focuspoint"
        }
    ],
```
You then see an error like:
```
[Emergency] Uncaught Jcupitt\Vips\Exception: libvips error: /var/www/html/silverstripe-cache/SOMEUSER/interventionimage_atzgPx.jpg: unable to open for read unix error: No such file or directory
```
The reason is that the temporary file is unlinked after the first operation but focuspoint chains multiple operations like `$backend->resizeByWidth($width)->crop($cropOffset, 0, $width, $height);`. See https://github.com/silverstripe/silverstripe-assets/pull/539#issuecomment-1686898158

One way to work around is to clone $backend and call crop on the clone like in DBFocusPoint.php:
```php
    public function applyCrop(Image_Backend $backend, array $cropData): ?Image_Backend
    {
        $width = $cropData['x']['TargetLength'];
        $height = $cropData['y']['TargetLength'];
        $cropAxis = $cropData['CropAxis'];
        $cropOffset = $cropData['CropOffset'];

        // Resize based on axis
        switch ($cropAxis) {
            case 'x':
                //Generate image
                $backend = $backend->resizeByHeight($height);
                $backendCopy = clone $backend;
                return $backendCopy->crop(0, $cropOffset, $width, $height);
            case 'y':
                //Generate image
                $backend = $backend->resizeByWidth($height);
                $backendCopy = clone $backend;
                return $backendCopy->crop($cropOffset, 0, $width, $height);
            default:
                //Generate image without cropping
                return $backend->resize($width, $height);
        }
    }
```

Another way would be to use streams. See: https://github.com/silverstripe/silverstripe-assets/pull/527 It appears that `intervention/image` is currently under active development and the issue might be resolved at some point, but not in the near future with SS.

### VSCode tasks - remember all the commands :information_desk_person:
There are a bunch of tasks in `.vscode/tasks.json` available per `Command+Shift+B`:
- `ddev start` (magenta)
- `ddev stop` (magenta)
- `ddev restart` (magenta)
- `composer update` (magenta)
- `composer vendor-expose` (blue)
- `ddev logs` (magenta)
- `flush` (blue)
- `flushh` (blue - flush hard) instead of `ddev exec rm -rf ./silverstripe-cache/*`
- `dev/build` (blue) instead of `ddev php ./vendor/silverstripe/framework/cli-script.php dev/build flush`
- `xdebug on / off` (magenta)
