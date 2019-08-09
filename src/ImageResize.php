<?php

namespace Uginroot;

use Exception\ImageResizeImpossibleToCreateDirectoryException;
use Uginroot\Exception\ImageResizeBadColorException;
use Uginroot\Exception\ImageResizeBadFormatException;
use Uginroot\Exception\ImageResizeBadPositionException;
use Uginroot\Exception\ImageResizeBadResourceException;
use Uginroot\Exception\ImageResizeBadFitException;
use Uginroot\Exception\ImageResizeFileAlreadyExistException;
use Uginroot\Exception\ImageResizeFileNotExistException;
use Uginroot\Exception\ImageResizeNotSupportResetOrientationException;
use Uginroot\Exception\ImageResizeBadContentException;

ini_set('gd.jpeg_ignore_warning', 1);

class ImageResize
{
    const POSITION_CENTER = 1;
    const POSITION_TOP = 2;
    const POSITION_RIGHT = 3;
    const POSITION_BOTTOM = 4;
    const POSITION_LEFT = 5;
    const POSITION_TOP_LEFT = 6;
    const POSITION_TOP_RIGHT = 7;
    const POSITION_BOTTOM_LEFT = 8;
    const POSITION_BOTTOM_RIGHT = 9;

    const FORMAT_JPEG = 1;
    const FORMAT_PNG = 2;
    const FORMAT_WEBP = 3;

    const SIDE_TOP = 1;
    const SIDE_RIGHT = 2;
    const SIDE_BOTTOM = 4;
    const SIDE_LEFT = 8;
    const SIDE_ALL = self::SIDE_TOP | self::SIDE_RIGHT | self::SIDE_BOTTOM | self::SIDE_LEFT;

    const FIT_CANCEL = 1;
    const FIT_RESIZE = 2;
    const FIT_AS_IS = 3;


    /**
     * @var resource
     */
    private $image;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var int
     */
    private $qualityJpeg = -1;

    /**
     * @var int
     */
    private $qualityPng = -1;

    /**
     * @var int
     */
    private $filtersPng = -1;

    /**
     * @param string $content
     * @return static
     * @throws ImageResizeBadContentException
     * @throws ImageResizeBadResourceException
     */
    public static function createFromString(string $content)
    {
        $image = @imagecreatefromstring($content);
        if ($image === false) {
            throw new ImageResizeBadContentException();
        }
        return new static($image, $content);
    }

    /**
     * @param string $path
     * @return static
     * @throws ImageResizeFileNotExistException
     * @throws ImageResizeBadContentException
     * @throws ImageResizeBadResourceException
     */
    public static function createFromPath(string $path)
    {
        $content = @file_get_contents($path);
        if ($content === false) {
            throw new ImageResizeFileNotExistException();
        }
        return static::createFromString($content);
    }

    /**
     * @param $output
     * @param int $format
     * @throws ImageResizeBadFormatException
     */
    protected function save_($output, int $format)
    {
        switch ($format) {
            case static::FORMAT_JPEG:
                imagejpeg($this->image, $output, $this->qualityJpeg);
                break;
            case static::FORMAT_PNG:
                imagepng($this->image, $output, $this->qualityPng, $this->filtersPng);
                break;
            case static::FORMAT_WEBP:
                imagewbmp($this->image, $output);
                break;
            default:
                throw new ImageResizeBadFormatException();
        }
    }


    /**
     * @param string $path
     * @param int $format
     * @param bool $overwrite
     * @param int $mode
     * @return static
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeFileAlreadyExistException
     * @throws ImageResizeImpossibleToCreateDirectoryException
     */
    public function save(string $path, int $format = self::FORMAT_JPEG, $overwrite = false, int $mode = 0666)
    {
        if (file_exists($path)) {
            if ($overwrite) {
                @unlink($path);
            } else {
                throw new ImageResizeFileAlreadyExistException();
            }
        }
        if (!is_dir(dirname($path))) {
            if (!@mkdir(dirname($path), 0777, true)) {
                throw new ImageResizeImpossibleToCreateDirectoryException();
            }
        }

        $this->save_($path, $format);

        chmod($path, $mode);

        return $this;
    }

