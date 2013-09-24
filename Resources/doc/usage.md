Sonatra BlockBundle Usage
=========================

## Prerequisites

[Installation and Configuration](index.md)

## Usage

For rendering automatically all javascripts and stylesheets of all block, you must used the twig functions:

- Block view: block_assets_widget()
- Stylesheets: block_global_stylesheets() in global html stylesheet
- Javascripts: block_global_javascripts() in global html javascript

If you do not want to add the javascripts and stylesheet of a block, you must used the twig function `block_widget`.

Controller:

``` php
<?php

namespace Acme\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/blog/post/create", name="blog_post_create")
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $data = new Post();
        $data->setTitle("Foo title");
        $data->setBody("Bar content.");
        $data->setPostedAt(new \Datetime());

        $panel = $this->get('sonatra.panel.factory')->createBuilder('block', $data, array(
                'block_name'     => 'post_create',
        ))
        ->add('title',  'text', array('label' => 'The title'))
        ->add('body',  'textarea', array('label' => 'The body'))
        ->add('postedAt', 'datetime', array('label' => 'Posted at'))
        ->getBlock();

        return $this->render("AcmeBlogBundle:Default:post_create.html.twig", array('block' => $block->createView()));
    }
}

```

Twig:

``` html
<html>
    <head>
        <style type="text/css">
        {{ block_global_stylesheets() }}
        </style>
    </head>
    <body>
        {{ block_assets_widget(block) }}

        <script type="text/javascript">
            $( document ).ready( function() {
                {{ block_global_javascripts() }}
            });
        </script>
    </body>
<html>
```
