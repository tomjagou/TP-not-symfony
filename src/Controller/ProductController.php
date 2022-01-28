<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductRepository;
use App\Entity\Product;
use DateTime;
use App\Entity\Command;
use Symfony\Component\HttpFoundation\Request;
use App\Form\CommandFormType; 

class ProductController extends AbstractController
{
    
    /**
     * @Route("/product", name="product")
     */
    public function product(ProductRepository $productRepository): Response
    {
        $product = $productRepository->findAll();
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController', 'list_products' => $product,
        ]);
    }


    /**
     * @Route("/product/{id}", name="product.show")
     */
    public function show($id){
        $productRepository = $this->getDoctrine()->getRepository(Product::class);
        $product = $productRepository->find($id);
        if(!$product){
            throw $this->createNotFoundException('Le produit n\'existe pas');
        }
        return $this->render('product/show.html.twig',[
        'controller_name'=> 'Product',
        'product' => $product]);
    }


    /**
     * @Route("/cart/add/{id}", name="product.add")
     */
    public function add($id, SessionInterface $session, ProductRepository $productRepository)
    {
        $product = $productRepository->find($id);
        if(!$product){
            throw $this->createNotFoundException('The product does not exist');
            $this->addFlash( 'warning', 'Une erreur est survenue !' );
            $message="NOK";
            $code = 404;
        }
        else{
            $cart = $session->get('panier', []);
            $cart[$id] = 1;
            $session->set('panier', $cart);
            $this->addFlash( 'success', 'Le produit séléctionné a été ajouté au panier' );
        }
        return $this->redirectToRoute("product");        
    }


    /**
     * @Route("/cart", name="cart")
     */
    public function cart(SessionInterface $session, ProductRepository $productRepository)
    {
        if($session){
            $cart = $session->get('panier',[]);
            $products=[];
            $total=0;
            if (count($cart)>0) {
                // $em = $doctrine->getManager();
                // $command = new Command();
                // $Formulaire = $this->createForm(CommandType::class, $command);
                // $Formulaire->handleRequest($request);
                // if ($Formulaire->isSubmitted()) {
                //     $command->setCreatedAt(new DateTime());
                //     foreach ($cart as $key=>$productCart)
                //         $command->addProduct($products[$key]);
                //     $em->persist($command);
                //     $em->flush();
                // }
                foreach ($cart as $id => $quantity) {
                    $product = $productRepository->find($id);
                    $products[$id]['name'] = $product->getName();
                    $products[$id]['quantity'] = 1;
                    $products[$id]['price'] = $product->getPrice();
                    $products[$id]['id'] = $product->getId();
                    $total += $product->getPrice();
                }
            }
             return $this->render('product/cart.html.twig',['products' => $products, 'total' => $total]);        
        }
    }


    /**
     * @Route("/cart/delete/{id}", name="delete")
     */
    public function delete($id, SessionInterface $session){
        $cart = $session->get('panier',[]);
        if($cart[$id]){
            unset($cart[$id]);
            $session->set('panier', $cart);
        }
        return $this->redirectToRoute("cart"); 
    }


    /**
     * @Route("/", name="index")
     */
    public function index(ProductRepository $productRepository){
        $moinscher = $productRepository->findBy([],['price' => 'ASC' ],5);
        $plusrecent = $productRepository->findBy([],['createdAt' => 'DESC'],5);
        return $this->render('product/accueil.html.twig',[ 'moinscher' => $moinscher, 'plusrecent' => $plusrecent]);        
    }
}