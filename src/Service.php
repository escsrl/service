<?php


namespace Esc\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @return int
     */
    private function create(AttributeBag $data): int
    {
        $obj = new $this->entity();
        return $this->makeObject($obj, $data);
    }

    /**
     * @param $obj
     * @param AttributeBag $data
     * @return int
     */
    private function update($obj, AttributeBag $data): int
    {
        return $this->makeObject($obj, $data);
    }

    /**
     * @param $obj
     * @param UploadedFile $uploadedFile
     * @param string $field
     * @param string|null $prefix
     */
    private function updateAttachment($obj, UploadedFile $uploadedFile, string $field, ?string $prefix): void
    {
        $this->makeObjectAttachment($obj, $uploadedFile, $field, $prefix);
    }

    /**
     * @param AttributeBag $data
     * @param int|null $id
     * @return int
     */
    public function save(AttributeBag $data, ?int $id = null): int
    {
        if ($id !== null) {
            $obj = $this->repository->findOneBy((['id' => $id]));
            if ($obj === null) {
                throw new RuntimeException(sprintf('%s ID %s does not exist', static::class, $id));
            }
            return $this->update($obj, $data);
        } else {
            return $this->create($data);
        }
    }


    /**
     * @param int $id
     * @param UploadedFile $uploadedFile
     * @param string $field
     * @param string|null $prefix
     */
    public function saveAttachment(int $id, UploadedFile $uploadedFile, string $field, ?string $prefix): void
    {

        $obj = $this->repository->findOneBy((['id' => $id]));
        if ($obj === null) {
            throw new RuntimeException(sprintf('%s ID %s does not exist', static::class, $id));
        }
        $this->updateAttachment($obj, $uploadedFile, $field, $prefix);

    }

    /**
     * @param $obj
     * @param AttributeBag $data
     * @return int|null
     */
    abstract public function makeObject($obj, AttributeBag $data): ?int;

    /**
     * @param $obj
     * @param UploadedFile $uploadedFile
     * @param string $field
     * @param string|null $prefix
     */
    public function makeObjectAttachment($obj, UploadedFile $uploadedFile, string $field, ?string $prefix): void
    {

    }

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
     * @param $obj
     */
    public function write($obj): void
    {
        $this->entityManager->persist($obj);
        $this->entityManager->flush();
    }

}
