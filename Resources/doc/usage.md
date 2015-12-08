Usage
=====

Controller:

```php
namespace Acme\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sonatra\Bundle\BlockBundle\Block\Extension\Core\Type\DateTimeType;
use Sonatra\Bundle\BlockBundle\Block\Extension\Core\Type\TextType;
use Sonatra\Bundle\BlockBundle\Block\Extension\Core\Type\TextareaType;
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
        ->add('title',  TextType::class, array('label' => 'The title'))
        ->add('body',  TextareaType::class, array('label' => 'The body'))
        ->add('postedAt', DateTimeType::class, array('label' => 'Posted at'))
        ->getBlock();

        return $this->render("AcmeBlogBundle:Default:post_create.html.twig", array('block' => $block->createView()));
    }
}
```

Twig:

```html+jinja
<html>
    <body>
        {{ block_widget(block) }}
    </body>
<html>
```
