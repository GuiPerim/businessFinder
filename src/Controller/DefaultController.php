<?php

namespace App\Controller;

use App\Entity\Business;
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

        if (empty($search)) {
            $errors .= "Enter a valid <strong>search</strong>";
        }

        if (!$errors) {

            //Realizamos a busca com base no valor informado
            $result = $businessRepository->findBySearch($search);
            if (!empty($result)) {
                $arr = array();
                $catArr = array();

                //Percorremos o resultado e montamos o array conforme iremos exibir
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

        //Se apresentar algum erro, redirecionamos para á página inicial mostrando a msg de erro
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

    /**
     * @Route("/detail/{id}", name="detail")
     */
    public function detail(BusinessRepository $businessRepository, $id)
    {
        $errors = null;

        if (empty($id)) {
            $errors .= "Enter a valid <strong>search</strong>";
        }

        if (!$errors) {
            //Realizamos a busca com base no valor informado
            $result = $businessRepository->findById($id);
            if (!empty($result)) {
                $arr = array();
                $catArr = array();

                //Percorremos o resultado e montamos o array conforme iremos exibir
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

        //Se apresentar algum erro, redirecionamos para á página inicial mostrando a msg de erro
        if ($errors) {
            $this->addFlash('warning', $errors);
            return $this->redirectToRoute('index');
        }
        else {
            return $this->render('default/detail.html.twig', [
                'business' => $arr,
                'colors'    => array("primary", "secondary", "success", "danger", "warning", "info", "dark")
            ]);
        }
    }
}
