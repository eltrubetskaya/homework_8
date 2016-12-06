<?php

namespace Veta\HomeworkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Veta\HomeworkBundle\Twig\VetaHomeworkExtension;

class PostController extends Controller
{
    /**
     * @var array
     */
    public $data = [];

    /**
     * @var array
     */
    public $data_begin = [];

    /**
     * @var array
     */
    public $data_end = [];

    /**
     * @var bool
     */
    public $status;

    /**
     * PostController constructor.
     */
    public function __construct()
    {
        $data_begin = [
            ['id' =>'1', 'text' => 'Test template engine for PHP', 'date' => mktime()],
            ['id' =>'2', 'text' => 'Twig Documentation', 'date' => mktime()],
        ];
        $this->data_begin = $data_begin;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getData(Request $request)
    {
        if ($request->hasSession() === true) {
            if ($this->isStatus() === false) {
                $data =  $this->data_begin;
                $session = $request->getSession();
                $session->set('data', $this->data_begin);
                $session->set('status', true);
                $this->setStatus($session->get('status'));
            } else {
                $session = $request->getSession();
                $data = $session->get('data');
            }
            return $data;
        }
    }

    /**
     * @Route("/post", name="post")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        if (!$request->getSession()->get('status')) {
            $session = new Session();
            $session->set('status', false);
            $this->setStatus($session->get('status'));
        }
        $this->data = $this->getData($request);
        return $this->render('VetaHomeworkBundle:Post:index.html.twig', [
            'data' => $this->data,

        ]);
    }

    /**
     * @Route("/post/{slug}", name="post_view", requirements={"slug": "\d+"})
     * @Method("GET")
     *
     * @param Request $request
     * @return Response
     */
    public function viewAction(Request $request)
    {
        if ('GET' === $request->getMethod()) {
            $id = $request->get('slug');
            return $this->forward('VetaHomeworkBundle:Post:getData', [
                'id' => $id,
                'action' => 'view',

            ]);
        }
    }

    /**
     * @param $id
     * @return Response
     */
    public function getDataAction($id, $action, Request $request)
    {
        $this->data = $this->getData($request);
        foreach ($this->data as $data) {
            if ($data['id'] == $id) {
                return $this->render("VetaHomeworkBundle:Post:$action.html.twig", [
                    'data' => $data,

                ]);
            }
        }

        return $this->render('VetaHomeworkBundle:Post:index.html.twig', [
            'data' => $this->data,

        ]);
    }

    /**
     * Create data Post
     *
     * @Route("/post", name="post_create")
     * @Method("POST")
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (isset($_POST['id'])) {
            $session = $request->getSession();
            $data = $session->get('data');

            $new_data['id'] = $_POST['id'];
            $new_data['text'] = $_POST['text'];
            $new_data['date'] = $_POST['post_date'];

            $this->data_end = $data;
            $this->data_end[] = $new_data;

            $session->remove("data");
            $session->set('data', $this->data_end);

            $this->data = $this->getData($request);
            return $this->render('VetaHomeworkBundle:Post:index.html.twig', [
                'data' => $this->data,
                'message' => 'Post create successful! ',

            ]);
        }

        return $this->render('VetaHomeworkBundle:Post:form.html.twig', [
           'date' => mktime(),

       ]);
    }

    /**
     * Edit data Post
     *
     * @Route("/post/{slug}", name="post_edit", requirements={"slug": "\d+"})
     * @Method("PUT")
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function editAction(Request $request)
    {
        $postId = $request->attributes->get('slug');
        $this->data = $this->getData($request);

        $put_str = $request->getContent();
        parse_str($put_str, $_PUT);
        $put = [];

        foreach ($this->data as $data => $d) {
            if ($d['id'] == $postId) {
                $put['id'] = $d['id'];
                $put['text'] = $_PUT['text'];
                $put['date'] = $_PUT['post_date'];
                $this->data_end[] = $put;
            } else {
                $this->data_end[] = $d;
            }
        }
        $session = $request->getSession();
        $session->remove("data");
        $session->set('data', $this->data_end);

        return $this->forward('VetaHomeworkBundle:Post:getData', [
            'id' => $postId,
            'action' => 'view',

        ]);
    }

    /**
     * Delete Post
     *
     * @Route("/post/{slug}", name="post_delete", requirements={"slug": "\d+"})
     * @Method("DELETE")
     *
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $postId = $request->attributes->get('slug');
        $this->data = $this->getData($request);
        foreach ($this->data as $data => $d) {
            if ($d['id'] != $postId) {
                $this->data_end[] = $d;
            }
        }
        $session = $request->getSession();
        $session->remove("data");
        $session->set('data', $this->data_end);
        $this->data = $this->getData($request);
        return $this->render('VetaHomeworkBundle:Post:index.html.twig', [
            'data' => $this->data,
            'message' => 'Delete post with id '.$postId,

        ]);
    }

    /**
     * @return mixed
     */
    public function isStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
