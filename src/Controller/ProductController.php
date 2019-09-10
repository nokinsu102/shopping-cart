<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="product_index", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * @Route("/cart/{id}", name="product_cart", methods={"GET", "POST"})
     */
    public function addToCart(Product $product)
     {
        $getCart = $this->session->get('cart', []);
        $total = 0;

        if (isset($getCart[$product->getId()])) {
            $getCart[$product->getId()]['amount']++;
        } else {
            $getCart[$product->getId()] = array(
                'amount' => 1,
                'id' => $product->getId(),
            );
        }
        $this->session->set('cart', $getCart);

        foreach($getCart as $id => $details)
        {
            $total = $total + ($getCart[$id]['amount'] * $getCart[$id]['price']);
        }

        $this->session->set('total', $total);

        return $this->render('product/cart.html.twig', [
            'amount' => $getCart[$product->getId()]['amount'],
            'total' => $total,
            'cart' => $getCart
        ]);
    }

    /**
     * @Route("/checkout", name="product_checkout", methods={"GET", "POST"})
     */
    public function checkOut(Product $product)
    {
        $getCart = $this->session->get('cart', []);
        $getTotal = $this->session->get('total', []);
        
        return $this->render('product/checkout.html.twig', [
            'amount' => $getCart[$product->getId()]['amount'],
            'total' => $getTotal,
            'cart' => $getCart
        ]);
    }
}

