<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ChatMessage;
use App\Repository\ChatMessageRepository;

class AIAssistantController extends AbstractController
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/assist', name: 'app_ai_assist', methods: ['POST'])]
    public function assist(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $messages = $data['messages'] ?? [];
        if (!is_array($messages) || empty($messages)) {
            return new JsonResponse(['error' => 'No conversation messages provided.'], 400);
        }
        // Persist last user message
        $lastUser = '';
        foreach (array_reverse($messages) as $m) {
            if (($m['role'] ?? '') === 'user') { $lastUser = trim($m['content']); break; }
        }
        if ($lastUser) {
            $chat = new ChatMessage();
            $chat->setUser($this->getUser())
                 ->setRole('user')
                 ->setContent($lastUser)
                 ->setCreatedAt(new \DateTime());
            $em->persist($chat);
        }

        // Custom logic for product/user/admin questions
        $user = $this->getUser();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());
        $lastMsg = strtolower($lastUser);
        $customAnswer = null;
        
        // If user asks about products
        if (strpos($lastMsg, 'product') !== false || strpos($lastMsg, 'products') !== false) {
            $productRepo = $em->getRepository(\App\Entity\Product::class);
            $products = $productRepo->findAll();
            if ($products) {
                $customAnswer = "Here are all our products:\n";
                foreach ($products as $p) {
                    $customAnswer .= "- " . $p->getName() . " (" . $p->getPrice() . " TND): " . $p->getDescription() . "\n";
                }
            } else {
                $customAnswer = "We currently have no products available.";
            }
        }
        // If admin asks about users or user details
        elseif ($isAdmin && (strpos($lastMsg, 'user') !== false || strpos($lastMsg, 'users') !== false)) {
            $userRepo = $em->getRepository(\App\Entity\User::class);
            if (strpos($lastMsg, 'description') !== false || strpos($lastMsg, 'info') !== false || strpos($lastMsg, 'detail') !== false) {
                // Try to extract user email or id from the message
                preg_match('/user (\\d+|[\w.-]+@[\w.-]+)/', $lastMsg, $matches);
                if (isset($matches[1])) {
                    $target = $matches[1];
                    $targetUser = is_numeric($target) ? $userRepo->find($target) : $userRepo->findOneBy(['email' => $target]);
                    if ($targetUser) {
                        $customAnswer = "User info:\nID: {$targetUser->getId()}\nEmail: {$targetUser->getEmail()}\nRoles: " . implode(', ', $targetUser->getRoles());
                    } else {
                        $customAnswer = "No user found with that identifier.";
                    }
                } else {
                    $customAnswer = "Please specify the user ID or email.";
                }
            } else {
                $allUsers = $userRepo->findAll();
                $customAnswer = "All users:\n";
                foreach ($allUsers as $u) {
                    $customAnswer .= "- ID: {$u->getId()}, Email: {$u->getEmail()}\n";
                }
            }
        }
        // If admin asks about product description
        elseif ($isAdmin && strpos($lastMsg, 'description') !== false && strpos($lastMsg, 'product') !== false) {
            $productRepo = $em->getRepository(\App\Entity\Product::class);
            preg_match('/product (\\d+)/', $lastMsg, $matches);
            if (isset($matches[1])) {
                $prod = $productRepo->find($matches[1]);
                if ($prod) {
                    $customAnswer = "Product info:\nName: {$prod->getName()}\nDescription: {$prod->getDescription()}\nPrice: {$prod->getPrice()} TND";
                } else {
                    $customAnswer = "No product found with that ID.";
                }
            } else {
                $customAnswer = "Please specify the product ID.";
            }
        }
        // List available commands
        if (in_array($lastMsg, ['help', 'commands', 'list commands', 'what can you do', 'show commands', 'command list'])) {
            $customAnswer = "Here are some things you can ask me:\n"
                . "- Show all products\n"
                . "- Show product description for product [ID]\n"
                . "- List all users (admin only)\n"
                . "- Show user info for user [ID or email] (admin only)\n"
                . "- Show product info for product [ID] (admin only)\n"
                . "- General questions or chat\n";
        }
        if ($customAnswer) {
            // Save assistant reply
            $assistant = new ChatMessage();
            $assistant->setUser($user)
                      ->setRole('assistant')
                      ->setContent($customAnswer)
                      ->setCreatedAt(new \DateTime());
            $em->persist($assistant);
            $em->flush();
            $messages[] = ['role'=>'assistant','content'=>$customAnswer];
            return new JsonResponse([
                'message' => $customAnswer,
                'conversation' => $messages,
            ]);
        }

        // Prepare payload for OpenRouter
        $apiKey = $this->getParameter('OPENROUTER_API_KEY');
        $payload = [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
        ];
        try {
            $response = $this->client->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
                'headers' => ['Authorization' => 'Bearer ' . $apiKey],
                'json'    => $payload,
            ]);
            $respData = $response->toArray();
            $botContent = $respData['choices'][0]['message']['content'] ?? '';
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'AI error: ' . $e->getMessage()], 500);
        }
        // If the AI returned an empty message, include full response for debugging
        if ('' === trim($botContent)) {
            return new JsonResponse([
                'error' => 'Empty bot response',
                'debug' => $respData,
            ], 200);
        }
        // Append and persist bot response
        // Save assistant reply
        $assistant = new ChatMessage();
        $assistant->setUser($this->getUser())
                  ->setRole('assistant')
                  ->setContent($botContent)
                  ->setCreatedAt(new \DateTime());
        $em->persist($assistant);
        $em->flush();
        // Append for response
        $messages[] = ['role'=>'assistant','content'=>$botContent];
        return new JsonResponse([
            'message' => $botContent,
            'conversation' => $messages,
        ]);
    }

    #[Route('/assist', name: 'app_ai_assist_form', methods: ['GET'])]
    #[Route('/ai/assist', name: 'app_ai_assist_form_alias', methods: ['GET'])]
    public function form(ChatMessageRepository $chatRepo): Response
    {
        // Load full chat history (all messages) in chronological order
        $history = $chatRepo->findBy(['user' => $this->getUser()], ['createdAt' => 'ASC']);
        return $this->render('ai/assist.html.twig', ['history'=>$history]);
    }
}