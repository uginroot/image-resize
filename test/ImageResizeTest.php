<?php

namespace Uginroot\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use Uginroot\Exception\ImageResizeFileNotExistException;
use Uginroot\Exception\ImageResizeBadColorException;
use Uginroot\Exception\ImageResizeBadContentException;
use Uginroot\Exception\ImageResizeBadFormatException;
use Uginroot\Exception\ImageResizeBadPositionException;
use Uginroot\Exception\ImageResizeBadResourceException;
use Uginroot\Exception\ImageResizeFileAlreadyExistException;
use Uginroot\Exception\ImageResizeNotSupportResetOrientationException;
use Uginroot\ImageResize;

class ImageResizeTest extends TestCase
{
    const COLOR_CENTER_LINE = 0x2196F3;
    const COLOR_CORNERS = 0xFF5722;
    const COLOR_SIDE_TOP_LEFT = 0x4CAF50;
    const COLOR_SIDE_TOP_RIGHT = 0xFFEB3B;
    const COLOR_SIDE_BOTTOM_LEFT = 0x009688;
    const COLOR_SIDE_BOTTOM_RIGHT = 0xE91E63;
    const COLOR_FILL = 0x000000;

    private $image;
    private $imageVertical;
    private $path;
    private $pathVertical;
    private $content;
    private $directory;
    private $directoryOutput;
    private $saveResult = true;
    private $removeResult = true;
    private $filesOutput = [];

    private function copyImage()
    {
        $width = imagesx($this->image);
        $height = imagesy($this->image);
        $copy = imagecreatetruecolor($width, $height);
        imagecopy($copy, $this->image, 0, 0, 0, 0, $width, $height);
        return $copy;
    }

    private function copyImageVertical()
    {
        $width = imagesx($this->imageVertical);
        $height = imagesy($this->imageVertical);
        $copy = imagecreatetruecolor($width, $height);
        imagecopy($copy, $this->imageVertical, 0, 0, 0, 0, $width, $height);
        return $copy;
    }

    public function setUp(): void
    {
        $this->directory = __DIR__ . '/image';
        $this->directoryOutput = __DIR__ . '/output';
        $this->path = $this->directory . '/original.png';
        $this->pathVertical = $this->directory . '/originalVertical.png';

        $this->content = file_get_contents($this->path);
        $this->image = imagecreatefromstring($this->content);
        $this->imageVertical = imagecreatefromstring(file_get_contents($this->pathVertical));
    }

    public function tearDown(): void
    {
        if ($this->saveResult) {
            foreach ($this->filesOutput as $path) {
                if ($this->removeResult) {
                    @unlink($path);
                }
            }
        }
    }

    /**
     * @param ImageResize $imageResize
     * @param string $path
     * @param int $format
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeFileAlreadyExistException
     */
    private function save(ImageResize $imageResize, string $path, int $format = ImageResize::FORMAT_PNG)
    {
        if ($this->saveResult) {
            $this->filesOutput[] = $path;
            $imageResize->save($path, $format, true);
        }
    }

    /**
     * @throws ImageResizeBadContentException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileNotExistException
     */
    public function testCreateFromPath()
    {
        ImageResize::createFromPath($this->path);
        $this->assertTrue(true);
    }

    /**
     * @throws ImageResizeBadContentException
     * @throws ImageResizeBadResourceException
     */
    public function testCreateFromContent()
    {
        ImageResize::createFromString($this->content);
        $this->assertTrue(true);
    }

    /**
     * @throws ImageResizeBadResourceException
     */
    public function testCreate()
    {
        new ImageResize($this->image);
        $this->assertTrue(true);
    }

    /**
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     */
    public function testWidthHeight()
    {
        $image = new ImageResize($this->copyImage());
        $this->assertEquals(200, $image->getWidth());
        $this->assertEquals(100, $image->getHeight());
        $this->save($image, $this->directoryOutput . '/testWidthHeight.png');
    }

