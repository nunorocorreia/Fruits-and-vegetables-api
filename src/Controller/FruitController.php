<?php

namespace App\Controller;

use App\DTO\AddFruitDTO;
use App\Service\FruitCollectionManager;
use App\Trait\ResponseTrait;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api/fruits')]
class FruitController extends AbstractController
{
    use ResponseTrait;

    public function __construct(
        private FruitCollectionManager $fruitCollectionManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'list_fruits', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $unit = $request->query->get('unit', 'g');
        $filters = $request->query->all('filter');
        $sorts = $request->query->all('sort');

        try {
            $items = $this->fruitCollectionManager->list($filters, $sorts, $unit);
            return $this->successResponse($items);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route('', name: 'add_fruit', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $this->serializer->deserialize(
                $request->getContent(),
                AddFruitDTO::class,
                'json'
            );

            $violations = $this->validator->validate($data);
            if (count($violations) > 0) {
                return $this->handleValidationErrors($violations);
            }

            $fruit = $this->fruitCollectionManager->addFromDTO($data);
            return $this->createdResponse($fruit);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->internalErrorResponse();
        }
    }

    #[Route('/{id}', name: 'delete_fruit', methods: ['DELETE'])]
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->fruitCollectionManager->remove($id);
            return $this->messageResponse('Fruit removed successfully');
        } catch (InvalidArgumentException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception) {
            return $this->internalErrorResponse();
        }
    }

    #[Route('/search', name: 'search_fruits', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $unit = $request->query->get('unit', 'g');

        try {
            $items = $this->fruitCollectionManager->search($query, $unit);
            return $this->successResponse($items);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (Exception) {
            return $this->internalErrorResponse();
        }
    }
}
