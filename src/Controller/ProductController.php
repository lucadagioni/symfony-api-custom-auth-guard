<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends ApiController
{
    /**
     * @Route("/api/product", name="create_product", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function createProduct(Request $request): Response
    {
        $params = $request->request;
        // you can fetch the EntityManager via $this->getDoctrine()
        // or you can add an argument to the action: createProduct(EntityManagerInterface $entityManager)
        $entityManager = $this->getDoctrine()->getManager();

        $product = new Product();
        $product->setName($params->get('name'));
        $product->setPrice($params->get('price'));
        $product->setDescription($params->get('description'));

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new JsonResponse(['data' => 'Saved new product with id ' . $product->getId()], $this->statusCode);
    }

    /**
     * @Route("/api/product/{id}", name="product_show", methods="GET")
     * @param $id
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function show($id, ProductRepository $productRepository)
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }

        return new JsonResponse(['data' => 'Check out this great product: ' . $product->getName() . ' - ' . $product->getDescription()], $this->statusCode);
    }
}
