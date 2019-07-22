# install
```bash
composer require uginroot/image-resize:^1.0
```
#Create
#### createFromString
```php
$image = ImageResize::createFromString(file_get_content($path));
```

#### createFromPath
```php
$image = ImageResize::createFromPath($path);
```

#### __constructor
```php
$content = file_get_content($path);
$resource = imagecreatefromstring($content);
$image = new ImageCreate($resource, $content);
$image = new ImageCreate($resource); // $image->resetOrientation() not working
```
# Save
### formats
```php
ImageCreate::FORMAT_JPEG;
ImageCreate::FORMAT_PNG;
ImageCreate::FORMAT_WEBP;
```

#### save
```php
$image->save($path);
// save(string $path[, int $format = ImageCreate::FORMAT_JPEG[, bool $owerwrite = true[, int $mode = 0666]]]):static
```

#### getContent
```php
echo $image->getContent();
// getContent([int $format = ImageCreate::FORMAT_JPEG]);
```

#### print
```php
$image->print();
// $image->print([int $format = ImageCreate::FORMAT_JPEG]);
```

#### __toString
```php
(string)$image === $image->getContent(ImageCreate::FORMAT_JPEG); // true
```

#### copyResource
```php
$image->copyResource();
```

# Info
#### getWidth
```php
$image->getWidth();
```

#### getHeight
```php
$image->getHeight();
```

# resetOrientation
Rotate photo if the set exif orientation tag
```php
$image->resetOrientation();
```

# Change image
### scale
```php
$image->scale(50);
// scale(int|float 50)
```
|original(600x300) |result(300x150) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/scale.jpg)|

### resize
```php
$image->resize(100, 100);
// resize(int $width, int $heihht[, bool $increase = true])
```
|original(600x300) |result(100x100) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resize.jpg)|


### resizeToHeight
```php
$image->resizeToHeight(100);
// resizeToHeight(int $height[, bool $increase = true])
```
|original(600x300) |result(100x200) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeToHeight.jpg)|


### resizeToWidth
```php
$image->resizeToWidth(100);
// resizeToWidth(int $width[, bool $increase = true])
```
|original(600x300) |result(100x50) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeToWidth.jpg)|


### resizeToLongSide
```php
$image->resizeToLongSide(100);
// resizeToLongSide(int $side[, $increase = true])
```
|original(600x300) |result(100x50) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeToLongSide.jpg)|


### resizeToShortSide
```php
$image->resizeToShortSide(100);
// resizeToShortSide(int $side[, $increase = true])
```
|original(600x300) |result(100x200) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeToShortSide.jpg)|


### resizeToBestFit
```php
$image->resizeToBestFit(100, 100);
// resizeToBestFit(int $width, int $height[, $increase = true])
```
|original(600x300) |result(100x50) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeToBestFit.jpg)|

### resizeToWorstFit
```php
$image->resizeToWorstFit(100, 100);
// resizeToWorstFit(int $width, int $height[, $increase = true])
```
|original(600x300) |result(100x200) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeToWorstFit.jpg)|

### crop
```php
$image->crop(0, 0, 100, 100);
// crop(int $x, int $y, int $width, int $height)
```
|original(600x300) |result(100x100) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/crop.jpg)|

### positions
```php
ImageResize::POSITION_CENTER;
ImageResize::POSITION_TOP;
ImageResize::POSITION_RIGHT;
ImageResize::POSITION_BOTTOM;
ImageResize::POSITION_LEFT;
ImageResize::POSITION_TOP_LEFT;
ImageResize::POSITION_TOP_RIGHT;
ImageResize::POSITION_BOTTOM_LEFT;
ImageResize::POSITION_BOTTOM_RIGHT;
```

### cropPosition
```php
$image->cropPosition(100, 100);
// cropPosition(int $width, int $height[, int $position = ImageResize::POSITION_CENTER])
```
|original(600x300) |result(100x100) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/cropPosition.jpg)|

### resizeCover
```php
$image->resizeCover(100, 100);
// resizeCover(int $width, int $height[, int $position = ImageResize::POSITION_CENTER])
```
|original(600x300) |result(100x100) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeCover.jpg)|

### resizeContain
```php
$image->resizeContain(100, 100);
// resizeContain(int $width, int $height[, int $position = ImageResize::POSITION_CENTER[, int $color = 0x000000]])
```
|original(600x300) |result(100x100) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/resizeContain.jpg)|

### sides
```php
ImageResize::SIDE_TOP;
ImageResize::SIDE_RIGHT;
ImageResize::SIDE_BOTTOM;
ImageResize::SIDE_LEFT;
ImageResize::SIDE_ALL;
```

### cropEdge
```php
$image->cropEdge(50, ImageResize::SIDE_ALL);
// cropEdge(int $cutLength[, int $side = ImageResize::SIDE_ALL])
```
|original(600x300) |result(500x200) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/cropEdge.jpg)|

### addBorder
```php
$image->addBorder(10);
// addBorder(int $borderWidth[, int $side = ImageResize::SIDE_ALL[, int $color = 0x000000]])
```
|original(600x300) |result(620x220) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/addBorder.jpg)|


# Watermark
Fits:
```php
ImageResize::FIT_CANCEL; // cancel if wathermark size grate then $image
ImageResize::FIT_RESIZE; // resize wathermak
ImageResize::FIT_AS_IS; // crop if watermark out of bounds
```
```php
$watermark = ImageResize::createFromPath($path);
$image->setWatermark($watermark);
// setWatermark(ImageResize $watermark[, int $position = ImageResize::POSITION_BOTTOM_RIGHT[, int $padding = 16[, int $fit = ImageResize::FIT_AS_IS]]]);
```
|original(600x300) |result(600x300) |
|--------|------|
|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/horizontal.jpg)|![](https://raw.githubusercontent.com/uginroot/image-resize/master/docs/result/setWatermark.jpg)|

### change
```php
$image->change(function(&$resource){
    $resource = imagerotate($resource, 90, 0x000000);
});
// change(callable(&$resource) $callback)
```

# Optimize image

See this package [link](https://github.com/uginroot/image-resize-optimizer)