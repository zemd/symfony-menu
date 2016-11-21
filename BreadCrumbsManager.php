<?php

namespace Zemd\Component\Menu;

use Doctrine\Common\Annotations\Reader;
use Zemd\Component\Menu\Annotations\Breadcrumbs;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class BreadCrumbsManager implements ContainerAwareInterface
{
  use ContainerAwareTrait;

  /** @var Router */
  protected $router;

  /** @var array */
  protected $tree = [];

  /** @var Reader */
  protected $reader;

  protected $requestStack;

  public function __construct(Router $router, Reader $reader, RequestStack $requestStack) {
    $this->router = $router;
    $this->reader = $reader;
    $this->requestStack = $requestStack;

    $this->buildTree();
  }

  protected function buildTree() {
    /** @var RouteCollection $routes */
    $routes = $this->router->getRouteCollection();

    /** @var Route $route */
    foreach ($routes->all() as $name => $route) {
      $path = $route->getPath();
      if (strlen($path) > 1 && $path[strlen($path) - 1] === '/') {
        $path = substr($path, 0, strlen($path) - 1);
      }
      $this->addPath($path, $name, $route);
    }
  }

  /**
   * @param string $path
   * @param string|null $name
   * @param Route|null $route
   * @return int
   */
  protected function addPath($path, $name = null, Route $route = null) {
    $key = $this->findNode($path);

    $controller = $route !== null ? $route->getDefault('_controller') : null;

    $this->setKeyValue($key, 'route_name', $name);
    $this->setKeyValue($key, '_controller', $controller);
    $this->setKeyValue($key, 'path', $path);
    $this->setKeyValue($key, 'route', $route);

    if ($path !== '/') {
      $this->tree[$key]['parent'] = $this->addPath(dirname($path));
    } else {
      $this->tree[$key]['parent'] = null;
    }

    return $key;
  }

  protected function setKeyValue($key, $subkey, $value) {
    if (isset($this->tree[$key][$subkey])) {
      return;
    }
    $this->tree[$key][$subkey] = $value;
  }

  /**
   * @param string $path
   * @return int
   */
  protected function findNode($path) {
    $key = array_search($path, array_column($this->tree, 'path'), true);
    if ($key === false) {
      $key = array_push($this->tree, ['path' => $path]) - 1;
    }

    return $key;
  }

  /**
   * if null is passed, route receives from master $request
   * @param null|string $currentRoute
   * @return NodeInterface[]
   */
  public function getBreadcrumbs($currentRoute = null) {
    if (null === $currentRoute) {
      $currentRoute = $this->requestStack->getMasterRequest()->get("_route");
    }
    $key = array_search($currentRoute, array_column($this->tree, 'route_name'), true);

    return $this->buildChain($key);
  }

  /**
   * @param string $key
   * @return NodeInterface[]
   */
  protected function buildChain($key) {
    $result = [];
    $result[] = $node = $this->getNode($key);
    while ($node->getParent() !== null) {
      if ($node->isRoot()) {
        break;
      }

      $node = $node->getParent();

      if ($node->isSkip()) {
        continue;
      }

      if (!empty($node->getGeneratorServiceId())) {
        $newChain = $this->generateChainWith($node->getGeneratorServiceId(), $node);
        if (is_array($newChain)) {
          $result = array_merge($result, $newChain);
        } else {
          $result[] = $newChain;
        }

        continue;
      }

      $result[] = $node;
    }

    return array_reverse($result);
  }

  /**
   * @param $serviceId
   * @param NodeInterface $node
   * @return NodeInterface|NodeInterface[]
   * @throws \Exception
   */
  protected function generateChainWith($serviceId, NodeInterface $node) {
    $generator = $this->container->get($serviceId);
    if (!($generator instanceof ChainGeneratorInterface)) {
      throw new \Exception(sprintf("Generator #%s MUST be an instance of ChainGeneratorInterface", $serviceId));
    }

    return $generator->getChain($node);
  }

  /**
   * @param $key
   * @return null|BreadcrumbNode
   */
  protected function getNode($key) {
    if (null === $key) {
      return null;
    }

    $node = $this->tree[$key];

    if (is_null($node['_controller']) ||
      is_null($node['route']) ||
      is_null($node['route_name']) ||
      strpos($node['_controller'], "::") === false
    ) {
      return $this->getNode($node['parent']);
    }

    list($klass, $method) = explode('::', $node['_controller']);

    /** @var Breadcrumbs $annotation */
    $annotation = $this->reader->getMethodAnnotation(new \ReflectionMethod($klass, $method), Breadcrumbs::class);

    $instance = new BreadcrumbNode($this->getNode($node['parent']), $node['route'], $node['route_name'], $node['path']);
    if ($annotation) {
      $instance->setSkip($annotation->skip);
      $instance->setGeneratorServiceId($annotation->generator);
      $instance->setRoot($annotation->root);
    }
    $instance->setPathParams($this->extractPathParams($instance->getRoute()->getPath()));

    return $instance;
  }

  /**
   * @param string $path
   * @return array
   */
  protected function extractPathParams($path) {
    preg_match_all('#\{\w+\}#', $path, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
    $result = [];
    $curRequest = $this->requestStack->getCurrentRequest();
    foreach ($matches as $match) {
      $varName = substr($match[0][0], 1, -1);
      $result[$varName] = $curRequest->get($varName);
    }

    return $result;
  }
}
