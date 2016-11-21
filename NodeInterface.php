<?php

namespace Zemd\Component\Menu;

use Symfony\Component\Routing\Route;

interface NodeInterface
{
  /**
   * @return NodeInterface
   */
  public function getParent();

  /**
   * @return bool
   */
  public function isSkip();

  /**
   * @return string|Route
   */
  public function getRoute();

  /**
   * @return string
   */
  public function getRouteName();

  /**
   * @return bool
   */
  public function isRoot();

  /**
   * @return string
   */
  public function getGeneratorServiceId();

  /**
   * @return array
   */
  public function getPathParams();
}
