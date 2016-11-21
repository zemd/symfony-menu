<?php

namespace Zemd\Component\Menu;

use Symfony\Component\Routing\Route;

class BreadcrumbNode implements NodeInterface
{
  /**
   * @var NodeInterface
   */
  protected $parent;

  /**
   * @var bool
   */
  protected $skip = false;

  /**
   * @var Route
   */
  protected $route;

  /**
   * @var string
   */
  protected $path;

  /**
   * @var string
   */
  protected $routeName;

  /**
   * @var bool
   */
  protected $root = false;

  /**
   * @var string
   */
  protected $generatorServiceId;

  /** @var array */
  protected $pathParams = [];

  public function __construct(NodeInterface $parent = null, Route $route, $routeName, $path) {
    $this->parent = $parent;
    $this->route = $route;
    $this->routeName = $routeName;
    $this->path = $path;
  }

  /**
   * @return NodeInterface
   */
  public function getParent() {
    return $this->parent;
  }

  /**
   * @return bool
   */
  public function isSkip() {
    return $this->skip;
  }

  /**
   * @return string|Route
   */
  public function getRoute() {
    return $this->route;
  }

  /**
   * @param boolean $skip
   */
  public function setSkip($skip) {
    $this->skip = $skip;
  }

  /**
   * @param NodeInterface $parent
   */
  public function setParent($parent) {
    $this->parent = $parent;
  }

  /**
   * @return boolean
   */
  public function isRoot() {
    return $this->root;
  }

  /**
   * @param boolean $root
   */
  public function setRoot($root) {
    $this->root = $root;
  }

  /**
   * @return string
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * @return string
   */
  public function getRouteName() {
    return $this->routeName;
  }

  /**
   * @return string
   */
  public function getGeneratorServiceId() {
    return $this->generatorServiceId;
  }

  /**
   * @param string $generatorServiceId
   */
  public function setGeneratorServiceId($generatorServiceId) {
    $this->generatorServiceId = $generatorServiceId;
  }

  /**
   * @return array
   */
  public function getPathParams() {
    return $this->pathParams;
  }

  /**
   * @param array $pathParams
   */
  public function setPathParams(array $pathParams) {
    $this->pathParams = $pathParams;
  }
}
