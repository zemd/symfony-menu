# Symfony Menu

> Very lightweight but powerful breadcrumbs builder for symfony

## Installation

```sh
composer require zemd/symfony-menu
```

## Usage

This component build breadcrumb path leveraging standard symfony router. If you want to skip or modify some part of the
 path feel free to use **@Breadcrumbs** annotation.

For instance we have next controller:
```php
class MyController {
  
  /**
   * This route should be skipped from menu chain
   * 
   * @Breadcrumbs(skip=true)
   */
  public function myActionIwantToSkip() {}
  
  /**
   * This route should be in the root of menu tree 
   *
   * @Route("/dashboard", name="dashboard")
   * @Breadcrumbs(root=true)
   */
  public function dashboardAction() {}
  
  /**
   * This route should be added automatically into menu chain 
   *
   * @Route("/dashboard/graphs")
   */
  public function viewMoreGraphsAction() {}
}
```

Let's now share breadcrumbs into the view by using twig globals as example:

```php
class BreadcrumbsGlobalExtension extends \Twig_Extension implements Twig_Extension_GlobalsInterface
{
    const NAME = 'zemd_breadcrumbs_extension';

    /** @var BreadCrumbsManager */
    protected $breadcrumbsManager;

    public function __construct(BreadCrumbsManager $breadcrumbsManager) {
        $this->breadcrumbsManager = $breadcrumbsManager;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName() {
        return self::NAME;
    }

    public function getGlobals() {
        return [
            'zemd_breadcrumbs' => $this->breadcrumbsManager->getBreadcrumbs()
        ];
    }
}
```

```yml
services:
  zemd.breadcrumbs_manager:
    class: Zemd\Component\Menu\BreadCrumbsManager
    arguments: ["@router", "@annotation_reader", "@request_stack"]
    calls:
      - [setContainer, ["@service_container"]]
      
  zemd.breadcrumbs.twig_extension:
    class: Path\To\Your\BreadcrumbsGlobalExtension
    public: false
    arguments: ["@zemd.breadcrumbs_manager"]
    tags:
      - { name: twig.extension }
      
  zemd.router_checker.twig_extension:
    class: Zemd\Component\Menu\Twig\Extension\RouteChecker
    public: false
    arguments: ["@request_stack", "@zemd.breadcrumbs_manager"]
    tags:
      - { name: twig.extension }
```

Now we can show our menu in the header and style or translate menu items as we want:

```twig
<nav id="Nav-bread" class="navbar navbar-breadcrumbs clearfix" role="navigation">
  {% for node in zemd_breadcrumbs %}
      <div class="navbar__item{% if (is_route_active(node.routeName)) %} active{% endif %}">
          <a href="{{ path(node.routeName, node.pathParams) }}">
              <span>{{ node.routeName|trans({}, "breadcrumbs") }}</span>
          </a>
      </div>
  {% endfor %}
</nav>
```

## Advanced usage

// TODO: Example for generator

## License

Symfony Menu is released under the MIT license.

## Donate

[![](https://img.shields.io/badge/patreon-donate-yellow.svg)](https://www.patreon.com/red_rabbit)
[![](https://img.shields.io/badge/flattr-donate-yellow.svg)](https://flattr.com/profile/red_rabbit)

