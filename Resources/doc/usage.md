Usage
=====

Controller:

```php
namespace Acme\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Fxp\Component\Block\Extension\Core\Type\DateTimeType;
use Fxp\Component\Block\Extension\Core\Type\TextType;
use Fxp\Component\Block\Extension\Core\Type\TextareaType;
use Symfony\Component\Routing\Annotation\Route;

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

        $panel = $this->get('fxp.panel.factory')->createBuilder('block', $data, array(
                'block_name'     => 'post_create',
        ))
        ->add('title',  TextType::class, array('label' => 'The title'))
        ->add('body',  TextareaType::class, array('label' => 'The body'))
        ->add('postedAt', DateTimeType::class, array('label' => 'Posted at'))
        ->getBlock();

        return $this->render("@AcmeBlog/Default/post_create.html.twig", array('block' => $block->createView()));
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
