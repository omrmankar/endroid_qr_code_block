<?php

namespace Drupal\endroid_qr_code_block\Response;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Response which is returned as the QR code.
 *
 * @package Drupal\endroid_qr_code_block\Response
 */
class QRImageResponse extends Response {

  /**
   * Recourse with generated image.
   *
   * @var resource
   */
  protected $image;

  /**
   * Data to be used.
   *
   * @var data
   */
  private $data;

  /**
   * Logo width.
   *
   * @var logoWidth
   */
  private $logoWidth;

  /**
   * Logo Size.
   *
   * @var logoSize
   */
  private $logoSize;

  /**
   * Logo margin.
   *
   * @var logoMargin
   */
  private $logoMargin;

  /**
   * {@inheritdoc}
   */
  public function __construct($content, $logoWidth, $logoSize, $logoMargin, $status = 200, $headers = []) {
    parent::__construct(NULL, $status, $headers);
    $this->data = $content;
    $this->logoWidth = (NULL !== $logoWidth) ? (int) $logoWidth : 150;
    $this->logoSize = (NULL !== $logoSize) ? (int) $logoSize : 600;
    $this->logoMargin = (NULL !== $logoMargin) ? (int) $logoMargin : 10;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(Request $request) {
    return parent::prepare($request);
  }

  /**
   * {@inheritdoc}
   */
  public function sendHeaders() {
    $this->headers->set('content-type', 'image/jpeg');

    return parent::sendHeaders();
  }

  /**
   * {@inheritdoc}
   */
  public function sendContent() {
    $this->generateQrCode($this->data);
  }

  /**
   * Function generate QR code for the string or URL.
   *
   * @param string $string
   *   String to be converted to Qr Code.
   */
  private function generateQrCode(string $string = '') {
    $qrCode = new QrCode($string);
    $qrCode->setLogoWidth($this->logoWidth);
    $qrCode->setSize($this->logoSize);
    $qrCode->setMargin($this->logoMargin);
    $qrCode->setEncoding('UTF-8');
    $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));
    $qrCode->setForegroundColor([
      'r' => 0,
      'g' => 0,
      'b' => 0,
      'a' => 0,
    ]);
    $qrCode->setBackgroundColor([
      'r' => 255,
      'g' => 255,
      'b' => 255,
      'a' => 0,
    ]);
    $qrCode->setRoundBlockSize(TRUE);
    $qrCode->setValidateResult(FALSE);
    $response = new QrCodeResponse($qrCode);
    if ($response->isOk()) {
      $im = imagecreatefromstring($response->getContent());
      ob_start();
      imagejpeg($im);
      imagedestroy($im);
    }
  }

}
