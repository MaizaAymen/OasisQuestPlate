<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;
use App\Entity\Product;

final class ShopController extends AbstractController
{
    #[Route('/shop', name: 'app_shop')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('shop/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/shop/product/{id}', name: 'app_product_show')]
    public function show(Product $product): Response
    {
        return $this->render('shop/show.html.twig', [
            'product' => $product,
        ]);
    }
}
