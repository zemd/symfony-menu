<?php

namespace Zemd\Component\Twig\Extension;

use Zemd\Component\Menu\BreadcrumbNode;
use Zemd\Component\Menu\BreadCrumbsManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig_SimpleFunction;

class RouteChecker extends \Twig_Extension
{
  /** @var RequestStack */
  protected $requestStack;

  /** @var BreadCrumbsManager */
  protected $breadCrumbsManager;

  public function __construct(RequestStack $requestStack, BreadCrumbsManager $breadCrumbsManager) {
    $this->requestStack = $requestStack;
    $this->breadCrumbsManager = $breadCrumbsManager;
  }

  /**
   * Returns the name of the extension.
   *
   * @return string The extension name
   */
  public function getName() {
    return 'route_checker';
  }

  /**
   * Returns a list of functions to add to the existing list.
   *
   * @return Twig_SimpleFunction[]
   */
  public function getFunctions() {
    return [
      new Twig_SimpleFunction('is_route_active', [$this, 'isRouteActive']),
      new Twig_SimpleFunction('is_route_chain_active', [$this, 'isRouteChainActive'])
    ];
  }

  /**
   * @param string $route
   * @return bool
   */
  public function isRouteActive($route) {
    return $this->requestStack->getCurrentRequest()->get('_route') === $route;
  }

  /**
   * @param string $curRoute
   * @return bool
   */
  public function isRouteChainActive($curRoute) {
    // ---x(a)---x(a)---A(a)---C---> current is not active
    // ---x(a)---x(a)---CA(a)--x---> current is active
    // ---x(a)---C(a)---x(a)---A---> current is active

    $activeRoute = $this->requestStack->getMasterRequest()->get('_route');
    if ($activeRoute === $curRoute) {
      return true;
    }

    /** @var BreadcrumbNode[] $nodes */
    $nodes = $this->breadCrumbsManager->getBreadcrumbs($activeRoute);
    $routes = array_map(function (BreadcrumbNode $node) {
      return $node->getRouteName();
    }, $nodes);

    // if current route is not in active chain - no need to check more
    if (!in_array($curRoute, $routes)) {
      return false;
    }

    $active = false;
    foreach ($nodes as $node) {

      // (1) if active was found before current route was reached then FALSE
      // (2) if active was not found before current route was reached then TRUE
      if ($node->getRouteName() === $curRoute) {
        if (!$active) {
          return true;
        }

        return false;
      }

      if ($node->getRouteName() === $activeRoute) {
        $active = true;
      }
    }

    return $active;
  }
}
