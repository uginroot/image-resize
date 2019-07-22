# image-resize
Image resizing in php

### createFromString
```php
/**
 * @param string $content
 * @return static
 * @throws ImageResizeBadContentException
 * @throws ImageResizeBadResourceException
 */
$image = ImageResize::createFromString(file_get_content($path));
```

### createFromPath
```php
/**
 * @param string $path
 * @return static
 * @throws ImageResizeFileNotExistException
 * @throws ImageResizeBadContentException
 * @throws ImageResizeBadResourceException
 */
$image = ImageResize::createFromPath($path);
```

### __constructor
```php
$content = file_get_content($path);
$resource = imagecreatefromstring($content);
/**
 * @param resource $image
 * @param string|null $content = null
 * @throws ImageResizeBadResourceException
 */
$image = new ImageCreate($resource, $content);
$image = new ImageCreate($resource); // $image->resetOrientation() not working
```

### formats
```php
echo ImageCreate::FORMAT_JPEG;
echo ImageCreate::FORMAT_PNG;
echo ImageCreate::FORMAT_WEBP;
```

### save
```php
/**
 * @param string $path
 * @param int $format = ImageCreate::FORMAT_JPEG
 * @param bool $overwrite = false
 * @param int $mode = 0666
 * @return static
 * @throws ImageResizeBadFormatException
 * @throws ImageResizeFileAlreadyExistException
 */
$image->save($path);
$image->save($path, ImageCreate::FORMAT_PNG, true, 0666);
```

### getContent
```php
/**
 * @param int $format = ImageCreate::FORMAT_JPEG
 * @return string
 * @throws ImageResizeBadFormatException
 */
echo $image->getContent();
echo $image->getContent(ImageCreate::FORMAT_PNG);
```

### print
```php
/**
 * @param int $format = ImageCreate::FORMAT_JPEG
 * @return void
 * @throws ImageResizeBadFormatException
 */
$image->print();
$image->print(ImageCreate::FORMAT_PNG);
```

### __toString
```php
$image = ImageResize::createFromPath($path);
$content =  (string)$image;
$content === $image->getContent(ImageCreate::FORMAT_JPEG); // true
```

### copyResource
```php
/**
 * @return resource
 */
$image->copyResource();
```

### getWidth
```php
/**
 * @return int
 */
$image->getWidth();
```

### getHeight
```php
/**
 * @return int
 */
$image->getHeight();
```

### resetOrientation
Rotate photo if the set exif orientation tag
```php
/**
 * @return static
 * @throws ImageResizeNotSupportResetOrientationException
 */
$image->resetOrientation();
```


### scale
```php
/**
 * @param int|float $percent
 * @return static
 */
$image->scale(50);
```
|original(600x300) |result(300x150) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/scale.jpg)|

### resize
```php
/**
 * @param int $width
 * @param int $height
 * @param bool $increase = true
 * @return static
 */
$image->resize(100, 100);
// $image->resize(1000, 1000, true);
```
|original(600x300) |result(100x100) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resize.jpg)|


### resizeToHeight
```php
/**
 * @param int $height
 * @param bool $increase = true
 * @return static
 */
$image->resizeToHeight(100);
// $image->resizeToHeight(1000, true);
```
|original(600x300) |result(100x200) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeToHeight.jpg)|


### resizeToWidth
```php
/**
 * @param int $width
 * @param bool $increase = true
 * @return static
 */
$image->resizeToWidth(100);
// $image->resizeToWidth(1000, true);
```
|original(600x300) |result(100x50) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeToWidth.jpg)|


### resizeToLongSide
```php
/**
 * @param int $side
 * @param bool $increase = true
 * @return static
 */
$image->resizeToLongSide(100);
// $image->resizeToLongSide(1000, true);
```
|original(600x300) |result(100x50) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeToLongSide.jpg)|


### resizeToShortSide
```php
/**
 * @param int $side
 * @param bool $increase = true
 * @return static
 */
$image->resizeToShortSide(100);
// $image->resizeToShortSide(1000, true);
```
|original(600x300) |result(100x200) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeToShortSide.jpg)|


### resizeToBestFit
```php
/**
 * @param int $width
 * @param int $height
 * @param bool $increase = true
 * @return static
 */
$image->resizeToBestFit(100, 100);
// $image->resizeToBestFit(1000, 1000, true);
```
|original(600x300) |result(100x50) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeToBestFit.jpg)|

