<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\Categories;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BusinessController extends AbstractController
{
    public function authorized()
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('warning', "Not authorized to access that page!");
            return false;
        }
        else
            return true;
    }


    /**
     * @Route("/admin/list", name="admin/list")
     */
    public function index()
    {
        if ($this->authorized()) {
            $business = $this->getDoctrine()->getRepository(Business::class)->findBy([], ['title' => 'ASC']);
            return $this->render('business/index.html.twig', [
                'business' => $business
            ]);
        }
        else
            return $this->redirectToRoute('index');
    }

    /**
    * @Route("/admin/new", name="admin/new")
    */
    public function create()
    {
        if ($this->authorized()) {
            $categories = $this->getDoctrine()->getRepository(Categories::class)->findBy([],['name' => 'ASC']);
            return $this->render('business/create.html.twig', [
                'categories' => $categories
            ]);
        }
        else
            return $this->redirectToRoute('index');

    }

    /**
     * @Route("/create", name="add_business", methods={"post"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createBusiness(Request $request)
    {
        $errors = null;

        //Recuperamos o valor enviado através do método POST
        $title = $request->request->get('title');
        $phone = preg_replace( '/[^0-9]/', '', $request->request->get('phone'));
        $address = $request->request->get('address');
        $zipcode = preg_replace( '/[^0-9]/', '', $request->request->get('zipcode'));
        $state = $request->request->get('state');
        $city = $request->request->get('city');
        $description = $request->request->get('description');
        $categories = $request->request->get('category');


        //Se o valor enviado for nulo, redirecionamos para á página inicial mostrando a msg de erro
        if (empty($title)) {
            $errors .= "<p>Enter a valid <strong>title</strong></p>";
        }
        if (empty($phone) || strlen($phone) < 10) {
            $errors .= "<p>Enter a valid <strong>phone</strong></p>";
        }
        if (empty($address)) {
            $errors .= "<p>Enter a valid <strong>address</strong></p>";
        }
        if (empty($zipcode)) {
            $errors .= "<p>Enter a valid <strong>zipcode</strong></p>";
        }
        if (empty($state)) {
            $errors .= "<p>Enter a valid <strong>state</strong></p>";
        }
        if (empty($city)) {
            $errors .= "<p>Enter a valid <strong>city</strong></p>";
        }
        if (empty($description)) {
            $errors .= "<p>Enter a valid <strong>description</strong></p>";
        }
        if (empty($categories)) {
            $errors .= "<p>Select at least one <strong>category</strong></p>";
        }

        if ($errors) {
            $this->addFlash(
                'warning',
                $errors
            );
        }
        else {
            try {
                //Criamos o gerenciador da entidade
                $entityManager = $this->getDoctrine()->getManager();

                //Criamos o objeto e setamos seus valores
                $business = new Business();
                $business->setTitle($title);
                $business->setPhone($phone);
                $business->setAddress($address);
                $business->setZipcode($zipcode);
                $business->setCity($city);
                $business->setState($state);
                $business->setDescription($description);

                foreach ($categories as $index => $catSelected) {
                    $cat = $this->getDoctrine()->getRepository(Categories::class)->find($catSelected);
                    $business->addCategory($cat);
                }

                //Persistimos as informação na base de dados
                $entityManager->persist($business);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'Business Created!!'
                );
            }
            catch(DBALException $e){
                $this->addFlash(
                    'error',
                    $e->getMessage()
                );
            }
            catch(\Exception $e){
                $this->addFlash(
                    'error',
                    $e->getMessage()
                );
            }
        }

        //TODO: Ao redirecionar, enviar os parametros cadastrados para não precisar digitar novamente os valores
        return $this->redirectToRoute('admin/new');
    }

    /**
     * @Route("/admin/delete/{id?}", name="delete")
     */
    public function delete($id)
    {
        if ($this->authorized()) {
            try {
                if (empty($id)) {
                    $this->addFlash(
                        'warning',
                        'Select one business to delete!'
                    );
                }
                else {
                    $entityManager = $this->getDoctrine()->getManager();
                    $business = $entityManager->getRepository(Business::class)->find($id);
                    if (empty($business)) {
                        $this->addFlash(
                            'warning',
                            'No business found!'
                        );
                    }
                    else {
                        $entityManager->remove($business);
                        $entityManager->flush();
                        $this->addFlash(
                            'success',
                            'Business removed!'
                        );
                    }
                }
            }
            catch(DBALException $e){
                $this->addFlash(
                    'error',
                    $e->getMessage()
                );
            }
            catch(\Exception $e){
                $this->addFlash(
                    'error',
                    $e->getMessage()
                );
            }

            return $this->redirectToRoute('admin/list');
        }
        else
            return $this->redirectToRoute('index');
    }
}
