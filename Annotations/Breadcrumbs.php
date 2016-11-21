<?php

namespace Zemd\Component\Menu\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Breadcrumbs extends Annotation
{
  /**
   * @var bool
   */
  public $skip = false;

  /**
   * @var bool
   */
  public $root = false;

  /**
   * @var string
   */
  public $generator;

  public function isSkip() {
    return $this->skip;
  }

  /**
   * @return string
   */
  public function getGenerator() {
    return $this->generator;
  }

  /**
   * @return boolean
   */
  public function isRoot() {
    return $this->root;
  }
}
