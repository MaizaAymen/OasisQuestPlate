<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\CheckoutTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
    public function index(Request $request, RequestStack $requestStack, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CheckoutTypeForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $session = $requestStack->getSession();
            $cart = $session->get('cart', []);

            $order = new Order();
            $order->setUser($this->getUser());
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setItems($cart);
            $order->setAddress($data['address']);
            $order->setCity($data['city']);
            $order->setPostalCode($data['postalCode']);

            $em->persist($order);
            $em->flush();

            $session->remove('cart');

            return $this->redirectToRoute('app_checkout_confirm');
        }

        return $this->render('checkout/index.html.twig', ['checkoutForm' => $form->createView()]);
    }

    #[Route('/checkout/confirm', name: 'app_checkout_confirm')]
    public function confirm(): Response
    {
        return $this->render('checkout/confirm.html.twig');
    }
}