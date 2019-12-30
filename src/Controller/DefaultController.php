<?php

namespace App\Controller;

use App\Repository\BusinessRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /**
     * @Route("/search", name="search_business", methods={"post"})
     */
    public function searchBusiness(Request $request, BusinessRepository $businessRepository)
    {
        $errors = null;

        //Recuperamos o valor enviado através do método POST
        $search = $request->request->get('search');

        //Se o valor enviado for nulo, redirecionamos para á página inicial mostrando a msg de erro
        if (empty($search)) {
            $errors .= "Enter a valid <strong>search</strong>";
        }

        if (!$errors) {
            $result = $businessRepository->findBySearch($search);
            if (!empty($result)) {

                $arr = array();
                $catArr = array();
                foreach ($result as $key => $item) {
                    $arr[$item['id']] = $item;
                    $catArr[$item['id']][$key] = $item['category'];
                    $arr[$item['id']]['categories'] = $catArr[$item['id']];
                }
            }
            else {
                $errors .= "No results found";
            }
        }

        if ($errors) {
            $this->addFlash('warning', $errors);
            return $this->redirectToRoute('index');
        }
        else {
            return $this->render('default/list.html.twig', [
                'search'   => ucfirst(strtolower($search)),
                'business' => $arr,
                'colors'    => array("primary", "secondary", "success", "danger", "warning", "info", "dark")
            ]);
        }
    }

//    function pr($var) { print '<pre>'; print_r($var); print '</pre>'; }
}