### resizeToWorstFit
```php
/**
 * @param int $width
 * @param int $height
 * @param bool $increase = true
 * @return static
 */
$image->resizeToWorstFit(100, 100);
// $image->resizeToWorstFit(1000, 1000, true);
```
|original(600x300) |result(100x200) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeToWorstFit.jpg)|

### crop
```php
/**
 * @param int $x
 * @param int $y
 * @param int $width
 * @param int $height
 * @return static
 */
$image->crop(0, 0, 100, 100);
```
|original(600x300) |result(100x100) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/crop.jpg)|

### positions
```php
echo ImageResize::POSITION_CENTER;
echo ImageResize::POSITION_TOP;
echo ImageResize::POSITION_RIGHT;
echo ImageResize::POSITION_BOTTOM;
echo ImageResize::POSITION_LEFT;
echo ImageResize::POSITION_TOP_LEFT;
echo ImageResize::POSITION_TOP_RIGHT;
echo ImageResize::POSITION_BOTTOM_LEFT;
echo ImageResize::POSITION_BOTTOM_RIGHT;
```

### crop
```php
/**
 * @param int $width
 * @param int $height
 * @param int $position = ImageResize::POSITION_CENTER
 * @return static
 * @throws ImageResizeBadPositionException
 */
$image->cropPosition(100, 100, ImageResize::POSITION_CENTER);
```
|original(600x300) |result(100x100) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/cropPosition.jpg)|

### resizeCover
```php
/**
 * @param int $width
 * @param int $height
 * @param int $position = ImageResize::POSITION_CENTER
 * @param bool $increase = true
 * @return static
 * @throws ImageResizeBadPositionException
 */
$image->resizeCover(100, 100, ImageResize::POSITION_CENTER);
```
|original(600x300) |result(100x100) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeCover.jpg)|

### resizeContain
```php
/**
 * @param int $width
 * @param int $height
 * @param int $position = ImageResize::POSITION_CENTER
 * @param int $color = 0x000000
 * @param bool $increase = true
 * @return static
 * @throws ImageResizeBadPositionException
 */
$image->resizeContain(100, 100, ImageResize::POSITION_CENTER, 0x000000);
```
|original(600x300) |result(100x100) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeContain.jpg)|

### sides
```php
echo SIDE_TOP;
echo SIDE_RIGHT;
echo SIDE_BOTTOM;
echo SIDE_LEFT;
echo SIDE_ALL;
```

### cropEdge
```php
/**
 * @param int $cutLength
 * @param int $side = ImageResize::SIDE_ALL
 * @return static
 */
$image->cropEdge(50, ImageResize::SIDE_ALL);
// $image->cropEdge(50, ImageResize::SIDE_TOP | ImageResize::SIDE_BOTTOM);
```
|original(600x300) |result(500x200) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/cropEdge.jpg)|

### addBorder
```php
/**
 * @param int $borderWidth
 * @param int $side = ImageResize::SIDE_ALL
 * @param int $color = 0x000000
 * @return $this
 */
$image->addBorder(10);
// $image->addBorder(10, ImageResize::SIDE_TOP | ImageResize::SIDE_BOTTOM);
```
|original(600x300) |result(620x220) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/addBorder.jpg)|

### change

fits:
```php
echo ImageResize::FIT_CANCEL; // cancel if wathermark size grate then $image
echo ImageResize::FIT_RESIZE; // resize wathermak
echo ImageResize::FIT_AS_IS; // crop if watermark out of bounds
```
```php
$watermark = ImageResize::createFromPath($path);
/**
 * @param ImageResize $watermark
 * @param int $position = ImageResize::POSITION_BOTTOM_RIGHT
 * @param int $padding = 16
 * @param int $fit = ImageResize::FIT_AS_IS
 * @return ImageResize
 * @throws ImageResizeBadFitException
 * @throws ImageResizeBadPositionException
 * @throws ImageResizeBadResourceException
 */
$image->setWatermark($watermark);
// $image->setWatermark($watermark, ImageResize::POSITION_BOTTOM, 24);
// $image->setWatermark($watermark, ImageResize::POSITION_CENTER, 16, ImageResize::FIT_RESIZE);
```
|original(600x300) |result(600x300) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/setWatermark.jpg)|
