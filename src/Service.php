<?php


namespace Esc\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use RuntimeException;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

abstract class Service
{
    private $entityManager;
    private $entity;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, string $entity)
    {
        $this->entityManager = $entityManager;
        if (!class_exists($entity)) {
            throw new \RuntimeException('Invalid class given');
        }
        $this->repository = $entityManager->getRepository($entity);
        $this->entity = $entity;
    }

    /**
     * @param AttributeBag $data
     * @return void
     */
    private function create(AttributeBag $data): void
    {
        $obj = new $this->entity();
        $this->makeObject($obj, $data);
    }

    /**
     * @param EscObjectEntity $obj
     * @param AttributeBag $data
     * @return void
     */
    private function update(EscObjectEntity $obj, AttributeBag $data): void
    {
        $this->makeObject($obj,$data);
    }

    /**
     * @param int|null $id
     * @param AttributeBag $data
     */
    public function save(AttributeBag $data, ?int $id = null): void
    {
        if ($id !== null) {
            $obj = $this->repository->findOneBy((['id' => $id]));
            if ($obj === null) {
                throw new RuntimeException(sprintf('%s ID %s does not exist', static::class, $id));
            }
            $this->update($obj, $data);
        } else {
            $this->create($data);
        }
    }


    /**
     * @param EscObjectEntity $obj
     * @param AttributeBag $data
     * @return void
     */
    abstract public function makeObject(EscObjectEntity $obj, AttributeBag $data): void;

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $obj = $this->repository->findOneBy((['id' => $id]));
        if ($obj === null) {
            throw new RuntimeException(sprintf('%s ID %s does not exist', static::class, $id));
        }

        $this->entityManager->remove($obj);
        $this->entityManager->flush();
    }

    /**
     * @param EscObjectEntity $obj
     */
    public function write(EscObjectEntity $obj): void
    {
        $this->entityManager->persist($obj);
        $this->entityManager->flush();
    }

}