    /**
     * @param resource $image
     * @param string|null $content
     * @throws ImageResizeBadResourceException
     */
    public function __construct($image, string $content = null)
    {
        if (!is_resource($image)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new ImageResizeBadResourceException('Expected resource from parameter $image');
        }
        if (get_resource_type($image) !== 'gd') {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new ImageResizeBadResourceException('Expected gd resource from parameter $image');
        }

        $this->content = $content;
        $this->setImage($image);
    }

    public function __destruct()
    {
        imagedestroy($this->image);
    }

    /**
     * @param int $format
     * @return string
     * @throws ImageResizeBadFormatException
     */
    public function getContent(int $format = self::FORMAT_JPEG)
    {
        $stream = fopen("php://memory", "w+");
        $this->save_($stream, $format);
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        return $content;
    }

    /**
     * @param int $format
     * @return void
     * @throws ImageResizeBadFormatException
     */
    public function print(int $format = self::FORMAT_JPEG): void
    {
        $this->save_(null, $format);
    }


    public function __toString()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getContent();
    }

    /**
     * @return resource
     */
    public function copyResource()
    {
        $copy = imagecreatetruecolor($this->width, $this->height);
        imagecopy($copy, $this->image, 0, 0, 0, 0, $this->width, $this->height);
        return $copy;
    }

    /**
     * @return resource
     */
    protected function getResource()
    {
        return $this->image;
    }

    /**
     * @return void
     */
    protected function updateImageSize(): void
    {
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }


    /**
     * @return static
     * @throws ImageResizeNotSupportResetOrientationException
     */
    public function resetOrientation()
    {
        if ($this->content === null) {
            throw new ImageResizeNotSupportResetOrientationException();
        }

        $imageMemory = fopen("php://temp:maxmemory:15728640", 'rwb+');
        fwrite($imageMemory, $this->content);
        $exif = @exif_read_data($imageMemory);
        fclose($imageMemory);

        if ($exif !== false && array_key_exists('Orientation', $exif)) {
            switch ($exif['Orientation']) {
                case 8:
                    $angle = 90;
                    break;
                case 3:
                    $angle = 180;
                    break;
                case 6:
                    $angle = -90;
                    break;
                default:
                    $angle = 0;
            }
            if ($angle !== 0) {
                $this->image = imagerotate($this->image, $angle, 0);
                $this->updateImageSize();
            }
        }

        return $this;
    }


    /**
     * @param int|float $percent
     * @return static
     */
    public function scale($percent)
    {
        $width = round($this->width / 100 * $percent);
        $height = round($this->height / 100 * $percent);
        return $this->resize($width, $height, true);
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $increase
     * @return static
     */
    public function resize(int $width, int $height, bool $increase = true)
    {
        if (false === $increase && ($width > $this->width || $height > $this->height)) {
            return $this;
        }
        $image = imagecreatetruecolor($width, $height);
        imagecopyresampled(
            $image,
            $this->image,
            0, 0,
            0, 0,
            $width, $height,
            $this->width, $this->height
        );
        return $this->setImage($image);
    }

    /**
     * @param int $height
     * @param bool $increase
     * @return static
     */
    public function resizeToHeight(int $height, bool $increase = true)
    {
        if (false === $increase && $height > $this->height) {
            return $this;
        }

        $width = (int)round($height / $this->height * $this->width);
        return $this->resize($width, $height, $increase);
    }

    /**
     * @param int $width
     * @param bool $increase
     * @return static
     */
    public function resizeToWidth(int $width, bool $increase = true)
    {
        if (false === $increase && $width >= $this->width) {
            return $this;
        }

        $height = (int)round($width / $this->width * $this->height);
        return $this->resize($width, $height, $increase);
    }

    /**
     * @param int $side
     * @param bool $increase
     * @return static
     */
    public function resizeToLongSide(int $side, bool $increase = true)
    {
        if ($this->width > $this->height) {
            return $this->resizeToWidth($side, $increase);
        } else {
            return $this->resizeToHeight($side, $increase);
        }
    }

    /**
     * @param int $side
     * @param bool $increase
     * @return static
     */
    public function resizeToShortSide(int $side, bool $increase = true)
    {
        if ($this->width < $this->height) {
            return $this->resizeToWidth($side, $increase);
        } else {
            return $this->resizeToHeight($side, $increase);
        }
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $increase
     * @return static
     */
    public function resizeToBestFit(int $width, int $height, bool $increase = true)
    {
        if ($this->height / $this->width < $height / $width) {
            return $this->resizeToWidth($width, $increase);
        } else {
            return $this->resizeToHeight($height, $increase);
        }
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $increase
     * @return static
     */
    public function resizeToWorstFit(int $width, int $height, bool $increase = true)
    {
        if ($this->height / $this->width > $height / $width) {
            return $this->resizeToWidth($width, $increase);
        } else {
            return $this->resizeToHeight($height, $increase);
        }
    }

    /**
     * @param resource $image
     * @return static
     */
    protected function setImage($image)
    {
        $this->image = $image;
        $this->updateImageSize();
        return $this;
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return static
     */
    public function crop(int $x, int $y, int $width, int $height)
    {
        if ($x < 0) {
            $width -= $x;
            $x = 0;
        }
        if ($y < 0) {
            $height -= $y;
        }

        if ($width > $this->width) {
            $width = $this->width;
        }
        if ($height > $this->height) {
            $height = $this->height;
        }

        if ($x === 0 && $y === 0 && $height === $this->height && $width === $this->width) {
            return $this;
        }

        $image = imagecreatetruecolor($width, $height);
        imagecopyresampled(
            $image, $this->image,
            0, 0,
            $x, $y,
            $width,
            $height,
            $width, $height
        );
        return $this->setImage($image);
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $position
     * @return static
     * @throws ImageResizeBadPositionException
     */
    public function cropPosition(int $width, int $height, int $position = self::POSITION_CENTER)
    {
        if ($height > $this->height && $width > $this->width) {
            return $this;
        }
        [$x, $y] = $this->calculatePosition($position, $width, $height);
        return $this->crop($x, $y, $width, $height);
    }

    /**
     * @param int $position
     * @param int $width
     * @param int $height
     * @param int $padding
     * @param bool $overflowResize
     * @return array = [0 => int, 1 => int]
     * @throws ImageResizeBadPositionException
     */
    protected function calculatePosition(int $position, int $width, int $height, int $padding = 0, bool $overflowResize = true)
    {
        if($overflowResize){
            if ($width > $this->width + $padding) {
                $width = $this->width - $padding;
            }
            if ($height > $this->height + $padding) {
                $height = $this->height - $padding;
            }
        }

        $xCenter = round($this->getWidth() / 2 - $width / 2);
        $yCenter = round($this->getHeight() / 2 - $height / 2);
        $xMax = $this->getWidth() - $padding - $width;
        $yMax = $this->getHeight() - $padding - $height;
        $xMin = $yMin = $padding;

        switch ($position) {
            case static::POSITION_CENTER:
                $x = $xCenter;
                $y = $yCenter;
                break;
            case static::POSITION_TOP:
                $x = $xCenter;
                $y = $yMin;
                break;
            case static::POSITION_RIGHT:
                $x = $xMax;
                $y = $yCenter;
                break;
            case static::POSITION_BOTTOM:
                $x = $xCenter;
                $y = $yMax;
                break;
            case static::POSITION_LEFT:
                $x = $xMin;
                $y = $yCenter;
                break;
            case static::POSITION_TOP_LEFT:
                $x = $xMin;
                $y = $yMin;
                break;
            case static::POSITION_TOP_RIGHT:
                $x = $xMax;
                $y = $yMin;
                break;
            case static::POSITION_BOTTOM_LEFT:
                $x = $xMin;
                $y = $yMax;
                break;
            case static::POSITION_BOTTOM_RIGHT:
                $x = $xMax;
                $y = $yMax;
                break;
            default:
                throw new ImageResizeBadPositionException();
        }
        return [$x, $y];
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $position
     * @param bool $increase
     * @return static
     * @throws ImageResizeBadPositionException
     */
    public function resizeCover(int $width, int $height, int $position = self::POSITION_CENTER, bool $increase = true)
    {
        $this->resizeToWorstFit($width, $height, $increase);
        $this->cropPosition($width, $height, $position);
        return $this;
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $color
     * @return resource
     */
    protected static function createBackground(int $width, int $height, int $color)
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, $color);
        return $image;
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $position
     * @param int $color
     * @param bool $increase
     * @return static
     * @throws ImageResizeBadPositionException
     */
    public function resizeContain(int $width, int $height, int $position = self::POSITION_CENTER, int $color = 0x000000, bool $increase = true)
    {

        $this->resizeToBestFit($width, $height, $increase);

        // result grate then original ($increase === false)
        if ($height !== $this->getHeight() && $width !== $this->getWidth()) {
            if ($this->getWidth() / $this->getHeight() > $width / $height) {
                $height = $width / $this->getWidth() * $height;
                $width = $this->getWidth();
            } else {
                $width = $height / $this->getHeight() * $width;
                $height = $this->getHeight();
            }
        }

        // create background
        $image = static::createBackground($width, $height, $color);


        // coordinate paste image to background
        if ($width === $this->getWidth()) {
            $x = 0;
            switch ($position) {
                case static::POSITION_LEFT:
                case static::POSITION_RIGHT:
                case static::POSITION_CENTER:
                    $y = round(($height - $this->getHeight()) / 2);
                    break;
                case static::POSITION_TOP:
                    $y = 0;
                    break;
                case static::POSITION_BOTTOM:
                    $y = $height - $this->getHeight();
                    break;
                default:
                    throw new ImageResizeBadPositionException();
            }
        } else {
            $y = 0;
            switch ($position) {
                case static::POSITION_TOP:
                case static::POSITION_BOTTOM:
                case static::POSITION_CENTER:
                    $x = round(($width - $this->getWidth()) / 2);
                    break;
                case static::POSITION_LEFT:
                    $x = 0;
                    break;
                case static::POSITION_RIGHT:
                    $x = $width - $this->getWidth();
                    break;
                default:
                    throw new ImageResizeBadPositionException();
            }
        }

        return $this->pasteImage($image, $x, $y);
    }

    /**
     * @param $image
     * @param $x
     * @param $y
     * @return ImageResize
     */
    protected function pasteImage($image, $x, $y)
    {
        imagecopy(
            $image, $this->image,
            $x, $y,
            0, 0,
            $this->getWidth(), $this->getHeight()
        );
        return $this->setImage($image);
    }


    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int|null $alpha
     * @return int
     * @throws ImageResizeBadColorException
     */
    public function createColor(int $red = 0, int $green = 0, int $blue = 0, int $alpha = null)
    {
        if ($alpha === null) {
            $color = @imagecolorallocate($this->image, $red, $green, $blue);
        } else {
            $color = @imagecolorallocatealpha($this->image, $red, $green, $blue, $alpha);
        }

        if ($color === false) {
            throw new ImageResizeBadColorException();
        }

        return $color;
    }

    /**
     * @param int $color
     * @return int
     * @throws ImageResizeBadColorException
     */
    public function createColorFromInt(int $color = 0x000000)
    {
        [$red, $green, $blue] = $this->unpackColor($color);
        return $this->createColor($red, $green, $blue);
    }

    /**
     * @param int $color
     * @param int $alpha
     * @return int
     * @throws ImageResizeBadColorException
     */
    public function createColorAlphaFromInt(int $color = 0x000000, int $alpha = 255)
    {
        [$red, $green, $blue] = $this->unpackColor($color);
        $alpha = round(127 / 255 * $alpha);
        return $this->createColor($red, $green, $blue, $alpha);
    }

    protected function unpackColor(int $color)
    {
        $red = ($color & 0xff0000) >> 16;
        $green = ($color & 0x00ff00) >> 8;
        $blue = $color & 0x0000ff;

        return [$red, $green, $blue];
    }


    /**
     * @param int $cutLength
     * @param int $side
     * @return static
     */
    public function cropEdge(int $cutLength, int $side = self::SIDE_ALL)
    {

        $x = $y = 0;
        $width = $this->getWidth();
        $height = $this->getHeight();

        if (0 !== ($side & static::SIDE_TOP)) {
            $y = $cutLength;
            $height -= $cutLength;
        }

        if (0 !== ($side & static::SIDE_LEFT)) {
            $x = $cutLength;
            $width -= $cutLength;
        }

        if (0 !== ($side & static::SIDE_RIGHT)) {
            $width -= $cutLength;
        }

        if (0 !== ($side & static::SIDE_BOTTOM)) {
            $height -= $cutLength;
        }

        return $this->crop($x, $y, $width, $height);
    }

    /**
     * @param int $borderWidth
     * @param int $side
     * @param int $color
     * @return $this
     */
    public function addBorder(int $borderWidth, int $side = self::SIDE_ALL, int $color = 0x000000)
    {
        $x = $y = 0;
        $width = $this->getWidth();
        $height = $this->getHeight();

        if (0 !== ($side & static::SIDE_TOP)) {
            $y = $borderWidth;
            $height += $borderWidth;
        }

        if (0 !== ($side & static::SIDE_LEFT)) {
            $x = $borderWidth;
            $width += $borderWidth;
        }

        if (0 !== ($side & static::SIDE_RIGHT)) {
            $width += $borderWidth;
        }

        if (0 !== ($side & static::SIDE_BOTTOM)) {
            $height += $borderWidth;
        }

        $image = static::createBackground($width, $height, $color);

        return $this->pasteImage($image, $x, $y);
    }


    /**
     * @param callable $callback
     * @return ImageResize
     */
    public function change(callable $callback)
    {
        $callback($this->image);
        $this->updateImageSize();
        return $this;
    }


    /**
     * @param ImageResize $watermark
     * @param int $position
     * @param int $padding
     * @param int $fit
     * @return ImageResize
     * @throws ImageResizeBadFitException
     * @throws ImageResizeBadPositionException
     * @throws ImageResizeBadResourceException
     */
    public function setWatermark(ImageResize $watermark, int $position = self::POSITION_BOTTOM_RIGHT, int $padding = 16, int $fit = self::FIT_AS_IS)
    {

        [$x, $y] = $this->calculatePosition(
            $position,
            $watermark->getWidth(),
            $watermark->getHeight(),
            $padding,
            false
        );

        // watermark does not fit
        if (
            $x < 0 ||
            $y < 0 ||
            $x + $watermark->getWidth() > $this->getWidth() - $padding ||
            $y + $watermark->getHeight() > $this->getHeight() - $padding
        ) {
            switch ($fit) {
                case static::FIT_CANCEL:
                    return $this;
                case static::FIT_RESIZE:
                    $watermark = new ImageResize($watermark->copyResource());
                    $width = $this->getWidth() - $padding * 2;
                    $height = $this->getHeight() - $padding * 2;
                    if($width <= 1 || $height <= 1){
                        return $this;
                    }
                    $watermark->resizeToBestFit($width, $height);
                    return $this->setWatermark($watermark, $position, $padding, $fit);
                case static::FIT_AS_IS:

                    if (
                        $x + $watermark->getWidth() <= $this->getWidth() &&
                        $y + $watermark->getHeight() <= $this->getHeight()
                    ) {
                        break;
                    }
                    $watermark = new ImageResize($watermark->copyResource());

                    if (in_array($position, [
                        self::POSITION_CENTER,
                        self::POSITION_TOP,
                        self::POSITION_BOTTOM,
                    ], true)) {
                        $width = $this->getWidth();
                    } else {
                        $width = $this->getWidth() - $padding;
                    }
                    if (in_array($position, [
                        self::POSITION_CENTER,
                        self::POSITION_LEFT,
                        self::POSITION_RIGHT,
                    ], true)) {
                        $height = $this->getHeight();
                    } else {
                        $height = $this->getHeight() - $padding;
                    }

                    $watermark->cropPosition($width, $height, $position);
                    return $this->setWatermark($watermark, $position, $padding, $fit);
                default:
                    throw new ImageResizeBadFitException();
            }
        }

        // paste watermark
        imagecopy(
            $this->image, $watermark->getResource(),
            $x, $y,
            0, 0,
            $watermark->getWidth(), $watermark->getHeight()
        );

        return $this;
    }

    /**
     * @return int
     */
    public function getQualityJpeg(): int
    {
        return $this->qualityJpeg;
    }

    /**
     * @param int $qualityJpeg
     * @return static
     */
    public function setQualityJpeg(int $qualityJpeg)
    {
        $this->qualityJpeg = $qualityJpeg;
        return $this;
    }

    /**
     * @return int
     */
    public function getQualityPng(): int
    {
        return $this->qualityPng;
    }

    /**
     * @param int $qualityPng
     * @return static
     */
    public function setQualityPng(int $qualityPng)
    {
        $this->qualityPng = $qualityPng;
        return $this;
    }

    /**
     * @return int
     */
    public function getFiltersPng(): int
    {
        return $this->filtersPng;
    }

    /**
     * @param int $filtersPng
     * @return static
     */
    public function setFiltersPng(int $filtersPng)
    {
        $this->filtersPng = $filtersPng;
        return $this;
    }
}