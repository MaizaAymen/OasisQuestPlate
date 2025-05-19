<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\User;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class ProductController extends AbstractController
{
    #[Route('/products', name: 'admin_product_index')]
    public function index(ProductRepository $repo): Response
    {
        $products = $repo->findAll();
        return $this->render('admin/product/index.html.twig', ['products' => $products]);
    }

    #[Route('/products/new', name: 'admin_product_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/new.html.twig', ['productForm' => $form->createView()]);
    }

    #[Route('/products/{id}/edit', name: 'admin_product_edit')]
    public function edit(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/edit.html.twig', ['productForm' => $form->createView()]);
    }

    #[Route('/products/{id}/delete', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $em->remove($product);
            $em->flush();
        }
        return $this->redirectToRoute('admin_product_index');
    }
    
    #[Route('/users', name: 'admin_users_index')]
    public function users(UserRepository $userRepo): Response
    {
        $users = $userRepo->findAll();
        return $this->render('admin/users/index.html.twig', ['users' => $users]);
    }

    #[Route('/users/{id}', name: 'admin_user_details')]
    public function userDetails(int $id, UserRepository $userRepo): Response
    {
        $userData = $userRepo->findWithAllData($id);
        
        if (!$userData) {
            throw $this->createNotFoundException('User not found');
        }
        
        return $this->render('admin/users/details.html.twig', [
            'userData' => $userData
        ]);
    }
}