    /**
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadResourceException
     */
    public function testGetContent()
    {
        $image = new ImageResize($this->image);
        $imageContent = imagecreatefromstring($image->getContent());
        $this->assertEquals($image->getWidth(), imagesx($imageContent));
        $this->assertEquals($image->getHeight(), imagesy($imageContent));
    }


    /**
     * @throws ImageResizeBadResourceException
     */
    public function testSave()
    {
        $image = new ImageResize($this->image);
        $patchSave = __DIR__ . '/output/save.jpeg';
        try {
            $image->save($patchSave);
            $contentSave = file_get_contents($patchSave);
            $content = file_get_contents($this->path);
            $this->assertEquals($contentSave, $content);
        } catch (Exception $exception) {

        } finally {
            unlink($patchSave);
        }
    }

    /**
     * @throws ImageResizeBadResourceException
     */
    public function testCopyImage()
    {
        $imageResize = new ImageResize($this->image);
        $image = $imageResize->copyResource();
        $copy = $imageResize->copyResource();
        $this->assertEquals(imagesx($image), imagesx($copy));
        $this->assertEquals(imagesy($image), imagesy($copy));
    }

    /**
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     */
    public function testResize()
    {
        $image = new ImageResize($this->copyImage());
        $image->resize(50, 50);

        $copy = $image->copyResource();
        $this->assertEquals(50, imagesx($copy));
        $this->assertEquals(50, imagesy($copy));
        $this->save($image, $this->directoryOutput . '/testResize.png');

        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 49, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 49, 49)));

    }

    /**
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     */
    public function testResizeToWidth()
    {
        $image = new ImageResize($this->copyImage());
        $image->resizeToWidth(100);
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $this->save($image, $this->directoryOutput . '/testResizeToWidth.png');
    }

    /**
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     */
    public function testResizeToHeight()
    {
        $image = new ImageResize($this->copyImage());
        $image->resizeToHeight(75);
        $this->assertEquals(150, $image->getWidth());
        $this->assertEquals(75, $image->getHeight());
        $this->save($image, $this->directoryOutput . '/testResizeToHeight.png');

    }

    /**
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     */
    public function testResizeToBestFit()
    {
        $image = new ImageResize($this->copyImage());
        $image->resizeToBestFit(100, 100);
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $this->save($image, $this->directoryOutput . '/testResizeToBestFitWidth.png');


        $image = new ImageResize($this->copyImage());
        $image->resizeToBestFit(200, 50);
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $this->save($image, $this->directoryOutput . '/testResizeToBestFitHeight.png');
    }

    /**
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     */
    public function testResizeToWorstFit()
    {
        $image = new ImageResize($this->copyImage());
        $image->resizeToWorstFit(50, 50);
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $this->save($image, $this->directoryOutput . '/testResizeToWorstFitHeight.png');


        $image = new ImageResize($this->copyImage());
        $image->resizeToWorstFit(100, 25);
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $this->save($image, $this->directoryOutput . '/testResizeToWorstFitWidth.png');
    }

    /**
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     */
    public function testResizeToLongSide()
    {
        $image = new ImageResize($this->copyImage());
        $image->resizeToLongSide(100);
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $this->save($image, $this->directoryOutput . '/testResizeToLongSide.png');
    }

    /**
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     */
    public function testResizeToShortSide()
    {
        $image = new ImageResize($this->copyImage());
        $image->resizeToShortSide(50);
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $this->save($image, $this->directoryOutput . '/testResizeToShortSide.png');
    }

    /**
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadPositionException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     */
    public function testCropPosition()
    {

        // position center
        $image = new ImageResize($this->copyImage());
        $image->cropPosition(80, 80, ImageResize::POSITION_CENTER);
        $this->save($image, $this->directoryOutput . '/testResizeToShortSide.png');
        $this->assertEquals(80, $image->getWidth());
        $this->assertEquals(80, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 39, 39)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_LEFT), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_RIGHT), dechex(imagecolorat($resource, 79, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_LEFT), dechex(imagecolorat($resource, 0, 79)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_RIGHT), dechex(imagecolorat($resource, 79, 79)));


        // position left
        $image = new ImageResize($this->copyImage());
        $image->cropPosition(100, 50, ImageResize::POSITION_LEFT);
        $this->save($image, $this->directoryOutput . '/testCropPositionLeft.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 24)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_LEFT), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_LEFT), dechex(imagecolorat($resource, 0, 49)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 49)));


        // position right
        $image = new ImageResize($this->copyImage());
        $image->cropPosition(100, 50, ImageResize::POSITION_RIGHT);
        $this->save($image, $this->directoryOutput . '/testCropPositionRight.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 0, 24)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_RIGHT), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_RIGHT), dechex(imagecolorat($resource, 99, 49)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 0, 49)));


        // position top
        $image = new ImageResize($this->copyImage());
        $image->cropPosition(100, 50, ImageResize::POSITION_TOP);
        $this->save($image, $this->directoryOutput . '/testCropPositionTop.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 49, 49)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_LEFT), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_RIGHT), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 0, 49)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 49)));


        // position bottom
        $image = new ImageResize($this->copyImage());
        $image->cropPosition(100, 50, ImageResize::POSITION_BOTTOM);
        $this->save($image, $this->directoryOutput . '/testCropPositionBottom.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 49, 0)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_LEFT), dechex(imagecolorat($resource, 0, 49)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_RIGHT), dechex(imagecolorat($resource, 99, 49)));


        // position top left
        $image = new ImageResize($this->copyImage());
        $image->cropPosition(100, 50, ImageResize::POSITION_TOP_LEFT);
        $this->save($image, $this->directoryOutput . '/testCropPositionTopLeft.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 49)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 0, 49)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_LEFT), dechex(imagecolorat($resource, 24, 24)));


        // position top left
        $image = new ImageResize($this->copyImage());
        $image->cropPosition(100, 50, ImageResize::POSITION_TOP_LEFT);
        $this->save($image, $this->directoryOutput . '/testCropPositionTopLeft.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 49)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 0, 49)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_LEFT), dechex(imagecolorat($resource, 24, 24)));


        // position top right
        $image = new ImageResize($this->copyImage());
        $image->cropPosition(100, 50, ImageResize::POSITION_TOP_RIGHT);
        $this->save($image, $this->directoryOutput . '/testCropPositionTopRight.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 0, 49)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_RIGHT), dechex(imagecolorat($resource, 99 - 24, 24)));


        // position bottom right
        $image = new ImageResize($this->copyImage());
        $image->cropPosition(100, 50, ImageResize::POSITION_BOTTOM_RIGHT);
        $this->save($image, $this->directoryOutput . '/testCropPositionBottomRight.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 0, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 49)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_RIGHT), dechex(imagecolorat($resource, 99 - 24, 49 - 24)));


        // position bottom left
        $image = new ImageResize($this->copyImage());
        $image->cropPosition(100, 50, ImageResize::POSITION_BOTTOM_LEFT);
        $this->save($image, $this->directoryOutput . '/testCropPositionBottomLeft.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 49)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_LEFT), dechex(imagecolorat($resource, 24, 49 - 24)));
    }

    /**
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadPositionException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     */
    public function testResizeCover()
    {

        // position center
        $image = new ImageResize($this->copyImage());
        $image->resizeCover(50, 50, ImageResize::POSITION_CENTER);
        $this->save($image, $this->directoryOutput . '/testResizeCoverCenter.png');
        $this->assertEquals(50, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 24, 24)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_LEFT), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_RIGHT), dechex(imagecolorat($resource, 49, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_LEFT), dechex(imagecolorat($resource, 0, 49)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_RIGHT), dechex(imagecolorat($resource, 49, 49)));


        // position left
        $image = new ImageResize($this->copyImage());
        $image->resizeCover(50, 50, ImageResize::POSITION_LEFT);
        $this->save($image, $this->directoryOutput . '/testResizeCoverLeft.png');
        $this->assertEquals(50, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 49, 24)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 49)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_LEFT), dechex(imagecolorat($resource, 24, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_LEFT), dechex(imagecolorat($resource, 24, 49)));


        // position right
        $image = new ImageResize($this->copyImage());
        $image->resizeCover(50, 50, ImageResize::POSITION_RIGHT);
        $this->save($image, $this->directoryOutput . '/testResizeCoverRight.png');
        $this->assertEquals(50, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 0, 24)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 49, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 49, 49)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_RIGHT), dechex(imagecolorat($resource, 24, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_RIGHT), dechex(imagecolorat($resource, 24, 49)));


        // position top
        $image = new ImageResize($this->copyImageVertical());
        $image->resizeCover(50, 50, ImageResize::POSITION_TOP);
        $this->save($image, $this->directoryOutput . '/testResizeCoverTop.png');
        $this->assertEquals(50, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 24, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 49, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_LEFT), dechex(imagecolorat($resource, 0, 25)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_LEFT), dechex(imagecolorat($resource, 49, 25)));


        // position bottom
        $image = new ImageResize($this->copyImageVertical());
        $image->resizeCover(50, 50, ImageResize::POSITION_BOTTOM);
        $this->save($image, $this->directoryOutput . '/testResizeCoverBottom.png');
        $this->assertEquals(50, $image->getWidth());
        $this->assertEquals(50, $image->getHeight());
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 24, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 49, 49)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_RIGHT), dechex(imagecolorat($resource, 49, 24)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_RIGHT), dechex(imagecolorat($resource, 0, 24)));
    }

    /**
     * @throws ImageResizeBadColorException
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadPositionException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     */
    public function testResizeContain()
    {

        // position center
        $image = new ImageResize($this->copyImage());
        $image->resizeContain(100, 100, ImageResize::POSITION_CENTER, $image->createColorFromInt(static::COLOR_FILL));
        $this->save($image, $this->directoryOutput . '/testResizeContainCenter.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(100, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_FILL), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_FILL), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(static::COLOR_FILL), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_FILL), dechex(imagecolorat($resource, 99, 99)));

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 49, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 25)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 99 - 25)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 25)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 99 - 25)));


        // position top
        $image = new ImageResize($this->copyImage());
        $image->resizeContain(100, 100, ImageResize::POSITION_TOP, $image->createColorFromInt(static::COLOR_FILL));
        $this->save($image, $this->directoryOutput . '/testResizeContainTop.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(100, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_FILL), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_FILL), dechex(imagecolorat($resource, 99, 99)));

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 49, 24)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 49)));


        // position top
        $image = new ImageResize($this->copyImage());
        $image->resizeContain(100, 100, ImageResize::POSITION_BOTTOM, $image->createColorFromInt(static::COLOR_FILL));
        $this->save($image, $this->directoryOutput . '/testResizeContainBottom.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(100, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_FILL), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(static::COLOR_FILL), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 99)));

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 49, 24 + 50)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 50)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 50)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 99)));


        // position left
        $image = new ImageResize($this->copyImageVertical());
        $image->resizeContain(100, 100, ImageResize::POSITION_LEFT, $image->createColorFromInt(static::COLOR_FILL));
        $this->save($image, $this->directoryOutput . '/testResizeContainLeft.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(100, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(static::COLOR_FILL), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_FILL), dechex(imagecolorat($resource, 99, 99)));

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 24, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 49, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 49, 99)));


        // position right
        $image = new ImageResize($this->copyImageVertical());
        $image->resizeContain(100, 100, ImageResize::POSITION_RIGHT, $image->createColorFromInt(static::COLOR_FILL));
        $this->save($image, $this->directoryOutput . '/testResizeContainRight.png');
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(100, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_FILL), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_FILL), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 99)));

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 24 + 50, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 50, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 50, 99)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 99, 99)));
    }


    /**
     * @throws ImageResizeBadColorException
     * @throws ImageResizeBadResourceException
     */
    public function testCreateColorFromInt()
    {
        $image = new ImageResize($this->copyImage());
        $color = $image->createColorFromInt(0xFFEEAA);
        $this->assertEquals(0xFFEEAA, $color);
    }

    /**
     * @throws ImageResizeBadColorException
     * @throws ImageResizeBadResourceException
     */
    public function testCreateColorAlphaFromInt()
    {
        $image = new ImageResize($this->copyImage());
        $color = $image->createColorAlphaFromInt(0xAABBCC, 255);
        // 7F - Max alpha chanel GD image (127)
        $this->assertEquals(0x7FAABBCC, $color);
    }


    /**
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     */
    public function testCropEdge()
    {
        // default all side
        $image = new ImageResize($this->copyImage());
        $image->cropEdge(20);

        $this->save($image, $this->directoryOutput . '/testCropEdgeAll.png');

        $this->assertEquals(160, $image->getWidth());
        $this->assertEquals(60, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 79, 29)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_LEFT), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_RIGHT), dechex(imagecolorat($resource, 159, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_LEFT), dechex(imagecolorat($resource, 0, 59)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_RIGHT), dechex(imagecolorat($resource, 159, 59)));

        // crop left
        $image = new ImageResize($this->copyImage());
        $image->cropEdge(20, ImageResize::SIDE_LEFT);

        $this->save($image, $this->directoryOutput . '/testCropEdgeLeft.png');

        $this->assertEquals(180, $image->getWidth());
        $this->assertEquals(100, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 79, 49)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_LEFT), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 179, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_LEFT), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 179, 99)));

        // crop right
        $image = new ImageResize($this->copyImage());
        $image->cropEdge(20, ImageResize::SIDE_RIGHT);

        $this->save($image, $this->directoryOutput . '/testCropEdgeRight.png');

        $this->assertEquals(180, $image->getWidth());
        $this->assertEquals(100, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_RIGHT), dechex(imagecolorat($resource, 179, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_RIGHT), dechex(imagecolorat($resource, 179, 99)));

        // crop top
        $image = new ImageResize($this->copyImage());
        $image->cropEdge(20, ImageResize::SIDE_TOP);

        $this->save($image, $this->directoryOutput . '/testCropEdgeTop.png');

        $this->assertEquals(200, $image->getWidth());
        $this->assertEquals(80, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 29)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_LEFT), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_TOP_RIGHT), dechex(imagecolorat($resource, 199, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 79)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 199, 79)));


        // crop bottom
        $image = new ImageResize($this->copyImage());
        $image->cropEdge(20, ImageResize::SIDE_BOTTOM);

        $this->save($image, $this->directoryOutput . '/testCropEdgeBottom.png');

        $this->assertEquals(200, $image->getWidth());
        $this->assertEquals(80, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 199, 0)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_LEFT), dechex(imagecolorat($resource, 0, 79)));
        $this->assertEquals(dechex(static::COLOR_SIDE_BOTTOM_RIGHT), dechex(imagecolorat($resource, 199, 79)));
    }


    /**
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     */
    public function testAddBorder()
    {
        // default all side
        $image = new ImageResize($this->copyImage());
        $image->addBorder(10);

        $this->save($image, $this->directoryOutput . '/testAddBorderAll.png');

        $this->assertEquals(220, $image->getWidth());
        $this->assertEquals(120, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 109, 59)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 219, 0)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 0, 59)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 219, 59)));

        // border left
        $image = new ImageResize($this->copyImage());
        $image->addBorder(10, ImageResize::SIDE_LEFT);

        $this->save($image, $this->directoryOutput . '/testAddBorderLeft.png');

        $this->assertEquals(210, $image->getWidth());
        $this->assertEquals(100, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 109, 49)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 209, 0)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 209, 99)));

        // border right
        $image = new ImageResize($this->copyImage());
        $image->addBorder(10, ImageResize::SIDE_RIGHT);

        $this->save($image, $this->directoryOutput . '/testAddBorderRight.png');

        $this->assertEquals(210, $image->getWidth());
        $this->assertEquals(100, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 209, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 209, 99)));

        // border top
        $image = new ImageResize($this->copyImage());
        $image->addBorder(10, ImageResize::SIDE_TOP);

        $this->save($image, $this->directoryOutput . '/testAddBorderTop.png');

        $this->assertEquals(200, $image->getWidth());
        $this->assertEquals(110, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 59)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 199, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 109)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 199, 109)));


        // border bottom
        $image = new ImageResize($this->copyImage());
        $image->addBorder(10, ImageResize::SIDE_BOTTOM);

        $this->save($image, $this->directoryOutput . '/testAddBorderBottom.png');

        $this->assertEquals(200, $image->getWidth());
        $this->assertEquals(110, $image->getHeight());
        $resource = $image->copyResource();

        $this->assertEquals(dechex(static::COLOR_CENTER_LINE), dechex(imagecolorat($resource, 99, 49)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 199, 0)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 0, 109)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 199, 109)));
    }

    /**
     * @throws ImageResizeBadContentException
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     * @throws ImageResizeFileNotExistException
     * @throws ImageResizeNotSupportResetOrientationException
     */
    public function testResetOrientation()
    {
        // rotate 0
        $image = ImageResize::createFromPath($this->directory . '/f0.jpg');
        $widthStart = $image->getWidth();
        $heightStart = $image->getHeight();
        $xStart = $widthStart - 1;
        $yStart = $heightStart - 1;
        $image->resetOrientation();
        $resource = $image->copyResource();
        $this->save($image, $this->directoryOutput . '/testRotate0.png');

        $this->assertEquals($widthStart, $image->getWidth());
        $this->assertEquals($heightStart, $image->getHeight());

        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, 4, 4)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, $xStart, 4)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, 4, $yStart)));
        $this->assertEquals(dechex(0xFFFFFF), dechex(imagecolorat($resource, $xStart, $yStart)));

        // rotate 90
        $image = ImageResize::createFromPath($this->directory . '/f90.jpg');

        $this->assertEquals($heightStart, $image->getWidth());
        $this->assertEquals($widthStart, $image->getHeight());

        $resource = $image->copyResource();

        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, 4, 4)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, $yStart, 4)));
        $this->assertEquals(dechex(0xFFFFFF), dechex(imagecolorat($resource, 4, $xStart)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, $yStart, $xStart)));

        $image->resetOrientation();
        $resource = $image->copyResource();

        $this->save($image, $this->directoryOutput . '/testRotate90.png');

        $this->assertEquals($widthStart, $image->getWidth());
        $this->assertEquals($heightStart, $image->getHeight());

        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, 4, 4)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, $xStart, 4)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, 4, $yStart)));
        $this->assertEquals(dechex(0xFFFFFF), dechex(imagecolorat($resource, $xStart, $yStart)));

        // rotate 270
        $image = ImageResize::createFromPath($this->directory . '/f270.jpg');

        $this->assertEquals($heightStart, $image->getWidth());
        $this->assertEquals($widthStart, $image->getHeight());

        $resource = $image->copyResource();

        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, 4, 4)));
        $this->assertEquals(dechex(0xFFFFFF), dechex(imagecolorat($resource, $yStart, 4)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, 4, $xStart)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, $yStart, $xStart)));

        $image->resetOrientation();
        $resource = $image->copyResource();

        $this->save($image, $this->directoryOutput . '/testRotate270.png');

        $this->assertEquals($widthStart, $image->getWidth());
        $this->assertEquals($heightStart, $image->getHeight());

        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, 4, 4)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, $xStart, 4)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, 4, $yStart)));
        $this->assertEquals(dechex(0xFFFFFF), dechex(imagecolorat($resource, $xStart, $yStart)));

        // rotate 180
        $image = ImageResize::createFromPath($this->directory . '/f180.jpg');

        $this->assertEquals($widthStart, $image->getWidth());
        $this->assertEquals($heightStart, $image->getHeight());

        $resource = $image->copyResource();

        $this->assertEquals(dechex(0xFFFFFF), dechex(imagecolorat($resource, 4, 4)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, $xStart, 4)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, 4, $yStart)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, $xStart, $yStart)));

        $image->resetOrientation();
        $resource = $image->copyResource();

        $this->save($image, $this->directoryOutput . '/testRotate180.png');

        $this->assertEquals($widthStart, $image->getWidth());
        $this->assertEquals($heightStart, $image->getHeight());

        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, 4, 4)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, $xStart, 4)));
        $this->assertEquals(dechex(0xFE0000), dechex(imagecolorat($resource, 4, $yStart)));
        $this->assertEquals(dechex(0xFFFFFF), dechex(imagecolorat($resource, $xStart, $yStart)));
    }

    /**
     * @throws ImageResizeBadContentException
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadPositionException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     * @throws ImageResizeFileNotExistException
     * @throws \Uginroot\Exception\ImageResizeBadFitException
     */
    public function testWatermark()
    {
        $watermark = ImageResize::createFromPath($this->directory . '/watermark.png');

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_CENTER, 10);
        $this->save($image, $this->directoryOutput . '/testWatermarkCenter.png');
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($image->copyResource(), 99, 49)));

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_LEFT, 10);
        $this->save($image, $this->directoryOutput . '/testWatermarkLeft.png');
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($image->copyResource(), 14, 49)));

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_RIGHT, 10);
        $this->save($image, $this->directoryOutput . '/testWatermarkRight.png');
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($image->copyResource(), 184, 49)));

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_TOP, 10);
        $this->save($image, $this->directoryOutput . '/testWatermarkTop.png');
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($image->copyResource(), 99, 14)));

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_BOTTOM, 10);
        $this->save($image, $this->directoryOutput . '/testWatermarkBottom.png');
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($image->copyResource(), 99, 84)));

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_TOP_LEFT, 10);
        $this->save($image, $this->directoryOutput . '/testWatermarkTopLeft.png');
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($image->copyResource(), 14, 14)));

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_TOP_RIGHT, 10);
        $this->save($image, $this->directoryOutput . '/testWatermarkTopRight.png');
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($image->copyResource(), 184, 14)));

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_BOTTOM_RIGHT, 10);
        $this->save($image, $this->directoryOutput . '/testWatermarkBottomRight.png');
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($image->copyResource(), 184, 84)));

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_BOTTOM_LEFT, 10);
        $this->save($image, $this->directoryOutput . '/testWatermarkBottomLeft.png');
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($image->copyResource(), 14, 84)));


        $watermark = ImageResize::createFromPath($this->directory . '/watermarkBig.png');

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_CENTER, 10, ImageResize::FIT_RESIZE);
        $this->save($image, $this->directoryOutput . '/testWatermarkBigCenter.png');
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 199, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 199, 99)));

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_CENTER, 10, ImageResize::FIT_AS_IS);
        $this->save($image, $this->directoryOutput . '/testWatermarkBigAsIsCenter.png');
        $resource = $image->copyResource();
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 199, 0)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 199, 99)));

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_TOP, 10, ImageResize::FIT_AS_IS);
        $this->save($image, $this->directoryOutput . '/testWatermarkBigAsIsTop.png');
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 199, 0)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 199, 99)));

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_LEFT, 10, ImageResize::FIT_AS_IS);
        $this->save($image, $this->directoryOutput . '/testWatermarkBigAsIsLeft.png');
        $resource = $image->copyResource();
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 199, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 199, 99)));

        $image = new ImageResize($this->copyImage());
        $image->setWatermark($watermark, ImageResize::POSITION_RIGHT, 10, ImageResize::FIT_AS_IS);
        $this->save($image, $this->directoryOutput . '/testWatermarkBigAsIsRight.png');
        $resource = $image->copyResource();
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 0, 0)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 199, 0)));
        $this->assertEquals(dechex(0x000000), dechex(imagecolorat($resource, 0, 99)));
        $this->assertEquals(dechex(static::COLOR_CORNERS), dechex(imagecolorat($resource, 199, 99)));
    }

    /**
     * @throws ImageResizeBadContentException
     * @throws ImageResizeBadFormatException
     * @throws ImageResizeBadResourceException
     * @throws ImageResizeFileAlreadyExistException
     * @throws ImageResizeFileNotExistException
     * @throws ImageResizeBadPositionException
     * @throws \Uginroot\Exception\ImageResizeBadFitException
     */
    public function testDocs()
    {
        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->scale(50);
        $image->save(__DIR__ . '/../docs/result/scale.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->resize(100, 100);
        $image->save(__DIR__ . '/../docs/result/resize.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->resizeToHeight(100);
        $image->save(__DIR__ . '/../docs/result/resizeToHeight.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->resizeToWidth(100);
        $image->save(__DIR__ . '/../docs/result/resizeToWidth.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->resizeToLongSide(100);
        $image->save(__DIR__ . '/../docs/result/resizeToLongSide.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->resizeToShortSide(100);
        $image->save(__DIR__ . '/../docs/result/resizeToShortSide.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->resizeToBestFit(100, 100);
        $image->save(__DIR__ . '/../docs/result/resizeToBestFit.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->resizeToWorstFit(100, 100);
        $image->save(__DIR__ . '/../docs/result/resizeToWorstFit.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->resizeToWorstFit(100, 100);
        $image->save(__DIR__ . '/../docs/result/resizeToWorstFit.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->crop(0, 0, 100, 100);
        $image->save(__DIR__ . '/../docs/result/crop.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->cropPosition(100, 100, ImageResize::POSITION_CENTER);
        $image->save(__DIR__ . '/../docs/result/cropPosition.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->resizeCover(100, 100, ImageResize::POSITION_CENTER);
        $image->save(__DIR__ . '/../docs/result/resizeCover.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->resizeCover(100, 100, ImageResize::POSITION_CENTER);
        $image->save(__DIR__ . '/../docs/result/resizeCover.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->resizeContain(100, 100, ImageResize::POSITION_CENTER, 0x000000);
        $image->save(__DIR__ . '/../docs/result/resizeContain.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->cropEdge(50, ImageResize::SIDE_ALL);
        $image->save(__DIR__ . '/../docs/result/cropEdge.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->cropEdge(50, ImageResize::SIDE_ALL);
        $image->save(__DIR__ . '/../docs/result/cropEdge.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->addBorder(10);
        $image->save(__DIR__ . '/../docs/result/addBorder.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $image->change(function(&$resource){
            $resource = imagerotate($resource, 90, 0);
        });
        $image->save(__DIR__ . '/../docs/result/change.jpg', ImageResize::FORMAT_JPEG, true);


        $image = ImageResize::createFromPath(__DIR__ . '/../docs/horizontal.jpg');
        $watermark = ImageResize::createFromPath(__DIR__ . '/../docs/watermark.png');
        $image->setWatermark($watermark);
        $image->save(__DIR__ . '/../docs/result/setWatermark.jpg', ImageResize::FORMAT_JPEG, true);



        $this->assertTrue(true);
    }
}