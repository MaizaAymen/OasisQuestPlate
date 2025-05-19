<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(RequestStack $requestStack, ProductRepository $productRepository): Response
    {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);
        $items = [];
        $total = 0;
        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if (!$product) continue;
            $subtotal = $product->getPrice() * $quantity;
            $items[] = ['product' => $product, 'quantity' => $quantity, 'subtotal' => $subtotal];
            $total += $subtotal;
        }
        return $this->render('cart/index.html.twig', ['items' => $items, 'total' => $total]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function add($id, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + 1;
        $session->set('cart', $cart);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function remove($id, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
        }
        return $this->redirectToRoute('app_cart');
    }
}
