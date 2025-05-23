<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findAdmins(): array
    {
        return array_filter($this->findAll(), fn($u) => in_array('ROLE_ADMIN', $u->getRoles()));
    }

    public function findGuests(int $limit, int $offset): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT u.id
            FROM "user" u
            WHERE u.roles::text LIKE :role
            AND u.is_blocked = false
            ORDER BY u.id ASC
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $conn->executeQuery($sql, [
            'role' => '%ROLE_USER%',
            'limit' => $limit,
            'offset' => $offset,
        ], [
            'role' => \PDO::PARAM_STR,
            'limit' => \PDO::PARAM_INT,
            'offset' => \PDO::PARAM_INT,
        ]);

        $userIds = array_column($stmt->fetchAllAssociative(), 'id');

        if (empty($userIds)) {
            return [];
        }

        return $this->createQueryBuilder('u')
            ->leftJoin('u.medias', 'm')
            ->addSelect('m')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $userIds)
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllGuests(): array
    {
        return array_filter($this->findAll(), fn($u) => !in_array('ROLE_ADMIN', $u->getRoles()));
    }

    public function countGuests(): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT COUNT(u.id) AS total
            FROM "user" u
            WHERE u.roles::text LIKE :role
            AND u.is_blocked = false
        ';

        $stmt = $conn->executeQuery($sql, ['role' => '%ROLE_USER%']);
        return (int) $stmt->fetchOne();
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
